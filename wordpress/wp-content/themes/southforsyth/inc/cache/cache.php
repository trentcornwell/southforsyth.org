<?php

/**
 * Cache layer loader (Phase 5). Required unconditionally from functions.php
 * — cheap (one class definition) and needed on both front-end and admin.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-cache-manager.php';
