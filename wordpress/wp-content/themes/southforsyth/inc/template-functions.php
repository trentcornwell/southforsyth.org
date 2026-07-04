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

if (! function_exists('southforsyth_get_post_type_archive_crumb')) {
    /**
     * Build an "<a>Plural Label</a>" breadcrumb link for a post type's
     * archive, or an empty string when the post type has no archive
     * definition (e.g. core 'post' and 'page'). Depends on the post type
     * definitions registered in inc/post-types.php.
     */
    function southforsyth_get_post_type_archive_crumb($post_type)
    {
        if (! function_exists('southforsyth_get_post_type_definitions')) {
            return '';
        }

        $definitions = southforsyth_get_post_type_definitions();
        if (empty($definitions[$post_type])) {
            return '';
        }

        $archive_link = get_post_type_archive_link($post_type);
        if (! $archive_link) {
            return '';
        }

        return '<a href="' . esc_url($archive_link) . '">' . esc_html($definitions[$post_type]['plural']) . '</a>';
    }
}

if (! function_exists('southforsyth_the_breadcrumbs')) {
    /**
     * Render the breadcrumb trail for the current request.
     *
     * This is the single canonical breadcrumb implementation for the theme —
     * template-parts/components/breadcrumbs.php calls it directly rather
     * than building its own trail, so there is only one place that knows
     * how to turn "where am I" into a crumb list. Covers: pages, regular
     * posts, every custom post type single (with its archive as the middle
     * crumb), post type archives, taxonomy archives (core and sf_*),
     * search results, and 404s.
     */
    function southforsyth_the_breadcrumbs()
    {
        if (is_front_page()) {
            return;
        }

        $items = array(
            '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'southforsyth') . '</a>',
        );

        if (is_page()) {
            $items[] = '<span>' . esc_html(get_the_title()) . '</span>';
        } elseif (is_singular()) {
            $archive_crumb = southforsyth_get_post_type_archive_crumb(get_post_type());
            if ($archive_crumb) {
                $items[] = $archive_crumb;
            }
            $items[] = '<span>' . esc_html(get_the_title()) . '</span>';
        } elseif (is_post_type_archive()) {
            $items[] = '<span>' . esc_html(post_type_archive_title('', false)) . '</span>';
        } elseif (is_category() || is_tag() || is_tax()) {
            $items[] = '<span>' . esc_html(single_term_title('', false)) . '</span>';
        } elseif (is_search()) {
            $items[] = '<span>' . sprintf(esc_html__('Search results for %s', 'southforsyth'), esc_html(get_search_query())) . '</span>';
        } elseif (is_404()) {
            $items[] = '<span>' . esc_html__('Page not found', 'southforsyth') . '</span>';
        }

        echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'southforsyth') . '"><ol class="breadcrumbs__list">';
        foreach ($items as $item) {
            echo '<li class="breadcrumbs__item">' . $item . '</li>';
        }
        echo '</ol></nav>';
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
