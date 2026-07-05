<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Google Places API provider (businesses, restaurants). STUB: Places
 * requires a billing-enabled API key, which this project doesn't have and
 * which must never be invented or hardcoded (see CLAUDE.md's "never commit
 * secrets"). search()/fetch() return empty results rather than fabricating
 * data until a real key is entered on the Settings admin page and read back
 * via get_option('southforsyth_google_places_api_key').
 */
class Southforsyth_Google_Places_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('google_places');
    }

    public function is_configured()
    {
        return (bool) get_option('southforsyth_google_places_api_key');
    }

    public function search($query, array $args = array())
    {
        if (! $this->is_configured()) {
            return array();
        }

        return $this->cache('search_' . $query . '_' . wp_json_encode($args), 15 * MINUTE_IN_SECONDS, function () use ($query, $args) {
            $url = add_query_arg(array(
                'query' => rawurlencode($query),
                'key'   => get_option('southforsyth_google_places_api_key'),
            ), 'https://maps.googleapis.com/maps/api/place/textsearch/json');

            $result = $this->http_get($url);
            return $result['results'] ?? array();
        });
    }

    public function fetch($id, array $args = array())
    {
        if (! $this->is_configured()) {
            return null;
        }

        return $this->cache('place_' . $id, HOUR_IN_SECONDS, function () use ($id) {
            $url = add_query_arg(array(
                'place_id' => $id,
                'key'      => get_option('southforsyth_google_places_api_key'),
            ), 'https://maps.googleapis.com/maps/api/place/details/json');

            $result = $this->http_get($url);
            return $result['result'] ?? null;
        });
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $raw['place_id'] ?? '',
            'title'     => $raw['name'] ?? '',
            'meta'      => array(
                'sf_address' => $raw['formatted_address'] ?? '',
                'sf_phone'   => $raw['formatted_phone_number'] ?? '',
                'sf_website' => $raw['website'] ?? '',
                'sf_lat'     => $raw['geometry']['location']['lat'] ?? '',
                'sf_lng'     => $raw['geometry']['location']['lng'] ?? '',
            ),
        ));
    }
}
