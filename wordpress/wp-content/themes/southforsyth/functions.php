<?php

/**
 * Theme bootstrap.
 * Keeps the theme modular so it can scale from a local community homepage into a larger content platform.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/menus.php';
require_once get_template_directory() . '/inc/widgets.php';
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/helpers.php';
