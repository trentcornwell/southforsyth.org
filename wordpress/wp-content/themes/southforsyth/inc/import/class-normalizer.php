<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Defines the one common shape every provider's normalize() method maps
 * onto, and every Importer/Validator/Duplicate_Detector reads from. Keeping
 * one shape (rather than each provider inventing its own) is what lets
 * Southforsyth_Importer stay provider-agnostic — see
 * docs/platform-architecture.md ("Import system").
 */
class Southforsyth_Normalizer
{
    /**
     * Build a record in the common import shape, filling in any field the
     * caller didn't supply with a safe default rather than leaving it
     * unset (so downstream code can rely on every key existing).
     */
    public static function shape(array $fields = array())
    {
        return array_merge(array(
            'source'      => '',   // provider slug, e.g. 'events_ics'
            'source_id'   => '',   // the provider's own stable ID for this record
            'post_type'   => '',   // target post type, e.g. 'event'
            'title'       => '',
            'content'     => '',
            'excerpt'     => '',
            'meta'        => array(),   // sf_* post meta, e.g. sf_address/sf_event_date
            'taxonomies'  => array(),   // taxonomy => array of term names
            'image_url'   => '',        // source image to download, if rights-confirmed (see Image_Downloader)
            'license'     => '',        // attribution/license note for GIS/RSS-derived content
            'recurring'   => false,
            'raw'         => array(),   // the provider's original, unmodified payload for this record — see Southforsyth_Importer::import(), which stores this as _sf_import_raw for attribution/debugging/re-processing
        ), $fields);
    }

    /** Trim + collapse whitespace on every string field in a normalized record. */
    public static function clean(array $record)
    {
        foreach (array('title', 'content', 'excerpt') as $key) {
            if (! empty($record[$key]) && is_string($record[$key])) {
                $record[$key] = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($record[$key])));
            }
        }

        return $record;
    }
}
