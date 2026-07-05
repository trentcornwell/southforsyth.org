<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Unified search across every platform post type (the twelve registered in
 * inc/post-types.php) plus core posts/pages, returning results in the same
 * normalized card shape (eyebrow/title/description/link) that
 * southforsyth_post_to_card() already produces — see inc/queries.php.
 *
 * This is additive, not a replacement for search.php: WordPress's default
 * search already includes every public post type automatically (none of
 * ours set exclude_from_search), so search.php keeps working exactly as it
 * does today. This service exists for anything that needs normalized,
 * programmatic results instead of a WP_Query loop — e.g. a future AJAX
 * live-search box or a unified `/wp-json/southforsyth/v1/search` endpoint
 * (see docs/platform-architecture.md, "Search architecture").
 */
class Southforsyth_Search_Service
{
    /**
     * @param string $term
     * @param array  $args  'post_types' (array, default: every platform type),
     *                      'per_page' (int, default 20), 'page' (int, default 1).
     * @return array{results: array, total: int} normalized results + total found
     */
    public static function search($term, array $args = array())
    {
        $post_types = $args['post_types'] ?? self::searchable_post_types();

        $query = new WP_Query(array(
            's'              => $term,
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => $args['per_page'] ?? 20,
            'paged'          => $args['page'] ?? 1,
            'ignore_sticky_posts' => true,
        ));

        $results = array();
        foreach ($query->posts as $post) {
            $results[] = self::normalize_result($post);
        }

        return array(
            'results' => $results,
            'total'   => (int) $query->found_posts,
        );
    }

    /** @return string[] every registered platform post type, plus core post/page */
    public static function searchable_post_types()
    {
        $platform_types = function_exists('southforsyth_get_post_type_definitions')
            ? array_keys(southforsyth_get_post_type_definitions())
            : array();

        return array_merge($platform_types, array('post', 'page'));
    }

    private static function normalize_result(WP_Post $post)
    {
        $definitions = function_exists('southforsyth_get_post_type_definitions') ? southforsyth_get_post_type_definitions() : array();
        $type_label = $definitions[$post->post_type]['singular'] ?? ucfirst($post->post_type);

        return array(
            'type'        => $post->post_type,
            'eyebrow'     => $type_label,
            'title'       => get_the_title($post),
            'description' => function_exists('southforsyth_get_excerpt') ? southforsyth_get_excerpt($post->ID, 20) : wp_trim_words($post->post_content, 20),
            'link'        => get_permalink($post),
        );
    }
}
