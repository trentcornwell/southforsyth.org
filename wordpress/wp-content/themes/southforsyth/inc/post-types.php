<?php

/**
 * Custom post types for the South Forsyth community platform.
 *
 * Each post type is a real content model (not a planning placeholder) so the
 * homepage and archive/single templates can query live WordPress content as
 * soon as it is authored. Definitions are data-driven so the registration
 * logic stays in one place as new types are added.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_post_type_definitions')) {
    function southforsyth_get_post_type_definitions()
    {
        return array(
            'event' => array(
                'singular' => 'Event',
                'plural'   => 'Events',
                'slug'     => 'events',
                'icon'     => 'dashicons-calendar-alt',
                'description' => 'Community events, markets, and recurring programming.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/event-card',
            ),
            'restaurant' => array(
                'singular' => 'Restaurant',
                'plural'   => 'Restaurants',
                'slug'     => 'restaurants',
                'icon'     => 'dashicons-food',
                'description' => 'Local restaurants, coffee shops, and dining spots.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/restaurant-card',
            ),
            'park' => array(
                'singular' => 'Park',
                'plural'   => 'Parks',
                'slug'     => 'parks',
                'icon'     => 'dashicons-palmtree',
                'description' => 'Parks, trails, playgrounds, and outdoor recreation.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/park-card',
            ),
            'neighborhood' => array(
                'singular' => 'Neighborhood',
                'plural'   => 'Neighborhoods',
                'slug'     => 'neighborhoods',
                'icon'     => 'dashicons-admin-multisite',
                'description' => 'Neighborhood profiles covering lifestyle, schools, and amenities.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/neighborhood-card',
            ),
            'school' => array(
                'singular' => 'School',
                'plural'   => 'Schools',
                'slug'     => 'schools',
                'icon'     => 'dashicons-welcome-learn-more',
                'description' => 'Local schools and education resources.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/school-card',
            ),
            'church' => array(
                'singular' => 'Church',
                'plural'   => 'Churches',
                'slug'     => 'churches',
                'icon'     => 'dashicons-buddicons-groups',
                'description' => 'Faith communities, service times, and volunteer programs.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/church-card',
            ),
            'business' => array(
                'singular' => 'Business',
                'plural'   => 'Businesses',
                'slug'     => 'business-directory',
                'icon'     => 'dashicons-store',
                'description' => 'Local businesses and service providers.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/directory-card',
            ),
            'guide' => array(
                'singular' => 'Guide',
                'plural'   => 'Guides',
                'slug'     => 'guides',
                'icon'     => 'dashicons-book-alt',
                'description' => 'Evergreen local guides such as best parks, moving guides, and seasonal roundups.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'card_template' => 'template-parts/components/guide-card',
            ),
            'article' => array(
                'singular' => 'Article',
                'plural'   => 'Articles',
                'slug'     => 'articles',
                'icon'     => 'dashicons-media-document',
                'description' => 'Editorial stories and local news, separate from the evergreen guide library.',
                'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
                'taxonomies' => array('category', 'post_tag'),
                'card_template' => 'template-parts/components/article-card',
            ),
        );
    }
}

if (! function_exists('southforsyth_register_post_types')) {
    function southforsyth_register_post_types()
    {
        foreach (southforsyth_get_post_type_definitions() as $post_type => $definition) {
            register_post_type($post_type, array(
                'labels' => array(
                    'name'          => __($definition['plural'], 'southforsyth'),
                    'singular_name' => __($definition['singular'], 'southforsyth'),
                    'add_new_item'  => sprintf(__('Add New %s', 'southforsyth'), $definition['singular']),
                    'edit_item'     => sprintf(__('Edit %s', 'southforsyth'), $definition['singular']),
                    'all_items'     => sprintf(__('All %s', 'southforsyth'), $definition['plural']),
                    'search_items'  => sprintf(__('Search %s', 'southforsyth'), $definition['plural']),
                    'not_found'     => sprintf(__('No %s found', 'southforsyth'), strtolower($definition['plural'])),
                ),
                'description'  => $definition['description'],
                'public'       => true,
                'show_in_rest' => true,
                'show_in_menu' => true,
                'menu_icon'    => $definition['icon'],
                'supports'     => $definition['supports'],
                'taxonomies'   => $definition['taxonomies'] ?? array(),
                'has_archive'  => $definition['slug'],
                'rewrite'      => array('slug' => $definition['slug'], 'with_front' => false),
                'menu_position' => 20,
            ));
        }
    }
}

add_action('init', 'southforsyth_register_post_types');

if (! function_exists('southforsyth_get_card_template_for_post_type')) {
    function southforsyth_get_card_template_for_post_type($post_type)
    {
        $definitions = southforsyth_get_post_type_definitions();

        return $definitions[$post_type]['card_template'] ?? '';
    }
}
