<?php

/**
 * Performance-focused hooks for Core Web Vitals and lean asset delivery.
 * TODO: Add Redis/object-cache aware transients as traffic grows.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_remove_emojis')) {
    function southforsyth_remove_emojis()
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }
}

add_action('init', 'southforsyth_remove_emojis');

if (! function_exists('southforsyth_jpeg_quality')) {
    function southforsyth_jpeg_quality()
    {
        return 82;
    }
}

add_filter('jpeg_quality', 'southforsyth_jpeg_quality');

if (! function_exists('southforsyth_add_lazy_loading')) {
    function southforsyth_add_lazy_loading($content)
    {
        return preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy" decoding="async">', $content);
    }
}

add_filter('the_content', 'southforsyth_add_lazy_loading', 20);

if (! function_exists('southforsyth_attachment_image_attributes')) {
    function southforsyth_attachment_image_attributes($attr, $attachment, $size)
    {
        if (empty($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        if (empty($attr['decoding'])) {
            $attr['decoding'] = 'async';
        }

        return $attr;
    }
}

add_filter('wp_get_attachment_image_attributes', 'southforsyth_attachment_image_attributes', 10, 3);
