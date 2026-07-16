<?php

/**
 * One-time bootstrap for the sf_resource_type taxonomy: seeds the standard
 * terms so community_resource (reused for sports organizations, government
 * facilities, libraries, etc. — see docs/platform-architecture.md, "How a
 * new content type plugs in") has a consistent starting vocabulary instead
 * of an empty taxonomy.
 *
 * Same idempotent shape as inc/school-provisioning.php and
 * inc/page-provisioning.php: check-then-create, short-circuit via an option
 * once done, never touch a term that already exists. Runs on
 * after_switch_theme (theme activation) and admin_init (so it also catches
 * a theme that's already active when this file is first deployed).
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_resource_type_terms')) {
    function southforsyth_get_resource_type_terms()
    {
        return array('Sports Organization', 'Government Facility', 'Library', 'Senior Resource', 'Public Safety', 'Healthcare', 'Civic Organization');
    }
}

if (! function_exists('southforsyth_provision_resource_type_terms')) {
    function southforsyth_provision_resource_type_terms()
    {
        if (get_option('southforsyth_resource_terms_provisioned')) {
            return;
        }

        foreach (southforsyth_get_resource_type_terms() as $term) {
            if (! term_exists($term, 'sf_resource_type')) {
                wp_insert_term($term, 'sf_resource_type');
            }
        }

        update_option('southforsyth_resource_terms_provisioned', true);
    }
}

add_action('after_switch_theme', 'southforsyth_provision_resource_type_terms');
add_action('admin_init', 'southforsyth_provision_resource_type_terms');
