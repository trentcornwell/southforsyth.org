<?php

/**
 * Reusable presentation helpers for layouts and template parts.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_svg')) {
    function southforsyth_get_svg($name, $class = '')
    {
        $path = get_template_directory() . '/assets/icons/' . sanitize_file_name($name) . '.svg';
        if (! file_exists($path)) {
            return '';
        }

        $svg = file_get_contents($path);
        if ($class) {
            $svg = preg_replace('/<svg\b/', '<svg class="' . esc_attr($class) . '"', $svg, 1);
        }

        return $svg;
    }
}

if (! function_exists('southforsyth_the_breadcrumbs')) {
    function southforsyth_the_breadcrumbs()
    {
        if (is_front_page()) {
            return;
        }

        $items = array();
        $items[] = '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'southforsyth') . '</a>';

        if (is_category() || is_tag()) {
            $items[] = single_term_title('', false);
        } elseif (is_single()) {
            $items[] = '<span>' . get_the_title() . '</span>';
        } elseif (is_page()) {
            $items[] = '<span>' . get_the_title() . '</span>';
        } elseif (is_search()) {
            $items[] = '<span>' . sprintf(__('Search results for %s', 'southforsyth'), get_search_query()) . '</span>';
        }

        if (! empty($items)) {
            echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol class="breadcrumbs__list">';
            foreach ($items as $item) {
                echo '<li class="breadcrumbs__item">' . $item . '</li>';
            }
            echo '</ol></nav>';
        }
    }
}

if (! function_exists('southforsyth_get_excerpt')) {
    function southforsyth_get_excerpt($post_id = 0, $length = 24)
    {
        $post = get_post($post_id);
        if (! $post instanceof WP_Post) {
            return '';
        }

        $excerpt = wp_strip_all_tags(get_the_excerpt($post));
        if (! $excerpt) {
            $excerpt = wp_strip_all_tags($post->post_content);
        }

        return wp_trim_words($excerpt, $length, '…');
    }
}
