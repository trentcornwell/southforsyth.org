<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Shared school-specific import safety rules.
 *
 * The generic importer stays provider-agnostic, but schools need stricter
 * behavior than other directory types: never overwrite a published profile,
 * update only a confident draft match, and surface ambiguous matches for
 * human review instead of trying to merge them automatically.
 */
class Southforsyth_School_Import_Safety
{
    const READY_META_KEY = 'sf_school_readiness';
    const DUPLICATE_WARNING_META_KEY = 'sf_duplicate_warning';
    const GEOCODE_MANUAL_META_KEY = 'sf_geocode_manually_verified';
    const COVERAGE_CONFIRMED = 'Confirmed South Forsyth';
    const COVERAGE_NEEDS_REVIEW = 'Needs Review';
    const COVERAGE_OUTSIDE = 'Outside Coverage';
    const COVERAGE_DECISION_SOURCE_META_KEY = 'sf_coverage_decision_source';
    const COVERAGE_DECISION_NOTE_META_KEY = 'sf_coverage_decision_note';
    const COVERAGE_DECISION_DATE_META_KEY = 'sf_coverage_decision_date';
    const COVERAGE_DECISION_TYPE_META_KEY = 'sf_coverage_decision_type';

    public static function normalize_whitespace($value)
    {
        return is_string($value) ? trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($value))) : $value;
    }

    public static function normalize_url($url)
    {
        $url = trim((string) $url);
        if ('' === $url) {
            return '';
        }

        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return esc_url_raw($url);
    }

    public static function normalize_phone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (11 === strlen($digits) && '1' === $digits[0]) {
            $digits = substr($digits, 1);
        }

        if (10 === strlen($digits)) {
            return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
        }

        return self::normalize_whitespace($phone);
    }

    public static function normalize_school_name($name)
    {
        $name = strtolower(self::normalize_whitespace($name));
        $name = preg_replace('/\b(high school|middle school|elementary school|academy|school)\b/', '', $name);
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $name));
    }

    public static function normalize_official_school_identity($name)
    {
        $name = strtolower(self::normalize_whitespace($name));
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $name));
    }

    public static function official_display_name($name, $level_label)
    {
        $name = self::normalize_whitespace($name);
        $label = strtolower(self::normalize_whitespace($level_label));

        $suffix = '';
        if (false !== strpos($label, 'elementary')) {
            $suffix = 'Elementary School';
        } elseif (false !== strpos($label, 'middle')) {
            $suffix = 'Middle School';
        } elseif (false !== strpos($label, 'high')) {
            $suffix = 'High School';
        }

        if ('' === $name || '' === $suffix) {
            return $name;
        }

        if (preg_match('/\bacademy\b/i', $name)) {
            return $name;
        }

        if (preg_match('/\b' . preg_quote($suffix, '/') . '$/i', $name)) {
            return $name;
        }

        return $name . ' ' . $suffix;
    }

    public static function normalize_coverage_status($status)
    {
        $status = self::normalize_whitespace($status);
        if (self::COVERAGE_CONFIRMED === $status || self::COVERAGE_NEEDS_REVIEW === $status || self::COVERAGE_OUTSIDE === $status) {
            return $status;
        }

        if ('Outside South Forsyth' === $status) {
            return self::COVERAGE_OUTSIDE;
        }

        if ('Possibly South Forsyth' === $status) {
            return self::COVERAGE_NEEDS_REVIEW;
        }

        return self::COVERAGE_NEEDS_REVIEW;
    }

    /**
     * Adopted editorial school coverage list. This is deliberately keyed by
     * normalized official name rather than WordPress post ID so it survives
     * imports and environment changes. Stable official source identities can
     * be added to source_ids without changing the classifier.
     */
    public static function approved_school_coverage_allowlist()
    {
        $official_names = array(
            'Big Creek Elementary School',
            'Brandywine Elementary School',
            'Brookwood Elementary School',
            'Daves Creek Elementary School',
            'Haw Creek Elementary School',
            'Johns Creek Elementary School',
            'Midway Elementary School',
            'Settles Bridge Elementary School',
            'Sharon Elementary School',
            'Shiloh Point Elementary School',
            'Vickery Creek Elementary School',
            'DeSana Middle School',
            'Lakeside Middle School',
            'Piney Grove Middle School',
            'Riverwatch Middle School',
            'South Forsyth Middle School',
            'Vickery Creek Middle School',
            'Denmark High School',
            'Lambert High School',
            'South Forsyth High School',
        );
        $allowlist = array();

        foreach ($official_names as $official_name) {
            $allowlist[self::normalize_official_school_identity($official_name)] = array(
                'official_name' => $official_name,
                'source_ids' => array(),
                'status' => self::COVERAGE_CONFIRMED,
                'decision_type' => 'editorial_configuration',
                'decision_source' => 'SouthForsyth.org approved school coverage list',
                'decision_note' => 'Included in the adopted SouthForsyth.org editorial school coverage area',
                'reason' => 'Matched the adopted SouthForsyth.org editorial school coverage allowlist.',
            );
        }

        return $allowlist;
    }

    /** Backward-compatible name used by reports/tests and older call sites. */
    public static function coverage_allowlist()
    {
        return self::approved_school_coverage_allowlist();
    }

    /**
     * Explicit editorial decisions that must be applied before preserving a
     * legacy stored value. This repairs older automatic Outside Coverage
     * values without weakening the separate manual-override rule generally.
     */
    public static function editorial_review_decisions()
    {
        $decisions = array();
        $needs_review = array(
            'new hope elementary',
            'whitlow elementary',
            'hendricks middle',
            'alliance academy for innovation',
        );
        foreach ($needs_review as $name) {
            $decisions[$name] = array(
                'status' => self::COVERAGE_NEEDS_REVIEW,
                'decision_type' => 'editorial_configuration',
                'decision_source' => 'SouthForsyth.org coverage review',
                'decision_note' => 'Coverage is uncertain; human review and conclusive official evidence are required.',
                'reason' => 'Explicit editorial review decision: uncertain coverage; human review required.',
            );
        }

        return $decisions;
    }

    public static function classify_coverage(array $record, $existing_status = '')
    {
        $meta = $record['meta'] ?? array();
        $existing_status = self::normalize_coverage_status($existing_status);
        $existing_decision_type = self::normalize_whitespace($meta[self::COVERAGE_DECISION_TYPE_META_KEY] ?? '');

        $title = self::normalize_whitespace($record['title'] ?? '');
        $source_identities = array_filter(array_map(array(__CLASS__, 'normalize_url'), array(
            $record['source_id'] ?? '',
            $meta['_sf_import_source_id'] ?? '',
            $meta['sf_source_url'] ?? '',
        )));

        foreach (self::approved_school_coverage_allowlist() as $name_key => $decision) {
            $source_match = ! empty(array_intersect($source_identities, $decision['source_ids']));
            $name_match = self::normalize_official_school_identity($title) === $name_key;
            if ($source_match || $name_match) {
                return array(
                    'status' => $decision['status'],
                    'decision_type' => $decision['decision_type'],
                    'decision_source' => $decision['decision_source'],
                    'decision_note' => $decision['decision_note'],
                    'reasons' => array($decision['reason']),
                );
            }
        }

        if ('manual' === $existing_decision_type && in_array($existing_status, array(self::COVERAGE_CONFIRMED, self::COVERAGE_NEEDS_REVIEW, self::COVERAGE_OUTSIDE), true)) {
            return array(
                'status' => $existing_status,
                'decision_type' => 'manual',
                'decision_source' => self::normalize_whitespace($meta[self::COVERAGE_DECISION_SOURCE_META_KEY] ?? 'Manual editorial classification'),
                'decision_note' => self::normalize_whitespace($meta[self::COVERAGE_DECISION_NOTE_META_KEY] ?? 'Existing manual editorial coverage status preserved.'),
                'reasons' => array('Existing editorial coverage status preserved.'),
            );
        }

        foreach (self::editorial_review_decisions() as $name_key => $decision) {
            if (preg_match('/\b' . preg_quote($name_key, '/') . '(?: school)?\b/i', strtolower($title))) {
                return array(
                    'status' => $decision['status'],
                    'decision_type' => $decision['decision_type'],
                    'decision_source' => $decision['decision_source'],
                    'decision_note' => $decision['decision_note'],
                    'reasons' => array($decision['reason']),
                );
            }
        }

        $street = self::normalize_whitespace($meta['sf_address'] ?? '');
        $haystack = strtolower(trim($title . ' ' . $street));

        $outside_names = array(
            'north forsyth', 'east forsyth', 'west forsyth', 'forsyth central',
            'coal mountain', 'chestatee', 'cumming', 'matt', 'sawnee',
            'kelly mill', 'otwell', 'liberty', 'little mill',
            'silver city', "poole's mill", 'mashburn', 'chattahoochee',
        );
        // 'lakeside' was removed from this list 2026-07: Lakeside Middle
        // School is one of South Forsyth High School's three official
        // feeder middle schools per forsyth.k12.ga.us (confirmed live,
        // alongside Piney Grove Middle and South Forsyth Middle in the
        // allowlist above). It previously auto-classified as Outside on
        // a bare keyword match with no verification against real feeder
        // data. Deliberately left out of both lists now — falls through to
        // Needs Review pending an explicit human decision (see
        // docs/data-integration-roadmap.md).
        foreach ($outside_names as $name) {
            if (false !== strpos($haystack, $name)) {
                return array(
                    'status' => self::COVERAGE_OUTSIDE,
                    'decision_type' => 'automatic',
                    'decision_source' => 'Conservative outside-coverage rule',
                    'decision_note' => 'Matched a clearly northern/central/eastern Forsyth County school or community signal.',
                    'reasons' => array('Matched outside-coverage county school/community name: ' . $name . '.'),
                );
            }
        }

        $outside_corridors = array(
            'coal mountain', 'matt highway', 'dahlonega highway', 'spot road',
            'tribble gap', 'keith bridge', 'little mill', 'jot em down',
            'gainesville highway',
        );
        foreach ($outside_corridors as $corridor) {
            if (false !== strpos($haystack, $corridor)) {
                return array(
                    'status' => self::COVERAGE_OUTSIDE,
                    'decision_type' => 'automatic',
                    'decision_source' => 'Conservative outside-coverage rule',
                    'decision_note' => 'Matched a clearly northern/central/eastern Forsyth County corridor/community signal.',
                    'reasons' => array('Matched outside-coverage county corridor/community: ' . $corridor . '.'),
                );
            }
        }

        return array(
            'status' => self::COVERAGE_NEEDS_REVIEW,
            'decision_type' => 'automatic',
            'decision_source' => 'Conservative coverage classifier',
            'decision_note' => 'Not on the confirmed allowlist and not clearly outside coverage. Requires official boundary, attendance-map, feeder, address, or manual editorial evidence.',
            'reasons' => array('No conservative confirmed allowlist or outside-coverage signal matched; human review required.'),
        );
    }

    public static function summarize_coverage_classifications(array $classifications)
    {
        $totals = array(
            self::COVERAGE_CONFIRMED => 0,
            self::COVERAGE_NEEDS_REVIEW => 0,
            self::COVERAGE_OUTSIDE => 0,
        );

        foreach ($classifications as $classification) {
            $status = self::normalize_coverage_status($classification['status'] ?? '');
            $totals[$status]++;
        }

        return $totals;
    }

    public static function normalize_record(array $record)
    {
        $record['title'] = self::normalize_whitespace($record['title'] ?? '');
        $record['source_id'] = self::normalize_url($record['source_id'] ?? '');

        foreach (array('content', 'excerpt') as $field) {
            if (isset($record[$field])) {
                $record[$field] = self::normalize_whitespace($record[$field]);
            }
        }

        foreach (($record['meta'] ?? array()) as $key => $value) {
            if (in_array($key, array('sf_website', 'sf_source_url', 'sf_boundary_url'), true)) {
                $record['meta'][$key] = self::normalize_url($value);
            } elseif ('sf_phone' === $key) {
                $record['meta'][$key] = self::normalize_phone($value);
            } else {
                $record['meta'][$key] = self::normalize_whitespace($value);
            }
        }

        return $record;
    }

    public static function analyze_record(array $record, $update_only = false)
    {
        $record = Southforsyth_Normalizer::clean(self::normalize_record($record));
        $validation = Southforsyth_Data_Validator::validate($record);

        if (! $validation['valid']) {
            return array(
                'record' => $record,
                'action' => 'skip',
                'post_id' => 0,
                'reason' => 'Validation failed: ' . implode(' ', $validation['errors']),
                'valid' => false,
            );
        }

        $decision = self::resolve_import_target($record);
        if ($update_only && 'create' === $decision['action']) {
            $decision['action'] = 'skip';
            $decision['reason'] = 'Update-only mode: no confident existing draft match.';
        }

        return array_merge($decision, array('record' => $record, 'valid' => true));
    }

    public static function resolve_import_target(array $record)
    {
        if ('school' !== ($record['post_type'] ?? '')) {
            return array('action' => 'create', 'post_id' => 0, 'reason' => 'New non-school record.', 'ambiguous_ids' => array());
        }

        $matches = self::candidate_matches($record);

        if (empty($matches)) {
            return array('action' => 'create', 'post_id' => 0, 'reason' => 'No existing school matched source URL, source ID, website, slug, or address.', 'ambiguous_ids' => array());
        }

        uasort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $top = reset($matches);
        $top_id = (int) key($matches);
        $confident_ids = array();

        foreach ($matches as $id => $match) {
            if ($match['score'] >= 70) {
                $confident_ids[] = (int) $id;
            }
        }

        if (count($confident_ids) > 1) {
            return array(
                'action' => 'skip',
                'post_id' => 0,
                'reason' => 'Ambiguous duplicate risk: multiple existing schools matched strong signals (' . implode(', ', $confident_ids) . ').',
                'ambiguous_ids' => $confident_ids,
            );
        }

        if ($top['score'] < 70) {
            return array(
                'action' => 'skip',
                'post_id' => 0,
                'reason' => 'Ambiguous duplicate risk: only weak school match found (#' . $top_id . ', ' . implode(', ', $top['signals']) . ').',
                'ambiguous_ids' => array($top_id),
            );
        }

        $post = get_post($top_id);
        if (! $post) {
            return array('action' => 'create', 'post_id' => 0, 'reason' => 'Matched post no longer exists; creating draft.', 'ambiguous_ids' => array());
        }

        if ('publish' === $post->post_status) {
            return array(
                'action' => 'skip',
                'post_id' => $top_id,
                'reason' => 'Published school match protected from automatic overwrite (#' . $top_id . ').',
                'ambiguous_ids' => array(),
            );
        }

        if ('draft' !== $post->post_status) {
            return array(
                'action' => 'skip',
                'post_id' => $top_id,
                'reason' => 'Existing school match is not a draft (#' . $top_id . ', status ' . $post->post_status . '); skipping automatic update.',
                'ambiguous_ids' => array(),
            );
        }

        return array(
            'action' => 'update',
            'post_id' => $top_id,
            'reason' => 'Confident draft match via ' . implode(', ', $top['signals']) . '.',
            'ambiguous_ids' => array(),
        );
    }

    private static function candidate_matches(array $record)
    {
        $matches = array();
        $meta = $record['meta'] ?? array();
        $title = $record['title'] ?? '';
        $slug = sanitize_title($title);
        $name_key = self::normalize_school_name($title);
        $address_key = strtolower(trim(($meta['sf_address'] ?? '') . '|' . ($meta['sf_city'] ?? '') . '|' . ($meta['sf_zip'] ?? '')));

        self::add_meta_matches($matches, '_sf_import_source_id', $record['source_id'] ?? '', 100, 'source_id');
        self::add_meta_matches($matches, 'sf_source_url', $meta['sf_source_url'] ?? '', 90, 'source_url');
        self::add_meta_matches($matches, 'sf_website', $meta['sf_website'] ?? '', 80, 'website');

        $slug_post = $slug ? get_page_by_path($slug, OBJECT, 'school') : null;
        if ($slug_post && ! self::has_conflicting_source_identity($slug_post->ID, $record)) {
            self::add_match($matches, $slug_post->ID, 70, 'slug');
        }

        $title_candidates = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'any',
            'posts_per_page' => 20,
            's' => $title,
        ));

        foreach ($title_candidates as $candidate) {
            if (self::has_conflicting_source_identity($candidate->ID, $record)) {
                continue;
            }

            $candidate_name = self::normalize_school_name($candidate->post_title);
            $candidate_address = strtolower(trim(
                get_post_meta($candidate->ID, 'sf_address', true) . '|' .
                get_post_meta($candidate->ID, 'sf_city', true) . '|' .
                get_post_meta($candidate->ID, 'sf_zip', true)
            ));

            if ($name_key && $candidate_name && $name_key === $candidate_name && $address_key && $candidate_address === $address_key) {
                self::add_match($matches, $candidate->ID, 75, 'name+address');
            }
        }

        return $matches;
    }

    private static function has_conflicting_source_identity($post_id, array $record)
    {
        $meta = $record['meta'] ?? array();
        $record_identities = array_filter(array(
            self::normalize_url($record['source_id'] ?? ''),
            self::normalize_url($meta['sf_source_url'] ?? ''),
        ));

        if (empty($record_identities)) {
            return false;
        }

        $post_identities = array_filter(array(
            self::normalize_url(get_post_meta($post_id, '_sf_import_source_id', true)),
            self::normalize_url(get_post_meta($post_id, 'sf_source_url', true)),
        ));

        if (empty($post_identities)) {
            return false;
        }

        return empty(array_intersect($record_identities, $post_identities));
    }

    private static function add_meta_matches(array &$matches, $key, $value, $score, $signal)
    {
        $value = trim((string) $value);
        if ('' === $value) {
            return;
        }

        $ids = get_posts(array(
            'post_type' => 'school',
            'post_status' => 'any',
            'posts_per_page' => 20,
            'fields' => 'ids',
            'meta_key' => $key,
            'meta_value' => $value,
        ));

        foreach ($ids as $id) {
            self::add_match($matches, $id, $score, $signal);
        }
    }

    private static function add_match(array &$matches, $post_id, $score, $signal)
    {
        $post_id = (int) $post_id;
        if (! isset($matches[$post_id])) {
            $matches[$post_id] = array('score' => 0, 'signals' => array());
        }

        $matches[$post_id]['score'] += $score;
        $matches[$post_id]['signals'][] = $signal;
        $matches[$post_id]['signals'] = array_values(array_unique($matches[$post_id]['signals']));
    }

    public static function geocode_status($post_id)
    {
        $manual = get_post_meta($post_id, self::GEOCODE_MANUAL_META_KEY, true);
        $lat = get_post_meta($post_id, 'sf_lat', true);
        $lng = get_post_meta($post_id, 'sf_lng', true);
        $candidate_lat = get_post_meta($post_id, 'sf_geocode_candidate_lat', true);
        $confidence = get_post_meta($post_id, 'sf_geocode_confidence', true);

        if ($manual && $lat && $lng) {
            return array('key' => 'manual', 'label' => 'Manually verified', 'acceptable' => true);
        }

        if ($lat && $lng && in_array($confidence, array('exact', 'strong'), true)) {
            return array('key' => 'geocoded', 'label' => 'Geocoded', 'acceptable' => true);
        }

        if ($candidate_lat || 'review' === $confidence) {
            return array('key' => 'needs_review', 'label' => 'Needs geocode review', 'acceptable' => false);
        }

        if ('rejected' === $confidence) {
            return array('key' => 'no_match', 'label' => 'No acceptable match', 'acceptable' => false);
        }

        return array('key' => 'missing', 'label' => 'Not geocoded', 'acceptable' => false);
    }

    public static function readiness($post_id)
    {
        $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
        $duplicate_warning = get_post_meta($post_id, self::DUPLICATE_WARNING_META_KEY, true);
        $website = get_post_meta($post_id, 'sf_website', true);
        $geocode = self::geocode_status($post_id);
        $phone = get_post_meta($post_id, 'sf_phone', true);
        $source_id = get_post_meta($post_id, '_sf_import_source_id', true);

        $required_checks = array(
            'official name' => (bool) get_the_title($post_id),
            'official source ID or URL' => (bool) ($source_id || get_post_meta($post_id, 'sf_source_url', true)),
            'valid website' => $website && (bool) filter_var($website, FILTER_VALIDATE_URL),
            'address' => (bool) get_post_meta($post_id, 'sf_address', true),
            'city' => (bool) get_post_meta($post_id, 'sf_city', true),
            'state' => (bool) get_post_meta($post_id, 'sf_state', true),
            'ZIP' => (bool) get_post_meta($post_id, 'sf_zip', true),
            'school type' => ! empty($terms) && ! is_wp_error($terms),
            'district' => (bool) get_post_meta($post_id, 'sf_district', true),
            'last verified' => (bool) get_post_meta($post_id, 'sf_last_verified', true),
            'no duplicate warning' => ! $duplicate_warning,
        );

        $recommended_checks = array(
            'grades served' => (bool) get_post_meta($post_id, 'sf_grades_served', true),
            'phone' => (bool) $phone,
            'principal' => (bool) get_post_meta($post_id, 'sf_principal_name', true),
            'latitude/longitude' => $geocode['acceptable'] || (bool) get_post_meta($post_id, 'sf_geocode_waived', true),
            'boundary link' => (bool) get_post_meta($post_id, 'sf_boundary_url', true),
            'feeder pattern' => (bool) get_post_meta($post_id, 'sf_feeder_pattern', true),
            'notable programs' => (bool) get_post_meta($post_id, 'sf_notable_programs', true),
            'mission' => (bool) get_post_meta($post_id, 'sf_mission', true),
            'mascot' => (bool) get_post_meta($post_id, 'sf_mascot', true),
            'colors' => (bool) get_post_meta($post_id, 'sf_school_colors', true),
        );

        $missing = array();
        foreach ($required_checks as $label => $passed) {
            if (! $passed) {
                $missing[] = $label;
            }
        }

        $warnings = array();
        foreach ($recommended_checks as $label => $passed) {
            if (! $passed) {
                $warnings[] = $label;
            }
        }

        return array(
            'ready' => empty($missing),
            'missing' => $missing,
            'warnings' => $warnings,
            'geocode' => $geocode,
            'label' => empty($missing) ? 'Ready to publish' : 'Not ready',
        );
    }
}
