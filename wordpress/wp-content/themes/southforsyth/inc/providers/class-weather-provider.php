<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * National Weather Service (api.weather.gov) provider. Fully functional
 * without any API key or secret — the NWS API is free and keyless, only
 * requiring a descriptive User-Agent, which Southforsyth_Abstract_Provider's
 * http_get() already sends. This directly fulfills the TODO already left in
 * template-parts/components/weather-placeholder.php.
 */
class Southforsyth_Weather_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('weather');
    }

    /**
     * NWS has no free-text search — "search" here means "look up the
     * forecast office/grid for a lat,lng", the mandatory first step before
     * fetch() can retrieve an actual forecast.
     */
    public function search($query, array $args = array())
    {
        list($lat, $lng) = $this->parse_coords($query);
        if (! $lat || ! $lng) {
            return array();
        }

        return $this->cache('points_' . $lat . ',' . $lng, DAY_IN_SECONDS, function () use ($lat, $lng) {
            $points = $this->http_get('https://api.weather.gov/points/' . $lat . ',' . $lng);
            return $points['properties'] ?? array();
        });
    }

    /** Fetch the forecast for a lat,lng pair (e.g. "34.2098,-84.1557"). */
    public function fetch($id, array $args = array())
    {
        $point = $this->search($id);
        if (empty($point['forecast'])) {
            return null;
        }

        return $this->cache('forecast_' . $id, HOUR_IN_SECONDS, function () use ($point) {
            return $this->http_get($point['forecast']);
        });
    }

    /** Maps NWS forecast periods onto a simple, template-ready shape. */
    public function normalize($raw)
    {
        $periods = $raw['properties']['periods'] ?? array();
        if (empty($periods)) {
            return array();
        }

        return array_map(function ($period) {
            return array(
                'name'          => $period['name'] ?? '',
                'temperature'   => $period['temperature'] ?? null,
                'temperature_unit' => $period['temperatureUnit'] ?? 'F',
                'short_forecast' => $period['shortForecast'] ?? '',
                'detailed_forecast' => $period['detailedForecast'] ?? '',
                'is_daytime'    => ! empty($period['isDaytime']),
            );
        }, $periods);
    }

    private function parse_coords($query)
    {
        if (is_array($query) && isset($query['lat'], $query['lng'])) {
            return array($query['lat'], $query['lng']);
        }

        if (is_string($query) && strpos($query, ',') !== false) {
            return array_map('trim', explode(',', $query, 2));
        }

        return array(null, null);
    }
}
