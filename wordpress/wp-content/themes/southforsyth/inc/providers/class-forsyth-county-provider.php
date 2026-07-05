<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Forsyth County government/schools/parks/library provider. Per
 * docs/data-integration-roadmap.md, the county has no confirmed open data
 * or RSS API today — most of this category "will require a periodic manual
 * or semi-manual pull rather than a live feed at first." Rather than
 * scraping county web pages (their ToS/robots.txt must be reviewed before
 * that's ever built — see the roadmap's "Caution" note), this provider
 * reads from a configurable endpoint URL (Settings admin page) so that if
 * the county ever publishes a real feed, this starts working immediately
 * with no code change. Until a URL is configured, it returns nothing.
 */
class Southforsyth_Forsyth_County_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('forsyth_county');
    }

    public function is_configured()
    {
        return (bool) get_option('southforsyth_forsyth_county_feed_url');
    }

    public function search($query, array $args = array())
    {
        if (! $this->is_configured()) {
            return array();
        }

        return $this->cache('search_' . $query, HOUR_IN_SECONDS, function () {
            $result = $this->http_get(get_option('southforsyth_forsyth_county_feed_url'));
            return is_array($result) ? $result : array();
        });
    }

    public function fetch($id, array $args = array())
    {
        $items = $this->search('');
        foreach ($items as $item) {
            if (($item['id'] ?? '') === $id) {
                return $item;
            }
        }
        return null;
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        // Left generic on purpose: the county could eventually feed schools,
        // parks, or events through this same endpoint. The importer decides
        // the target post_type based on admin configuration per source, not
        // this provider guessing from the payload shape.
        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $raw['id'] ?? '',
            'title'     => $raw['title'] ?? $raw['name'] ?? '',
            'content'   => $raw['description'] ?? '',
        ));
    }
}
