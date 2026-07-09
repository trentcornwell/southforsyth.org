<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Minimum-field validation before an imported record may leave the queue —
 * enforces docs/data-integration-roadmap.md's "Content quality rules":
 * "event needs a date and a venue at minimum; business/restaurant/church/
 * school need an address; article needs more than a one-sentence excerpt."
 * A record that fails validation is logged and never reaches
 * wp_insert_post() — see Southforsyth_Importer::import().
 *
 * Schools additionally require an official website (see the Schools
 * data-model plan) — "official website" is one of the core fields that
 * section is built around, and a real accredited school realistically
 * always has one.
 */
class Southforsyth_Data_Validator
{
    public static function validate(array $record)
    {
        $errors = array();

        if (empty($record['title'])) {
            $errors[] = 'Missing title.';
        }

        if (empty($record['post_type'])) {
            $errors[] = 'Missing target post_type.';
        }

        switch ($record['post_type'] ?? '') {
            case 'event':
                if (empty($record['meta']['sf_event_date'])) {
                    $errors[] = 'Event is missing a date.';
                }
                if (empty($record['meta']['sf_event_venue'])) {
                    $errors[] = 'Event is missing a venue.';
                }
                break;

            case 'restaurant':
            case 'business':
            case 'church':
            case 'trail':
            case 'community_resource':
                if (empty($record['meta']['sf_address'])) {
                    $errors[] = ucfirst(str_replace('_', ' ', $record['post_type'])) . ' is missing an address.';
                }
                break;

            case 'school':
                if (empty($record['meta']['sf_address'])) {
                    $errors[] = 'School is missing an address.';
                }
                if (empty($record['meta']['sf_website'])) {
                    $errors[] = 'School is missing an official website.';
                }
                break;

            case 'article':
                if (empty($record['excerpt']) || str_word_count($record['excerpt']) < 8) {
                    $errors[] = 'Article excerpt is missing or too short to publish as-is.';
                }
                break;
        }

        return array(
            'valid'  => empty($errors),
            'errors' => $errors,
        );
    }
}
