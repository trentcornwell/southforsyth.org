<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * School Editorial Review — extends the native Posts → Schools list table
 * (columns, filters, bulk actions) rather than a new custom admin page, per
 * the plan behind this file. Draft/Published filtering is already native
 * (the "All | Published | Draft" status links WordPress gives every post
 * type), so this doesn't duplicate it.
 *
 * Every bulk-action handler checks current_user_can() per post before
 * acting, on top of the list table's own access gate — belt and suspenders,
 * since a bulk action must never be a way to bypass normal capabilities.
 */
class Southforsyth_School_List_Columns
{
    const LEVEL_TERMS = array('Elementary', 'Middle', 'High', 'K-8');

    public static function register()
    {
        add_filter('manage_school_posts_columns', array(__CLASS__, 'add_columns'));
        add_action('manage_school_posts_custom_column', array(__CLASS__, 'render_column'), 10, 2);

        add_action('restrict_manage_posts', array(__CLASS__, 'render_filters'));
        add_action('pre_get_posts', array(__CLASS__, 'apply_filters'));

        add_filter('bulk_actions-edit-school', array(__CLASS__, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-edit-school', array(__CLASS__, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array(__CLASS__, 'bulk_action_notice'));

        // Keeps sf_completeness_pct in sync so the Completeness filter below
        // can use a normal, indexable meta_query instead of a PHP-side
        // filter that would break the list table's pagination.
        add_action('save_post_school', array(__CLASS__, 'store_completeness'));
    }

    // ---------------------------------------------------------------
    // Columns
    // ---------------------------------------------------------------

    public static function add_columns($columns)
    {
        $date = $columns['date'] ?? null;
        unset($columns['date']);

        $columns['sf_school_type']    = 'Type';
        $columns['sf_grades']         = 'Grades';
        $columns['sf_city']           = 'City';
        $columns['sf_sf_status']      = 'South Forsyth';
        $columns['sf_principal_name'] = 'Principal';
        $columns['sf_source']         = 'Source';
        $columns['sf_last_verified']  = 'Last Verified';
        $columns['sf_geocoding']      = 'Geocode Status';
        $columns['sf_readiness']      = 'Content Status';
        $columns['sf_details']        = 'Details';

        if ($date) {
            $columns['date'] = $date;
        }

        return $columns;
    }

    public static function render_column($column, $post_id)
    {
        switch ($column) {
            case 'sf_school_type':
                $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
                echo (! empty($terms) && ! is_wp_error($terms)) ? esc_html(implode(', ', $terms)) : '—';
                break;

            case 'sf_grades':
                $grades = get_post_meta($post_id, 'sf_grades_served', true);
                echo $grades ? esc_html($grades) : '—';
                break;

            case 'sf_city':
                $city = get_post_meta($post_id, 'sf_city', true);
                echo $city ? esc_html($city) : '—';
                break;

            case 'sf_sf_status':
                $status = get_post_meta($post_id, 'sf_south_forsyth_status', true);
                echo esc_html($status ?: 'Needs Review');
                break;

            case 'sf_principal_name':
                $principal = get_post_meta($post_id, 'sf_principal_name', true);
                echo $principal ? esc_html($principal) : '—';
                break;

            case 'sf_source':
                $source = get_post_meta($post_id, '_sf_import_source', true);
                $source_url = get_post_meta($post_id, 'sf_source_url', true);
                $label = $source ? esc_html($source) : 'Manual';
                echo $source_url ? '<a href="' . esc_url($source_url) . '" target="_blank" rel="noopener">' . $label . '</a>' : $label;
                break;

            case 'sf_last_verified':
                $verified = get_post_meta($post_id, 'sf_last_verified', true);
                echo $verified ? esc_html($verified) : '—';
                break;

            case 'sf_geocoding':
                self::render_geocoding_column($post_id);
                break;

            case 'sf_readiness':
                self::render_readiness_column($post_id);
                break;

            case 'sf_details':
                self::render_details_column($post_id);
                break;
        }
    }

    /**
     * Match status, the geocoder's matched address, its explanation (why
     * it was classified that way — truncated with the full text in a
     * title-attribute tooltip), and a map/coordinate link for whichever
     * pair (trusted or candidate) exists.
     */
    private static function render_geocoding_column($post_id)
    {
        $status = Southforsyth_School_Import_Safety::geocode_status($post_id);
        $class = get_post_meta($post_id, 'sf_geocode_confidence', true);

        $colors = array('geocoded' => '#2f7d4f', 'manual' => '#2f7d4f', 'needs_review' => '#a9791f', 'no_match' => '#b32d2e', 'missing' => '#798073');
        $color = $colors[$status['key']] ?? '#798073';
        $explanation = get_post_meta($post_id, 'sf_geocode_match_explanation', true);
        $matched_address = get_post_meta($post_id, 'sf_geocode_matched_address', true);

        echo '<strong style="color:' . esc_attr($color) . ';">' . esc_html($status['label']) . '</strong>';
        if ($class && ! in_array($status['key'], array('manual', 'missing'), true)) {
            echo '<br><span>' . esc_html(ucfirst($class)) . '</span>';
        }

        if ($matched_address) {
            echo '<br><span title="' . esc_attr($explanation) . '">' . esc_html(wp_trim_words($matched_address, 8)) . '</span>';
        }

        $lat = get_post_meta($post_id, 'sf_lat', true);
        $lng = get_post_meta($post_id, 'sf_lng', true);
        if (! $lat || ! $lng) {
            $lat = get_post_meta($post_id, 'sf_geocode_candidate_lat', true);
            $lng = get_post_meta($post_id, 'sf_geocode_candidate_lng', true);
        }
        if ($lat && $lng) {
            $map_url = 'https://www.openstreetmap.org/?mlat=' . rawurlencode($lat) . '&mlon=' . rawurlencode($lng) . '#map=16/' . rawurlencode($lat) . '/' . rawurlencode($lng);
            echo '<br><a href="' . esc_url($map_url) . '" target="_blank" rel="noopener">View on map</a>';
        }
    }

    private static function render_readiness_column($post_id)
    {
        $readiness = Southforsyth_School_Import_Safety::readiness($post_id);
        $color = $readiness['ready'] ? '#2f7d4f' : '#a9791f';
        echo '<strong style="color:' . esc_attr($color) . ';">' . esc_html($readiness['label']) . '</strong>';
        echo '<br><span>' . esc_html(southforsyth_get_school_completeness($post_id) . '% complete') . '</span>';
        if (! empty($readiness['missing'])) {
            echo '<br><span title="' . esc_attr(implode(', ', $readiness['missing'])) . '">' . esc_html(count($readiness['missing']) . ' readiness issue(s)') . '</span>';
        }
    }

    private static function render_details_column($post_id)
    {
        $missing = array();
        foreach (southforsyth_get_school_completeness_fields() as $field) {
            if ('' === (string) get_post_meta($post_id, $field, true)) {
                $missing[] = $field;
            }
        }

        $lines = array();

        if (! empty($missing)) {
            $lines[] = count($missing) . ' field(s) missing';
        }

        $has_coords = get_post_meta($post_id, 'sf_lat', true) && get_post_meta($post_id, 'sf_lng', true);
        if (! $has_coords) {
            $lines[] = 'No coordinates';
        }

        $duplicate = self::find_possible_duplicate($post_id);
        if ($duplicate) {
            $lines[] = '<strong style="color:#b32d2e;">Possible duplicate of #' . (int) $duplicate . '</strong>';
        }

        echo empty($lines) ? '<span style="color:#2f7d4f;">Complete</span>' : wp_kses_post(implode('<br>', $lines));
    }

    /**
     * Same-title-fragment heuristic used to protect school-provisioning.php
     * from recreating a legacy duplicate (southforsyth_school_already_imported())
     * applied here in reverse: flag when two *different* school posts share
     * enough of a title to plausibly be the same real school. Deliberately
     * simple (substring containment, not fuzzy matching) — a false negative
     * here just means a human notices it manually; a false positive is
     * harmless since this is advisory, not an automatic merge.
     */
    private static function find_possible_duplicate($post_id)
    {
        $title = get_the_title($post_id);
        if (strlen($title) < 4) {
            return null;
        }

        $candidates = get_posts(array(
            'post_type'      => 'school',
            'post_status'    => 'any',
            'posts_per_page' => 5,
            'post__not_in'   => array($post_id),
            's'              => $title,
            'fields'         => 'ids',
        ));

        return $candidates[0] ?? null;
    }

    // ---------------------------------------------------------------
    // Filters
    // ---------------------------------------------------------------

    public static function render_filters($post_type)
    {
        if ('school' !== $post_type) {
            return;
        }

        self::render_select('sf_filter_status', 'All South Forsyth statuses', southforsyth_get_south_forsyth_status_options());
        self::render_select('sf_filter_level', 'All levels', self::LEVEL_TERMS);
        self::render_select('sf_filter_geocode', 'Any geocode status', array(
            'geocoded' => 'Geocoded',
            'manual' => 'Manually verified',
            'needs_review' => 'Needs geocode review',
            'missing' => 'Not geocoded',
            'no_match' => 'No acceptable match',
        ));
        self::render_select('sf_filter_verified', 'Any verification age', array(
            'missing' => 'Missing verification date',
            'stale' => 'Stale (365+ days)',
            'fresh' => 'Fresh (<365 days)',
        ));
        self::render_select('sf_filter_source', 'All sources', self::get_distinct_sources());
    }

    private static function render_select($name, $label, $options)
    {
        $current = $_GET[$name] ?? ''; // phpcs:ignore -- read-only, admin list table filter, no state change
        echo '<select name="' . esc_attr($name) . '"><option value="">' . esc_html($label) . '</option>';
        foreach ($options as $key => $value) {
            $option_value = is_int($key) ? $value : $key;
            echo '<option value="' . esc_attr($option_value) . '"' . selected($current, $option_value, false) . '>' . esc_html($value) . '</option>';
        }
        echo '</select>';
    }

    private static function get_distinct_sources()
    {
        global $wpdb;
        $sources = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
             INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key = %s AND p.post_type = %s AND pm.meta_value != ''",
            '_sf_import_source',
            'school'
        ));

        return array_combine($sources, $sources);
    }

    public static function apply_filters($query)
    {
        if (! is_admin() || ! $query->is_main_query() || 'school' !== $query->get('post_type')) {
            return;
        }

        $meta_query = (array) $query->get('meta_query');

        $status = $_GET['sf_filter_status'] ?? ''; // phpcs:ignore
        if ($status) {
            $meta_query[] = array('key' => 'sf_south_forsyth_status', 'value' => $status);
        }

        $geocode = $_GET['sf_filter_geocode'] ?? ''; // phpcs:ignore
        if ('missing' === $geocode) {
            $meta_query[] = array(
                'relation' => 'OR',
                array('key' => 'sf_lat', 'compare' => 'NOT EXISTS'),
                array('key' => 'sf_lat', 'value' => '', 'compare' => '='),
            );
        } elseif ('needs_review' === $geocode) {
            $meta_query[] = array('key' => 'sf_geocode_confidence', 'value' => 'review');
        } elseif ('no_match' === $geocode) {
            $meta_query[] = array('key' => 'sf_geocode_confidence', 'value' => 'rejected');
        } elseif ('manual' === $geocode) {
            $meta_query[] = array('key' => Southforsyth_School_Import_Safety::GEOCODE_MANUAL_META_KEY, 'compare' => 'EXISTS');
        } elseif ('geocoded' === $geocode) {
            $meta_query[] = array('key' => 'sf_lat', 'compare' => 'EXISTS');
            $meta_query[] = array('key' => 'sf_geocode_confidence', 'value' => array('exact', 'strong'), 'compare' => 'IN');
        }

        $verified = $_GET['sf_filter_verified'] ?? ''; // phpcs:ignore
        if ('missing' === $verified) {
            $meta_query[] = array(
                'relation' => 'OR',
                array('key' => 'sf_last_verified', 'compare' => 'NOT EXISTS'),
                array('key' => 'sf_last_verified', 'value' => '', 'compare' => '='),
            );
        } elseif ('stale' === $verified) {
            $meta_query[] = array('key' => 'sf_last_verified', 'value' => gmdate('Y-m-d', strtotime('-365 days')), 'compare' => '<', 'type' => 'DATE');
        } elseif ('fresh' === $verified) {
            $meta_query[] = array('key' => 'sf_last_verified', 'value' => gmdate('Y-m-d', strtotime('-365 days')), 'compare' => '>=', 'type' => 'DATE');
        }

        $source = $_GET['sf_filter_source'] ?? ''; // phpcs:ignore
        if ($source) {
            $meta_query[] = array('key' => '_sf_import_source', 'value' => $source);
        }

        if (count($meta_query) > 1 && empty($meta_query['relation'])) {
            $meta_query['relation'] = 'AND';
        }

        if (! empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }

        $level = $_GET['sf_filter_level'] ?? ''; // phpcs:ignore
        if ($level) {
            $tax_query = (array) $query->get('tax_query');
            $tax_query[] = array('taxonomy' => 'sf_school_type', 'field' => 'name', 'terms' => $level);
            $query->set('tax_query', $tax_query);
        }
    }

    public static function store_completeness($post_id)
    {
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        update_post_meta($post_id, 'sf_completeness_pct', southforsyth_get_school_completeness($post_id));
        update_post_meta($post_id, Southforsyth_School_Import_Safety::READY_META_KEY, Southforsyth_School_Import_Safety::readiness($post_id)['label']);
    }

    // ---------------------------------------------------------------
    // Bulk actions
    // ---------------------------------------------------------------

    public static function add_bulk_actions($actions)
    {
        $actions['sf_mark_confirmed']   = 'Mark: Confirmed South Forsyth';
        $actions['sf_mark_needsreview'] = 'Mark: Needs Review';
        $actions['sf_mark_outside']     = 'Mark: Outside Coverage';
        $actions['sf_publish']          = 'Publish selected';
        $actions['sf_unpublish']        = 'Return selected to draft';
        $actions['sf_geocode_accept']   = 'Accept reviewed coordinates';
        $actions['sf_geocode_reject']   = 'Reject reviewed coordinates';
        $actions['sf_geocode_rerun']    = 'Rerun geocoding for selected';
        return $actions;
    }

    private static function status_bulk_action_map()
    {
        return array(
            'sf_mark_confirmed'   => Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED,
            'sf_mark_needsreview' => Southforsyth_School_Import_Safety::COVERAGE_NEEDS_REVIEW,
            'sf_mark_outside'     => Southforsyth_School_Import_Safety::COVERAGE_OUTSIDE,
        );
    }

    public static function handle_bulk_actions($redirect_to, $doaction, $post_ids)
    {
        $status_map = self::status_bulk_action_map();
        $affected = 0;
        $denied = 0;

        if (isset($status_map[$doaction])) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('edit_post', $post_id)) {
                    $denied++;
                    continue;
                }
                update_post_meta($post_id, 'sf_south_forsyth_status', $status_map[$doaction]);
                update_post_meta($post_id, Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY, 'WordPress Schools admin bulk action');
                update_post_meta($post_id, Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY, 'Manual editorial coverage decision set from the Schools admin list.');
                update_post_meta($post_id, Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY, current_time('Y-m-d'));
                update_post_meta($post_id, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, 'manual');
                $affected++;
            }
        } elseif ('sf_publish' === $doaction) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('publish_post', $post_id)) {
                    $denied++;
                    continue;
                }
                if (Southforsyth_School_Import_Safety::COVERAGE_CONFIRMED !== Southforsyth_School_Import_Safety::normalize_coverage_status(get_post_meta($post_id, 'sf_south_forsyth_status', true))) {
                    $denied++;
                    continue;
                }
                if (! Southforsyth_School_Import_Safety::readiness($post_id)['ready']) {
                    $denied++;
                    continue;
                }
                wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
                $affected++;
            }
        } elseif ('sf_unpublish' === $doaction) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('edit_post', $post_id)) {
                    $denied++;
                    continue;
                }
                wp_update_post(array('ID' => $post_id, 'post_status' => 'draft'));
                $affected++;
            }
        } elseif ('sf_geocode_accept' === $doaction) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('edit_post', $post_id)) {
                    $denied++;
                    continue;
                }
                $affected += self::accept_reviewed_coordinates($post_id) ? 1 : 0;
            }
        } elseif ('sf_geocode_reject' === $doaction) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('edit_post', $post_id)) {
                    $denied++;
                    continue;
                }
                delete_post_meta($post_id, 'sf_geocode_candidate_lat');
                delete_post_meta($post_id, 'sf_geocode_candidate_lng');
                update_post_meta($post_id, 'sf_geocode_confidence', 'rejected');
                $affected++;
            }
        } elseif ('sf_geocode_rerun' === $doaction) {
            foreach ($post_ids as $post_id) {
                if (! current_user_can('edit_post', $post_id)) {
                    $denied++;
                    continue;
                }
                self::rerun_geocoding($post_id);
                $affected++;
            }
        } else {
            return $redirect_to;
        }

        return add_query_arg(array('sf_bulk_affected' => $affected, 'sf_bulk_denied' => $denied), $redirect_to);
    }

    /** Promotes a review-class candidate pair to trusted sf_lat/sf_lng — never touches a post with no candidate. */
    private static function accept_reviewed_coordinates($post_id)
    {
        $lat = get_post_meta($post_id, 'sf_geocode_candidate_lat', true);
        $lng = get_post_meta($post_id, 'sf_geocode_candidate_lng', true);
        if (! $lat || ! $lng) {
            return false;
        }

        update_post_meta($post_id, 'sf_lat', $lat);
        update_post_meta($post_id, 'sf_lng', $lng);
        update_post_meta($post_id, 'sf_geocode_confidence', 'strong'); // editor-confirmed, treated as trusted going forward
        update_post_meta($post_id, Southforsyth_School_Import_Safety::GEOCODE_MANUAL_META_KEY, current_time('Y-m-d'));
        delete_post_meta($post_id, 'sf_geocode_candidate_lat');
        delete_post_meta($post_id, 'sf_geocode_candidate_lng');
        return true;
    }

    /**
     * Synchronous, for a deliberately small selection from the admin UI —
     * not a replacement for `wp southforsyth geocode-schools`, which is
     * the right tool for the full directory. Fetches + evaluates here,
     * then hands off to Southforsyth_Geocode_Command::apply_match() — the
     * exact same write logic the CLI command uses, so a result is applied
     * identically regardless of which one ran it.
     */
    private static function rerun_geocoding($post_id)
    {
        $provider = Southforsyth_Provider_Registry::get('openstreetmap');
        if (! $provider || ! class_exists('Southforsyth_Geocode_Match_Evaluator') || ! class_exists('Southforsyth_Geocode_Command')) {
            return;
        }

        $school = array(
            'title'   => get_the_title($post_id),
            'address' => get_post_meta($post_id, 'sf_address', true),
            'city'    => get_post_meta($post_id, 'sf_city', true),
            'state'   => get_post_meta($post_id, 'sf_state', true),
            'zip'     => get_post_meta($post_id, 'sf_zip', true),
        );

        $query = trim(implode(', ', array_filter(array($school['address'], $school['city'], $school['state'], $school['zip']))));
        if ('' === $query) {
            return;
        }

        sleep(1); // same Nominatim rate-limit courtesy as the CLI command
        $raw_result = $provider->fetch($query);
        if (empty($raw_result)) {
            return;
        }

        $match = Southforsyth_Geocode_Match_Evaluator::evaluate($school, $raw_result);
        Southforsyth_Geocode_Command::apply_match($post_id, $raw_result, $match);
    }

    public static function bulk_action_notice()
    {
        if (empty($_GET['sf_bulk_affected']) && empty($_GET['sf_bulk_denied'])) { // phpcs:ignore
            return;
        }

        $affected = (int) ($_GET['sf_bulk_affected'] ?? 0); // phpcs:ignore
        $denied = (int) ($_GET['sf_bulk_denied'] ?? 0); // phpcs:ignore

        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html("Updated {$affected} school(s).") . '</p></div>';
        if ($denied > 0) {
            echo '<div class="notice notice-warning"><p>' . esc_html("{$denied} school(s) skipped — you don't have permission to edit them.") . '</p></div>';
        }
    }
}

Southforsyth_School_List_Columns::register();
