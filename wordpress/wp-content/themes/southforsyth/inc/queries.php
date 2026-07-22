<?php

/**
 * Homepage and archive query helpers.
 *
 * Every helper here normalizes results into the same "card" shape used by
 * the template-parts/components card partials (eyebrow, title, description,
 * link) and falls back to a realistic placeholder array when a post type
 * has no published content yet. This lets the homepage stay meaningful
 * before any content exists, while automatically switching to live
 * WordPress content the moment posts are published — no template changes
 * required later.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_school_completeness_fields')) {
    /**
     * The fixed checklist "completeness %" is measured against — matches
     * the field list from the Forsyth County Schools import work. Shared
     * between inc/admin/class-school-list-columns.php (the admin column)
     * and Southforsyth_Forsyth_County_Import_Command (the CLI report) so
     * both always agree on what "complete" means.
     */
    function southforsyth_get_school_completeness_fields()
    {
        return array(
            'sf_address', 'sf_city', 'sf_state', 'sf_zip', 'sf_phone', 'sf_website',
            'sf_principal_name', 'sf_lat', 'sf_lng', 'sf_district', 'sf_source_url',
            'sf_last_verified', 'sf_mission', 'sf_mascot', 'sf_school_colors',
            'sf_notable_programs', 'sf_feeder_pattern', 'sf_boundary_url', 'sf_grades_served',
        );
    }
}

if (! function_exists('southforsyth_get_school_completeness')) {
    /**
     * Percentage of the fixed field checklist that's populated, plus
     * whether a level/sector term is tagged (sf_school_type) — one more
     * checklist item, since level/sector live in the taxonomy, not meta
     * (see inc/meta.php).
     */
    function southforsyth_get_school_completeness($post_id)
    {
        $fields = southforsyth_get_school_completeness_fields();
        $filled = 0;

        foreach ($fields as $field) {
            if ('' !== (string) get_post_meta($post_id, $field, true)) {
                $filled++;
            }
        }

        $total = count($fields) + 1; // +1 for the sf_school_type check below
        $terms = wp_get_post_terms($post_id, 'sf_school_type');
        if (! empty($terms) && ! is_wp_error($terms)) {
            $filled++;
        }

        return (int) round(($filled / $total) * 100);
    }
}

if (! function_exists('southforsyth_get_directory_completeness')) {
    /**
     * Generic completeness check for the "Help us improve this school
     * guide." fallback (single.php) across any directory-type post type —
     * schools get the rich field-by-field percentage above; every other
     * directory type (which doesn't have the school-specific fields yet)
     * falls back to a simple check against the core shared fields.
     */
    function southforsyth_get_directory_completeness($post_id)
    {
        if ('school' === get_post_type($post_id)) {
            return southforsyth_get_school_completeness($post_id);
        }

        $core_fields = array('sf_address', 'sf_phone', 'sf_website', 'sf_hours');
        $filled = 0;
        foreach ($core_fields as $field) {
            if ('' !== (string) get_post_meta($post_id, $field, true)) {
                $filled++;
            }
        }

        return (int) round(($filled / count($core_fields)) * 100);
    }
}

if (! function_exists('southforsyth_post_to_card')) {
    function southforsyth_post_to_card($post, $eyebrow = '')
    {
        if (! $eyebrow) {
            $terms = get_the_terms($post, 'category');
            if (empty($terms) || is_wp_error($terms)) {
                $definitions = southforsyth_get_post_type_definitions();
                $eyebrow = $definitions[$post->post_type]['singular'] ?? 'Local guide';
            } else {
                $eyebrow = $terms[0]->name;
            }
        }

        $address = get_post_meta($post->ID, 'sf_address', true);
        $area_terms = get_the_terms($post, 'sf_area');
        $city_terms = get_the_terms($post, 'sf_city');
        $area = (! empty($area_terms) && ! is_wp_error($area_terms)) ? $area_terms[0]->name : '';
        $city = (! empty($city_terms) && ! is_wp_error($city_terms)) ? $city_terms[0]->name : '';
        $location_parts = array_filter(array($address, $area, $city));

        $card = array(
            'eyebrow'     => $eyebrow,
            'title'       => get_the_title($post),
            'description' => southforsyth_get_excerpt($post->ID, 20),
            'link'        => get_permalink($post),
            'date'        => ('event' === $post->post_type) ? get_post_meta($post->ID, 'sf_event_date', true) : '',
            'address'     => $address,
            'area'        => $area,
            'city'        => $city,
            'location'    => implode(' · ', $location_parts),
            'grades'      => ('school' === $post->post_type) ? get_post_meta($post->ID, 'sf_grades_served', true) : '',
        );

        if ('school' === $post->post_type) {
            $terms = wp_get_post_terms($post->ID, 'sf_school_type', array('fields' => 'names'));
            $terms = (! empty($terms) && ! is_wp_error($terms)) ? $terms : array();
            $level = '';
            foreach (array('Elementary', 'Middle', 'High', 'K-8') as $key) {
                if (in_array($key, $terms, true)) {
                    $level = $key;
                    break;
                }
            }
            $sector = '';
            foreach (array('Public', 'Private', 'Charter', 'Homeschool Resource') as $key) {
                if (in_array($key, $terms, true)) {
                    $sector = $key;
                    break;
                }
            }

            $card['level']      = $level;
            $card['sector']     = $sector;
            $card['city_meta']  = get_post_meta($post->ID, 'sf_city', true);
            $card['state']      = get_post_meta($post->ID, 'sf_state', true);
            $card['zip']        = get_post_meta($post->ID, 'sf_zip', true);
            $card['phone']      = get_post_meta($post->ID, 'sf_phone', true);
            $card['website']    = get_post_meta($post->ID, 'sf_website', true);
        }

        return $card;
    }
}

if (! function_exists('southforsyth_get_latest_items')) {
    function southforsyth_get_latest_items($post_type, $count = 3, $fallback = array(), $eyebrow = '')
    {
        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
        );

        if ('school' === $post_type) {
            $args['meta_query'] = southforsyth_get_public_school_meta_query();
        }

        $query = new WP_Query($args);

        if (! $query->have_posts()) {
            return $fallback;
        }

        $cards = array();
        foreach ($query->posts as $post) {
            $cards[] = southforsyth_post_to_card($post, $eyebrow);
        }

        return $cards;
    }
}

if (! function_exists('southforsyth_get_public_school_meta_query')) {
    /**
     * One public-visibility rule for every school query. A post must be
     * editorially confirmed and must not carry an unresolved duplicate flag.
     */
    function southforsyth_get_public_school_meta_query()
    {
        return array(
            'relation' => 'AND',
            array(
                'key'   => 'sf_south_forsyth_status',
                'value' => 'Confirmed South Forsyth',
            ),
            array(
                'relation' => 'OR',
                array(
                    'key'     => 'sf_duplicate_warning',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key'   => 'sf_duplicate_warning',
                    'value' => '',
                ),
            ),
        );
    }
}

if (! function_exists('southforsyth_is_public_school')) {
    function southforsyth_is_public_school($post)
    {
        if (! $post || 'school' !== $post->post_type) {
            return true;
        }

        return 'publish' === $post->post_status
            && 'Confirmed South Forsyth' === get_post_meta($post->ID, 'sf_south_forsyth_status', true)
            && '' === (string) get_post_meta($post->ID, 'sf_duplicate_warning', true);
    }
}

if (! function_exists('southforsyth_filter_public_school_posts')) {
    function southforsyth_filter_public_school_posts(array $posts)
    {
        return array_values(array_filter($posts, 'southforsyth_is_public_school'));
    }
}

if (! function_exists('southforsyth_limit_public_school_queries_to_confirmed')) {
    function southforsyth_limit_public_school_queries_to_confirmed($query)
    {
        if (is_admin() || ! $query->is_main_query()) {
            return;
        }

        $post_type = $query->get('post_type');
        $targets_school = 'school' === $post_type || (is_array($post_type) && in_array('school', $post_type, true)) || $query->is_post_type_archive('school');
        if (! $targets_school) {
            return;
        }

        $meta_query = (array) $query->get('meta_query');
        $visibility_query = southforsyth_get_public_school_meta_query();
        $meta_query = empty($meta_query)
            ? $visibility_query
            : array('relation' => 'AND', $meta_query, $visibility_query);
        $query->set('meta_query', $meta_query);
    }
}
add_action('pre_get_posts', 'southforsyth_limit_public_school_queries_to_confirmed');

if (! function_exists('southforsyth_get_post_faqs')) {
    /**
     * Decode the sf_faqs meta field (see inc/meta.php) into an array of
     * {question, answer} pairs for template-parts/components/faq-block.php.
     * No system of its own — reuses the same FAQ component the hub pages
     * already use (southforsyth_render_hub_faq()) for per-entity FAQs.
     */
    function southforsyth_get_post_faqs($post_id)
    {
        $raw = get_post_meta($post_id, 'sf_faqs', true);
        if (! $raw) {
            return array();
        }

        $items = json_decode($raw, true);
        return is_array($items) ? $items : array();
    }
}

if (! function_exists('southforsyth_get_related_entities')) {
    /**
     * Other directory-type posts (any post type in
     * southforsyth_get_directory_meta_post_types(), not just the same one)
     * sharing at least one sf_area term with $post. Returns an empty array
     * — never a guess — when $post has no sf_area tagged, since "related"
     * is only ever computed from data the editor actually set.
     */
    function southforsyth_get_related_entities($post, $count = 3)
    {
        $area_terms = wp_get_post_terms($post->ID, 'sf_area', array('fields' => 'ids'));
        if (empty($area_terms) || is_wp_error($area_terms)) {
            return array();
        }

        $query = new WP_Query(array(
            'post_type'      => southforsyth_get_directory_meta_post_types(),
            'post__not_in'   => array($post->ID),
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
            'tax_query'      => array(array(
                'taxonomy' => 'sf_area',
                'field'    => 'term_id',
                'terms'    => $area_terms,
            )),
        ));

        return array_slice(southforsyth_filter_public_school_posts($query->posts), 0, $count);
    }
}

if (! function_exists('southforsyth_get_nearby_places')) {
    /**
     * Other directory-type posts within $radius_miles of $post, using
     * sf_lat/sf_lng. Plain-PHP haversine over candidates that have lat/lng
     * set, rather than a spatial SQL extension or an external geo library —
     * matches this theme's "no plugins, dependency-free" pattern (see the
     * ICS parser precedent in Southforsyth_Events_Provider) and is fast
     * enough at the content volumes a haversine-in-PHP pass is meant for.
     * Returns an empty array when $post has no lat/lng — never a guess.
     */
    function southforsyth_get_nearby_places($post, $radius_miles = 5, $count = 5)
    {
        $lat = (float) get_post_meta($post->ID, 'sf_lat', true);
        $lng = (float) get_post_meta($post->ID, 'sf_lng', true);
        if (! $lat || ! $lng) {
            return array();
        }

        $candidates = get_posts(array(
            'post_type'      => southforsyth_get_directory_meta_post_types(),
            'post__not_in'   => array($post->ID),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
            'meta_query'     => array(
                array('key' => 'sf_lat', 'compare' => 'EXISTS'),
                array('key' => 'sf_lng', 'compare' => 'EXISTS'),
            ),
        ));

        $nearby = array();
        foreach ($candidates as $candidate) {
            if (! southforsyth_is_public_school($candidate)) {
                continue;
            }
            $c_lat = (float) get_post_meta($candidate->ID, 'sf_lat', true);
            $c_lng = (float) get_post_meta($candidate->ID, 'sf_lng', true);
            if (! $c_lat || ! $c_lng) {
                continue;
            }

            $distance = southforsyth_haversine_miles($lat, $lng, $c_lat, $c_lng);
            if ($distance <= $radius_miles) {
                $nearby[] = array('post' => $candidate, 'distance' => $distance);
            }
        }

        usort($nearby, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return array_map(function ($item) {
            return $item['post'];
        }, array_slice($nearby, 0, $count));
    }
}

if (! function_exists('southforsyth_haversine_miles')) {
    function southforsyth_haversine_miles($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius_miles = 3958.8;
        $lat_delta = deg2rad($lat2 - $lat1);
        $lng_delta = deg2rad($lng2 - $lng1);

        $a = sin($lat_delta / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lng_delta / 2) ** 2;
        return $earth_radius_miles * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}

if (! function_exists('southforsyth_get_featured_places')) {
    function southforsyth_get_featured_places($count = 6, $fallback = array())
    {
        $query = new WP_Query(array(
            'post_type'      => southforsyth_get_featured_flag_post_types(),
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
            'meta_key'       => 'sf_featured',
            'meta_value'     => '1',
        ));

        if (! $query->have_posts()) {
            return $fallback;
        }

        $cards = array();
        foreach ($query->posts as $post) {
            if (! southforsyth_is_public_school($post)) {
                continue;
            }
            $cards[] = southforsyth_post_to_card($post);
        }

        return $cards;
    }
}
