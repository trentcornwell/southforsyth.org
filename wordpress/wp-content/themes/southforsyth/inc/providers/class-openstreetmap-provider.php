<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * OpenStreetMap (Nominatim) geocoding provider. Functional without an API
 * key, but Nominatim's usage policy caps free use at ~1 request/second and
 * requires a genuine, identifying User-Agent (already sent by
 * Southforsyth_Abstract_Provider::http_get()) — heavy production use should
 * either self-host Nominatim or move to a paid geocoder. Every result is
 * cached for a full day specifically to stay well under that rate limit.
 */
class Southforsyth_Openstreetmap_Provider extends Southforsyth_Abstract_Provider
{
    const ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    public function __construct()
    {
        parent::__construct('openstreetmap');
    }

    /** Free-text geocoding search, e.g. "Halcyon, Forsyth County, GA". */
    public function search($query, array $args = array())
    {
        return $this->cache('search_' . $query, DAY_IN_SECONDS, function () use ($query, $args) {
            $url = add_query_arg(array(
                'q'              => rawurlencode($query),
                'format'         => 'json',
                'limit'          => $args['limit'] ?? 5,
                'countrycodes'   => 'us',
                'addressdetails' => 1,
            ), self::ENDPOINT);

            $results = $this->http_get($url);
            return is_array($results) ? $results : array();
        });
    }

    /** Nominatim's "fetch by ID" is really just a single-result search. */
    public function fetch($id, array $args = array())
    {
        $results = $this->search($id, array_merge($args, array('limit' => 1)));
        return $results[0] ?? null;
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $raw['place_id'] ?? '',
            'title'     => $raw['display_name'] ?? '',
            'meta'      => array(
                'sf_address' => $raw['display_name'] ?? '',
                'sf_lat'     => $raw['lat'] ?? '',
                'sf_lng'     => $raw['lon'] ?? '',
            ),
            'license' => 'Data © OpenStreetMap contributors, ODbL',
        ));
    }
}
