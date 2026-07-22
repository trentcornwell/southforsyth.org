<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * The theme's first WP-CLI command. Registered only when running under
 * WP-CLI (see the guard at the bottom of this file) — never loaded on a
 * normal web request. Drives Southforsyth_Forsyth_County_Provider through
 * the existing Southforsyth_Importer pipeline; adds no import logic of its
 * own beyond flag handling and reporting.
 *
 * `wp southforsyth import-schools` always imports as `draft` — that's not a
 * flag, it's Southforsyth_Importer::import()'s own default, and this
 * command never overrides it. There is no "publish" flag on purpose.
 */
class Southforsyth_Forsyth_County_Import_Command
{
    /**
     * Import Forsyth County Schools' public school directory as draft content.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Fetch and normalize every school but write nothing. Reports what
     * would happen.
     *
     * [--school=<name>]
     * : Import only the one school whose name contains this text
     * (case-insensitive), instead of the whole directory.
     *
     * [--update-only]
     * : Only update schools that already exist (matched via the standard
     * source+source_id dedupe). Never create a new post.
     *
     * [--south-forsyth-only]
     * : Process only records classified Confirmed South Forsyth. Countywide
     * source records are still fetched/audited, but outside/needs-review
     * schools are not created or updated by this import run.
     *
     * [--limit=<number>]
     * : Process at most this many schools.
     *
     * [--verbose]
     * : Print one line per school as it's processed, not just the summary.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth import-schools --dry-run --verbose
     *     wp southforsyth import-schools --limit=3 --verbose
     *     wp southforsyth import-schools
     *     wp southforsyth import-schools --school="South Forsyth High"
     *     wp southforsyth import-schools --update-only
     *     wp southforsyth import-schools --south-forsyth-only --dry-run --verbose
     *
     * @when after_wp_load
     */
    public function import_schools($args, $assoc_args)
    {
        $dry_run = ! empty($assoc_args['dry-run']);
        $update_only = ! empty($assoc_args['update-only']);
        $south_forsyth_only = ! empty($assoc_args['south-forsyth-only']);
        $verbose = ! empty($assoc_args['verbose']);
        $school_filter = $assoc_args['school'] ?? '';
        $limit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : 0;

        $provider = Southforsyth_Provider_Registry::get('forsyth_county');
        if (! $provider) {
            WP_CLI::error('Southforsyth_Forsyth_County_Provider is not registered.');
            return;
        }

        WP_CLI::log('Fetching the Forsyth County Schools directory (one request, cached)...');
        $stubs = $provider->search();

        if (empty($stubs)) {
            WP_CLI::error('No schools found — the directory page may be unreachable or its structure may have changed. Check inc/providers/class-forsyth-county-provider.php against the live site.');
            return;
        }

        if ($school_filter) {
            $stubs = array_values(array_filter($stubs, function ($stub) use ($school_filter) {
                return false !== stripos($stub['name'], $school_filter);
            }));
            if (empty($stubs)) {
                WP_CLI::error("No school matched --school=\"{$school_filter}\".");
                return;
            }
        }

        $total_found = count($stubs);

        if ($limit > 0) {
            $stubs = array_slice($stubs, 0, $limit);
        }

        WP_CLI::log(sprintf(
            'Found %d schools in the directory%s. Processing %d%s.',
            $total_found,
            $school_filter ? " matching \"{$school_filter}\"" : '',
            count($stubs),
            $dry_run ? ' (dry run — nothing will be written)' : ''
        ));

        $stats = array(
            'imported'             => 0,
            'updated'              => 0,
            'duplicates_prevented' => 0,
            'skipped'              => 0,
            'ambiguous'            => 0,
            'published_protected'  => 0,
            'source_failures'      => 0,
            'needs_classification' => 0,
            'outside_coverage'     => 0,
            'coverage_skipped'     => 0,
        );
        $fields_unavailable = array();

        $progress = WP_CLI\Utils\make_progress_bar('Importing schools', count($stubs));

        foreach ($stubs as $stub) {
            $raw = $provider->fetch($stub['page_url']);

            if (empty($raw) || ! empty($raw['fetch_failed'])) {
                $stats['source_failures']++;
                if ($verbose) {
                    WP_CLI::warning("Source fetch failed: {$stub['name']} ({$stub['page_url']})");
                }
                $progress->tick();
                continue;
            }

            $record = $provider->normalize($raw);
            if (empty($record)) {
                $stats['skipped']++;
                if ($verbose) {
                    WP_CLI::warning("Normalize produced an empty record: {$stub['name']}");
                }
                $progress->tick();
                continue;
            }

            $analysis = Southforsyth_School_Import_Safety::analyze_record($record, $update_only);
            $record = $analysis['record'];
            $existing_status = $analysis['post_id'] ? get_post_meta($analysis['post_id'], 'sf_south_forsyth_status', true) : '';
            $coverage = array();

            if ($analysis['post_id'] && $existing_status) {
                // This post already has a coverage decision -- from the
                // keyword classifier, a human, or (since
                // wp southforsyth update-school-boundaries) official FCS
                // boundary data. Classification is that command's job and
                // classify-schools's job, not this directory scraper's:
                // preserve it untouched rather than re-deriving with
                // classify_coverage(), which only knows the conservative
                // keyword rule and would silently downgrade a
                // boundary-verified "Confirmed South Forsyth" back to
                // "Needs Review" every time this importer re-runs.
                foreach (array(
                    'sf_south_forsyth_status',
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY,
                ) as $coverage_meta_key) {
                    $record['meta'][$coverage_meta_key] = get_post_meta($analysis['post_id'], $coverage_meta_key, true);
                }
            } else {
                // New record, or an existing post with no classification
                // yet -- give it a first-pass automatic classification.
                $coverage = Southforsyth_School_Import_Safety::classify_coverage($record, $existing_status);
                $record['meta']['sf_south_forsyth_status'] = $coverage['status'];
                $record['meta'][Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY] = $coverage['decision_source'];
                $record['meta'][Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY] = $coverage['decision_note'];
                $record['meta'][Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY] = current_time('Y-m-d');
                $record['meta'][Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY] = $coverage['decision_type'];
            }

            foreach (southforsyth_get_school_completeness_fields() as $field) {
                if (empty($record['meta'][$field])) {
                    $fields_unavailable[$field] = ($fields_unavailable[$field] ?? 0) + 1;
                }
            }

            if ('Needs Review' === ($record['meta']['sf_south_forsyth_status'] ?? '')) {
                $stats['needs_classification']++;
            }
            if (Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE === ($record['meta']['sf_south_forsyth_status'] ?? '')) {
                $stats['outside_coverage']++;
            }

            if ($south_forsyth_only && Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED !== ($record['meta']['sf_south_forsyth_status'] ?? '')) {
                $stats['coverage_skipped']++;
                if ($verbose) {
                    $skip_reason = ! empty($coverage['reasons']) ? implode(' ', $coverage['reasons']) : ($record['meta'][Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY] ?? '');
                    WP_CLI::log("Skipped outside South Forsyth import scope: {$record['title']} — {$record['meta']['sf_south_forsyth_status']} ({$skip_reason})");
                }
                $progress->tick();
                continue;
            }

            if ('skip' === $analysis['action']) {
                $stats['skipped']++;
                if (false !== stripos($analysis['reason'], 'Ambiguous duplicate')) {
                    $stats['ambiguous']++;
                }
                if (false !== stripos($analysis['reason'], 'Published school match protected')) {
                    $stats['published_protected']++;
                }
                if ($verbose) {
                    WP_CLI::warning("Skipped: {$stub['name']} — {$analysis['reason']}");
                }
                $progress->tick();
                continue;
            }

            if ($dry_run) {
                if ('update' === $analysis['action']) {
                    $stats['duplicates_prevented']++;
                    $stats['updated']++;
                } else {
                    $stats['imported']++;
                }
                if ($verbose) {
                    $prefix = 'update' === $analysis['action']
                        ? "Would update draft (#{$analysis['post_id']}): "
                        : 'Would create draft: ';
                    WP_CLI::log($prefix . $stub['name'] . ' — ' . $analysis['reason']);
                }
                $progress->tick();
                continue;
            }

            $result = Southforsyth_Importer::import($record);

            if (is_wp_error($result)) {
                $stats['skipped']++;
                if ($verbose) {
                    WP_CLI::warning("Import failed for {$stub['name']}: " . $result->get_error_message());
                }
                $progress->tick();
                continue;
            }

            if ('update' === $analysis['action']) {
                $stats['duplicates_prevented']++;
                $stats['updated']++;
                if ($verbose) {
                    WP_CLI::log("Updated (#{$result}): {$stub['name']}");
                }
            } else {
                $stats['imported']++;
                if ($verbose) {
                    WP_CLI::log("Created draft (#{$result}): {$stub['name']}");
                }
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::log('');
        WP_CLI::log('===== Import report =====');
        WP_CLI::log("Total schools found in directory: {$total_found}");
        WP_CLI::log('Total imported (new draft posts): ' . $stats['imported']);
        WP_CLI::log('Total updated (existing posts): ' . $stats['updated']);
        WP_CLI::log('Duplicates prevented (matched an existing post instead of creating a new one): ' . $stats['duplicates_prevented']);
        WP_CLI::log('Records skipped (validation/update-only/import failure): ' . $stats['skipped']);
        WP_CLI::log('Ambiguous duplicate risks skipped: ' . $stats['ambiguous']);
        WP_CLI::log('Published schools protected from overwrite: ' . $stats['published_protected']);
        WP_CLI::log('Source failures (page fetch failed): ' . $stats['source_failures']);
        WP_CLI::log('Schools needing manual South Forsyth classification: ' . $stats['needs_classification']);
        WP_CLI::log('Schools classified Outside Coverage: ' . $stats['outside_coverage']);
        WP_CLI::log('Skipped by --south-forsyth-only: ' . $stats['coverage_skipped']);
        WP_CLI::log('Fields unavailable (count of schools missing each field):');
        foreach (southforsyth_get_school_completeness_fields() as $field) {
            WP_CLI::log("  {$field}: " . ($fields_unavailable[$field] ?? 0));
        }

        if ($dry_run) {
            WP_CLI::success('Dry run complete — nothing was written.');
        } else {
            WP_CLI::success('Import complete. Every post above is a draft — nothing was published.');
        }
    }

    /**
     * Audit provider records against existing WordPress school posts without writing anything.
     *
     * ## OPTIONS
     *
     * [--verbose]
     * : Include matched title rows in addition to problem rows.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth audit-schools
     *     wp southforsyth audit-schools --verbose
     *
     * @when after_wp_load
     */
    public function audit_schools($args, $assoc_args)
    {
        $verbose = ! empty($assoc_args['verbose']);
        $provider = $this->get_provider();
        $source_records = $this->source_records($provider);
        $posts = $this->school_posts();
        $post_index = $this->index_posts_by_identity($posts);
        $source_ids = array();
        $source_urls = array();

        foreach ($source_records as $record) {
            $source_ids[$record['source_id']] = true;
            $source_urls[$record['source_url']] = true;
        }

        WP_CLI::log('===== School source audit =====');
        WP_CLI::log('Expected total source records: ' . count($source_records));
        WP_CLI::log('Actual WordPress school posts: ' . count($posts));
        WP_CLI::log('');

        $missing_posts = array();
        $incorrect_titles = array();
        $matched_post_ids = array();

        foreach ($source_records as $record) {
            $post = $this->find_post_for_source_record($record, $post_index);

            if (! $post) {
                $missing_posts[] = $record;
                continue;
            }

            $matched_post_ids[$post->ID] = true;
            if ($post->post_title !== $record['expected_title']) {
                $incorrect_titles[] = array($record, $post);
            } elseif ($verbose) {
                WP_CLI::log(sprintf('OK title: #%d %s', $post->ID, $post->post_title));
            }
        }

        $orphan_posts = array();
        foreach ($posts as $post) {
            $source_id = Southforsyth_School_Import_Safety::normalize_url(get_post_meta($post->ID, '_sf_import_source_id', true));
            $source_url = Southforsyth_School_Import_Safety::normalize_url(get_post_meta($post->ID, 'sf_source_url', true));
            if (! isset($matched_post_ids[$post->ID]) && ! isset($source_ids[$source_id]) && ! isset($source_urls[$source_url])) {
                $orphan_posts[] = $post;
            }
        }

        $this->log_source_record_list('Source records with no WordPress post', $missing_posts);
        $this->log_post_list('WordPress posts with no current source record', $orphan_posts);
        $this->log_duplicate_identity_report('Duplicate source IDs', $post_index['source_ids']);
        $this->log_duplicate_identity_report('Duplicate source URLs', $post_index['source_urls']);
        $this->log_ambiguous_short_titles($source_records);
        $this->log_incorrect_title_report($incorrect_titles);

        WP_CLI::log('');
        WP_CLI::success('Audit complete — no writes performed.');
    }

    /**
     * Correct existing draft school titles/slugs to full official names. Published posts are protected.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report title/slug changes without writing anything.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth correct-school-titles --dry-run
     *     wp southforsyth correct-school-titles
     *
     * @when after_wp_load
     */
    public function correct_school_titles($args, $assoc_args)
    {
        $dry_run = ! empty($assoc_args['dry-run']);
        $provider = $this->get_provider();
        $source_records = $this->source_records($provider);
        $posts = $this->school_posts();
        $post_index = $this->index_posts_by_identity($posts);
        $changed = 0;
        $missing = 0;
        $protected = 0;
        $unchanged = 0;

        WP_CLI::log('Correcting draft school titles to official full names' . ($dry_run ? ' (dry run — nothing will be written)' : '') . '.');

        foreach ($source_records as $record) {
            $post = $this->find_post_for_source_record($record, $post_index);
            if (! $post) {
                $missing++;
                WP_CLI::warning(sprintf('No existing post for source record: %s (%s)', $record['expected_title'], $record['source_url']));
                continue;
            }

            if ('publish' === $post->post_status) {
                $protected++;
                WP_CLI::warning(sprintf('Protected published post #%d: %s', $post->ID, $post->post_title));
                continue;
            }

            if ('draft' !== $post->post_status) {
                $protected++;
                WP_CLI::warning(sprintf('Skipped non-draft post #%d (%s): %s', $post->ID, $post->post_status, $post->post_title));
                continue;
            }

            $expected_slug = $this->unique_school_slug($record['expected_title'], $post->ID);
            $title_changed = $post->post_title !== $record['expected_title'];
            $slug_changed = $post->post_name !== $expected_slug;

            if (! $title_changed && ! $slug_changed) {
                $unchanged++;
                continue;
            }

            $changed++;
            WP_CLI::log(sprintf(
                '%s #%d title "%s" -> "%s"; slug "%s" -> "%s"',
                $dry_run ? 'Would update' : 'Updating',
                $post->ID,
                $post->post_title,
                $record['expected_title'],
                $post->post_name,
                $expected_slug
            ));

            if ($dry_run) {
                continue;
            }

            $result = wp_update_post(array(
                'ID' => $post->ID,
                'post_title' => $record['expected_title'],
                'post_name' => $expected_slug,
            ), true);

            if (is_wp_error($result)) {
                WP_CLI::warning(sprintf('Failed to update #%d: %s', $post->ID, $result->get_error_message()));
            }
        }

        WP_CLI::log('');
        WP_CLI::log('Changed drafts: ' . $changed);
        WP_CLI::log('Already correct drafts: ' . $unchanged);
        WP_CLI::log('Protected/skipped published or non-draft posts: ' . $protected);
        WP_CLI::log('Source records without an existing post: ' . $missing);

        if ($dry_run) {
            WP_CLI::success('Dry run complete — nothing was written.');
        } else {
            WP_CLI::success('Title correction complete. Published posts were not renamed.');
        }
    }

    /**
     * Read-only report of existing school posts grouped by South Forsyth coverage status.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth school-coverage-report
     *
     * @when after_wp_load
     */
    public function school_coverage_report($args, $assoc_args)
    {
        $posts = $this->school_posts();
        $groups = array(
            Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED => array(),
            Southforsyth_School_Import_Safety::COVERAGE_NEEDS_REVIEW => array(),
            Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE => array(),
        );
        $current_classifications = array();
        $proposed_classifications = array();

        foreach ($posts as $post) {
            $current_status = Southforsyth_School_Import_Safety::normalize_coverage_status(get_post_meta($post->ID, 'sf_south_forsyth_status', true));
            $record = $this->post_to_classification_record($post);
            $classification = Southforsyth_School_Import_Safety::classify_coverage($record, $current_status);
            $groups[$classification['status']][] = array(
                'post' => $post,
                'current_status' => $current_status,
                'classification' => $classification,
            );
            $current_classifications[] = array('status' => $current_status);
            $proposed_classifications[] = $classification;
        }

        WP_CLI::log('===== School coverage report =====');
        $current_totals = Southforsyth_School_Import_Safety::summarize_coverage_classifications($current_classifications);
        $proposed_totals = Southforsyth_School_Import_Safety::summarize_coverage_classifications($proposed_classifications);
        WP_CLI::log('Current stored totals: ' . $this->coverage_totals_line($current_totals));
        WP_CLI::log('Proposed classifier totals: ' . $this->coverage_totals_line($proposed_totals));

        foreach ($groups as $status => $records) {
            WP_CLI::log('');
            WP_CLI::log('Proposed ' . $status . ': ' . count($records));
            foreach ($records as $item) {
                $post = $item['post'];
                $classification = $item['classification'];
                $readiness = Southforsyth_School_Import_Safety::readiness($post->ID);
                $eligible = 'draft' === $post->post_status
                    && Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED === $classification['status']
                    && $readiness['ready'];
                WP_CLI::log(sprintf(
                    '  #%d [%s] %s',
                    $post->ID,
                    $post->post_status,
                    $post->post_title
                ));
                WP_CLI::log('    Address: ' . trim(sprintf(
                    '%s, %s %s',
                    get_post_meta($post->ID, 'sf_address', true) ?: '(no address)',
                    get_post_meta($post->ID, 'sf_city', true) ?: '(no city)',
                    get_post_meta($post->ID, 'sf_zip', true) ?: '(no ZIP)'
                )));
                WP_CLI::log('    Type: ' . $this->get_school_type_label($post->ID));
                WP_CLI::log('    Current status: ' . $item['current_status']);
                WP_CLI::log('    Proposed status: ' . $classification['status']);
                WP_CLI::log('    Classification reason: ' . implode(' ', $classification['reasons']));
                WP_CLI::log('    Decision type: ' . ($classification['decision_type'] ?? '(none)'));
                WP_CLI::log('    Evidence/source: ' . ($classification['decision_source'] ?? '(none)'));
                WP_CLI::log('    Decision note: ' . ($classification['decision_note'] ?? '(none)'));
                WP_CLI::log('    Official source URL: ' . (get_post_meta($post->ID, 'sf_source_url', true) ?: '(none)'));
                WP_CLI::log('    Eligible for publishing: ' . ($eligible ? 'yes' : 'no'));
            }
        }

        WP_CLI::success('Coverage report complete — no writes performed.');
    }

    private function coverage_totals_line(array $totals)
    {
        return sprintf(
            '%s: %d; %s: %d; %s: %d',
            Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED,
            $totals[Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED],
            Southforsyth_School_Import_Safety::COVERAGE_NEEDS_REVIEW,
            $totals[Southforsyth_School_Import_Safety::COVERAGE_NEEDS_REVIEW],
            Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE,
            $totals[Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE]
        );
    }

    /**
     * Focused duplicate-only report -- the same identity-matching audit_schools()
     * runs, without the full source-audit noise, for a quick idempotency check.
     * Checks both structural duplicates (two posts sharing a source ID/URL,
     * which the importer should never allow) and title collisions between
     * genuinely different schools that share a bare community name (e.g. an
     * elementary and a middle school both called "Vickery Creek") so an
     * editor can tell the two apart before assuming either is a real dupe.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth detect-school-duplicates
     *
     * @when after_wp_load
     */
    public function detect_school_duplicates($args, $assoc_args)
    {
        $provider = $this->get_provider();
        $source_records = $this->source_records($provider);
        $posts = $this->school_posts();
        $post_index = $this->index_posts_by_identity($posts);

        WP_CLI::log('===== School duplicate detection =====');
        $this->log_duplicate_identity_report('Duplicate source IDs (same official FCS record matched to more than one post)', $post_index['source_ids']);
        $this->log_duplicate_identity_report('Duplicate source URLs', $post_index['source_urls']);
        $this->log_ambiguous_short_titles($source_records);

        $title_counts = array();
        foreach ($posts as $post) {
            $title_counts[$post->post_title][] = $post;
        }
        $title_duplicates = array_filter($title_counts, function ($group) {
            return count($group) > 1;
        });
        WP_CLI::log('');
        WP_CLI::log('Posts sharing an identical title: ' . count($title_duplicates));
        foreach ($title_duplicates as $title => $group) {
            $ids = array_map(function ($post) {
                return '#' . $post->ID . ' [' . $post->post_status . ']';
            }, $group);
            WP_CLI::warning('  "' . $title . '" => ' . implode(', ', $ids));
        }

        $total_issues = count(array_filter($post_index['source_ids'], function ($g) {
            return count($g) > 1;
        })) + count(array_filter($post_index['source_urls'], function ($g) {
            return count($g) > 1;
        })) + count($title_duplicates);

        WP_CLI::log('');
        if ($total_issues > 0) {
            WP_CLI::warning("Found {$total_issues} unresolved duplicate group(s) requiring manual review.");
        } else {
            WP_CLI::success('No unresolved duplicate posts found. (Note: schools sharing a bare community name across levels, e.g. an ES/MS pair, are reported above as informational only -- they are not duplicates.)');
        }
    }

    /**
     * Apply the shared South Forsyth coverage classifier to existing school posts.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report classification changes without writing anything.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth classify-schools --dry-run
     *     wp southforsyth classify-schools
     *
     * @when after_wp_load
     */
    public function classify_schools($args, $assoc_args)
    {
        $dry_run = ! empty($assoc_args['dry-run']);
        $posts = $this->school_posts();
        $changed = 0;
        $unchanged = 0;

        WP_CLI::log('Classifying existing school coverage' . ($dry_run ? ' (dry run — nothing will be written)' : '') . '.');

        foreach ($posts as $post) {
            $stored_status = get_post_meta($post->ID, 'sf_south_forsyth_status', true);
            $record = $this->post_to_classification_record($post);
            $classification = Southforsyth_School_Import_Safety::classify_coverage($record, $stored_status);
            $target_status = $classification['status'];
            $current_source = get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY, true);
            $current_note = get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY, true);
            $current_type = get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, true);
            $current_date = get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY, true);

            if ($stored_status === $target_status
                && $current_source === ($classification['decision_source'] ?? '')
                && $current_note === ($classification['decision_note'] ?? '')
                && $current_type === ($classification['decision_type'] ?? '')
                && '' !== (string) $current_date) {
                $unchanged++;
                continue;
            }

            $changed++;
            WP_CLI::log(sprintf(
                '%s #%d %s: "%s" -> "%s" (%s)',
                $dry_run ? 'Would update' : 'Updating',
                $post->ID,
                $post->post_title,
                $stored_status ?: '(empty)',
                $target_status,
                implode(' ', $classification['reasons'])
            ));

            if (! $dry_run) {
                update_post_meta($post->ID, 'sf_south_forsyth_status', $target_status);
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY, $classification['decision_source'] ?? '');
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY, $classification['decision_note'] ?? '');
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY, current_time('Y-m-d'));
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, $classification['decision_type'] ?? 'automatic');
            }
        }

        WP_CLI::log('');
        WP_CLI::log('Classification changes: ' . $changed);
        WP_CLI::log('Already classified: ' . $unchanged);

        if ($dry_run) {
            WP_CLI::success('Dry run complete — nothing was written.');
        } else {
            WP_CLI::success('Classification update complete.');
        }
    }

    private function get_provider()
    {
        $provider = Southforsyth_Provider_Registry::get('forsyth_county');
        if (! $provider) {
            WP_CLI::error('Southforsyth_Forsyth_County_Provider is not registered.');
        }

        return $provider;
    }

    private function source_records($provider)
    {
        WP_CLI::log('Fetching the Forsyth County Schools directory (cached provider source)...');
        $stubs = $provider->search();

        if (empty($stubs)) {
            WP_CLI::error('No schools found — the directory page may be unreachable or its structure may have changed.');
        }

        $records = array();
        foreach ($stubs as $stub) {
            $source_url = Southforsyth_School_Import_Safety::normalize_url($stub['page_url'] ?? '');
            $expected_title = Southforsyth_School_Import_Safety::official_display_name($stub['name'] ?? '', $stub['level_label'] ?? '');
            $records[] = array(
                'raw_name' => Southforsyth_School_Import_Safety::normalize_whitespace($stub['name'] ?? ''),
                'level_label' => Southforsyth_School_Import_Safety::normalize_whitespace($stub['level_label'] ?? ''),
                'source_id' => $source_url,
                'source_url' => $source_url,
                'expected_title' => $expected_title,
            );
        }

        return $records;
    }

    private function school_posts()
    {
        return get_posts(array(
            'post_type' => 'school',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'ASC',
        ));
    }

    private function index_posts_by_identity(array $posts)
    {
        $index = array('source_ids' => array(), 'source_urls' => array());

        foreach ($posts as $post) {
            $source_id = Southforsyth_School_Import_Safety::normalize_url(get_post_meta($post->ID, '_sf_import_source_id', true));
            $source_url = Southforsyth_School_Import_Safety::normalize_url(get_post_meta($post->ID, 'sf_source_url', true));

            if ($source_id) {
                $index['source_ids'][$source_id][] = $post;
            }
            if ($source_url) {
                $index['source_urls'][$source_url][] = $post;
            }
        }

        return $index;
    }

    private function find_post_for_source_record(array $record, array $post_index)
    {
        foreach (array('source_id' => 'source_ids', 'source_url' => 'source_urls') as $record_key => $index_key) {
            $value = $record[$record_key] ?? '';
            if ($value && ! empty($post_index[$index_key][$value])) {
                return reset($post_index[$index_key][$value]);
            }
        }

        return null;
    }

    private function unique_school_slug($title, $post_id)
    {
        $slug = sanitize_title($title);
        if (function_exists('wp_unique_post_slug')) {
            return wp_unique_post_slug($slug, $post_id, 'draft', 'school', 0);
        }

        return $slug;
    }

    private function post_to_classification_record($post)
    {
        return array(
            'title' => $post->post_title,
            'meta' => array(
                'sf_address' => get_post_meta($post->ID, 'sf_address', true),
                'sf_city' => get_post_meta($post->ID, 'sf_city', true),
                'sf_zip' => get_post_meta($post->ID, 'sf_zip', true),
                Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY => get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY, true),
                Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY => get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY, true),
                Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY => get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY, true),
                Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY => get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, true),
            ),
        );
    }

    private function get_school_type_label($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
        return (! empty($terms) && ! is_wp_error($terms)) ? implode(', ', $terms) : '(none)';
    }

    private function log_source_record_list($label, array $records)
    {
        WP_CLI::log('');
        WP_CLI::log($label . ': ' . count($records));
        foreach ($records as $record) {
            WP_CLI::warning(sprintf('  %s | %s | %s', $record['expected_title'], $record['level_label'], $record['source_url']));
        }
    }

    private function log_post_list($label, array $posts)
    {
        WP_CLI::log('');
        WP_CLI::log($label . ': ' . count($posts));
        foreach ($posts as $post) {
            WP_CLI::warning(sprintf('  #%d [%s] %s', $post->ID, $post->post_status, $post->post_title));
        }
    }

    private function log_duplicate_identity_report($label, array $identity_map)
    {
        $duplicates = array_filter($identity_map, function ($posts) {
            return count($posts) > 1;
        });

        WP_CLI::log('');
        WP_CLI::log($label . ': ' . count($duplicates));
        foreach ($duplicates as $value => $posts) {
            $ids = array_map(function ($post) {
                return '#' . $post->ID . ' ' . $post->post_title;
            }, $posts);
            WP_CLI::warning(sprintf('  %s => %s', $value, implode('; ', $ids)));
        }
    }

    private function log_ambiguous_short_titles(array $source_records)
    {
        $groups = array();
        foreach ($source_records as $record) {
            $key = Southforsyth_School_Import_Safety::normalize_school_name($record['raw_name']);
            if ($key) {
                $groups[$key][] = $record;
            }
        }

        $ambiguous = array_filter($groups, function ($records) {
            return count(array_unique(wp_list_pluck($records, 'expected_title'))) > 1;
        });

        WP_CLI::log('');
        WP_CLI::log('Ambiguous shortened titles: ' . count($ambiguous));
        foreach ($ambiguous as $records) {
            $titles = array_map(function ($record) {
                return $record['expected_title'] . ' (' . $record['source_url'] . ')';
            }, $records);
            WP_CLI::warning('  ' . implode('; ', $titles));
        }
    }

    private function log_incorrect_title_report(array $rows)
    {
        WP_CLI::log('');
        WP_CLI::log('Incorrect current titles: ' . count($rows));
        foreach ($rows as $row) {
            list($record, $post) = $row;
            WP_CLI::warning(sprintf('  #%d "%s" should be "%s" (%s)', $post->ID, $post->post_title, $record['expected_title'], $record['source_url']));
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    $southforsyth_fcs_command = new Southforsyth_Forsyth_County_Import_Command();
    WP_CLI::add_command('southforsyth import-schools', array($southforsyth_fcs_command, 'import_schools'));
    WP_CLI::add_command('southforsyth audit-schools', array($southforsyth_fcs_command, 'audit_schools'));
    WP_CLI::add_command('southforsyth correct-school-titles', array($southforsyth_fcs_command, 'correct_school_titles'));
    WP_CLI::add_command('southforsyth school-coverage-report', array($southforsyth_fcs_command, 'school_coverage_report'));
    WP_CLI::add_command('southforsyth classify-schools', array($southforsyth_fcs_command, 'classify_schools'));
    WP_CLI::add_command('southforsyth detect-school-duplicates', array($southforsyth_fcs_command, 'detect_school_duplicates'));
}
