<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Source-attributed school profile enrichment.
 *
 * This is an editorial updater for existing published profiles, not an
 * importer: it cannot create, publish, rename, reclassify, or delete posts.
 * Facts arrive in a reviewed JSON manifest and every field carries its own
 * official source URL and checked date.
 */
class Southforsyth_School_Enrichment_Command
{
    const SOURCE_NOTES_META_KEY = 'sf_enrichment_source_notes';

    public static function audit_fields()
    {
        return array(
            'sf_grades_served' => 'grades served',
            'sf_principal_name' => 'principal',
            'sf_boundary_url' => 'boundary URL',
            'sf_feeder_pattern' => 'feeder pattern',
            'sf_notable_programs' => 'notable programs',
            'sf_mascot' => 'mascot',
            'sf_school_colors' => 'school colors',
            'sf_lat' => 'latitude',
            'sf_lng' => 'longitude',
            'sf_hours' => 'school hours',
            'sf_enrollment_information_url' => 'enrollment information URL',
            'sf_parent_resources_url' => 'parent resources URL',
            'sf_transportation_information_url' => 'transportation information URL',
            'sf_editorial_summary' => 'editorial summary',
            'sf_website' => 'official website',
            'sf_source_url' => 'official source URL',
            'sf_last_verified' => 'last verified date',
            self::SOURCE_NOTES_META_KEY => 'enrichment source notes',
            'sf_enrichment_last_checked' => 'enrichment last checked date',
        );
    }

    public static function audit_value_is_present($value)
    {
        if (function_exists('maybe_unserialize')) {
            $value = maybe_unserialize($value);
        }
        if (is_string($value) && in_array(substr(ltrim($value), 0, 1), array('{', '['), true)) {
            $decoded = json_decode($value, true);
            if (JSON_ERROR_NONE === json_last_error()) {
                $value = $decoded;
            }
        }
        if (is_array($value)) {
            foreach ($value as $item) {
                if (self::audit_value_is_present($item)) {
                    return true;
                }
            }
            return false;
        }
        if (is_object($value)) {
            return self::audit_value_is_present(get_object_vars($value));
        }

        return null !== $value && '' !== trim((string) $value);
    }

    public static function allowed_fields()
    {
        return array(
            'sf_grades_served', 'sf_principal_name', 'sf_boundary_url',
            'sf_feeder_pattern', 'sf_notable_programs', 'sf_mascot',
            'sf_school_colors', 'sf_mission', 'sf_lat', 'sf_lng',
            'sf_website', 'sf_source_url', 'sf_last_verified', 'sf_hours',
            'sf_staff_directory_url', 'sf_extracurricular_activities',
            'sf_athletics', 'sf_enrollment_information_url',
            'sf_parent_resources_url', 'sf_transportation_information_url',
            'sf_editorial_summary',
        );
    }

    public static function url_fields()
    {
        return array(
            'sf_boundary_url', 'sf_website', 'sf_source_url',
            'sf_staff_directory_url', 'sf_enrollment_information_url',
            'sf_parent_resources_url', 'sf_transportation_information_url',
        );
    }

    public static function is_official_source_url($url)
    {
        $parts = wp_parse_url((string) $url);
        if (! is_array($parts) || 'https' !== strtolower($parts['scheme'] ?? '') || empty($parts['host'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        return 'forsyth.k12.ga.us' === $host || 'www.forsyth.k12.ga.us' === $host
            || '.forsyth.k12.ga.us' === substr($host, -strlen('.forsyth.k12.ga.us'));
    }

    /**
     * ## OPTIONS
     *
     * [--file=<path>]
     * : Reviewed JSON enrichment manifest. Omit for a read-only gap report.
     *
     * [--dry-run]
     * : Validate and report changes without writing metadata.
     *
     * [--verbose]
     * : Show populated and missing enrichment fields for every profile.
     *
     * @when after_wp_load
     */
    public function enrich_schools($args, $assoc_args)
    {
        $file = $assoc_args['file'] ?? '';
        $dry_run = ! empty($assoc_args['dry-run']);
        $verbose = ! empty($assoc_args['verbose']);

        if (! $file) {
            $this->report($verbose);
            return;
        }

        if (! is_readable($file)) {
            WP_CLI::error('Enrichment manifest is not readable: ' . $file);
        }

        $manifest = json_decode(file_get_contents($file), true);
        if (! is_array($manifest) || ! isset($manifest['schools']) || ! is_array($manifest['schools'])) {
            WP_CLI::error('Manifest must be JSON with a top-level "schools" array.');
        }

        $stats = array('considered' => 0, 'updated' => 0, 'unchanged' => 0, 'blocked' => 0);
        foreach ($manifest['schools'] as $entry) {
            $stats['considered']++;
            $result = $this->evaluate_entry($entry);
            $label = $entry['official_name'] ?? ($entry['source_id'] ?? '(unidentified school)');

            if (! empty($result['errors'])) {
                $stats['blocked']++;
                WP_CLI::warning($label . ' — blocked: ' . implode('; ', $result['errors']));
                continue;
            }

            if (! $result['has_changes']) {
                $stats['unchanged']++;
                WP_CLI::log($label . ' — no changes.');
                continue;
            }

            $change_labels = array_keys($result['changes']);
            if ($result['provenance_changed']) {
                $change_labels[] = 'field-level provenance';
            }
            WP_CLI::log(($dry_run ? 'Would enrich ' : 'Enriching ') . $label . ': ' . implode(', ', array_unique($change_labels)));
            if ($dry_run) {
                $stats['updated']++;
                continue;
            }

            foreach ($result['changes'] as $field => $value) {
                update_post_meta($result['post_id'], $field, $value);
            }
            update_post_meta($result['post_id'], self::SOURCE_NOTES_META_KEY, wp_json_encode($result['source_notes'], JSON_UNESCAPED_SLASHES));
            update_post_meta($result['post_id'], 'sf_enrichment_status', $result['status']);
            update_post_meta($result['post_id'], 'sf_enrichment_last_checked', $result['checked_at']);
            $stats['updated']++;
        }

        WP_CLI::log(sprintf(
            'Summary: %d considered; %d %s; %d unchanged; %d blocked.',
            $stats['considered'],
            $stats['updated'],
            $dry_run ? 'would update' : 'updated',
            $stats['unchanged'],
            $stats['blocked']
        ));
        WP_CLI::success($dry_run ? 'Dry run complete — no writes performed.' : 'Enrichment metadata updated. No posts or coverage classifications were changed.');
    }

    /**
     * Audit enrichment completeness for published Confirmed South Forsyth
     * school profiles. This command is strictly read-only: it performs no
     * HTTP requests and makes no database, cache, option, transient, log,
     * post, taxonomy, or metadata writes.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth audit-school-profiles
     *
     * @when after_wp_load
     */
    public function audit_school_profiles($args, $assoc_args)
    {
        $fields = self::audit_fields();
        $posts = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => 'sf_south_forsyth_status',
            'meta_value' => Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED,
        ));
        $missing_counts = array_fill_keys(array_keys($fields), 0);
        $percentage_total = 0;
        $complete_profiles = 0;
        $missing_identity_source = 0;

        WP_CLI::log('===== Published Confirmed South Forsyth school profile audit =====');
        foreach ($posts as $post) {
            $missing = array();
            foreach ($fields as $meta_key => $label) {
                if (! self::audit_value_is_present(get_post_meta($post->ID, $meta_key, true))) {
                    $missing[] = $label;
                    $missing_counts[$meta_key]++;
                }
            }

            $completed = count($fields) - count($missing);
            $percentage = count($fields) ? round(($completed / count($fields)) * 100, 1) : 100;
            $percentage_total += $percentage;
            if (empty($missing)) {
                $complete_profiles++;
            }
            if (! self::audit_value_is_present($post->post_title)
                || ! self::audit_value_is_present(get_post_meta($post->ID, 'sf_website', true))
                || ! self::audit_value_is_present(get_post_meta($post->ID, 'sf_source_url', true))
                || ! self::audit_value_is_present(get_post_meta($post->ID, 'sf_last_verified', true))) {
                $missing_identity_source++;
            }

            WP_CLI::log(sprintf('#%d %s', $post->ID, $post->post_title));
            WP_CLI::log(sprintf(
                '  Publication status: %s; Coverage status: %s',
                $post->post_status,
                get_post_meta($post->ID, 'sf_south_forsyth_status', true)
            ));
            WP_CLI::log(sprintf(
                '  Completed: %d; Missing: %d; Completion: %.1f%%',
                $completed,
                count($missing),
                $percentage
            ));
            WP_CLI::log('  Exact missing fields: ' . ($missing ? implode(', ', $missing) : '(none)'));
        }

        $profile_count = count($posts);
        $average = $profile_count ? round($percentage_total / $profile_count, 1) : 0;
        WP_CLI::log('');
        WP_CLI::log('===== Audit totals =====');
        WP_CLI::log('Profiles audited: ' . $profile_count);
        WP_CLI::log(sprintf('Average completion percentage: %.1f%%', $average));
        WP_CLI::log('Profiles at 100%: ' . $complete_profiles);
        WP_CLI::log('Profiles missing required identity/source fields: ' . $missing_identity_source);
        WP_CLI::log('Missing by tracked field:');
        foreach ($fields as $meta_key => $label) {
            WP_CLI::log(sprintf('  %s: %d', $label, $missing_counts[$meta_key]));
        }
        WP_CLI::success('School profile audit complete — no writes or external requests performed.');
    }

    private function evaluate_entry($entry)
    {
        $errors = array();
        $post = $this->find_target($entry);
        if (! $post) {
            return array('errors' => array('no unique published Confirmed South Forsyth profile matched'));
        }

        $fields = $entry['fields'] ?? array();
        if (! is_array($fields) || empty($fields)) {
            return array('errors' => array('no sourced fields supplied'));
        }

        $checked_at = sanitize_text_field($entry['checked_at'] ?? current_time('Y-m-d'));
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $checked_at)) {
            $errors[] = 'checked_at must use YYYY-MM-DD';
        }
        $status = sanitize_key($entry['status'] ?? 'verified');
        if (! in_array($status, array('not_started', 'in_review', 'verified', 'needs_review'), true)) {
            $errors[] = 'invalid enrichment status';
        }

        $changes = array();
        $original_source_notes = json_decode((string) get_post_meta($post->ID, self::SOURCE_NOTES_META_KEY, true), true);
        $original_source_notes = is_array($original_source_notes) ? $original_source_notes : array();
        $source_notes = $original_source_notes;
        $source_notes = is_array($source_notes) ? $source_notes : array();

        foreach ($fields as $field => $fact) {
            if (! in_array($field, self::allowed_fields(), true) || ! is_array($fact)) {
                $errors[] = 'unsupported field: ' . sanitize_key($field);
                continue;
            }

            $value = in_array($field, array('sf_editorial_summary', 'sf_extracurricular_activities', 'sf_athletics'), true)
                ? sanitize_textarea_field($fact['value'] ?? '')
                : sanitize_text_field($fact['value'] ?? '');
            $source_url = esc_url_raw($fact['source_url'] ?? '');
            if ('' === $value || ! self::is_official_source_url($source_url)) {
                $errors[] = $field . ' requires a value and an official Forsyth County Schools HTTPS source URL';
                continue;
            }
            if (in_array($field, self::url_fields(), true) && ! self::is_official_source_url($value)) {
                $errors[] = $field . ' value must itself be an official Forsyth County Schools URL';
                continue;
            }

            $existing = (string) get_post_meta($post->ID, $field, true);
            if ('' !== $existing && $existing !== $value) {
                $errors[] = $field . ' already contains different verified content; manual review required';
                continue;
            }
            if ($existing !== $value) {
                $changes[$field] = $value;
            }
            $source_notes[$field] = array(
                'source_url' => $source_url,
                'source_note' => sanitize_text_field($fact['source_note'] ?? 'Official source reviewed.'),
                'checked_at' => $checked_at,
            );
        }

        return array(
            'errors' => $errors,
            'post_id' => $post->ID,
            'changes' => $changes,
            'source_notes' => $source_notes,
            'provenance_changed' => $source_notes !== $original_source_notes,
            'has_changes' => ! empty($changes)
                || $source_notes !== $original_source_notes
                || $status !== (string) get_post_meta($post->ID, 'sf_enrichment_status', true)
                || $checked_at !== (string) get_post_meta($post->ID, 'sf_enrichment_last_checked', true),
            'status' => $status,
            'checked_at' => $checked_at,
        );
    }

    private function find_target($entry)
    {
        $posts = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_key' => 'sf_south_forsyth_status',
            'meta_value' => Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED,
        ));
        $name = Southforsyth_School_Import_Safety::normalize_official_school_identity($entry['official_name'] ?? '');
        $source_id = Southforsyth_School_Import_Safety::normalize_url($entry['source_id'] ?? '');
        $matches = array();

        foreach ($posts as $post) {
            $post_sources = array_filter(array_map(array('Southforsyth_School_Import_Safety', 'normalize_url'), array(
                get_post_meta($post->ID, '_sf_import_source_id', true),
                get_post_meta($post->ID, 'sf_source_url', true),
            )));
            $source_match = $source_id && in_array($source_id, $post_sources, true);
            $name_match = $name && $name === Southforsyth_School_Import_Safety::normalize_official_school_identity($post->post_title);
            if ($source_match || $name_match) {
                $matches[] = $post;
            }
        }

        return 1 === count($matches) ? $matches[0] : null;
    }

    private function report($verbose)
    {
        $posts = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_key' => 'sf_south_forsyth_status',
            'meta_value' => Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED,
        ));

        WP_CLI::log('===== Published school enrichment report =====');
        foreach ($posts as $post) {
            $missing = array();
            foreach (self::allowed_fields() as $field) {
                if ('' === (string) get_post_meta($post->ID, $field, true)) {
                    $missing[] = $field;
                }
            }
            WP_CLI::log(sprintf(
                '#%d %s — %s; %d enrichment field(s) missing%s',
                $post->ID,
                $post->post_title,
                get_post_meta($post->ID, 'sf_enrichment_status', true) ?: 'not_started',
                count($missing),
                $verbose && $missing ? ': ' . implode(', ', $missing) : ''
            ));
        }
        WP_CLI::success(count($posts) . ' published confirmed school profile(s) reviewed — no writes performed.');
    }
}

if (defined('WP_CLI') && WP_CLI) {
    $southforsyth_school_enrichment_command = new Southforsyth_School_Enrichment_Command();
    WP_CLI::add_command('southforsyth audit-school-profiles', array($southforsyth_school_enrichment_command, 'audit_school_profiles'));
    WP_CLI::add_command('southforsyth enrich-schools', array($southforsyth_school_enrichment_command, 'enrich_schools'));
}
