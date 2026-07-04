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
                'post_types'  => array('event', 'restaurant', 'park', 'school', 'church', 'business'),
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
                'post_types'  => array('park'),
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
