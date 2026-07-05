<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Traffic/road-conditions provider (GDOT / 511 Georgia, per
 * docs/data-integration-roadmap.md's GIS/open-data section). GDOT's feeds
 * generally require account registration and aren't confirmed/keyless like
 * the Weather provider's NWS API, so this reads from a configurable
 * endpoint + optional API key (Settings admin page) rather than
 * hardcoding an unconfirmed URL. Returns nothing until configured — the
 * homepage's traffic-placeholder.php component keeps showing its static
 * "coming soon" copy until this actually has data to serve.
 */
class Southforsyth_Traffic_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('traffic');
    }

    public function is_configured()
    {
        return (bool) get_option('southforsyth_traffic_feed_url');
    }

    public function search($query, array $args = array())
    {
        if (! $this->is_configured()) {
            return array();
        }

        return $this->cache('incidents_' . $query, 10 * MINUTE_IN_SECONDS, function () {
            $url = get_option('southforsyth_traffic_feed_url');
            $api_key = get_option('southforsyth_traffic_api_key');
            if ($api_key) {
                $url = add_query_arg('key', $api_key, $url);
            }

            $result = $this->http_get($url);
            return $result['events'] ?? (is_array($result) ? $result : array());
        });
    }

    public function fetch($id, array $args = array())
    {
        foreach ($this->search('') as $incident) {
            if (($incident['id'] ?? '') === $id) {
                return $incident;
            }
        }
        return null;
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        return array(
            'id'          => $raw['id'] ?? '',
            'description' => $raw['description'] ?? $raw['summary'] ?? '',
            'roadway'     => $raw['roadName'] ?? $raw['roadway'] ?? '',
            'severity'    => $raw['severity'] ?? '',
            'updated'     => $raw['lastUpdated'] ?? '',
        );
    }
}
