<?php

/**
 * Admin area loader (Phase 6). Only required when is_admin() — see
 * functions.php — so none of this parses on a front-end page request,
 * matching the theme's existing performance philosophy.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-admin-menu.php';

Southforsyth_Admin_Menu::register();
