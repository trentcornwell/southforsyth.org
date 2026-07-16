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
        $school_decision = null;

        if ('school' === ($record['post_type'] ?? '')) {
            $school_decision = Southforsyth_School_Import_Safety::analyze_record($record);
            $record = $school_decision['record'];

            if ('skip' === $school_decision['action']) {
                Southforsyth_Import_Logger::warning(
                    $record['source'] ?: 'unknown',
                    'Skipped school import: ' . $school_decision['reason'],
                    array('record' => $record, 'matched_post_id' => $school_decision['post_id'])
                );
                return new WP_Error('sf_import_school_skipped', $school_decision['reason']);
            }
        }

        $validation = Southforsyth_Data_Validator::validate($record);

        if (! $validation['valid']) {
            $message = 'Validation failed: ' . implode(' ', $validation['errors']);
            Southforsyth_Import_Logger::error($record['source'] ?: 'unknown', $message, $record);
            return new WP_Error('sf_import_invalid', $message);
        }

        $hash = Southforsyth_Duplicate_Detector::hash($record);
        if ($school_decision) {
            $existing_id = 'update' === $school_decision['action'] ? (int) $school_decision['post_id'] : 0;
        } else {
            $existing_id = Southforsyth_Duplicate_Detector::find_existing($record['source'], $record['source_id'], $record['post_type'], $hash);
        }

        $postarr = array(
            'post_type'    => $record['post_type'],
            'post_title'   => wp_strip_all_tags($record['title']),
            'post_content' => $record['content'],
            'post_excerpt' => $record['excerpt'],
        );

        if ($existing_id) {
            $postarr['ID'] = $existing_id;
            // Deliberately NOT defaulting post_status here. Every new
            // import still always lands as draft (below) — but re-running
            // an import against a post that's since been published (or
            // moved to pending, etc.) must never silently drag it back to
            // draft. Only touch status on update if the caller explicitly
            // asks for a specific one.
            if (isset($args['status'])) {
                $postarr['post_status'] = $args['status'];
            }
            $post_id = wp_update_post($postarr, true);
        } else {
            $postarr['post_status'] = $args['status'] ?? 'draft';
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

        if ($school_decision && 'school' === $record['post_type']) {
            delete_post_meta($post_id, Southforsyth_School_Import_Safety::DUPLICATE_WARNING_META_KEY);
            Southforsyth_Import_Logger::info($record['source'] ?: 'unknown', 'School duplicate decision: ' . $school_decision['reason'], array('post_id' => $post_id));
        }

        // The provider's original, unmodified payload — internal/underscore-
        // prefixed like the source-attribution meta above, so it never shows
        // in the Custom Fields UI. Kept as postmeta rather than a new table:
        // one small JSON blob per content post, not the high-volume
        // operational-event data the queue/log tables exist for (see
        // docs/platform-architecture.md, "Why custom tables").
        if (! empty($record['raw'])) {
            update_post_meta($post_id, '_sf_import_raw', wp_json_encode($record['raw']));
        }

        foreach ($record['meta'] as $meta_key => $meta_value) {
            if ('' !== $meta_value && null !== $meta_value) {
                if ($school_decision && in_array($meta_key, array(
                    'sf_south_forsyth_status',
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY,
                    Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY,
                ), true)) {
                    $existing_decision_type = get_post_meta($post_id, Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY, true);
                    if ('manual' === $existing_decision_type) {
                        continue;
                    }
                }

                if ($school_decision && 'sf_south_forsyth_status' === $meta_key) {
                    $existing_status = Southforsyth_School_Import_Safety::normalize_coverage_status(get_post_meta($post_id, $meta_key, true));
                    $incoming_status = Southforsyth_School_Import_Safety::normalize_coverage_status($meta_value);
                    $meta_value = $incoming_status;
                }
                update_post_meta($post_id, $meta_key, sanitize_text_field($meta_value));
            }
        }

        foreach ($record['taxonomies'] as $taxonomy => $terms) {
            if (! empty($terms) && taxonomy_exists($taxonomy)) {
                wp_set_object_terms($post_id, $terms, $taxonomy);
            }
        }

        if ($school_decision && 'school' === $record['post_type']) {
            update_post_meta($post_id, Southforsyth_School_Import_Safety::READY_META_KEY, Southforsyth_School_Import_Safety::readiness($post_id)['label']);
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
