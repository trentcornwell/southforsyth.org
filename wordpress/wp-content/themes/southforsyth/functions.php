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
require_once get_template_directory() . '/inc/template-functions.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/performance.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/taxonomies.php';
require_once get_template_directory() . '/inc/meta.php';
require_once get_template_directory() . '/inc/queries.php';
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/architecture.php';
require_once get_template_directory() . '/inc/evergreen-content.php';
require_once get_template_directory() . '/inc/community-platform.php';
