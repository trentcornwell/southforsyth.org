<?php

/**
 * One-time bootstrap for the Schools data model: seeds the standard
 * sf_school_type terms (level + sector) and two draft seed posts, so the
 * Schools section has a consistent starting vocabulary and a concrete
 * example in wp-admin instead of an empty taxonomy and an empty archive.
 *
 * Same idempotent shape as inc/page-provisioning.php: check-then-create,
 * short-circuit via an option once done, never touch a term or post that
 * already exists. Runs on after_switch_theme (theme activation) and
 * admin_init (so it also catches a theme that's already active when this
 * file is first deployed).
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_school_type_terms')) {
    /**
     * The sf_school_type taxonomy is hierarchical and allows multiple terms
     * per post, so it holds both facets at once: a school is tagged with
     * whichever level term(s) apply (Elementary/Middle/High/K-8) *and*
     * whichever sector term applies (Public/Private/Charter/Homeschool
     * Resource) — e.g. "Elementary" + "Public" — rather than needing a
     * second taxonomy just to separate the two facets. Matches the level/
     * sector split the Schools hub's own sample cards already imply (see
     * inc/hub-content.php's 'school' entry: Elementary / Middle / High /
     * Private & Independent).
     */
    function southforsyth_get_school_type_terms()
    {
        return array('Elementary', 'Middle', 'High', 'K-8', 'Public', 'Private', 'Charter', 'Homeschool Resource');
    }
}

if (! function_exists('southforsyth_provision_school_type_terms')) {
    function southforsyth_provision_school_type_terms()
    {
        if (get_option('southforsyth_school_terms_provisioned')) {
            return;
        }

        foreach (southforsyth_get_school_type_terms() as $term) {
            if (! term_exists($term, 'sf_school_type')) {
                wp_insert_term($term, 'sf_school_type');
            }
        }

        update_option('southforsyth_school_terms_provisioned', true);
    }
}

if (! function_exists('southforsyth_get_seed_schools')) {
    /**
     * Two draft-only seed posts by name — see docs/content-platform-architecture.md's
     * placeholder-content policy and the plan behind this file for why every
     * fact field below is deliberately left blank rather than guessed:
     * address, phone, principal, grades, and boundary link all need
     * confirming against the official Forsyth County Schools site before
     * anything here is publish-ready. A feeder middle/elementary pair is
     * deliberately NOT seeded here — Forsyth County redraws attendance
     * boundaries frequently, and naming a specific current feeder chain
     * needs the same verification these two names already call for, not a
     * guess. See docs/editorial-roadmap.md items #7-8 for that next step.
     */
    function southforsyth_get_seed_schools()
    {
        return array(
            array(
                'slug'  => 'south-forsyth-high-school',
                'title' => 'South Forsyth High School',
            ),
            array(
                'slug'  => 'denmark-high-school',
                'title' => 'Denmark High School',
            ),
        );
    }
}

if (! function_exists('southforsyth_provision_seed_schools')) {
    function southforsyth_provision_seed_schools()
    {
        if (get_option('southforsyth_seed_schools_provisioned')) {
            return;
        }

        $notice = "Seed draft — not verified, not ready to publish.\n\n" .
            "This profile was created as a starting point, not researched content. " .
            'Every fact field (address, phone, principal, grades served, attendance zone) ' .
            'is intentionally blank. Before publishing, confirm each detail directly against ' .
            "the official Forsyth County Schools site and the school's own official website, " .
            'then fill in the fields below and set sf_last_verified to today\'s date.';

        foreach (southforsyth_get_seed_schools() as $school) {
            if (get_page_by_path($school['slug'], OBJECT, 'school')) {
                continue;
            }

            $post_id = wp_insert_post(array(
                'post_title'   => $school['title'],
                'post_name'    => $school['slug'],
                'post_type'    => 'school',
                'post_status'  => 'draft',
                'post_content' => $notice,
            ));

            if ($post_id && ! is_wp_error($post_id) && term_exists('High', 'sf_school_type') && term_exists('Public', 'sf_school_type')) {
                wp_set_object_terms($post_id, array('High', 'Public'), 'sf_school_type');
            }
        }

        update_option('southforsyth_seed_schools_provisioned', true);
    }
}

add_action('after_switch_theme', 'southforsyth_provision_school_type_terms');
add_action('admin_init', 'southforsyth_provision_school_type_terms');

// Seed posts intentionally run after the terms above (same request is fine —
// admin_init runs both in registration order) so the sf_school_type lookup
// in southforsyth_provision_seed_schools() always finds real term IDs.
add_action('after_switch_theme', 'southforsyth_provision_seed_schools');
add_action('admin_init', 'southforsyth_provision_seed_schools');
