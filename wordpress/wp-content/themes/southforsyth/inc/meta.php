<?php

/**
 * Post meta for the community post types.
 *
 * Kept intentionally small: a shared "directory" field set (address, phone,
 * website, hours) covers most location-based post types, events get their
 * own date/time/venue fields, and `sf_featured` is a single boolean flag
 * reused across post types so the homepage can pull a cross-type "Popular
 * Places" section without a dedicated taxonomy. Fields are edited through
 * WordPress's native Custom Fields metabox — no plugin required.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_directory_meta_post_types')) {
    function southforsyth_get_directory_meta_post_types()
    {
        return array('restaurant', 'park', 'school', 'church', 'business');
    }
}

if (! function_exists('southforsyth_get_featured_flag_post_types')) {
    function southforsyth_get_featured_flag_post_types()
    {
        return array('event', 'restaurant', 'park', 'neighborhood', 'business');
    }
}

if (! function_exists('southforsyth_register_post_meta')) {
    function southforsyth_register_post_meta()
    {
        $directory_fields = array(
            'sf_address' => 'string',
            'sf_phone'   => 'string',
            'sf_website' => 'string',
            'sf_hours'   => 'string',
        );

        foreach (southforsyth_get_directory_meta_post_types() as $post_type) {
            foreach ($directory_fields as $meta_key => $type) {
                register_post_meta($post_type, $meta_key, array(
                    'type'         => $type,
                    'single'       => true,
                    'show_in_rest' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ));
            }
        }

        $event_fields = array(
            'sf_event_date'  => 'string',
            'sf_event_time'  => 'string',
            'sf_event_venue' => 'string',
        );

        foreach ($event_fields as $meta_key => $type) {
            register_post_meta('event', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
        }

        foreach (southforsyth_get_featured_flag_post_types() as $post_type) {
            register_post_meta($post_type, 'sf_featured', array(
                'type'         => 'boolean',
                'single'       => true,
                'show_in_rest' => true,
                'default'      => false,
            ));
        }
    }
}

add_action('init', 'southforsyth_register_post_meta');
