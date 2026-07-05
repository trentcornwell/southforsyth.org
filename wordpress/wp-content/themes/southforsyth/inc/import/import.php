<?php

/**
 * Import engine loader (Phase 4). Required unconditionally from
 * functions.php — these are class definitions only; the custom-table
 * installer is hooked, not run eagerly (see below).
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-import-install.php';
require_once __DIR__ . '/class-normalizer.php';
require_once __DIR__ . '/class-slug-generator.php';
require_once __DIR__ . '/class-data-validator.php';
require_once __DIR__ . '/class-duplicate-detector.php';
require_once __DIR__ . '/class-image-downloader.php';
require_once __DIR__ . '/class-import-queue.php';
require_once __DIR__ . '/class-import-logger.php';
require_once __DIR__ . '/class-importer.php';

// Same idempotent, version-gated pattern as inc/page-provisioning.php:
// creates the queue/log tables on theme activation, and self-heals on the
// next wp-admin request if they're somehow missing (e.g. restored from a
// backup taken before this schema existed).
add_action('after_switch_theme', array('Southforsyth_Import_Install', 'maybe_install'));
add_action('admin_init', array('Southforsyth_Import_Install', 'maybe_install'));
