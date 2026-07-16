<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * U.S. Census Bureau (American Community Survey) provider — demographic
 * context for `neighborhood` profiles, per docs/data-integration-roadmap.md's
 * existing GIS/open-data guidance ("useful as descriptive background text,
 * not as the primary content"). Only numbers are ever normalized here
 * (population, income, age, the ACS data year) — never prose; see
 * inc/meta.php's sf_census_* fields.
 *
 * Same posture as Southforsyth_Google_Places_Provider: the Census API
 * requires a free API key for the endpoints this needs, which this project
 * doesn't have and which must never be invented or hardcoded (see
 * CLAUDE.md's "never commit secrets"). search()/fetch() return empty
 * results, never fabricated data, until a real key is entered on the
 * Settings admin page and read back via
 * get_option('southforsyth_census_api_key').
 */
class Southforsyth_Census_Provider extends Southforsyth_Abstract_Provider
{
    public function __construct()
    {
        parent::__construct('census');
    }

    public function is_configured()
    {
        return (bool) get_option('southforsyth_census_api_key');
    }

    public function search($query, array $args = array())
    {
        if (! $this->is_configured()) {
            return array();
        }

        // $query is expected to be a Census geography identifier (e.g. a
        // tract or place FIPS code) resolved ahead of time by whoever
        // configures an import — this provider does not geocode.
        return $this->cache('acs_' . $query, DAY_IN_SECONDS, function () use ($query) {
            $url = add_query_arg(array(
                'get' => 'NAME,B01003_001E,B19013_001E,B01002_001E',
                'for' => rawurlencode($query),
                'key' => get_option('southforsyth_census_api_key'),
            ), 'https://api.census.gov/data/2023/acs/acs5');

            $result = $this->http_get($url);
            return is_array($result) ? $result : array();
        });
    }

    public function fetch($id, array $args = array())
    {
        $rows = $this->search($id);
        return $rows[1] ?? null; // row 0 is the ACS header row
    }

    public function normalize($raw)
    {
        if (empty($raw)) {
            return array();
        }

        // ACS returns requested data columns first (NAME, population, income,
        // age — the order given to the `get` param in search()), then
        // appends geography identifier columns whose count depends on the
        // `for`/`in` clause used. Rather than assume a fixed geography
        // column count, join whatever trails the four known data columns as
        // the geography id — that's ACS's own stable identifier for this
        // record and a good source_id for dedup.
        $geo_id = implode('-', array_slice($raw, 4));

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $geo_id,
            'post_type' => 'neighborhood',
            'title'     => $raw[0] ?? '',
            'meta'      => array(
                'sf_census_population'    => $raw[1] ?? '',
                'sf_census_median_income' => $raw[2] ?? '',
                'sf_census_median_age'    => $raw[3] ?? '',
                'sf_census_source_year'   => '2023',
            ),
            'raw' => $raw,
        ));
    }
}
