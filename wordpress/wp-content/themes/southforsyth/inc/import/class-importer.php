<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Orchestrates one normalized record from any provider into a draft/pending
 * WordPress post: validate -> dedupe -> slug -> insert/update -> tag source
 * meta -> (optionally) download image -> log. Nothing here writes directly
 * to 'publish' — see docs/data-integration-roadmap.md's "Import queue
 * strategy", rule 1. This class is intentionally provider-agnostic: it only
 * ever reads the common shape from Southforsyth_Normalizer, never a
 * provider-specific field.
 */
class Southforsyth_Importer
{
    /**
     * @param array $record A normalized record (Southforsyth_Normalizer::shape()).
     * @param array $args   'status' ('draft'|'pending', default 'draft'),
     *                      'rights_confirmed' (bool, for image download).
     * @return int|WP_Error The resulting post ID, or a WP_Error on validation/insert failure.
     */
    public static function import(array $record, array $args = array())
    {
        $record = Southforsyth_Normalizer::clean($record);
        $validation = Southforsyth_Data_Validator::validate($record);

        if (! $validation['valid']) {
            $message = 'Validation failed: ' . implode(' ', $validation['errors']);
            Southforsyth_Import_Logger::error($record['source'] ?: 'unknown', $message, $record);
            return new WP_Error('sf_import_invalid', $message);
        }

        $hash = Southforsyth_Duplicate_Detector::hash($record);
        $existing_id = Southforsyth_Duplicate_Detector::find_existing($record['source'], $record['source_id'], $record['post_type'], $hash);

        $postarr = array(
            'post_type'    => $record['post_type'],
            'post_title'   => wp_strip_all_tags($record['title']),
            'post_content' => $record['content'],
            'post_excerpt' => $record['excerpt'],
            'post_status'  => $args['status'] ?? 'draft',
        );

        if ($existing_id) {
            $postarr['ID'] = $existing_id;
            $post_id = wp_update_post($postarr, true);
        } else {
            $postarr['post_name'] = Southforsyth_Slug_Generator::unique_slug($record['title'], $record['post_type']);
            $post_id = wp_insert_post($postarr, true);
        }

        if (is_wp_error($post_id)) {
            Southforsyth_Import_Logger::error($record['source'] ?: 'unknown', 'Post save failed: ' . $post_id->get_error_message(), $record);
            return $post_id;
        }

        update_post_meta($post_id, Southforsyth_Duplicate_Detector::SOURCE_META_KEY, $record['source']);
        update_post_meta($post_id, Southforsyth_Duplicate_Detector::SOURCE_ID_META_KEY, $record['source_id']);
        update_post_meta($post_id, Southforsyth_Duplicate_Detector::HASH_META_KEY, $hash);
        update_post_meta($post_id, '_sf_import_fetched_at', current_time('mysql'));

        foreach ($record['meta'] as $meta_key => $meta_value) {
            if ('' !== $meta_value && null !== $meta_value) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($meta_value));
            }
        }

        foreach ($record['taxonomies'] as $taxonomy => $terms) {
            if (! empty($terms) && taxonomy_exists($taxonomy)) {
                wp_set_object_terms($post_id, $terms, $taxonomy);
            }
        }

        if (! empty($record['image_url'])) {
            Southforsyth_Image_Downloader::download_and_attach(
                $post_id,
                $record['image_url'],
                ! empty($args['rights_confirmed']),
                $record['license'] ?? ''
            );
        }

        Southforsyth_Import_Logger::info(
            $record['source'] ?: 'unknown',
            ($existing_id ? 'Updated' : 'Created') . ' ' . $record['post_type'] . ' #' . $post_id . ': ' . $record['title'],
            array('post_id' => $post_id)
        );

        return $post_id;
    }

    /** Pulls the next queued job (if any) and imports it. */
    public static function process_next()
    {
        $job = Southforsyth_Import_Queue::pop();
        if (! $job) {
            return null;
        }

        $result = self::import($job['payload']);

        if (is_wp_error($result)) {
            Southforsyth_Import_Queue::mark_failed($job['id']);
        } else {
            Southforsyth_Import_Queue::mark_done($job['id']);
        }

        return $result;
    }
}
