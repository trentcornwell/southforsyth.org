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

if (! function_exists('southforsyth_get_primary_nav_items')) {
    /**
     * The default primary navigation, resolved against real hub URLs (see
     * southforsyth_get_hub_url() in inc/hub-content.php). Used as the
     * fallback menu below so the header reads like a real site immediately,
     * without requiring an admin to build a menu in Appearance > Menus
     * first — an admin-assigned "primary" menu still overrides this the
     * moment one exists, since fallback_cb only runs when no menu is set.
     */
    function southforsyth_get_primary_nav_items()
    {
        return array(
            array('label' => 'Things To Do', 'key' => 'things-to-do'),
            array('label' => 'Events', 'key' => 'event'),
            array('label' => 'Eat & Drink', 'key' => 'restaurant'),
            array('label' => 'Parks', 'key' => 'park'),
            array('label' => 'Schools', 'key' => 'school'),
            array('label' => 'Churches', 'key' => 'church'),
            array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
            array('label' => 'Directory', 'key' => 'business'),
            array('label' => 'New Residents', 'key' => 'new-resident-guide'),
        );
    }
}

if (! function_exists('southforsyth_primary_nav_fallback')) {
    function southforsyth_primary_nav_fallback($args = array())
    {
        $menu_class = ! empty($args['menu_class']) ? $args['menu_class'] : '';

        echo '<ul class="' . esc_attr($menu_class) . '">' . PHP_EOL;
        foreach (southforsyth_get_primary_nav_items() as $item) {
            $url = function_exists('southforsyth_get_hub_url') ? southforsyth_get_hub_url($item['key']) : '#';
            echo '<li><a href="' . esc_url($url) . '">' . esc_html($item['label']) . '</a></li>' . PHP_EOL;
        }
        echo '</ul>' . PHP_EOL;
    }
}
