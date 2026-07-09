<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * NCES (National Center for Education Statistics) public school data
 * provider. Same posture as Southforsyth_Forsyth_County_Provider: no
 * hardcoded or guessed endpoint. NCES publishes its Common Core of Data as
 * downloadable files rather than a simple keyless REST API, and this theme's
 * "don't scrape aggressively, prefer official/public sources" rule means we
 * don't wire this up to a third-party wrapper API we haven't confirmed and
 * vetted — so, like Forsyth County's, this provider reads from a
 * configurable endpoint URL (Settings admin page) and returns nothing until
 * one is set. Whoever configures it should point it at a real, confirmed
 * NCES (or a vetted pass-through of NCES CCD data) endpoint.
 *
 * Intended use: enrichment/verification, not narrative content. NCES data is
 * administrative (grade span, public/private status, locale, lat/lng) —
 * useful for cross-checking sf_grades_served and sf_school_type against an
 * independent federal source, not for writing a school's description. Every
 * NCES record has a stable per-school NCES ID, which is a strong source_id
 * for Southforsyth_Duplicate_Detector.
 *
 * Georgia DOE / GOSA (Governor's Office of Student Achievement) publishes
 * similar public data at the state level, also without a confirmed simple
 * API today. Rather than add a second near-identical dormant provider class
 * for it, treat it the same way docs/data-integration-roadmap.md treats the
 * Chamber of Commerce's member list: a secondary manual cross-check source,
 * not an automated feed, until it's actually needed.
 */
class Southforsyth_Nces_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('nces');
    }

    public function is_configured()
    {
        return (bool) get_option('southforsyth_nces_feed_url');
    }

    public function search($query, array $args = array())
    {
        if (! $this->is_configured()) {
            return array();
        }

        return $this->cache('search_' . $query, DAY_IN_SECONDS, function () use ($query) {
            $result = $this->http_get(add_query_arg('q', rawurlencode($query), get_option('southforsyth_nces_feed_url')));
            return is_array($result) ? $result : array();
        });
    }

    public function fetch($id, array $args = array())
    {
        $items = $this->search('');
        foreach ($items as $item) {
            if (($item['nces_id'] ?? $item['id'] ?? '') === $id) {
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

        return Southforsyth_Normalizer::shape(array(
            'source'     => $this->get_slug(),
            'source_id'  => $raw['nces_id'] ?? $raw['id'] ?? '',
            'post_type'  => 'school',
            'title'      => $raw['name'] ?? '',
            'meta'       => array(
                'sf_address'       => $raw['address'] ?? '',
                'sf_lat'           => $raw['lat'] ?? '',
                'sf_lng'           => $raw['lng'] ?? '',
                'sf_grades_served' => $raw['grades'] ?? '',
                'sf_source_url'    => $raw['source_url'] ?? '',
            ),
        ));
    }
}
