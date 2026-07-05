<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Central registry of every data provider, keyed by slug. Adding a new
 * provider means: create its class file, require it from providers.php,
 * and add one line to self::definitions() below — see
 * docs/platform-architecture.md ("How to add a provider").
 */
class Southforsyth_Provider_Registry
{
    private static $instances = array();

    private static function definitions()
    {
        return array(
            'google_places'  => 'Southforsyth_Google_Places_Provider',
            'openstreetmap'  => 'Southforsyth_Openstreetmap_Provider',
            'forsyth_county' => 'Southforsyth_Forsyth_County_Provider',
            'weather'        => 'Southforsyth_Weather_Provider',
            'traffic'        => 'Southforsyth_Traffic_Provider',
            'rss'            => 'Southforsyth_Rss_Provider',
            'events_ics'     => 'Southforsyth_Events_Provider',
        );
    }

    /** @return Southforsyth_Data_Provider|null */
    public static function get($slug)
    {
        if (isset(self::$instances[$slug])) {
            return self::$instances[$slug];
        }

        $definitions = self::definitions();
        if (empty($definitions[$slug]) || ! class_exists($definitions[$slug])) {
            return null;
        }

        self::$instances[$slug] = new $definitions[$slug]();
        return self::$instances[$slug];
    }

    /** @return Southforsyth_Data_Provider[] every registered provider, instantiated */
    public static function all()
    {
        $providers = array();
        foreach (array_keys(self::definitions()) as $slug) {
            $providers[$slug] = self::get($slug);
        }
        return $providers;
    }
}
