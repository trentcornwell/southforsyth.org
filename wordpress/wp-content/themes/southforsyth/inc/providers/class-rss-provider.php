<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * RSS/Atom provider built on WordPress core's own feed parser (SimplePie),
 * so no external library is needed. Per docs/data-integration-roadmap.md's
 * "RSS/news sources" rules: this returns excerpt + link only, never full
 * article content — republishing a full feed item is a copyright problem,
 * not just a style preference. The human-written framing sentence that
 * policy requires before publishing is an editorial step for the importer/
 * reviewer, not something this provider can supply.
 */
class Southforsyth_Rss_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('rss');
    }

    /** $query is a feed URL; "search" here means "list recent items". */
    public function search($query, array $args = array())
    {
        return $this->cache('feed_' . $query, HOUR_IN_SECONDS, function () use ($query, $args) {
            if (! function_exists('fetch_feed')) {
                require_once ABSPATH . WPINC . '/feed.php';
            }

            $feed = fetch_feed($query);
            if (is_wp_error($feed)) {
                return array();
            }

            $limit = $args['limit'] ?? 10;
            $items = $feed->get_items(0, $limit);

            return array_map(function ($item) {
                return array(
                    'guid'      => $item->get_id(),
                    'title'     => $item->get_title(),
                    'link'      => $item->get_permalink(),
                    'published' => $item->get_date('c'),
                    'excerpt'   => wp_trim_words(wp_strip_all_tags($item->get_description()), 40, '…'),
                );
            }, $items);
        });
    }

    /** No single-item lookup for a feed — fetch() returns the whole list. */
    public function fetch($id, array $args = array())
    {
        return $this->search($id, $args);
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $raw['guid'] ?? '',
            'post_type' => 'article',
            'title'     => $raw['title'] ?? '',
            'excerpt'   => $raw['excerpt'] ?? '',
            // Deliberately no 'content' — see the class doc comment above.
            'meta'      => array(
                'sf_source_url'       => $raw['link'] ?? '',
                'sf_source_published' => $raw['published'] ?? '',
            ),
        ));
    }
}
