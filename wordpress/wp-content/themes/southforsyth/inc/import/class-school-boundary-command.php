<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * WP-CLI commands built on Southforsyth_School_Boundary_Service (official
 * FCS attendance-zone data). Separate from Southforsyth_Forsyth_County_Import_Command
 * for the same reason geocoding got its own command class: a distinct
 * concern (where a school's zone actually sits) from the directory
 * scraper (what the district publishes about the school itself).
 */
class Southforsyth_School_Boundary_Command
{
    /**
     * The high schools this project has confirmed as South Forsyth
     * anchors. A school whose own official attendance zone feeds one of
     * these is confirmed the same way; a school that clearly feeds a
     * different high school is classified Outside Coverage. Matches
     * Southforsyth_School_Import_Safety::coverage_allowlist()'s three
     * confirmed high schools -- kept as a small local list (not a call
     * into that class) because this command reasons about the *boundary
     * data's* high school name text, not post titles.
     */
    const CONFIRMED_HS_ZONE_NAMES = array('SOUTH FORSYTH HS', 'DENMARK HS', 'LAMBERT HS');

    /**
     * Verify every school's coverage classification against FCS's own
     * official attendance-boundary data, and populate sf_feeder_pattern
     * with the real, sourced feeder chain. A school whose own site
     * address is confirmed to feed South Forsyth High, Denmark High, or
     * Lambert High is classified Confirmed South Forsyth; one confirmed
     * to feed a different high school is classified Outside Coverage.
     * Existing manual classifications are never overridden (same
     * preservation rule as classify-schools). Schools not found in FCS's
     * address database are left unchanged and reported separately.
     *
     * ## OPTIONS
     *
     * [--dry-run]
     * : Report what would change without writing anything.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth update-school-boundaries --dry-run
     *     wp southforsyth update-school-boundaries
     *
     * @when after_wp_load
     */
    public function update_school_boundaries($args, $assoc_args)
    {
        $dry_run = ! empty($assoc_args['dry-run']);

        $posts = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        $stats = array('confirmed' => 0, 'outside' => 0, 'unchanged' => 0, 'not_found' => 0, 'preserved_manual' => 0);

        WP_CLI::log('Verifying school coverage against official FCS attendance-boundary data' . ($dry_run ? ' (dry run)' : '') . '.');
        WP_CLI::log('Source: ' . Southforsyth_School_Boundary_Service::DECISION_SOURCE);
        WP_CLI::log('');

        foreach ($posts as $post) {
            $address = get_post_meta($post->ID, 'sf_address', true);
            $zip = get_post_meta($post->ID, 'sf_zip', true);

            if (! $address) {
                WP_CLI::log("#{$post->ID} {$post->post_title}: no address on file, skipped.");
                $stats['not_found']++;
                continue;
            }

            list($house_number, $street) = Southforsyth_School_Boundary_Service::split_address($address);
            $match = Southforsyth_School_Boundary_Service::lookup_by_address($house_number, $street, $zip);

            if (! $match) {
                WP_CLI::warning("#{$post->ID} {$post->post_title}: not found in FCS's official address database ({$address}).");
                $stats['not_found']++;
                continue;
            }

            $feeder_text = self::format_feeder_pattern($post, $match);
            $existing_feeder = get_post_meta($post->ID, 'sf_feeder_pattern', true);
            if ($feeder_text !== $existing_feeder) {
                WP_CLI::log("#{$post->ID} {$post->post_title}: feeder pattern -> \"{$feeder_text}\"");
                if (! $dry_run) {
                    update_post_meta($post->ID, 'sf_feeder_pattern', $feeder_text);
                }
            }

            $existing_decision_type = get_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, true);
            if ('manual' === $existing_decision_type) {
                $stats['preserved_manual']++;
                continue;
            }

            $target_status = in_array($match['hs'], self::CONFIRMED_HS_ZONE_NAMES, true)
                ? Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED
                : Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE;

            $current_status = Southforsyth_School_Import_Safety::normalize_coverage_status(get_post_meta($post->ID, 'sf_south_forsyth_status', true));

            if ($current_status === $target_status) {
                $stats['unchanged']++;
                continue;
            }

            $note = sprintf(
                'Official attendance zone (%s) feeds %s, %s the confirmed South Forsyth anchor high schools (%s).',
                $match['matched_address'],
                $match['hs'],
                (Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED === $target_status) ? 'one of' : 'not one of',
                implode(', ', self::CONFIRMED_HS_ZONE_NAMES)
            );

            WP_CLI::log(sprintf(
                '%s #%d %s: "%s" -> "%s" (%s)',
                $dry_run ? 'Would update' : 'Updating',
                $post->ID,
                $post->post_title,
                $current_status ?: '(empty)',
                $target_status,
                $note
            ));

            if (Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED === $target_status) {
                $stats['confirmed']++;
            } else {
                $stats['outside']++;
            }

            if (! $dry_run) {
                update_post_meta($post->ID, 'sf_south_forsyth_status', $target_status);
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY, Southforsyth_School_Boundary_Service::DECISION_SOURCE);
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY, $note);
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY, current_time('Y-m-d'));
                update_post_meta($post->ID, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, 'automatic');
            }
        }

        WP_CLI::log('');
        WP_CLI::log('===== Boundary verification report =====');
        WP_CLI::log('Newly confirmed South Forsyth: ' . $stats['confirmed']);
        WP_CLI::log('Newly classified Outside Coverage: ' . $stats['outside']);
        WP_CLI::log('Already correctly classified: ' . $stats['unchanged']);
        WP_CLI::log('Preserved existing manual classification: ' . $stats['preserved_manual']);
        WP_CLI::log('Not found in official address database: ' . $stats['not_found']);

        if ($dry_run) {
            WP_CLI::success('Dry run complete - nothing was written.');
        } else {
            WP_CLI::success('Boundary verification complete.');
        }
    }

    /**
     * Test the public address-finder lookup from the command line against
     * the same official data source the public form uses, without going
     * through HTTP/REST at all.
     *
     * ## OPTIONS
     *
     * <address>
     * : A street address, e.g. "585 Peachtree Parkway".
     *
     * [--zip=<zip>]
     * : Optional ZIP code to disambiguate a street name that exists in more than one part of the county.
     *
     * [--dry-run]
     * : No-op flag for consistency with other commands -- this command never writes anything.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth find-schools-for-address "585 Peachtree Parkway"
     *     wp southforsyth find-schools-for-address "585 Peachtree Parkway" --zip=30041
     *
     * @when after_wp_load
     */
    public function find_schools_for_address($args, $assoc_args)
    {
        $address = $args[0] ?? '';
        $zip = $assoc_args['zip'] ?? '';

        if (! $address) {
            WP_CLI::error('Provide a street address, e.g. wp southforsyth find-schools-for-address "585 Peachtree Parkway"');
            return;
        }

        list($house_number, $street) = Southforsyth_School_Boundary_Service::split_address($address);
        WP_CLI::log("Looking up: house number {$house_number}, street \"{$street}\"" . ($zip ? ", ZIP {$zip}" : ''));
        WP_CLI::log('Source: ' . Southforsyth_School_Boundary_Service::DECISION_SOURCE);
        WP_CLI::log('');

        $match = Southforsyth_School_Boundary_Service::lookup_by_address($house_number, $street, $zip);

        if (! $match) {
            WP_CLI::warning('No confident match found in the official address database.');
            return;
        }

        WP_CLI::log('Matched address: ' . $match['matched_address']);
        WP_CLI::log('Elementary: ' . $match['es']);
        WP_CLI::log('Middle: ' . $match['ms']);
        WP_CLI::log('High: ' . $match['hs']);
        WP_CLI::success('Lookup complete.');
    }

    private static function format_feeder_pattern($post, array $match)
    {
        $terms = wp_get_post_terms($post->ID, 'sf_school_type', array('fields' => 'names'));
        $terms = (! empty($terms) && ! is_wp_error($terms)) ? $terms : array();

        // For an elementary/middle school, describe what it feeds into.
        // For a high school (or any school not itself ES/MS), describe its own feeder chain.
        if (in_array('Elementary', $terms, true)) {
            return sprintf('Feeds %s, then %s.', Southforsyth_School_Boundary_Service::zone_name_to_display_name($match['ms']), Southforsyth_School_Boundary_Service::zone_name_to_display_name($match['hs']));
        }
        if (in_array('Middle', $terms, true)) {
            return sprintf('Feeds %s.', Southforsyth_School_Boundary_Service::zone_name_to_display_name($match['hs']));
        }

        return sprintf('Fed by %s and %s.', Southforsyth_School_Boundary_Service::zone_name_to_display_name($match['es']), Southforsyth_School_Boundary_Service::zone_name_to_display_name($match['ms']));
    }
}

if (defined('WP_CLI') && WP_CLI) {
    $southforsyth_boundary_command = new Southforsyth_School_Boundary_Command();
    WP_CLI::add_command('southforsyth update-school-boundaries', array($southforsyth_boundary_command, 'update_school_boundaries'));
    WP_CLI::add_command('southforsyth find-schools-for-address', array($southforsyth_boundary_command, 'find_schools_for_address'));
}
