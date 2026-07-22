<?php

/**
 * Post meta for the community post types.
 *
 * Kept intentionally small: a shared "directory" field set (address, phone,
 * website, hours, lat/lng, source URL, last verified) covers most
 * location-based post types, events get their own date/time/venue fields,
 * articles get a source-attribution field pair for RSS-imported content,
 * schools get a small field group of their own (see below), and
 * `sf_featured` is a single boolean flag reused across post types so the
 * homepage can pull a cross-type "Popular Places" section without a
 * dedicated taxonomy. Fields are edited through WordPress's native Custom
 * Fields metabox — no plugin required.
 *
 * `sf_lat`/`sf_lng` were added alongside the Phase 1–10 platform-scaling
 * work specifically as the prerequisite docs/data-integration-roadmap.md
 * already flagged: "Geo meta fields don't exist yet... a prerequisite for
 * any GIS ingestion." They're consumed today by
 * Southforsyth_Openstreetmap_Provider and Southforsyth_Google_Places_Provider
 * (see inc/providers/) and are ready for the future "Interactive Maps"
 * system in inc/community-platform.php.
 *
 * `sf_source_url`/`sf_last_verified` were added to the shared directory
 * group for the Schools data-model work: every directory-style listing
 * benefits identically from "where did this come from" and "how current is
 * this" as a trust signal, not just schools — matching the same reasoning
 * `sf_lat`/`sf_lng` were added to the whole group rather than one post type.
 * `sf_source_url` already existed as a key registered for `article`
 * (RSS attribution, below); this just extends its registration to the
 * directory post types too — same key, same meaning ("where this content
 * came from"), no collision.
 *
 * `school`-specific fields (grades served, principal, attendance-zone/
 * boundary link, feeder pattern, notable programs) are a small group of
 * their own, the same pattern as the event and article groups below — see
 * docs/content-platform-architecture.md's "Post meta" section for the full
 * rationale per field.
 *
 * `sf_faqs` was added to the shared directory group for the ingestion-
 * framework work: a JSON-encoded array of {question, answer} pairs, empty
 * by default. Deliberately holds structure only, never generated content —
 * see docs/data-integration-roadmap.md's human-review rules. Rendered
 * through the existing template-parts/components/faq-block.php (which
 * already accepts an `items` array) via southforsyth_get_post_faqs() in
 * inc/queries.php — no separate FAQ system.
 *
 * `sf_census_*` fields are scoped to `neighborhood` only, fed by
 * Southforsyth_Census_Provider (see inc/providers/): numbers only (population,
 * median income, median age, the ACS data year), never descriptive prose,
 * matching this file's "kept intentionally small" rule and
 * docs/data-integration-roadmap.md's existing guidance that Census data is
 * "useful as descriptive background text, not as the primary content."
 *
 * Added for the real Forsyth County Schools import (see
 * Southforsyth_Forsyth_County_Provider):
 * - Shared directory group: `sf_city`/`sf_state`/`sf_zip` (structured geo
 *   alongside the existing single-string `sf_address`), `sf_district`, and
 *   `sf_south_forsyth_status` — a 3-value editorial workflow field
 *   (Confirmed South Forsyth / Needs Review / Outside Coverage), kept as meta rather than a taxonomy since it's
 *   a single mutually-exclusive status, not a browsable multi-tag facet —
 *   the same reasoning `sf_featured` is meta, not a taxonomy.
 * - School-only group: `sf_mascot`, `sf_school_colors`, `sf_mission`.
 *   `sf_mission`, when populated, is an official statement fetched from the
 *   school's own page with `sf_source_url` attribution — a sourced fact,
 *   not generated prose.
 *
 * Added for the geocoding + community-suggestion work:
 * - Shared directory group: `sf_geocode_provider`/`sf_geocode_place_id`/
 *   `sf_geocode_date`/`sf_geocode_confidence` (provenance for `sf_lat`/
 *   `sf_lng`, kept separate from them so "where the coordinates came from"
 *   survives independently of the coordinates themselves — see
 *   Southforsyth_Geocode_Command, inc/import/class-geocode-command.php);
 *   `sf_community_updated`/`sf_contributor_credit` (trust signals set only
 *   by an approved suggestion — see inc/community/class-suggestion-moderation.php).
 * - `sf_suggestion` fields (its own group, scoped to that post type only —
 *   see inc/post-types.php and inc/community/): everything a moderated
 *   correction needs to review and, if approved, apply — target post/field,
 *   the value at submission time, the suggested replacement, the
 *   submitter's explanation and optional identity, an abuse-prevention IP
 *   hash (never the raw IP), and the moderation audit trail
 *   (notes/approving moderator/resolution date). `sf_submitter_email` is
 *   registered here but intentionally never read by any public-facing
 *   template — see docs/data-integration-roadmap.md's suggestion-privacy
 *   note.
 *
 * Added for the deterministic geocoding match evaluator (replacing the
 * old importance-score threshold — see
 * inc/import/class-geocode-match-evaluator.php): `sf_geocode_match_explanation`
 * (human-readable, e.g. "House number, street, ZIP, and city all match"),
 * `sf_geocode_matched_address` (the geocoder's own formatted address for
 * the accepted/candidate result), `sf_geocode_candidate_lat`/
 * `sf_geocode_candidate_lng` (populated only for a `review`-class match —
 * a not-yet-trusted candidate, kept separate from `sf_lat`/`sf_lng` until
 * an editor explicitly accepts it in the School Editorial Review screen).
 * `sf_geocode_confidence` changed meaning in this pass: it now holds the
 * match class (`exact`/`strong`/`review`/`rejected`), not a raw
 * `importance` float — an intentional replacement of the old model, not a
 * relabeled threshold.
 *
 * Added for pilot publishing: `sf_published_by` (the user who ran the
 * publish action), `sf_published_date` — an audit pair matching the same
 * shape as the moderation audit trail above, so "who made this public and
 * when" is answerable the same way "who approved this suggestion and
 * when" already is.
 *
 * `sf_staff_directory_url`: a link to the school's own official staff
 * directory page, added to the school-only group. Deliberately a link, not
 * scraped/stored teacher contact data — individual staff emails are the
 * school's own published property and change constantly (turnover,
 * reassignment); sending users to the source stays accurate and avoids
 * republishing personal contact info without consent, which a stored,
 * theme-owned copy could not honestly guarantee stayed current or scoped.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_directory_meta_post_types')) {
    function southforsyth_get_directory_meta_post_types()
    {
        return array('restaurant', 'park', 'school', 'church', 'business', 'trail', 'community_resource');
    }
}

if (! function_exists('southforsyth_get_south_forsyth_status_options')) {
    /**
     * The 3 allowed values for sf_south_forsyth_status. "Needs Review" is
     * the required default for anything imported without corroboration —
     * see Southforsyth_Forsyth_County_Provider's classification logic and
     * docs/data-integration-roadmap.md.
     */
    function southforsyth_get_south_forsyth_status_options()
    {
        return array('Confirmed South Forsyth', 'Needs Review', 'Outside Coverage');
    }
}

if (! function_exists('southforsyth_get_featured_flag_post_types')) {
    function southforsyth_get_featured_flag_post_types()
    {
        return array('event', 'restaurant', 'park', 'neighborhood', 'business');
    }
}

if (! function_exists('southforsyth_register_post_meta')) {
    function southforsyth_register_post_meta()
    {
        $directory_fields = array(
            'sf_address'       => 'string',
            'sf_phone'         => 'string',
            'sf_website'       => 'string',
            'sf_hours'         => 'string',
            'sf_lat'           => 'string',
            'sf_lng'           => 'string',
            'sf_source_url'    => 'string',
            'sf_last_verified' => 'string',
            'sf_faqs'          => 'string',
            'sf_city'          => 'string',
            'sf_state'         => 'string',
            'sf_zip'           => 'string',
            'sf_district'      => 'string',
            'sf_south_forsyth_status' => 'string',
            'sf_coverage_decision_source' => 'string',
            'sf_coverage_decision_note'   => 'string',
            'sf_coverage_decision_date'   => 'string',
            'sf_coverage_decision_type'   => 'string',
            'sf_geocode_provider'   => 'string',
            'sf_geocode_place_id'   => 'string',
            'sf_geocode_date'       => 'string',
            'sf_geocode_confidence' => 'string',
            'sf_geocode_match_explanation' => 'string',
            'sf_geocode_matched_address'   => 'string',
            'sf_geocode_candidate_lat'     => 'string',
            'sf_geocode_candidate_lng'     => 'string',
            'sf_geocode_manually_verified' => 'string',
            'sf_geocode_waived'            => 'string',
            'sf_community_updated'   => 'string',
            'sf_contributor_credit'  => 'string',
            'sf_published_by'   => 'string',
            'sf_published_date' => 'string',
            'sf_school_readiness' => 'string',
            'sf_duplicate_warning' => 'string',
        );

        foreach (southforsyth_get_directory_meta_post_types() as $post_type) {
            foreach ($directory_fields as $meta_key => $type) {
                register_post_meta($post_type, $meta_key, array(
                    'type'         => $type,
                    'single'       => true,
                    'show_in_rest' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ));
            }
        }

        // School-specific fields — see the header comment above and
        // docs/content-platform-architecture.md's "Post meta" section.
        // sf_grades_served is a precise range (e.g. "PK-5"); the more
        // categorical level/sector facets (Elementary/Middle/High,
        // Public/Private/Charter/Homeschool Resource) live in the
        // sf_school_type taxonomy instead — see inc/school-provisioning.php.
        $school_fields = array(
            'sf_grades_served'      => 'string',
            'sf_principal_name'     => 'string',
            'sf_boundary_url'       => 'string',
            'sf_feeder_pattern'     => 'string',
            'sf_notable_programs'   => 'string',
            'sf_mascot'             => 'string',
            'sf_school_colors'      => 'string',
            'sf_mission'            => 'string',
            'sf_staff_directory_url' => 'string',
        );

        foreach ($school_fields as $meta_key => $type) {
            register_post_meta('school', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
        }

        // Census/ACS enrichment fields — see the header comment above and
        // Southforsyth_Census_Provider (inc/providers/).
        $census_fields = array(
            'sf_census_population'     => 'string',
            'sf_census_median_income'  => 'string',
            'sf_census_median_age'     => 'string',
            'sf_census_source_year'    => 'string',
        );

        foreach ($census_fields as $meta_key => $type) {
            register_post_meta('neighborhood', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
        }

        // Suggestion fields — see the header comment above and
        // inc/post-types.php's sf_suggestion registration. Not part of the
        // shared directory group: a suggestion is never itself a
        // directory-style listing.
        $suggestion_fields = array(
            'sf_target_post_id'        => 'integer',
            'sf_target_post_type'      => 'string',
            'sf_requested_field'       => 'string',
            'sf_current_value_snapshot' => 'string',
            'sf_suggested_value'       => 'string',
            'sf_explanation'           => 'string',
            'sf_source_url'            => 'string',
            'sf_submitter_name'        => 'string',
            'sf_submitter_email'       => 'string',
            'sf_ip_hash'               => 'string',
            'sf_moderator_notes'       => 'string',
            'sf_approving_moderator'   => 'integer',
            'sf_resolution_date'       => 'string',
            'sf_credit_consent'        => 'boolean',
        );

        foreach ($suggestion_fields as $meta_key => $type) {
            $args = array(
                'type'         => $type,
                'single'       => true,
                // Deliberately not show_in_rest: a suggestion carries a
                // submitter's (optional) name/email and an internal IP
                // hash — this stays out of the public REST API even
                // though the post type itself is already non-public.
                'show_in_rest' => false,
            );

            // Matches southforsyth_get_featured_flag_post_types()'s
            // registration below: string fields get sanitize_text_field,
            // integer/boolean fields rely on register_post_meta()'s own
            // type coercion instead, same as sf_featured.
            if ('string' === $type) {
                $args['sanitize_callback'] = 'sanitize_text_field';
            }

            register_post_meta('sf_suggestion', $meta_key, $args);
        }

        $event_fields = array(
            'sf_event_date'  => 'string',
            'sf_event_time'  => 'string',
            'sf_event_venue' => 'string',
        );

        foreach ($event_fields as $meta_key => $type) {
            register_post_meta('event', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
        }

        // Source-attribution fields for RSS-imported Articles — see
        // Southforsyth_Rss_Provider and docs/data-integration-roadmap.md's
        // "excerpt + link only, never full reproduction" rule.
        $article_fields = array(
            'sf_source_url'       => 'string',
            'sf_source_published' => 'string',
        );

        foreach ($article_fields as $meta_key => $type) {
            register_post_meta('article', $meta_key, array(
                'type'         => $type,
                'single'       => true,
                'show_in_rest' => true,
                'sanitize_callback' => 'sanitize_text_field',
            ));
        }

        foreach (southforsyth_get_featured_flag_post_types() as $post_type) {
            register_post_meta($post_type, 'sf_featured', array(
                'type'         => 'boolean',
                'single'       => true,
                'show_in_rest' => true,
                'default'      => false,
            ));
        }
    }
}

add_action('init', 'southforsyth_register_post_meta');
