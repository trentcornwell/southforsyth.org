<?php

/**
 * Community suggestion system loader — this project's first public
 * write-path. Registers the sf_suggestion post type (moderation-queue
 * data, not content: no archive, no rewrite, not public) and its custom
 * statuses, then requires the handler classes. Kept out of
 * inc/post-types.php on purpose: that file's registration loop hardcodes
 * assumptions (public, has_archive, rewrite) that fit the 12 real content
 * types, not a private moderation record — forcing sf_suggestion through
 * that loop would mean adding per-type override plumbing to a loop that
 * works correctly today for everything it currently handles.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_register_suggestion_post_type')) {
    function southforsyth_register_suggestion_post_type()
    {
        register_post_type('sf_suggestion', array(
            'labels' => array(
                'name'          => __('Suggestions', 'southforsyth'),
                'singular_name' => __('Suggestion', 'southforsyth'),
                'all_items'     => __('Suggestions', 'southforsyth'),
                'edit_item'     => __('Review Suggestion', 'southforsyth'),
                'search_items'  => __('Search Suggestions', 'southforsyth'),
                'not_found'     => __('No suggestions found', 'southforsyth'),
            ),
            'description'  => 'Moderated community corrections for directory-style content — never public, never auto-applied.',
            'public'       => false,
            'show_ui'      => true,
            // Attached under the existing "Community Platform" menu
            // (Southforsyth_Admin_Menu, inc/admin/) rather than a new
            // top-level menu — see class-suggestion-moderation.php.
            'show_in_menu' => false,
            'show_in_rest' => false,
            'has_archive'  => false,
            'rewrite'      => false,
            'supports'     => array('title'),
            'capability_type' => 'post',
            // Suggestions are created by the public handler (which uses
            // wp_insert_post() directly with an explicit capability
            // bypass documented there — see class-suggestion-handler.php)
            // and moderated by editors; nobody creates one from the
            // normal "Add New" screen.
            'map_meta_cap' => true,
        ));
    }
}
add_action('init', 'southforsyth_register_suggestion_post_type');

if (! function_exists('southforsyth_get_suggestion_statuses')) {
    /**
     * pending (WordPress's own native status — "awaiting review" already
     * means exactly this, no reason to invent a fifth custom one) plus the
     * 4 custom statuses a moderator can resolve a suggestion to.
     */
    function southforsyth_get_suggestion_statuses()
    {
        return array(
            'pending'          => 'Pending Review',
            'approved'         => 'Approved',
            'rejected'         => 'Rejected',
            'needs-more-info'  => 'Needs More Info',
            'duplicate'        => 'Duplicate',
        );
    }
}

if (! function_exists('southforsyth_register_suggestion_statuses')) {
    function southforsyth_register_suggestion_statuses()
    {
        foreach (southforsyth_get_suggestion_statuses() as $slug => $label) {
            if ('pending' === $slug) {
                continue; // native WordPress status, not registered here
            }

            register_post_status($slug, array(
                'label'                     => $label,
                'public'                    => false,
                'internal'                  => true,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: %s: number of suggestions with this status */
                'label_count'               => _n_noop($label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'southforsyth'),
            ));
        }
    }
}
add_action('init', 'southforsyth_register_suggestion_statuses');

if (! function_exists('southforsyth_get_suggestible_fields')) {
    /**
     * The dropdown template-parts/components/suggestion-form.php offers for
     * "what needs changing" — deliberately a fixed list of real meta keys
     * (plus "other" for freeform feedback) rather than a free-text field
     * name, so the moderation screen can snapshot the *current* value of a
     * structured field automatically and show the moderator exactly what
     * would change. Covers the fields most likely to go stale on a
     * directory-style listing; "other" is always the honest fallback for
     * anything not on this list.
     */
    function southforsyth_get_suggestible_fields()
    {
        return array(
            'sf_address'          => 'Address',
            'sf_phone'            => 'Phone number',
            'sf_hours'            => 'Hours',
            'sf_website'          => 'Website',
            'sf_principal_name'   => 'Principal',
            'sf_grades_served'    => 'Grades served',
            'sf_notable_programs' => 'Notable programs',
            'sf_mascot'           => 'Mascot',
            'sf_school_colors'    => 'School colors',
            'sf_mission'          => 'Mission / description',
            'other'               => 'Other / general feedback',
        );
    }
}

require_once __DIR__ . '/class-suggestion-handler.php';
require_once __DIR__ . '/class-suggestion-moderation.php';
