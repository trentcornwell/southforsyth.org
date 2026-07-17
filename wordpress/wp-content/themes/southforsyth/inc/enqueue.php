<?php

/**
 * Enqueue theme styles and scripts with versioned assets.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_enqueue_assets')) {
    function southforsyth_enqueue_assets()
    {
        $theme_uri = get_template_directory_uri();
        $theme_dir = get_template_directory();

        $style_version = file_exists($theme_dir . '/assets/css/main.css') ? filemtime($theme_dir . '/assets/css/main.css') : '1.0.0';
        $script_version = file_exists($theme_dir . '/assets/js/main.js') ? filemtime($theme_dir . '/assets/js/main.js') : '1.0.0';

        wp_enqueue_style('southforsyth-style', $theme_uri . '/assets/css/main.css', array(), $style_version);
        wp_style_add_data('southforsyth-style', 'preload', true);

        if (is_front_page() || is_singular() || is_home() || is_archive() || is_search()) {
            wp_enqueue_script('southforsyth-main', $theme_uri . '/assets/js/main.js', array(), $script_version, true);
            wp_script_add_data('southforsyth-main', 'defer', true);
        }

        // "Find My Schools" JS: only where the component can actually
        // appear (front page, the school archive/hub, and single school
        // pages) -- not a global enqueue, matching this theme's
        // performance philosophy (see inc/performance.php).
        $find_schools_context = is_front_page()
            || (is_post_type_archive('school'))
            || (is_singular('school'));
        if ($find_schools_context && file_exists($theme_dir . '/assets/js/find-my-schools.js')) {
            $find_schools_version = filemtime($theme_dir . '/assets/js/find-my-schools.js');
            wp_enqueue_script('southforsyth-find-my-schools', $theme_uri . '/assets/js/find-my-schools.js', array(), $find_schools_version, true);
            wp_script_add_data('southforsyth-find-my-schools', 'defer', true);
            wp_localize_script('southforsyth-find-my-schools', 'sfFindSchools', array(
                'restUrl' => esc_url_raw(rest_url('southforsyth/v1/find-schools')),
                'nonce'   => wp_create_nonce('wp_rest'),
            ));
        }
    }
}

add_action('wp_enqueue_scripts', 'southforsyth_enqueue_assets');
