<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Deterministic geocoding match quality — replaces the old
 * Nominatim `importance` threshold entirely (see
 * inc/import/class-geocode-command.php's history: `importance` measures a
 * place's global prominence, not address-match precision, and was
 * rejecting exact matches for ordinary suburban schools — confirmed live
 * against South Forsyth High School's real address, which came back a
 * perfect field-for-field match at importance 0.246, below the old 0.3
 * cutoff).
 *
 * Veto-first design, not a point total: a single strong negative signal
 * (conflicting house number, ZIP, city, county, state, an unrelated name,
 * or coordinates outside Forsyth County) rejects a result outright,
 * regardless of how many other fields happen to agree. This is both safer
 * and far more explainable to a human reviewer than an opaque score — see
 * evaluate()'s returned 'explanation'.
 *
 * A pure function on purpose: no side effects, no WordPress post reads or
 * writes, so its output is easy to inspect directly (see
 * Southforsyth_Geocode_Command and the "rerun geocoding" bulk action in
 * inc/admin/class-school-list-columns.php, both of which just call this
 * and act on the result).
 */
class Southforsyth_Geocode_Match_Evaluator
{
    // Forsyth County, GA's real bounding box, fetched live from Nominatim
    // (`Forsyth County, Georgia, United States`) — not estimated. A small
    // buffer accounts for schools genuinely near the county line without
    // opening the door to a same-named place in a different county.
    const COUNTY_LAT_MIN = 34.0506574;
    const COUNTY_LAT_MAX = 34.3351877;
    const COUNTY_LNG_MIN = -84.2589340;
    const COUNTY_LNG_MAX = -83.9255290;
    const BBOX_BUFFER_DEGREES = 0.03; // roughly 2 miles

    /**
     * @param array $school A stored record: ['title', 'address', 'city', 'state', 'zip'].
     * @param array $result One raw Nominatim result (addressdetails=1 shape).
     * @return array ['class' => exact|strong|review|rejected, 'explanation' => string, 'matched_address' => string]
     */
    public static function evaluate(array $school, array $result)
    {
        $address = $result['address'] ?? array();
        $result_house_number = $address['house_number'] ?? '';
        $result_road = $address['road'] ?? '';
        $result_city = $address['city'] ?? $address['town'] ?? $address['village'] ?? '';
        $result_county = $address['county'] ?? '';
        $result_state = $address['state'] ?? '';
        $result_zip = $address['postcode'] ?? '';
        $matched_address = $result['display_name'] ?? '';

        list($stored_house_number, $stored_street) = self::split_address($school['address'] ?? '');
        $stored_city = $school['city'] ?? '';
        $stored_zip = $school['zip'] ?? '';

        // --- veto checks: any one of these rejects the match outright ---
        if ($stored_house_number && $result_house_number && $stored_house_number !== $result_house_number) {
            return self::rejected("House number conflict: our record says {$stored_house_number}, the result says {$result_house_number}.", $matched_address);
        }
        if ($stored_zip && $result_zip && $stored_zip !== $result_zip) {
            return self::rejected("ZIP conflict: our record says {$stored_zip}, the result says {$result_zip}.", $matched_address);
        }
        if ($stored_city && $result_city && ! self::strings_related($stored_city, $result_city)) {
            return self::rejected("City conflict: our record says \"{$stored_city}\", the result says \"{$result_city}\".", $matched_address);
        }
        if ($result_county && false === stripos($result_county, 'Forsyth')) {
            return self::rejected("County conflict: the result is in \"{$result_county}\", not Forsyth County.", $matched_address);
        }
        if ($result_state && false === stripos($result_state, 'Georgia')) {
            return self::rejected("State conflict: the result is in \"{$result_state}\", not Georgia.", $matched_address);
        }

        $name_similarity = self::name_similarity($school['title'] ?? '', $result);
        if ($name_similarity < 30) {
            return self::rejected("Name similarity too low ({$name_similarity}%) between \"{$school['title']}\" and the matched result's name.", $matched_address);
        }

        $lat = isset($result['lat']) ? (float) $result['lat'] : null;
        $lng = isset($result['lon']) ? (float) $result['lon'] : null;
        if (null !== $lat && null !== $lng && ! self::within_forsyth_county($lat, $lng)) {
            return self::rejected("Coordinates ({$lat}, {$lng}) fall outside Forsyth County's bounding box.", $matched_address);
        }

        // --- positive confirmation, for exact/strong/review ---
        $house_number_match = $stored_house_number && $result_house_number && $stored_house_number === $result_house_number;
        $street_similarity = self::street_similarity($stored_street, $result_road);
        $zip_match = $stored_zip && $result_zip && $stored_zip === $result_zip;
        $city_match = $stored_city && $result_city && self::strings_related($stored_city, $result_city);

        if ($house_number_match && $street_similarity >= 85 && $zip_match && $city_match && $name_similarity >= 70) {
            return array(
                'class'           => 'exact',
                'explanation'     => "House number, street, ZIP, and city all match; name similarity {$name_similarity}%.",
                'matched_address' => $matched_address,
            );
        }

        if ($zip_match && ($house_number_match || $street_similarity >= 70) && $name_similarity >= 50) {
            $street_note = $house_number_match ? 'house number matches' : "street name is a close match ({$street_similarity}%)";
            return array(
                'class'           => 'strong',
                'explanation'     => "ZIP matches and {$street_note}; name similarity {$name_similarity}%.",
                'matched_address' => $matched_address,
            );
        }

        return array(
            'class'       => 'review',
            'explanation' => sprintf(
                'No conflicting fields, but not enough matching detail to auto-apply (house number match: %s, street similarity %d%%, ZIP match: %s, city match: %s, name similarity %d%%).',
                $house_number_match ? 'yes' : 'no',
                $street_similarity,
                $zip_match ? 'yes' : 'no',
                $city_match ? 'yes' : 'no',
                $name_similarity
            ),
            'matched_address' => $matched_address,
        );
    }

    private static function rejected($reason, $matched_address)
    {
        return array('class' => 'rejected', 'explanation' => $reason, 'matched_address' => $matched_address);
    }

    /** "585 Peachtree Parkway" -> ['585', 'Peachtree Parkway']; no leading number -> ['', trimmed original]. */
    private static function split_address($address)
    {
        if (preg_match('/^(\d+)\s+(.+)$/', trim($address), $m)) {
            return array($m[1], $m[2]);
        }
        return array('', trim($address));
    }

    private static function normalize_street($street)
    {
        $street = strtolower(trim($street));
        $street = preg_replace('/\b(road|rd|parkway|pkwy|drive|dr|street|st|avenue|ave|boulevard|blvd|lane|ln|way|court|ct|circle|cir|highway|hwy|trail|trl)\b\.?/', '', $street);
        return trim(preg_replace('/\s+/', ' ', $street));
    }

    private static function street_similarity($a, $b)
    {
        if ('' === $a || '' === $b) {
            return 0;
        }
        similar_text(self::normalize_street($a), self::normalize_street($b), $pct);
        return (int) round($pct);
    }

    private static function strings_related($a, $b)
    {
        $a = strtolower(trim($a));
        $b = strtolower(trim($b));
        if ('' === $a || '' === $b) {
            return false;
        }
        return $a === $b || false !== stripos($a, $b) || false !== stripos($b, $a);
    }

    private static function name_similarity($title, array $result)
    {
        $result_name = $result['address']['amenity'] ?? $result['name'] ?? '';
        if ('' === $result_name || '' === $title) {
            return 0;
        }

        $normalize = function ($s) {
            $s = strtolower(trim($s));
            $s = preg_replace('/\b(high school|middle school|elementary school|academy|school)\b/', '', $s);
            return trim(preg_replace('/\s+/', ' ', $s));
        };

        similar_text($normalize($title), $normalize($result_name), $pct);
        return (int) round($pct);
    }

    /** Public: also used directly by Southforsyth_Schools_Pilot_Command's "conflicting location" flag. */
    public static function within_forsyth_county($lat, $lng)
    {
        $buffer = self::BBOX_BUFFER_DEGREES;
        return $lat >= (self::COUNTY_LAT_MIN - $buffer) && $lat <= (self::COUNTY_LAT_MAX + $buffer)
            && $lng >= (self::COUNTY_LNG_MIN - $buffer) && $lng <= (self::COUNTY_LNG_MAX + $buffer);
    }
}
