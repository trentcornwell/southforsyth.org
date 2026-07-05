<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Dedupe primarily on (source, source_id) per
 * docs/data-integration-roadmap.md's "Duplicate prevention" section — "the
 * `_sf_import_source` + `_sf_import_source_id`" pattern already documented
 * there — with a content-hash fallback for sources without a stable ID.
 */
class Southforsyth_Duplicate_Detector
{
    const SOURCE_META_KEY = '_sf_import_source';
    const SOURCE_ID_META_KEY = '_sf_import_source_id';
    const HASH_META_KEY = '_sf_import_hash';

    /** @return int|null existing post ID, or null if no duplicate found */
    public static function find_existing($source, $source_id, $post_type, $hash = null)
    {
        if ($source_id) {
            $posts = get_posts(array(
                'post_type'   => $post_type,
                'post_status' => 'any',
                'numberposts' => 1,
                'fields'      => 'ids',
                'meta_query'  => array(
                    array('key' => self::SOURCE_META_KEY, 'value' => $source),
                    array('key' => self::SOURCE_ID_META_KEY, 'value' => $source_id),
                ),
            ));

            if (! empty($posts)) {
                return (int) $posts[0];
            }
        }

        if ($hash) {
            $posts = get_posts(array(
                'post_type'   => $post_type,
                'post_status' => 'any',
                'numberposts' => 1,
                'fields'      => 'ids',
                'meta_key'    => self::HASH_META_KEY,
                'meta_value'  => $hash,
            ));

            if (! empty($posts)) {
                return (int) $posts[0];
            }
        }

        return null;
    }

    /** Hash of normalized title + date + venue/address — the "sources without a reliable ID" fallback. */
    public static function hash(array $record)
    {
        $location = $record['meta']['sf_event_venue'] ?? $record['meta']['sf_address'] ?? '';
        return md5(strtolower(trim(($record['title'] ?? '') . '|' . ($record['meta']['sf_event_date'] ?? '') . '|' . $location)));
    }
}
