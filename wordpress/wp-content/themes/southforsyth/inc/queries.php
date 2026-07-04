<?php

/**
 * Homepage and archive query helpers.
 *
 * Every helper here normalizes results into the same "card" shape used by
 * the template-parts/components card partials (eyebrow, title, description,
 * link) and falls back to a realistic placeholder array when a post type
 * has no published content yet. This lets the homepage stay meaningful
 * before any content exists, while automatically switching to live
 * WordPress content the moment posts are published — no template changes
 * required later.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_post_to_card')) {
    function southforsyth_post_to_card($post, $eyebrow = '')
    {
        if (! $eyebrow) {
            $terms = get_the_terms($post, 'category');
            if (empty($terms) || is_wp_error($terms)) {
                $definitions = southforsyth_get_post_type_definitions();
                $eyebrow = $definitions[$post->post_type]['singular'] ?? 'Local guide';
            } else {
                $eyebrow = $terms[0]->name;
            }
        }

        return array(
            'eyebrow'     => $eyebrow,
            'title'       => get_the_title($post),
            'description' => southforsyth_get_excerpt($post->ID, 20),
            'link'        => get_permalink($post),
        );
    }
}

if (! function_exists('southforsyth_get_latest_items')) {
    function southforsyth_get_latest_items($post_type, $count = 3, $fallback = array(), $eyebrow = '')
    {
        $query = new WP_Query(array(
            'post_type'      => $post_type,
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
        ));

        if (! $query->have_posts()) {
            return $fallback;
        }

        $cards = array();
        foreach ($query->posts as $post) {
            $cards[] = southforsyth_post_to_card($post, $eyebrow);
        }

        return $cards;
    }
}

if (! function_exists('southforsyth_get_featured_places')) {
    function southforsyth_get_featured_places($count = 6, $fallback = array())
    {
        $query = new WP_Query(array(
            'post_type'      => southforsyth_get_featured_flag_post_types(),
            'posts_per_page' => $count,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
            'ignore_sticky_posts' => true,
            'meta_key'       => 'sf_featured',
            'meta_value'     => '1',
        ));

        if (! $query->have_posts()) {
            return $fallback;
        }

        $cards = array();
        foreach ($query->posts as $post) {
            $cards[] = southforsyth_post_to_card($post);
        }

        return $cards;
    }
}
