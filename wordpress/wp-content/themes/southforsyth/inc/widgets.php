<?php

/**
 * Widget area registration for the theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_register_widgets')) {
    function southforsyth_register_widgets()
    {
        register_sidebar(array(
            'name'          => __('Main Sidebar', 'southforsyth'),
            'id'            => 'sidebar-1',
            'before_widget' => '<section class="widget">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget__title">',
            'after_title'   => '</h2>',
        ));

        register_sidebar(array(
            'name'          => __('Footer Column One', 'southforsyth'),
            'id'            => 'footer-1',
            'before_widget' => '<section class="widget widget--footer">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget__title">',
            'after_title'   => '</h2>',
        ));

        register_sidebar(array(
            'name'          => __('Footer Column Two', 'southforsyth'),
            'id'            => 'footer-2',
            'before_widget' => '<section class="widget widget--footer">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget__title">',
            'after_title'   => '</h2>',
        ));
    }
}

add_action('widgets_init', 'southforsyth_register_widgets');
