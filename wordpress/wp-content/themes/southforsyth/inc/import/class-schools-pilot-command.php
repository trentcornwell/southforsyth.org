<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * `wp southforsyth schools-pilot` — the pre-publication report and the
 * only path that actually publishes a school. Deliberately two separate
 * steps, not one: `--report` is read-only and can be run as many times as
 * needed; `--publish` requires an explicit, already-reviewed ID list — it
 * never infers "the top N" or "everything that looks ready." That's a
 * direct implementation of "do not automatically assume which schools
 * qualify" — this command surfaces facts and flags, a human decides.
 */
class Southforsyth_Schools_Pilot_Command
{
    /**
     * Pre-publication report and controlled publishing for Confirmed
     * South Forsyth schools.
     *
     * ## OPTIONS
     *
     * [--report]
     * : Print the pre-publication report. Read-only. This is the default
     * action if no other flag is given.
     *
     * [--confirmed-only]
     * : Limit the report to schools marked Confirmed South Forsyth.
     *
     * [--publish=<ids>]
     * : Publish exactly this comma-separated list of post IDs. Every ID
     * must already be Confirmed South Forsyth — nothing else is
     * published, regardless of how many pass. Records sf_published_by
     * and sf_published_date.
     *
     * [--reviewer=<name>]
     * : Name recorded as sf_published_by when publishing. Defaults to the
     * current WP-CLI user's display name (or "WP-CLI" if run without
     * --user).
     *
     * ## EXAMPLES
     *
     *     wp southforsyth schools-pilot --report
     *     wp southforsyth schools-pilot --publish=47,52 --reviewer="Trent Cornwell"
     *
     * @when after_wp_load
     */
    public function schools_pilot($args, $assoc_args)
    {
        if (! empty($assoc_args['publish'])) {
            $this->publish(explode(',', $assoc_args['publish']), $assoc_args['reviewer'] ?? '');
            return;
        }

        $this->report(! empty($assoc_args['confirmed-only']));
    }

    private function get_review_schools($confirmed_only = false)
    {
        $args = array(
            'post_type'      => 'school',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if ($confirmed_only) {
            $args['meta_key'] = 'sf_south_forsyth_status';
            $args['meta_value'] = 'Confirmed South Forsyth';
        }

        return get_posts($args);
    }

    private function report($confirmed_only = false)
    {
        $schools = $this->get_review_schools($confirmed_only);

        if (empty($schools)) {
            WP_CLI::warning('No school posts found. Nothing to report.');
            return;
        }

        WP_CLI::log(sprintf(
            '===== School editorial review report: %d school(s)%s =====',
            count($schools),
            $confirmed_only ? ' marked Confirmed South Forsyth' : ''
        ));
        WP_CLI::log('');

        $ready_count = 0;
        $flagged_count = 0;

        foreach ($schools as $school) {
            $flags = $this->evaluate_candidate($school);
            $is_ready = empty($flags);
            $ready_count += $is_ready ? 1 : 0;
            $flagged_count += $is_ready ? 0 : 1;
            $readiness = Southforsyth_School_Import_Safety::readiness($school->ID);

            WP_CLI::log("----- #{$school->ID}: {$school->post_title} " . ($is_ready ? '(READY)' : '(FLAGGED)') . ' -----');
            WP_CLI::log('  Status: ' . $school->post_status);
            WP_CLI::log('  Level: ' . $this->get_level_label($school->ID));
            WP_CLI::log('  Grades: ' . ($this->meta($school->ID, 'sf_grades_served') ?: '(none)'));
            WP_CLI::log('  Address: ' . ($this->meta($school->ID, 'sf_address') ?: '(none)'));
            WP_CLI::log('  Phone: ' . ($this->meta($school->ID, 'sf_phone') ?: '(none)'));
            WP_CLI::log('  Website: ' . ($this->meta($school->ID, 'sf_website') ?: '(none)'));
            WP_CLI::log('  Coordinates: ' . $this->coordinate_status($school->ID));
            WP_CLI::log('  Completeness: ' . southforsyth_get_school_completeness($school->ID) . '%');
            WP_CLI::log('  Missing fields: ' . $this->missing_fields_summary($school->ID));
            WP_CLI::log('  Source URL: ' . ($this->meta($school->ID, 'sf_source_url') ?: '(none)'));
            WP_CLI::log('  Last verified: ' . ($this->meta($school->ID, 'sf_last_verified') ?: '(none)'));
            WP_CLI::log('  Classification: ' . $this->meta($school->ID, 'sf_south_forsyth_status'));
            WP_CLI::log('  Duplicate check: ' . $this->duplicate_status($school->ID));
            WP_CLI::log('  Readiness: ' . $readiness['label'] . (empty($readiness['missing']) ? '' : ' — missing ' . implode(', ', $readiness['missing'])));
            WP_CLI::log('  Preview URL: ' . get_preview_post_link($school->ID));

            if (! empty($flags)) {
                WP_CLI::log('  FLAGS:');
                foreach ($flags as $flag) {
                    WP_CLI::log('    - ' . $flag);
                }
            }

            WP_CLI::log('');
        }

        WP_CLI::log("===== {$ready_count} ready, {$flagged_count} flagged, " . count($schools) . ' reviewed. =====');
        WP_CLI::log('Publish exactly the IDs you have manually reviewed: wp southforsyth schools-pilot --publish=<id,id,...>');
    }

    /** @return string[] Human-readable flags — empty array means the candidate is clean. */
    private function evaluate_candidate($school)
    {
        $flags = array();
        $id = $school->ID;

        $readiness = Southforsyth_School_Import_Safety::readiness($id);
        foreach ($readiness['missing'] as $missing) {
            $flags[] = 'Readiness check failed: ' . $missing . '.';
        }

        $phone = $this->meta($id, 'sf_phone');
        if (! $phone) {
            $flags[] = 'Missing phone.';
        } elseif (! preg_match('/^[\d\s()+.\-]{7,20}$/', $phone)) {
            $flags[] = "Phone number doesn't look valid: \"{$phone}\".";
        }

        $website = $this->meta($id, 'sf_website');
        if ($website && ! filter_var($website, FILTER_VALIDATE_URL)) {
            $flags[] = "Website doesn't look like a valid URL: \"{$website}\".";
        }

        $duplicate_status = $this->duplicate_status($id);
        if (false !== strpos($duplicate_status, 'Possible duplicate')) {
            $flags[] = 'Unresolved possible duplicate — see duplicate check above.';
        }

        $lat = $this->meta($id, 'sf_lat');
        $lng = $this->meta($id, 'sf_lng');
        if ($lat && $lng && class_exists('Southforsyth_Geocode_Match_Evaluator')
            && ! Southforsyth_Geocode_Match_Evaluator::within_forsyth_county((float) $lat, (float) $lng)) {
            $flags[] = "Coordinates ({$lat}, {$lng}) fall outside Forsyth County — conflicting location.";
        }

        $last_verified = $this->meta($id, 'sf_last_verified');
        if ($last_verified && strtotime($last_verified) < strtotime('-365 days', current_time('timestamp'))) {
            $flags[] = 'Last verified date is stale: ' . $last_verified . '.';
        }

        $terms = wp_get_post_terms($id, 'sf_school_type', array('fields' => 'names'));
        $sectors = array('Public', 'Private', 'Charter', 'Homeschool Resource');
        $assigned_sectors = array_values(array_intersect($sectors, is_array($terms) ? $terms : array()));
        if (empty($terms) || is_wp_error($terms)) {
            $flags[] = 'No level/sector taxonomy assigned.';
        } elseif (count($assigned_sectors) > 1) {
            $flags[] = 'Suspicious taxonomy: more than one sector assigned (' . implode(', ', $assigned_sectors) . ').';
        }

        $pending_suggestions = get_posts(array(
            'post_type'      => 'sf_suggestion',
            'post_status'    => 'pending',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => 'sf_target_post_id',
            'meta_value'     => $id,
        ));
        if (! empty($pending_suggestions)) {
            $flags[] = count($pending_suggestions) . ' unreviewed community suggestion(s) pending against this page.';
        }

        return $flags;
    }

    private function meta($post_id, $key)
    {
        return get_post_meta($post_id, $key, true);
    }

    private function get_level_label($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
        return (! empty($terms) && ! is_wp_error($terms)) ? implode(', ', $terms) : '(none assigned)';
    }

    private function coordinate_status($post_id)
    {
        $lat = $this->meta($post_id, 'sf_lat');
        $lng = $this->meta($post_id, 'sf_lng');
        if ($lat && $lng) {
            $status = Southforsyth_School_Import_Safety::geocode_status($post_id);
            return "{$status['label']} — {$lat}, {$lng}";
        }
        $candidate_lat = $this->meta($post_id, 'sf_geocode_candidate_lat');
        if ($candidate_lat) {
            return 'Pending review (candidate coordinates not yet accepted)';
        }
        return 'Not geocoded';
    }

    private function missing_fields_summary($post_id)
    {
        $missing = array();
        foreach (southforsyth_get_school_completeness_fields() as $field) {
            if ('' === (string) $this->meta($post_id, $field)) {
                $missing[] = $field;
            }
        }
        return empty($missing) ? '(none)' : implode(', ', $missing);
    }

    private function duplicate_status($post_id)
    {
        $title = get_the_title($post_id);
        if (strlen($title) < 4) {
            return 'Not checked (title too short)';
        }

        $candidates = get_posts(array(
            'post_type'      => 'school',
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'post__not_in'   => array($post_id),
            's'              => $title,
            'fields'         => 'ids',
        ));

        return empty($candidates) ? 'No likely duplicate found' : 'Possible duplicate of #' . $candidates[0];
    }

    private function publish(array $ids, $reviewer)
    {
        $ids = array_map('intval', array_filter(array_map('trim', $ids)));
        if (empty($ids)) {
            WP_CLI::error('No valid IDs given to --publish.');
            return;
        }

        if (! $reviewer) {
            $current_user = wp_get_current_user();
            $reviewer = ($current_user && $current_user->display_name) ? $current_user->display_name : 'WP-CLI';
        }

        $published = array();
        $skipped = array();

        foreach ($ids as $id) {
            $post = get_post($id);
            if (! $post || 'school' !== $post->post_type) {
                $skipped[] = "#{$id}: not a school post.";
                continue;
            }

            if ('Confirmed South Forsyth' !== get_post_meta($id, 'sf_south_forsyth_status', true)) {
                $skipped[] = "#{$id} ({$post->post_title}): not classified Confirmed South Forsyth — refusing to publish.";
                continue;
            }

            $duplicate_status = $this->duplicate_status($id);
            if (false !== strpos($duplicate_status, 'Possible duplicate')) {
                $skipped[] = "#{$id} ({$post->post_title}): unresolved duplicate risk — {$duplicate_status}.";
                continue;
            }

            $readiness = Southforsyth_School_Import_Safety::readiness($id);
            if (! $readiness['ready']) {
                $skipped[] = "#{$id} ({$post->post_title}): not publish-ready — missing " . implode(', ', $readiness['missing']) . '.';
                continue;
            }

            wp_update_post(array('ID' => $id, 'post_status' => 'publish'));
            update_post_meta($id, 'sf_published_by', $reviewer);
            update_post_meta($id, 'sf_published_date', current_time('Y-m-d'));
            $published[] = "#{$id}: {$post->post_title}";
        }

        WP_CLI::log('===== Publish results =====');
        foreach ($published as $line) {
            WP_CLI::log('Published ' . $line);
        }
        foreach ($skipped as $line) {
            WP_CLI::warning('Skipped ' . $line);
        }

        WP_CLI::success(sprintf('%d published, %d skipped. Reviewer: %s', count($published), count($skipped), $reviewer));
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('southforsyth schools-pilot', array(new Southforsyth_Schools_Pilot_Command(), 'schools_pilot'));
}
