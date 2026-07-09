<?php

/**
 * Provider architecture loader (Phase 1). Required unconditionally from
 * functions.php — these are class definitions and stub/lightweight
 * integrations only; nothing here does real work until called.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/interface-data-provider.php';
require_once __DIR__ . '/class-provider-abstract.php';
require_once __DIR__ . '/class-google-places-provider.php';
require_once __DIR__ . '/class-openstreetmap-provider.php';
require_once __DIR__ . '/class-forsyth-county-provider.php';
require_once __DIR__ . '/class-nces-provider.php';
require_once __DIR__ . '/class-weather-provider.php';
require_once __DIR__ . '/class-traffic-provider.php';
require_once __DIR__ . '/class-rss-provider.php';
require_once __DIR__ . '/class-events-provider.php';
require_once __DIR__ . '/class-provider-registry.php';

if (! function_exists('southforsyth_get_providers')) {
    /** @return Southforsyth_Data_Provider[] */
    function southforsyth_get_providers()
    {
        return Southforsyth_Provider_Registry::all();
    }
}

if (! function_exists('southforsyth_get_provider')) {
    /** @return Southforsyth_Data_Provider|null */
    function southforsyth_get_provider($slug)
    {
        return Southforsyth_Provider_Registry::get($slug);
    }
}
