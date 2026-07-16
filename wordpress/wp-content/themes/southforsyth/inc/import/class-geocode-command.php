<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * `wp southforsyth geocode-schools` — a separate pass from the Forsyth
 * County scraper on purpose (per the plan behind this file): the scraper's
 * job is "what does the district publish about this school," geocoding is
 * a distinct concern ("where is this address"), using a different provider
 * (Southforsyth_Openstreetmap_Provider, unchanged) with its own rate-limit
 * (~1 req/sec, Nominatim's usage policy).
 *
 * Acceptance is now driven entirely by Southforsyth_Geocode_Match_Evaluator
 * (inc/import/class-geocode-match-evaluator.php) — a deterministic,
 * field-by-field comparison, not Nominatim's `importance` score (which
 * measures a place's global prominence, not address-match precision, and
 * was rejecting exact matches for ordinary suburban schools). Only
 * `exact`/`strong` matches write trusted `sf_lat`/`sf_lng`. `review`
 * matches write a separate, not-yet-trusted candidate pair for a human to
 * accept or reject in the School Editorial Review screen. `rejected`
 * writes nothing but the classification + explanation, for the audit
 * trail.
 */
class Southforsyth_Geocode_Command
{
    const RATE_LIMIT_SECONDS = 1;

    /**
     * Geocode school addresses via OpenStreetMap/Nominatim, classified by
     * a deterministic field-comparison match evaluator.
     *
     * ## OPTIONS
     *
     * [--school=<name>]
     * : Geocode only the one school whose title contains this text.
     *
     * [--dry-run]
     * : Look up and classify matches but write nothing.
     *
     * [--limit=<number>]
     * : Process at most this many schools.
     *
     * [--update-existing]
     * : Without this flag, any school that already has trusted sf_lat/
     * sf_lng is skipped entirely. With it, those schools are re-evaluated
     * too (still only overwritten by a new exact/strong match).
     *
     * [--verbose]
     * : Print one line per school, including the match explanation.
     *
     * ## EXAMPLES
     *
     *     wp southforsyth geocode-schools --dry-run --verbose
     *     wp southforsyth geocode-schools --limit=3 --verbose
     *     wp southforsyth geocode-schools
     *     wp southforsyth geocode-schools --school="South Forsyth" --update-existing
     *
     * @when after_wp_load
     */
    public function geocode_schools($args, $assoc_args)
    {
        $dry_run = ! empty($assoc_args['dry-run']);
        $update_existing = ! empty($assoc_args['update-existing']);
        $verbose = ! empty($assoc_args['verbose']);
        $school_filter = $assoc_args['school'] ?? '';
        $limit = isset($assoc_args['limit']) ? (int) $assoc_args['limit'] : 0;

        $provider = Southforsyth_Provider_Registry::get('openstreetmap');
        if (! $provider) {
            WP_CLI::error('Southforsyth_Openstreetmap_Provider is not registered.');
            return;
        }

        $query_args = array(
            'post_type'      => 'school',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );

        if ($school_filter) {
            $query_args['s'] = $school_filter;
        }

        if (! $update_existing) {
            $query_args['meta_query'] = array(
                'relation' => 'OR',
                array('key' => 'sf_lat', 'compare' => 'NOT EXISTS'),
                array('key' => 'sf_lat', 'value' => '', 'compare' => '='),
            );
        }

        $post_ids = get_posts($query_args);

        if ($school_filter && empty($post_ids)) {
            WP_CLI::error("No school matched --school=\"{$school_filter}\".");
            return;
        }

        $total_candidates = count($post_ids);

        if ($limit > 0) {
            $post_ids = array_slice($post_ids, 0, $limit);
        }

        WP_CLI::log(sprintf(
            'Found %d school(s) %s. Processing %d%s.',
            $total_candidates,
            $update_existing ? 'total' : 'missing coordinates',
            count($post_ids),
            $dry_run ? ' (dry run — nothing will be written)' : ''
        ));

        $stats = array('exact' => 0, 'strong' => 0, 'review' => 0, 'rejected' => 0, 'no_result' => 0);
        $progress = WP_CLI\Utils\make_progress_bar('Geocoding', count($post_ids));

        foreach ($post_ids as $post_id) {
            $title = get_the_title($post_id);
            $school = array(
                'title'   => $title,
                'address' => get_post_meta($post_id, 'sf_address', true),
                'city'    => get_post_meta($post_id, 'sf_city', true),
                'state'   => get_post_meta($post_id, 'sf_state', true),
                'zip'     => get_post_meta($post_id, 'sf_zip', true),
            );

            $query = trim(implode(', ', array_filter(array($school['address'], $school['city'], $school['state'], $school['zip']))));

            if ('' === $query) {
                $stats['no_result']++;
                if ($verbose) {
                    WP_CLI::warning("No address to geocode: {$title}");
                }
                $progress->tick();
                continue;
            }

            if (! $dry_run) {
                sleep(self::RATE_LIMIT_SECONDS);
            }

            $raw_result = $provider->fetch($query);

            if (empty($raw_result)) {
                $stats['no_result']++;
                if ($verbose) {
                    WP_CLI::warning("No geocoding result: {$title} ({$query})");
                }
                $progress->tick();
                continue;
            }

            $match = Southforsyth_Geocode_Match_Evaluator::evaluate($school, $raw_result);
            $stats[$match['class']]++;

            if ($verbose) {
                WP_CLI::log(sprintf('[%s] %s: %s', strtoupper($match['class']), $title, $match['explanation']));
            }

            if (! $dry_run) {
                self::apply_match($post_id, $raw_result, $match);
            }

            $progress->tick();
        }

        $progress->finish();

        WP_CLI::log('');
        WP_CLI::log('===== Geocoding report =====');
        WP_CLI::log('Exact matches: ' . $stats['exact']);
        WP_CLI::log('Strong matches: ' . $stats['strong']);
        WP_CLI::log('Review matches (not auto-applied — needs editor decision): ' . $stats['review']);
        WP_CLI::log('Rejected: ' . $stats['rejected']);
        WP_CLI::log('No result / no address: ' . $stats['no_result']);

        if ($dry_run) {
            WP_CLI::success('Dry run complete — nothing was written.');
        } else {
            WP_CLI::success('Geocoding complete.');
        }
    }

    /**
     * exact/strong write trusted coordinates directly. review writes only
     * the candidate pair — sf_lat/sf_lng are never touched until a human
     * accepts it (see the review-screen bulk action). rejected writes no
     * coordinates at all. The classification + explanation are recorded
     * for every outcome, including rejected, so there's an audit trail of
     * "we tried, here's why it didn't apply" rather than silence.
     *
     * Public + static: also called directly by
     * Southforsyth_School_List_Columns's "Rerun geocoding for selected"
     * bulk action, so a result is applied identically regardless of
     * whether it came from this CLI command or the admin UI — one
     * implementation, not two copies to keep in sync.
     */
    public static function apply_match($post_id, array $raw_result, array $match)
    {
        $existing_lat = get_post_meta($post_id, 'sf_lat', true);
        $existing_lng = get_post_meta($post_id, 'sf_lng', true);

        update_post_meta($post_id, 'sf_geocode_provider', 'openstreetmap');
        update_post_meta($post_id, 'sf_geocode_place_id', $raw_result['place_id'] ?? '');
        update_post_meta($post_id, 'sf_geocode_date', current_time('Y-m-d'));
        update_post_meta($post_id, 'sf_geocode_confidence', $match['class']);
        update_post_meta($post_id, 'sf_geocode_match_explanation', $match['explanation']);
        update_post_meta($post_id, 'sf_geocode_matched_address', $match['matched_address']);

        if (in_array($match['class'], array('exact', 'strong'), true)) {
            if ($existing_lat && $existing_lng) {
                update_post_meta($post_id, 'sf_geocode_confidence', 'review');
                update_post_meta($post_id, 'sf_geocode_match_explanation', 'Existing coordinates preserved; new ' . $match['class'] . ' candidate needs editor review. ' . $match['explanation']);
                update_post_meta($post_id, 'sf_geocode_candidate_lat', $raw_result['lat'] ?? '');
                update_post_meta($post_id, 'sf_geocode_candidate_lng', $raw_result['lon'] ?? '');
                return;
            }

            update_post_meta($post_id, 'sf_lat', $raw_result['lat'] ?? '');
            update_post_meta($post_id, 'sf_lng', $raw_result['lon'] ?? '');
            // A fresh auto-applied result supersedes any stale review candidate.
            delete_post_meta($post_id, 'sf_geocode_candidate_lat');
            delete_post_meta($post_id, 'sf_geocode_candidate_lng');
            delete_post_meta($post_id, Southforsyth_School_Import_Safety::GEOCODE_MANUAL_META_KEY);
        } elseif ('review' === $match['class']) {
            update_post_meta($post_id, 'sf_geocode_candidate_lat', $raw_result['lat'] ?? '');
            update_post_meta($post_id, 'sf_geocode_candidate_lng', $raw_result['lon'] ?? '');
        }
    }
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('southforsyth geocode-schools', array(new Southforsyth_Geocode_Command(), 'geocode_schools'));
}
