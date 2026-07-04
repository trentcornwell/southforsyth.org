<?php

/**
 * SEO helpers for schema, social metadata, and metadata output.
 * These helpers are intentionally modular so future AI search and rich results can be extended safely.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_meta_description')) {
    function southforsyth_get_meta_description()
    {
        if (is_singular()) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $excerpt = wp_strip_all_tags(get_the_excerpt($post));
                if ($excerpt) {
                    return wp_trim_words($excerpt, 24, '...');
                }
            }
        }

        if (is_front_page()) {
            return get_bloginfo('description');
        }

        return sprintf(__('Explore South Forsyth, Georgia with local news, resources, and community information.', 'southforsyth'));
    }
}

if (! function_exists('southforsyth_output_seo_tags')) {
    function southforsyth_output_seo_tags()
    {
        $description = southforsyth_get_meta_description();
        $canonical   = wp_get_canonical_url();
        $image       = get_template_directory_uri() . '/assets/images/hero-placeholder.svg';
        $title       = wp_get_document_title();

        if ($description) {
            echo '<meta name="description" content="' . esc_attr($description) . '" />' . PHP_EOL;
        }

        if ($canonical) {
            echo '<link rel="canonical" href="' . esc_url($canonical) . '" />' . PHP_EOL;
        }

        echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . PHP_EOL;
        echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . PHP_EOL;
        echo '<meta property="og:type" content="website" />' . PHP_EOL;
        echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '" />' . PHP_EOL;
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . PHP_EOL;
        echo '<meta name="twitter:card" content="summary_large_image" />' . PHP_EOL;
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . PHP_EOL;
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . PHP_EOL;
        echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . PHP_EOL;
    }
}

add_action('wp_head', 'southforsyth_output_seo_tags', 1);

if (! function_exists('southforsyth_get_organization_schema')) {
    function southforsyth_get_organization_schema()
    {
        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => get_bloginfo('name'),
            'url'      => home_url('/'),
            'logo'     => get_template_directory_uri() . '/assets/images/hero-placeholder.svg',
            'address'  => array(
                '@type' => 'PostalAddress',
                'addressLocality' => 'South Forsyth',
                'addressRegion'   => 'GA',
                'addressCountry'  => 'US',
            ),
        );
    }
}

if (! function_exists('southforsyth_get_local_business_schema')) {
    function southforsyth_get_local_business_schema()
    {
        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => get_bloginfo('name'),
            'url'      => home_url('/'),
            'areaServed' => 'South Forsyth, GA',
            'description' => southforsyth_get_meta_description(),
        );
    }
}

if (! function_exists('southforsyth_get_breadcrumb_schema')) {
    function southforsyth_get_breadcrumb_schema()
    {
        $crumbs = array();
        $position = 1;
        $crumbs[] = array('@type' => 'ListItem', 'position' => $position++, 'name' => __('Home', 'southforsyth'), 'item' => home_url('/'));

        if (is_single()) {
            $crumbs[] = array('@type' => 'ListItem', 'position' => $position++, 'name' => get_the_title(), 'item' => get_permalink());
        }

        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => $crumbs,
        );
    }
}

if (! function_exists('southforsyth_get_article_schema')) {
    function southforsyth_get_article_schema()
    {
        $post = get_post();
        if (! $post instanceof WP_Post) {
            return array();
        }

        return array(
            '@context' => 'https://schema.org',
            '@type'    => 'Article',
            'headline' => get_the_title($post),
            'author'   => array('@type' => 'Organization', 'name' => get_bloginfo('name')),
            'datePublished' => get_the_date(DATE_W3C, $post),
            'dateModified'  => get_the_modified_date(DATE_W3C, $post),
            'mainEntityOfPage' => get_permalink($post),
            'publisher' => southforsyth_get_organization_schema(),
        );
    }
}

if (! function_exists('southforsyth_render_schema')) {
    function southforsyth_render_schema()
    {
        $schemas = array();

        if (is_front_page()) {
            $schemas[] = southforsyth_get_organization_schema();
            $schemas[] = southforsyth_get_local_business_schema();
        }

        if (is_single()) {
            $schemas[] = southforsyth_get_article_schema();
        }

        if (! empty($schemas)) {
            echo '<script type="application/ld+json">' . wp_json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . PHP_EOL;
        }
    }
}

add_action('wp_head', 'southforsyth_render_schema', 2);
