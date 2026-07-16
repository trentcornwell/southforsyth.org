<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Forsyth County Schools provider — a real scraper against
 * www.forsyth.k12.ga.us, built from live research (robots.txt, page
 * structure, redirect behavior — see docs/data-integration-roadmap.md's
 * "Forsyth County Schools" section for the full findings):
 *
 * - robots.txt declares Crawl-delay: 5 for all agents. Every real request
 *   this class makes in fetch() sleeps first — see CRAWL_DELAY_SECONDS.
 * - The district staff directory
 *   (/schools/directions-contact-information/staff-directory-for-schools)
 *   is explicitly disallowed, so this provider never requests it — that's
 *   why sf_principal_name is never populated here.
 * - /schools/about is real, server-rendered HTML: four clean
 *   <section class="fsPanel"> blocks (Elementary/Middle/High/Academies of
 *   Creative Education), each with a <h2 class="fsElementTitle"> label and
 *   <a href="/fs/pages/NNNNN"> links, one per school. Confirmed via direct
 *   `curl` — no JS execution needed, parseable with PHP's built-in
 *   DOMDocument/DOMXPath (no external library).
 * - Each /fs/pages/NNNNN link redirects to that school's own subdomain
 *   (e.g. brookwood.forsyth.k12.ga.us). Individual school pages carry a
 *   consistent, semantic "fsLocationSingleItem" component (confirmed on a
 *   live page) with clean sub-fields for street/city/state/zip/phone — a
 *   real structured source, not prose-mining. Mascot, colors, mission,
 *   feeder pattern, and boundary URL have no equivalent clean structured
 *   field on that page, so this provider deliberately does not attempt to
 *   scrape them — guessing at unstructured text would violate "don't
 *   infer missing facts." They stay empty; see the class doc above for
 *   what a deliberate follow-up pass would need.
 * - Grades served is only captured for the three "Academies of Creative
 *   Education" programs, where the overview page itself states the range
 *   directly (e.g. "(9-12 non-traditional program)") — a direct sourced
 *   fact. Regular schools' grade span isn't stated anywhere scraped here,
 *   so it stays empty rather than inferring one from the Elementary/
 *   Middle/High label (Georgia schools vary — not a safe inference).
 */
class Southforsyth_Forsyth_County_Provider extends Southforsyth_Abstract_Provider
{
    const OVERVIEW_URL = 'https://www.forsyth.k12.ga.us/schools/about';
    const CRAWL_DELAY_SECONDS = 5;

    public function __construct()
    {
        parent::__construct('forsyth_county');
    }

    /** Always true now — this is a real scraper against a known public page, not a configurable/unconfigured feed. */
    public function is_configured()
    {
        return true;
    }

    /**
     * Fetches and parses the school directory once (cached — this list
     * barely changes). Returns stub records: name, level label, and the
     * /fs/pages/ URL to fetch for detail. $query is unused; every school
     * is always returned, matching how this provider is actually driven
     * (see Southforsyth_Forsyth_County_Import_Command).
     */
    public function search($query = '', array $args = array())
    {
        return $this->cache('school_directory', DAY_IN_SECONDS, function () {
            list($html, ) = $this->fetch_url(self::OVERVIEW_URL);
            if (! $html) {
                return array();
            }

            return $this->parse_overview($html);
        });
    }

    /**
     * $id is a stub's 'page_url' (from search()). Sleeps for the declared
     * crawl-delay, follows the redirect to the school's own subdomain, and
     * parses that page's structured location component.
     */
    public function fetch($id, array $args = array())
    {
        $stubs = $this->search();
        $stub = null;
        foreach ($stubs as $candidate) {
            if ($candidate['page_url'] === $id) {
                $stub = $candidate;
                break;
            }
        }

        if (! $stub) {
            return null;
        }

        sleep(self::CRAWL_DELAY_SECONDS);
        list($html, $final_url) = $this->fetch_url($stub['page_url']);
        if (! $html) {
            return array_merge($stub, array('fetch_failed' => true));
        }

        return array_merge($stub, $this->parse_school_page($html), array('final_url' => $final_url));
    }

    public function normalize($raw)
    {
        if (empty($raw) || ! empty($raw['fetch_failed'])) {
            return array();
        }

        $site_url = Southforsyth_School_Import_Safety::normalize_url($raw['final_url'] ?? $raw['page_url']);
        $source_url = Southforsyth_School_Import_Safety::normalize_url($raw['page_url'] ?? $site_url);
        $title = Southforsyth_School_Import_Safety::official_display_name($raw['name'] ?? '', $raw['level_label'] ?? '');
        $meta = array_filter(array(
            'sf_address'              => Southforsyth_School_Import_Safety::normalize_whitespace($raw['street'] ?? ''),
            'sf_city'                 => Southforsyth_School_Import_Safety::normalize_whitespace($raw['city'] ?? ''),
            'sf_state'                => Southforsyth_School_Import_Safety::normalize_whitespace($raw['state'] ?? ''),
            'sf_zip'                  => Southforsyth_School_Import_Safety::normalize_whitespace($raw['zip'] ?? ''),
            'sf_phone'                => Southforsyth_School_Import_Safety::normalize_phone($raw['phone'] ?? ''),
            'sf_website'              => $site_url,
            'sf_district'             => 'Forsyth County Schools',
            'sf_grades_served'        => Southforsyth_School_Import_Safety::normalize_whitespace($raw['grades'] ?? ''),
            'sf_source_url'           => $source_url,
            'sf_last_verified'        => current_time('Y-m-d'),
        ), function ($value) {
            return '' !== $value && null !== $value;
        });
        $coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $title, 'meta' => $meta));
        $meta['sf_south_forsyth_status'] = $coverage['status'];
        $meta[Southforsyth_School_Import_Safety::COVERAGE_DECISION_SOURCE_META_KEY] = $coverage['decision_source'];
        $meta[Southforsyth_School_Import_Safety::COVERAGE_DECISION_NOTE_META_KEY] = $coverage['decision_note'];
        $meta[Southforsyth_School_Import_Safety::COVERAGE_DECISION_DATE_META_KEY] = current_time('Y-m-d');
        $meta[Southforsyth_School_Import_Safety::COVERAGE_DECISION_TYPE_META_KEY] = $coverage['decision_type'];

        return Southforsyth_Normalizer::shape(array(
            'source'    => $this->get_slug(),
            'source_id' => $source_url, // the /fs/pages/NNNNN URL is the district's own stable identifier for this school
            'post_type' => 'school',
            'title'     => $title,
            'meta'      => $meta,
            'taxonomies' => array(
                'sf_school_type' => array_filter(array($this->map_level_term($raw['level_label'] ?? ''), 'Public')),
            ),
            'raw' => $raw,
        ));
    }

    private function map_level_term($level_label)
    {
        $label = strtolower($level_label);
        if (false !== strpos($label, 'elementary')) {
            return 'Elementary';
        }
        if (false !== strpos($label, 'middle')) {
            return 'Middle';
        }
        if (false !== strpos($label, 'high')) {
            return 'High';
        }
        return ''; // Academies of Creative Education aren't graded the same way — no level term forced.
    }

    /** Parses the four fsPanel sections into stub school records. */
    private function parse_overview($html)
    {
        $xpath = $this->load_xpath($html);
        if (! $xpath) {
            return array();
        }

        $schools = array();
        $sections = $xpath->query("//section[contains(concat(' ', normalize-space(@class), ' '), ' fsPanel ')]");

        foreach ($sections as $section) {
            $header = $xpath->query(".//h2[contains(@class, 'fsElementTitle')]", $section)->item(0);
            $level_label = $header ? trim($header->textContent) : '';

            $links = $xpath->query(".//ul//a[starts-with(@href, '/fs/pages/')]", $section);
            foreach ($links as $link) {
                $name = trim($link->textContent);
                if ('' === $name) {
                    continue;
                }

                $li_text = $link->parentNode ? trim($link->parentNode->textContent) : '';
                $grades = '';
                if (preg_match('/\(((?:K|PK|\d{1,2})\s*[-–]\s*(?:\d{1,2}))/i', $li_text, $m)) {
                    $grades = str_replace(array(' ', '–'), array('', '-'), $m[1]);
                }

                $schools[] = array(
                    'name'        => $name,
                    'level_label' => $level_label,
                    'page_url'    => 'https://www.forsyth.k12.ga.us' . $link->getAttribute('href'),
                    'grades'      => $grades,
                );
            }
        }

        return $schools;
    }

    /**
     * Parses one school's own page for its structured location fields.
     *
     * A page can contain several <article class="fsLocationSingleItem">
     * elements (a logo thumbnail, the school name, and the real address
     * block are each their own "location item" in this CMS's component
     * model — confirmed live: brookwood.forsyth.k12.ga.us's homepage has
     * five). Querying the field classes (fsLocationCity/Zip/Phone/etc.)
     * directly, rather than picking "the first fsLocationSingleItem" and
     * assuming it's the address one, is what makes this reliable — those
     * specific field classes only ever appear once per page, inside
     * whichever article is the real address block.
     */
    private function parse_school_page($html)
    {
        $xpath = $this->load_xpath($html);
        if (! $xpath) {
            return array();
        }

        $get_field = function ($class) use ($xpath) {
            $node = $xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]")->item(0);
            return $node ? trim($node->textContent) : '';
        };

        return array(
            'street' => $get_field('fsLocationAddress-2'),
            'city'   => $get_field('fsLocationCity'),
            'state'  => $get_field('fsLocationState'),
            'zip'    => $get_field('fsLocationZip'),
            'phone'  => $get_field('fsLocationPhone'),
        );
    }

    private function load_xpath($html)
    {
        $dom = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $loaded ? new DOMXPath($dom) : null;
    }

    /**
     * A dedicated fetch (rather than the shared http_get() helper) because
     * this needs the final URL after WordPress's default redirect-following,
     * not just the body — school links redirect from /fs/pages/NNNNN to the
     * school's own subdomain, and that final URL is the real sf_website.
     *
     * @return array{0: string|null, 1: string} [$html_or_null, $final_url]
     */
    private function fetch_url($url)
    {
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'User-Agent' => 'SouthForsyth.org/1.0 (community platform; ' . home_url('/') . ')',
            ),
        ));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return array(null, $url);
        }

        $final_url = $url;
        if (! empty($response['http_response']) && is_callable(array($response['http_response'], 'get_response_object'))) {
            $requests_response = $response['http_response']->get_response_object();
            if (! empty($requests_response->url)) {
                $final_url = $requests_response->url;
            }
        }

        return array(wp_remote_retrieve_body($response), $final_url);
    }
}
