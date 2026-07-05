<?php

/**
 * Search architecture loader (Phase 8). Required unconditionally — a single
 * lightweight class definition.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-search-service.php';

if (! function_exists('southforsyth_search')) {
    function southforsyth_search($term, array $args = array())
    {
        return Southforsyth_Search_Service::search($term, $args);
    }
}
