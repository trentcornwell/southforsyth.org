<?php

/**
 * Taxonomies for the South Forsyth community platform.
 *
 * `sf_area` is the one cross-cutting taxonomy: it is attached to every
 * location-bound post type (events, restaurants, parks, schools, churches,
 * businesses) so any of them can be tagged with a neighborhood/area and
 * cross-linked from a Neighborhood profile page. Every other taxonomy here
 * is scoped to a single post type. Names are prefixed with `sf_` to avoid
 * colliding with plugin or core taxonomies as the site grows.
 *
 * Geographic hierarchy (added for the Phase 1–10 platform-scaling work —
 * see docs/platform-architecture.md, "Taxonomies"): `sf_region` > `sf_city`
 * > `sf_area` > `sf_community`, broadest to narrowest. `sf_area` (Halcyon,
 * Vickery, Windermere, ...) already existed and keeps its existing meaning;
 * the three new tiers exist for content whose scope genuinely doesn't fit
 * that one level — e.g. a regional festival (`sf_region`), a listing best
 * described as "near Cumming" rather than a specific area (`sf_city`), or a
 * specific subdivision/HOA within one area (`sf_community`). Deliberately
 * NOT added: separate "Category"/"Tags"/"Business Type"/"Cuisine"/
 * "Denomination"/"Park Type" taxonomies — those already exist (core
 * `category`/`post_tag`, or `sf_business_category`/`sf_cuisine`/
 * `sf_denomination`/`sf_park_amenity` below); registering parallel
 * taxonomies with the same meaning would confuse editors choosing between
 * two "cuisine-like" fields, not make the taxonomy layer more reusable.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_taxonomy_definitions')) {
    function southforsyth_get_taxonomy_definitions()
    {
        return array(
            'sf_area' => array(
                'label'       => 'Areas',
                'singular'    => 'Area',
                'post_types'  => array('event', 'restaurant', 'park', 'school', 'church', 'business', 'trail', 'community_resource'),
                'hierarchical' => false,
                'slug'        => 'area',
            ),
            'sf_event_category' => array(
                'label'       => 'Event Categories',
                'singular'    => 'Event Category',
                'post_types'  => array('event'),
                'hierarchical' => true,
                'slug'        => 'event-category',
            ),
            'sf_cuisine' => array(
                'label'       => 'Cuisines',
                'singular'    => 'Cuisine',
                'post_types'  => array('restaurant'),
                'hierarchical' => false,
                'slug'        => 'cuisine',
            ),
            'sf_business_category' => array(
                'label'       => 'Business Categories',
                'singular'    => 'Business Category',
                'post_types'  => array('business'),
                'hierarchical' => true,
                'slug'        => 'business-category',
            ),
            'sf_denomination' => array(
                'label'       => 'Denominations',
                'singular'    => 'Denomination',
                'post_types'  => array('church'),
                'hierarchical' => false,
                'slug'        => 'denomination',
            ),
            'sf_school_type' => array(
                'label'       => 'School Types',
                'singular'    => 'School Type',
                'post_types'  => array('school'),
                'hierarchical' => true,
                'slug'        => 'school-type',
            ),
            'sf_park_amenity' => array(
                'label'       => 'Park Amenities',
                'singular'    => 'Amenity',
                'post_types'  => array('park', 'trail'),
                'hierarchical' => false,
                'slug'        => 'park-amenity',
            ),
            'sf_lifestyle_tag' => array(
                'label'       => 'Lifestyle Tags',
                'singular'    => 'Lifestyle Tag',
                'post_types'  => array('neighborhood'),
                'hierarchical' => false,
                'slug'        => 'lifestyle',
            ),
            'sf_guide_topic' => array(
                'label'       => 'Guide Topics',
                'singular'    => 'Guide Topic',
                'post_types'  => array('guide'),
                'hierarchical' => true,
                'slug'        => 'guide-topic',
            ),
            // --- Added for the Phase 1–10 platform-scaling work; see the
            // "Geographic hierarchy" and dedup notes in this file's header
            // comment for why these seven (and not a full parallel set of
            // Category/Tags/Business Type/Cuisine/Denomination/Park Type
            // taxonomies) were added. ---
            'sf_region' => array(
                'label'       => 'Regions',
                'singular'    => 'Region',
                'post_types'  => array('event', 'guide', 'article'),
                'hierarchical' => false,
                'slug'        => 'region',
            ),
            'sf_city' => array(
                'label'       => 'Cities',
                'singular'    => 'City',
                'post_types'  => array('event', 'restaurant', 'business', 'church', 'school'),
                'hierarchical' => false,
                'slug'        => 'city',
            ),
            'sf_community' => array(
                'label'       => 'Communities',
                'singular'    => 'Community',
                'post_types'  => array('neighborhood', 'business', 'restaurant', 'church'),
                'hierarchical' => false,
                'slug'        => 'community',
            ),
            'sf_audience' => array(
                'label'       => 'Audiences',
                'singular'    => 'Audience',
                'post_types'  => array('event', 'guide', 'article', 'topic', 'community_resource'),
                'hierarchical' => false,
                'slug'        => 'audience',
            ),
            'sf_interest' => array(
                'label'       => 'Interests',
                'singular'    => 'Interest',
                'post_types'  => array('event', 'guide', 'article', 'topic'),
                'hierarchical' => false,
                'slug'        => 'interest',
            ),
            'sf_school_district' => array(
                'label'       => 'School Districts',
                'singular'    => 'School District',
                'post_types'  => array('school', 'neighborhood'),
                'hierarchical' => false,
                'slug'        => 'school-district',
            ),
            'sf_topic' => array(
                'label'       => 'Topics',
                'singular'    => 'Topic',
                'post_types'  => array('guide', 'article'),
                'hierarchical' => false,
                'slug'        => 'topic',
            ),
            // Added for the ingestion-framework work: community_resource is
            // deliberately reused for sports organizations, government
            // facilities, libraries, etc. rather than splitting each into
            // its own post type (see docs/platform-architecture.md, "How a
            // new content type plugs in") — this taxonomy is what makes
            // that reuse actually distinguishable, the same role
            // sf_school_type plays for schools. Terms are seeded by
            // inc/resource-provisioning.php.
            'sf_resource_type' => array(
                'label'       => 'Resource Types',
                'singular'    => 'Resource Type',
                'post_types'  => array('community_resource'),
                'hierarchical' => true,
                'slug'        => 'resource-type',
            ),
        );
    }
}

if (! function_exists('southforsyth_register_taxonomies')) {
    function southforsyth_register_taxonomies()
    {
        foreach (southforsyth_get_taxonomy_definitions() as $taxonomy => $definition) {
            register_taxonomy($taxonomy, $definition['post_types'], array(
                'labels' => array(
                    'name'          => __($definition['label'], 'southforsyth'),
                    'singular_name' => __($definition['singular'], 'southforsyth'),
                    'search_items'  => sprintf(__('Search %s', 'southforsyth'), $definition['label']),
                    'all_items'     => sprintf(__('All %s', 'southforsyth'), $definition['label']),
                ),
                'public'       => true,
                'show_in_rest' => true,
                'hierarchical' => $definition['hierarchical'],
                'rewrite'      => array('slug' => $definition['slug'], 'with_front' => false),
            ));
        }
    }
}

add_action('init', 'southforsyth_register_taxonomies');
