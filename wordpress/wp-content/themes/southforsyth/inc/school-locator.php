<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Public "Find My Schools" address lookup.
 *
 * A REST endpoint rather than an admin-post.php redirect (the pattern the
 * suggestion form uses) because results are structured, multi-part data
 * (three schools' worth of details) that doesn't fit cleanly into a
 * redirect query string -- and putting a visitor's address in any URL
 * risks it landing in ordinary web server access logs, which this
 * feature's privacy requirement (never log or store the address) treats
 * as out of bounds. A POST body sent via fetch() never touches a URL.
 *
 * Privacy design, all deliberate:
 * - No caching layer (see Southforsyth_School_Boundary_Service's class
 *   doc) -- nothing about the lookup is persisted anywhere.
 * - The address is used only for the duration of this one request; it is
 *   never written to post meta, options, or any log call.
 * - Rate-limited by IP hash (same transient pattern as the community
 *   suggestion form), not by anything address-derived.
 */

if (! function_exists('southforsyth_register_find_schools_route')) {
    function southforsyth_register_find_schools_route()
    {
        register_rest_route('southforsyth/v1', '/find-schools', array(
            'methods'             => 'POST',
            'callback'            => 'southforsyth_handle_find_schools_request',
            'permission_callback' => '__return_true',
        ));
    }
}
add_action('rest_api_init', 'southforsyth_register_find_schools_route');

if (! function_exists('southforsyth_find_schools_rate_limit_key')) {
    function southforsyth_find_schools_rate_limit_key()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        return 'sf_findschools_rl_' . hash('sha256', $ip . wp_salt());
    }
}

if (! function_exists('southforsyth_handle_find_schools_request')) {
    function southforsyth_handle_find_schools_request(WP_REST_Request $request)
    {
        $nonce = $request->get_header('X-WP-Nonce');
        if (! $nonce || ! wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_REST_Response(array('error' => 'invalid_nonce', 'message' => 'Your session expired. Please reload the page and try again.'), 403);
        }

        // Honeypot: a real visitor never fills this.
        if (! empty($request->get_param('sf_hp_website'))) {
            return new WP_REST_Response(array('error' => 'invalid_request', 'message' => 'Something went wrong. Please try again.'), 400);
        }

        $rate_limit_key = southforsyth_find_schools_rate_limit_key();
        if (get_transient($rate_limit_key)) {
            return new WP_REST_Response(array('error' => 'rate_limited', 'message' => 'Please wait a moment before checking another address.'), 429);
        }
        set_transient($rate_limit_key, 1, 15);

        $street = sanitize_text_field((string) $request->get_param('street'));
        $zip = sanitize_text_field((string) $request->get_param('zip'));
        // City/state are collected for the visitor's own confirmation and
        // are not used in the lookup itself -- FCS's address database is
        // matched on house number + street name (+ ZIP to disambiguate),
        // and is not indexed by city name.

        if ('' === trim($street)) {
            return new WP_REST_Response(array('error' => 'missing_address', 'message' => 'Please enter a street address.'), 400);
        }

        list($house_number, $street_name) = Southforsyth_School_Boundary_Service::split_address($street);

        if ($house_number <= 0 || '' === $street_name) {
            return new WP_REST_Response(array('error' => 'unparseable_address', 'message' => "We couldn't read that as a street address. Please include a house number and street name."), 400);
        }

        $match = Southforsyth_School_Boundary_Service::lookup_by_address($house_number, $street_name, $zip);

        if (! $match) {
            return new WP_REST_Response(array(
                'error'   => 'no_match',
                'message' => "That address isn't in Forsyth County Schools' official records, or the match wasn't clear enough to show confidently. It may be outside Forsyth County, a very new address, or a typo. Please double-check the address or use the official Forsyth County Schools lookup tool.",
                'official_tool_url' => 'https://www.forsyth.k12.ga.us/district-services/facilities/gis-boundary-planning',
            ), 200);
        }

        $response = array(
            'matched_address' => $match['matched_address'],
            'elementary'      => southforsyth_build_school_result($match['es']),
            'middle'          => southforsyth_build_school_result($match['ms']),
            'high'            => southforsyth_build_school_result($match['hs']),
            'source'          => Southforsyth_School_Boundary_Service::DECISION_SOURCE,
            'boundary_vintage' => Southforsyth_School_Boundary_Service::BOUNDARY_VINTAGE,
        );

        return new WP_REST_Response($response, 200);
    }
}

if (! function_exists('southforsyth_build_school_result')) {
    /**
     * Turns a raw zone name like "SHILOH POINT ES" into a display-ready
     * result, linking to the matching published SouthForsyth.org profile
     * when one exists. Never links to a draft/unpublished post -- a
     * visitor using this tool should never be handed a URL that 404s or
     * exposes unreviewed content.
     */
    function southforsyth_build_school_result($zone_name)
    {
        if (! $zone_name) {
            return null;
        }

        $display_name = Southforsyth_School_Boundary_Service::zone_name_to_display_name($zone_name);
        $normalized_zone_name = Southforsyth_School_Import_Safety::normalize_school_name($display_name);

        $result = array(
            'name'         => $display_name,
            'grades'       => '',
            'address'      => '',
            'profile_url'  => '',
            'official_url' => '',
        );

        $candidates = get_posts(array(
            'post_type'      => 'school',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            's'              => $display_name,
        ));

        foreach ($candidates as $candidate) {
            if (Southforsyth_School_Import_Safety::normalize_school_name($candidate->post_title) === $normalized_zone_name) {
                $result['name'] = $candidate->post_title;
                $result['grades'] = (string) get_post_meta($candidate->ID, 'sf_grades_served', true);
                $address = get_post_meta($candidate->ID, 'sf_address', true);
                $city = get_post_meta($candidate->ID, 'sf_city', true);
                $state = get_post_meta($candidate->ID, 'sf_state', true);
                $zip = get_post_meta($candidate->ID, 'sf_zip', true);
                $result['address'] = trim(implode(', ', array_filter(array($address, trim("$city $state $zip")))));
                $result['profile_url'] = get_permalink($candidate->ID);
                $result['official_url'] = (string) get_post_meta($candidate->ID, 'sf_source_url', true);
                break;
            }
        }

        return $result;
    }
}
