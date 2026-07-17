<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Official Forsyth County Schools attendance-boundary lookups.
 *
 * Data source: fcsmaps.forsyth.k12.ga.us's public ArcGIS REST service
 * "SchoolLocatorRolta1071v8" — the same service that backs FCS's own
 * public "Locate School by Address" tool (linked from
 * forsyth.k12.ga.us/district-services/facilities/gis-boundary-planning).
 * Discovered by inspecting that public tool's own Web AppBuilder
 * configuration, not guessed or reverse-engineered from anything private.
 *
 * Layer 1 ("Schools by Student Address") is FCS's own maintained,
 * parcel-level address database with pre-computed ES/MS/HS assignments —
 * proven far more reliable during this project's research than either
 * geocoding a free-text address or computing a zone polygon's centroid
 * (both produced inconsistent results near irregular zone boundaries;
 * layer 1's address-point match is what FCS's own tool actually uses, so
 * it is treated as authoritative here). Layers 4/5/6 hold the raw zone
 * polygons themselves and are kept only as a documented fallback.
 *
 * Deliberately has NO caching layer, unlike every other provider in this
 * theme (see Southforsyth_Abstract_Provider::cache()) — caching a lookup
 * keyed by (or containing) a visitor-submitted address would itself be a
 * form of storing that address, which this feature must not do.
 */
class Southforsyth_School_Boundary_Service
{
    const SERVICE_BASE = 'https://fcsmaps.forsyth.k12.ga.us/arcgis/rest/services/SchoolLocatorRolta1071v8/MapServer';
    const ADDRESS_LAYER = 1;
    const ES_ZONE_LAYER = 4;
    const MS_ZONE_LAYER = 5;
    const HS_ZONE_LAYER = 6;
    const SOURCE_LABEL = 'Forsyth County Schools official GIS (fcsmaps.forsyth.k12.ga.us)';
    const BOUNDARY_VINTAGE = 'FY2023 attendance zones (most recent published by FCS at time of lookup)';
    const DECISION_SOURCE = 'Forsyth County Schools official address-point database (fcsmaps.forsyth.k12.ga.us)';

    /**
     * Look up the official ES/MS/HS assignment for a street address by
     * matching FCS's own address-point database — never geocoded, never
     * estimated. Returns null if no confident match exists (ambiguous or
     * simply not in the district's address database), never a guess.
     *
     * @return array{matched_address:string, es:string, ms:string, hs:string}|null
     */
    public static function lookup_by_address($house_number, $street_fragment, $zip = '')
    {
        $house_number = (int) $house_number;
        $street_fragment = trim(strtoupper((string) $street_fragment));

        if ($house_number <= 0 || '' === $street_fragment) {
            return null;
        }

        $where = sprintf(
            "HOUSENUM=%d AND UPPER(NAME) LIKE '%%%s%%'",
            $house_number,
            self::escape_sql_like(self::strip_street_suffix($street_fragment))
        );

        if ($zip) {
            $where .= sprintf(" AND ZIP='%s'", self::escape_sql_like(preg_replace('/[^0-9]/', '', $zip)));
        }

        $result = self::query_layer(self::ADDRESS_LAYER, array(
            'where'      => $where,
            'outFields'  => 'ADDRESS,ES,MS,HS',
            'returnGeometry' => 'false',
            'resultRecordCount' => 5,
        ));

        if (empty($result['features'])) {
            // Retry once without the ZIP constraint -- visitors sometimes
            // enter a nearby mailing ZIP that differs from the parcel's ZIP.
            if ($zip) {
                return self::lookup_by_address($house_number, $street_fragment, '');
            }
            return null;
        }

        // More than one distinct ES/MS/HS combination for this house
        // number + street fragment means the match is ambiguous (could be
        // two different streets with the same name in different parts of
        // the county) -- refuse rather than guess which one is right.
        $combos = array();
        foreach ($result['features'] as $feature) {
            $a = $feature['attributes'];
            $combos[$a['ES'] . '|' . $a['MS'] . '|' . $a['HS']] = $a;
        }
        if (count($combos) > 1) {
            return null;
        }

        $attrs = reset($combos);
        return array(
            'matched_address' => $attrs['ADDRESS'] ?? '',
            'es' => $attrs['ES'] ?? '',
            'ms' => $attrs['MS'] ?? '',
            'hs' => $attrs['HS'] ?? '',
        );
    }

    /** Parses "1994 Peachtree Parkway" into [1994, "Peachtree Parkway"]. */
    public static function split_address($address)
    {
        if (preg_match('/^(\d+)\s+(.+)$/', trim($address), $m)) {
            return array((int) $m[1], $m[2]);
        }
        return array(0, trim($address));
    }

    /**
     * "SHILOH POINT ES" -> "Shiloh Point Elementary School" (display
     * form). A pure formatting helper, kept here (rather than on the
     * WP-CLI-only boundary command) so both the CLI command and the
     * public REST address-finder handler (inc/school-locator.php, loaded
     * on every request) can use it without depending on a class that
     * only loads under WP_CLI.
     */
    public static function zone_name_to_display_name($zone_name)
    {
        $zone_name = preg_replace('/\bES\b/', 'Elementary School', $zone_name);
        $zone_name = preg_replace('/\bMS\b/', 'Middle School', $zone_name);
        $zone_name = preg_replace('/\bHS\b/', 'High School', $zone_name);
        return ucwords(strtolower($zone_name));
    }

    /** Strips a trailing street-type word so "PEACHTREE PARKWAY" -> "PEACHTREE" (layer 1's NAME field excludes the type). */
    private static function strip_street_suffix($street)
    {
        $street = preg_replace(
            '/\b(ROAD|RD|PARKWAY|PKWY|DRIVE|DR|STREET|ST|AVENUE|AVE|BOULEVARD|BLVD|LANE|LN|WAY|COURT|CT|CIRCLE|CIR|HIGHWAY|HWY|TRAIL|TRL|PLACE|PL|TERRACE|TER)\.?\s*$/i',
            '',
            trim($street)
        );
        return trim($street);
    }

    private static function escape_sql_like($value)
    {
        return str_replace(array("'", '%', '_'), array("''", '\%', '\_'), $value);
    }

    /**
     * Raw layer query — used directly by the boundary-audit CLI command
     * for zone-polygon research; lookup_by_address() above is the
     * privacy-safe path for visitor-facing use.
     */
    public static function query_layer($layer_id, array $params)
    {
        $url = self::SERVICE_BASE . '/' . $layer_id . '/query?' . http_build_query(array_merge(array('f' => 'json'), $params));

        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'SouthForsyth.org/1.0 (community platform; ' . home_url('/') . ')',
            ),
        ));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return null;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return is_array($body) ? $body : null;
    }
}
