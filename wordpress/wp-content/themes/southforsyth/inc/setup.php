<?php

/**
 * Core theme setup and support registration.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_setup')) {
    function southforsyth_setup()
    {
        load_theme_textdomain('southforsyth', get_template_directory() . '/languages');

        add_theme_support('automatic-feed-links');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('responsive-embeds');
        add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets'));
        add_theme_support('custom-logo', array(
            'height'      => 120,
            'width'       => 320,
            'flex-height' => true,
            'flex-width'  => true,
            'header-text' => array('site-title', 'site-description'),
        ));
        add_theme_support('editor-styles');
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');

        add_editor_style('assets/css/main.css');
        add_image_size('southforsyth-card', 900, 600, true);
        add_image_size('southforsyth-hero', 1600, 900, true);
    }
}

add_action('after_setup_theme', 'southforsyth_setup');
