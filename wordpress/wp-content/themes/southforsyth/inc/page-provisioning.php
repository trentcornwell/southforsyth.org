<?php

/**
 * Auto-provisions the three hub pages that have no custom post type of
 * their own: Things To Do, New Resident Guide, and Weekend Guide (Events,
 * Restaurants, Parks, Schools, Churches, Neighborhoods, and Business
 * Directory are all real CPT archives already — see inc/post-types.php —
 * and need no page of their own).
 *
 * Without this, page-templates/hub.php exists but nothing ever creates a
 * WordPress Page that uses it, so /things-to-do/, /new-resident-guide/,
 * and /weekend-guide/ would 404 until someone manually creates each page
 * and assigns the template in wp-admin. Running this on theme activation
 * and on admin_init makes the three URLs work immediately, while staying
 * idempotent: it only ever creates a page that doesn't already exist by
 * slug, and never edits or overwrites one that does — so hand-edited
 * content in wp-admin is never touched by this.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_provisioned_page_slugs')) {
    function southforsyth_get_provisioned_page_slugs()
    {
        return array('things-to-do', 'new-resident-guide', 'weekend-guide');
    }
}

if (! function_exists('southforsyth_provision_hub_pages')) {
    function southforsyth_provision_hub_pages()
    {
        // Once every page exists, skip the get_page_by_path() lookups on
        // every future admin_init call — this only needs to actually run
        // once (or again if the option is cleared, e.g. after a manual
        // page deletion).
        if (get_option('southforsyth_hub_pages_provisioned')) {
            return;
        }

        $all_exist = true;

        foreach (southforsyth_get_provisioned_page_slugs() as $slug) {
            if (get_page_by_path($slug)) {
                continue;
            }

            $hub = function_exists('southforsyth_get_hub_content') ? southforsyth_get_hub_content($slug) : null;
            $title = $hub['title'] ?? ucwords(str_replace('-', ' ', $slug));

            $post_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_content' => '',
                'meta_input'   => array(
                    '_wp_page_template' => 'page-templates/hub.php',
                ),
            ));

            if (! $post_id || is_wp_error($post_id)) {
                $all_exist = false;
            }
        }

        if ($all_exist) {
            update_option('southforsyth_hub_pages_provisioned', true);
        }
    }
}

add_action('after_switch_theme', 'southforsyth_provision_hub_pages');
add_action('admin_init', 'southforsyth_provision_hub_pages');
