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

        if (! $excerpt && 'school' === $post->post_type) {
            $excerpt = southforsyth_get_school_factual_summary($post_id);
        }

        return $excerpt ? wp_trim_words($excerpt, $length, '…') : '';
    }
}

if (! function_exists('southforsyth_get_school_factual_summary')) {
    /**
     * One-sentence factual fallback for a school with no real excerpt/
     * content, built only from stored, sourced fields (level/sector
     * taxonomy, sf_grades_served, sf_city) — never a generic placeholder
     * and never an invented fact. Returns '' if too little is known to say
     * anything factual (e.g. no level/sector term assigned yet), so the
     * caller can render nothing rather than an empty sentence.
     *
     * Deliberately uses the school's real mailing city, not the site's
     * "South Forsyth" editorial coverage label — sf_south_forsyth_status is
     * a classification decision, not a verified fact about the school (see
     * docs/data-integration-roadmap.md, "South Forsyth classification
     * policy"), so it never gets folded into generated content text.
     */
    function southforsyth_get_school_factual_summary($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
        $terms = (! empty($terms) && ! is_wp_error($terms)) ? $terms : array();

        $level_words = array(
            'Elementary' => 'elementary school',
            'Middle'     => 'middle school',
            'High'       => 'high school',
            'K-8'        => 'K-8 school',
        );
        $level = '';
        foreach ($level_words as $key => $word) {
            if (in_array($key, $terms, true)) {
                $level = $word;
                break;
            }
        }

        $sectors = array('Public', 'Private', 'Charter', 'Homeschool Resource');
        $sector = '';
        foreach ($sectors as $key) {
            if (in_array($key, $terms, true)) {
                $sector = $key;
                break;
            }
        }

        if (! $level && ! $sector) {
            return '';
        }

        $descriptor = trim(strtolower($sector) . ' ' . ($level ?: 'school'));
        $article = preg_match('/^[aeiou]/i', $descriptor) ? 'an' : 'a';
        $district = get_post_meta($post_id, 'sf_district', true);

        $sentence = sprintf('%s is %s %s', get_the_title($post_id), $article, $descriptor);

        $grades = get_post_meta($post_id, 'sf_grades_served', true);
        if ($grades) {
            $sentence .= sprintf(' serving grades %s', $grades);
        }

        $city = get_post_meta($post_id, 'sf_city', true);
        if ($city) {
            $sentence .= sprintf(' in %s', $city);
        }

        $sentence .= $district ? sprintf(', part of %s.', $district) : '.';

        return $sentence;
    }
}
