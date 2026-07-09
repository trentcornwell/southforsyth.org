<?php

/**
 * Post meta for the community post types.
 *
 * Kept intentionally small: a shared "directory" field set (address, phone,
 * website, hours, lat/lng, source URL, last verified) covers most
 * location-based post types, events get their own date/time/venue fields,
 * articles get a source-attribution field pair for RSS-imported content,
 * schools get a small field group of their own (see below), and
 * `sf_featured` is a single boolean flag reused across post types so the
 * homepage can pull a cross-type "Popular Places" section without a
 * dedicated taxonomy. Fields are edited through WordPress's native Custom
 * Fields metabox — no plugin required.
 *
 * `sf_lat`/`sf_lng` were added alongside the Phase 1–10 platform-scaling
 * work specifically as the prerequisite docs/data-integration-roadmap.md
 * already flagged: "Geo meta fields don't exist yet... a prerequisite for
 * any GIS ingestion." They're consumed today by
 * Southforsyth_Openstreetmap_Provider and Southforsyth_Google_Places_Provider
 * (see inc/providers/) and are ready for the future "Interactive Maps"
 * system in inc/community-platform.php.
 *
 * `sf_source_url`/`sf_last_verified` were added to the shared directory
 * group for the Schools data-model work: every directory-style listing
 * benefits identically from "where did this come from" and "how current is
 * this" as a trust signal, not just schools — matching the same reasoning
 * `sf_lat`/`sf_lng` were added to the whole group rather than one post type.
 * `sf_source_url` already existed as a key registered for `article`
 * (RSS attribution, below); this just extends its registration to the
 * directory post types too — same key, same meaning ("where this content
 * came from"), no collision.
 *
 * `school`-specific fields (grades served, principal, attendance-zone/
 * boundary link, feeder pattern, notable programs) are a small group of
 * their own, the same pattern as the event and article groups below — see
 * docs/content-platform-architecture.md's "Post meta" section for the full
 * rationale per field.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_directory_meta_post_types')) {
    function southforsyth_get_directory_meta_post_types()
    {
        return array('restaurant', 'park', 'school', 'church', 'business', 'trail', 'community_resource');
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
            'sf_address'       => 'string',
            'sf_phone'         => 'string',
            'sf_website'       => 'string',
            'sf_hours'         => 'string',
            'sf_lat'           => 'string',
            'sf_lng'           => 'string',
            'sf_source_url'    => 'string',
            'sf_last_verified' => 'string',
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

        // School-specific fields — see the header comment above and
        // docs/content-platform-architecture.md's "Post meta" section.
        // sf_grades_served is a precise range (e.g. "PK-5"); the more
        // categorical level/sector facets (Elementary/Middle/High,
        // Public/Private/Charter/Homeschool Resource) live in the
        // sf_school_type taxonomy instead — see inc/school-provisioning.php.
        $school_fields = array(
            'sf_grades_served'    => 'string',
            'sf_principal_name'   => 'string',
            'sf_boundary_url'     => 'string',
            'sf_feeder_pattern'   => 'string',
            'sf_notable_programs' => 'string',
        );

        foreach ($school_fields as $meta_key => $type) {
            register_post_meta('school', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
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

        // Source-attribution fields for RSS-imported Articles — see
        // Southforsyth_Rss_Provider and docs/data-integration-roadmap.md's
        // "excerpt + link only, never full reproduction" rule.
        $article_fields = array(
            'sf_source_url'       => 'string',
            'sf_source_published' => 'string',
        );

        foreach ($article_fields as $meta_key => $type) {
            register_post_meta('article', $meta_key, array(
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
