<?php

/**
 * Menu registration for the theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_register_menus')) {
    function southforsyth_register_menus()
    {
        register_nav_menus(array(
            'primary' => __('Primary Navigation', 'southforsyth'),
            'footer'  => __('Footer Navigation', 'southforsyth'),
            'utility' => __('Utility Navigation', 'southforsyth'),
        ));
    }
}

add_action('after_setup_theme', 'southforsyth_register_menus');
