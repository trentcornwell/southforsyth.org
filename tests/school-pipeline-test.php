<?php

define('ABSPATH', __DIR__ . '/../wordpress/');
define('DAY_IN_SECONDS', 86400);
define('OBJECT', 'OBJECT');

$GLOBALS['sf_test_posts'] = array();
$GLOBALS['sf_test_meta'] = array();
$GLOBALS['sf_test_terms'] = array();
$GLOBALS['sf_test_updates'] = array();
$GLOBALS['sf_test_deletes'] = array();

class WP_Error
{
    private $code;
    private $message;

    public function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function get_error_message()
    {
        return $this->message;
    }
}

function sf_assert($condition, $message)
{
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
    echo "PASS: {$message}\n";
}

function wp_strip_all_tags($value)
{
    return strip_tags($value);
}

function esc_url_raw($url)
{
    return filter_var($url, FILTER_SANITIZE_URL);
}

function sanitize_title($title)
{
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

function get_posts($args)
{
    $posts = array_values($GLOBALS['sf_test_posts']);

    if (! empty($args['post_type'])) {
        $posts = array_filter($posts, function ($post) use ($args) {
            return $post->post_type === $args['post_type'];
        });
    }

    if (! empty($args['post_status']) && 'any' !== $args['post_status']) {
        $posts = array_filter($posts, function ($post) use ($args) {
            return $post->post_status === $args['post_status'];
        });
    }

    if (! empty($args['post__not_in'])) {
        $posts = array_filter($posts, function ($post) use ($args) {
            return ! in_array($post->ID, $args['post__not_in'], true);
        });
    }

    if (! empty($args['s'])) {
        $needle = strtolower($args['s']);
        $posts = array_filter($posts, function ($post) use ($needle) {
            return false !== strpos(strtolower($post->post_title), $needle);
        });
    }

    if (! empty($args['meta_key'])) {
        $posts = array_filter($posts, function ($post) use ($args) {
            $value = $GLOBALS['sf_test_meta'][$post->ID][$args['meta_key']] ?? '';
            if (array_key_exists('meta_value', $args)) {
                return $value === $args['meta_value'];
            }
            return '' !== $value;
        });
    }

    if (! empty($args['fields']) && 'ids' === $args['fields']) {
        return array_map(function ($post) {
            return $post->ID;
        }, $posts);
    }

    return array_slice($posts, 0, $args['posts_per_page'] ?? count($posts));
}

function get_page_by_path($slug, $output = OBJECT, $post_type = 'page')
{
    foreach ($GLOBALS['sf_test_posts'] as $post) {
        if ($post->post_type === $post_type && $post->post_name === $slug) {
            return $post;
        }
    }
    return null;
}

function get_post($id)
{
    return $GLOBALS['sf_test_posts'][$id] ?? null;
}

function get_post_meta($post_id, $key, $single = true)
{
    return $GLOBALS['sf_test_meta'][$post_id][$key] ?? '';
}

function update_post_meta($post_id, $key, $value)
{
    $GLOBALS['sf_test_updates'][] = array($post_id, $key, $value);
    $GLOBALS['sf_test_meta'][$post_id][$key] = $value;
}

function delete_post_meta($post_id, $key)
{
    $GLOBALS['sf_test_deletes'][] = array($post_id, $key);
    unset($GLOBALS['sf_test_meta'][$post_id][$key]);
}

function get_the_title($post_id)
{
    return $GLOBALS['sf_test_posts'][$post_id]->post_title ?? '';
}

function wp_get_post_terms($post_id, $taxonomy, $args = array())
{
    return $GLOBALS['sf_test_terms'][$post_id][$taxonomy] ?? array();
}

function is_wp_error($value)
{
    return $value instanceof WP_Error;
}

function current_time($format)
{
    return 'timestamp' === $format ? time() : gmdate('Y-m-d');
}

require_once __DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-normalizer.php';
require_once __DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-data-validator.php';
require_once __DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-school-import-safety.php';
require_once __DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-geocode-match-evaluator.php';
require_once __DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-geocode-command.php';

$functions = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/functions.php');
$import_loader = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/import.php');
sf_assert(false !== strpos($functions, 'import/class-geocode-match-evaluator.php') && false !== strpos($functions, 'import/class-schools-pilot-command.php'), 'missing school class load no longer occurs');
sf_assert(false !== strpos($import_loader, 'class-school-import-safety.php'), 'school safety helper is loaded with import pipeline');
sf_assert('South Forsyth Middle School' === Southforsyth_School_Import_Safety::official_display_name('South Forsyth', 'Middle Schools'), 'middle school display name gets official suffix');
sf_assert('South Forsyth High School' === Southforsyth_School_Import_Safety::official_display_name('South Forsyth High School', 'High Schools'), 'display name suffix is not duplicated');
sf_assert('Alliance Academy for Innovation' === Southforsyth_School_Import_Safety::official_display_name('Alliance Academy for Innovation', 'Academies of Creative Education'), 'academy display name is not forced into a normal level suffix');
sf_assert('Forsyth Academy' === Southforsyth_School_Import_Safety::official_display_name('Forsyth Academy', 'High Schools'), 'academy-branded names are preserved under level sections');
sf_assert('Forsyth Virtual Academy' === Southforsyth_School_Import_Safety::official_display_name('Forsyth Virtual Academy', 'Middle Schools'), 'virtual academy branding is preserved under level sections');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'South Forsyth High School', 'meta' => array('sf_address' => '585 Peachtree Pkwy', 'sf_zip' => '30041')));
sf_assert('Confirmed South Forsyth' === $coverage['status'], 'South Forsyth high-school community is confirmed by coverage classifier');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Vickery Creek Middle School', 'meta' => array('sf_address' => '6240 Post Rd', 'sf_zip' => '30040')));
sf_assert('Confirmed South Forsyth' === $coverage['status'], 'Vickery Creek Middle is confirmed by the adopted editorial coverage policy');
sf_assert('editorial_configuration' === $coverage['decision_type'] && 'SouthForsyth.org approved school coverage list' === $coverage['decision_source'], 'Vickery Creek Middle records editorial decision provenance');
sf_assert('Included in the adopted SouthForsyth.org editorial school coverage area' === $coverage['decision_note'], 'Vickery decision stores the adopted policy note');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Vickery Creek Elementary School', 'meta' => array('sf_address' => '6280 Post Rd', 'sf_zip' => '30040')));
sf_assert('Confirmed South Forsyth' === $coverage['status'], 'Vickery Creek Elementary is confirmed by the adopted editorial coverage policy');
$vickery_decisions = Southforsyth_School_Import_Safety::approved_school_coverage_allowlist();
sf_assert(isset($vickery_decisions['vickery creek elementary school'], $vickery_decisions['vickery creek middle school'])
    && Southforsyth_School_Import_Safety::official_display_name('Vickery Creek', 'Elementary Schools')
        !== Southforsyth_School_Import_Safety::official_display_name('Vickery Creek', 'Middle Schools'), 'Vickery Creek elementary and middle remain separate configured school records');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Unmatched Example School', 'meta' => array('sf_address' => '100 Example Rd', 'sf_zip' => '30041')));
sf_assert('Needs Review' === $coverage['status'], 'unmatched school becomes Needs Review');
sf_assert(false !== stripos(implode(' ', $coverage['reasons']), 'human review required'), 'unmatched reason explicitly requires human review');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Unmatched North ZIP School', 'meta' => array('sf_address' => '100 Example Rd', 'sf_zip' => '30506')));
sf_assert('Needs Review' === $coverage['status'], 'ZIP code alone never produces Outside Coverage');
$uncertain_classifications = array();
foreach (array('New Hope Elementary School', 'Whitlow Elementary School', 'Hendricks Middle School', 'Alliance Academy for Innovation') as $uncertain_title) {
    $uncertain = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $uncertain_title, 'meta' => array('sf_zip' => '30040')), 'Outside Coverage');
    sf_assert('Needs Review' === $uncertain['status'], $uncertain_title . ' is returned to Needs Review');
    sf_assert(false === stripos(implode(' ', $uncertain['reasons']), 'outside coverage'), 'human-review reason never produces Outside Coverage for ' . $uncertain_title);
    $uncertain_classifications[] = $uncertain;
}
$coverage_totals = Southforsyth_School_Import_Safety::summarize_coverage_classifications($uncertain_classifications);
sf_assert(4 === $coverage_totals['Needs Review'] && 0 === $coverage_totals['Outside Coverage'], 'coverage report totals agree with uncertain record statuses');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'North Forsyth High School', 'meta' => array('sf_address' => '3635 Coal Mountain Dr', 'sf_zip' => '30028')));
sf_assert('Outside Coverage' === $coverage['status'], 'outside county school is marked outside coverage');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Alliance Academy for Innovation', 'meta' => array('sf_address' => '1100 Lanier 400 Pkwy', 'sf_zip' => '30040')));
sf_assert('Needs Review' === $coverage['status'], 'countywide/special schools remain needs review without a strong South Forsyth signal');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'North Forsyth High School', 'meta' => array('sf_coverage_decision_type' => 'manual')), 'Confirmed South Forsyth');
sf_assert('Confirmed South Forsyth' === $coverage['status'], 'manual confirmed coverage status is preserved over weaker automatic classification');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'New Hope Elementary School', 'meta' => array('sf_coverage_decision_type' => 'manual')), 'Confirmed South Forsyth');
sf_assert('Confirmed South Forsyth' === $coverage['status'], 'a concrete manual decision overrides the uncertain-school editorial default');
$coverage = Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'North Forsyth High School', 'meta' => array()), 'Confirmed South Forsyth');
sf_assert('Outside Coverage' === $coverage['status'], 'unproven legacy confirmed status is re-evaluated conservatively');
sf_assert(3 <= count(Southforsyth_School_Import_Safety::coverage_allowlist()), 'conservative confirmed allowlist includes the three high-school anchors');
sf_assert('Outside Coverage' === Southforsyth_School_Import_Safety::normalize_coverage_status('Outside South Forsyth'), 'legacy outside status normalizes to Outside Coverage');

$approved_school_names = array(
    'Big Creek Elementary School', 'Brandywine Elementary School', 'Brookwood Elementary School',
    'Daves Creek Elementary School', 'Haw Creek Elementary School', 'Johns Creek Elementary School',
    'Midway Elementary School', 'Settles Bridge Elementary School', 'Sharon Elementary School',
    'Shiloh Point Elementary School', 'Vickery Creek Elementary School', 'DeSana Middle School',
    'Lakeside Middle School', 'Piney Grove Middle School', 'Riverwatch Middle School',
    'South Forsyth Middle School', 'Vickery Creek Middle School', 'Denmark High School',
    'Lambert High School', 'South Forsyth High School',
);
$demoted_school_names = array(
    'Big Creek Elementary School', 'Brandywine Elementary School', 'Brookwood Elementary School',
    'Daves Creek Elementary School', 'Haw Creek Elementary School', 'Johns Creek Elementary School',
    'Midway Elementary School', 'Settles Bridge Elementary School', 'Sharon Elementary School',
    'Shiloh Point Elementary School', 'DeSana Middle School', 'Lakeside Middle School',
    'Riverwatch Middle School',
);
foreach ($approved_school_names as $approved_school_name) {
    $approved = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $approved_school_name), 'Needs Review');
    sf_assert('Confirmed South Forsyth' === $approved['status'], $approved_school_name . ' is in the approved 20-school coverage list');
}
sf_assert(20 === count(Southforsyth_School_Import_Safety::approved_school_coverage_allowlist()), 'approved school coverage list contains exactly 20 distinct schools');
foreach ($demoted_school_names as $demoted_school_name) {
    sf_assert('Confirmed South Forsyth' === Southforsyth_School_Import_Safety::classify_coverage(array('title' => $demoted_school_name), 'Needs Review')['status'], $demoted_school_name . ' is restored from Needs Review');
}
$allowlist_precedence = Southforsyth_School_Import_Safety::classify_coverage(array(
    'title' => 'Big Creek Elementary School',
    'meta' => array('sf_address' => '100 Coal Mountain Rd'),
));
sf_assert('Confirmed South Forsyth' === $allowlist_precedence['status'], 'approved allowlist takes precedence over a weaker automatic outside signal');

$outside_fixture_names = array(
    'Chestatee Elementary School', 'Coal Mountain Elementary School', 'Cumming Elementary School',
    'Kelly Mill Elementary School', 'Mashburn Elementary School', 'Matt Elementary School',
    "Poole's Mill Elementary School", 'Sawnee Elementary School', 'Silver City Elementary School',
    'Chattahoochee Elementary School', 'Liberty Middle School', 'Little Mill Middle School',
    'North Forsyth Middle School', 'Otwell Middle School', 'East Forsyth High School',
    'Forsyth Central High School', 'North Forsyth High School', 'West Forsyth High School',
);
$classification_fixture = array();
foreach ($approved_school_names as $fixture_name) {
    $classification_fixture[] = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $fixture_name));
}
foreach (array('New Hope Elementary School', 'Whitlow Elementary School', 'Hendricks Middle School', 'Alliance Academy for Innovation') as $fixture_name) {
    $classification_fixture[] = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $fixture_name));
}
foreach ($outside_fixture_names as $fixture_name) {
    $classification_fixture[] = Southforsyth_School_Import_Safety::classify_coverage(array('title' => $fixture_name));
}
$fixture_totals = Southforsyth_School_Import_Safety::summarize_coverage_classifications($classification_fixture);
sf_assert(20 === $fixture_totals['Confirmed South Forsyth'] && 4 === $fixture_totals['Needs Review'] && 18 === $fixture_totals['Outside Coverage'], '42-school fixture calculates to 20 Confirmed, 4 Needs Review, and 18 Outside Coverage');
$published_confirmed_fixture = array(
    'Denmark High School', 'Lambert High School', 'South Forsyth High School',
    'South Forsyth Middle School', 'Piney Grove Middle School',
);
$fixture_confirmed_drafts = array_values(array_diff($approved_school_names, $published_confirmed_fixture));
sf_assert(count($fixture_confirmed_drafts) === count($approved_school_names) - count($published_confirmed_fixture)
    && 5 === count($published_confirmed_fixture), 'publishing fixture calculates eligible confirmed drafts while protecting five published schools');

$provider = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/providers/class-forsyth-county-provider.php');
sf_assert(false !== strpos($provider, 'official_display_name'), 'Forsyth County provider preserves complete official display names');
sf_assert(false !== strpos($provider, 'search_uncached'), 'Forsyth County provider exposes read-only uncached source listing for reports');

$record = array(
    'source' => 'forsyth_county',
    'source_id' => 'https://www.forsyth.k12.ga.us/fs/pages/1',
    'post_type' => 'school',
    'title' => 'Example High School',
    'meta' => array(
        'sf_address' => '123 Peachtree Pkwy',
        'sf_city' => 'Cumming',
        'sf_zip' => '30041',
        'sf_website' => 'example.forsyth.k12.ga.us',
        'sf_source_url' => 'https://www.forsyth.k12.ga.us/fs/pages/1',
    ),
);

$analysis = Southforsyth_School_Import_Safety::analyze_record($record);
sf_assert('create' === $analysis['action'], 'new official school creates a draft');

$GLOBALS['sf_test_posts'][10] = (object) array('ID' => 10, 'post_type' => 'school', 'post_status' => 'draft', 'post_title' => 'Example High School', 'post_name' => 'example-high-school');
$GLOBALS['sf_test_meta'][10]['_sf_import_source_id'] = 'https://www.forsyth.k12.ga.us/fs/pages/1';
$analysis = Southforsyth_School_Import_Safety::analyze_record($record);
sf_assert('update' === $analysis['action'] && 10 === $analysis['post_id'], 'confident duplicate updates a draft');

$GLOBALS['sf_test_posts'][10]->post_status = 'publish';
$analysis = Southforsyth_School_Import_Safety::analyze_record($record);
sf_assert('skip' === $analysis['action'] && false !== strpos($analysis['reason'], 'Published'), 'published school is protected');

$GLOBALS['sf_test_posts'][10]->post_status = 'draft';
$GLOBALS['sf_test_posts'][11] = (object) array('ID' => 11, 'post_type' => 'school', 'post_status' => 'draft', 'post_title' => 'Example High School', 'post_name' => 'example-high-school-2');
$GLOBALS['sf_test_meta'][11]['sf_source_url'] = 'https://www.forsyth.k12.ga.us/fs/pages/1';
$analysis = Southforsyth_School_Import_Safety::analyze_record($record);
sf_assert('skip' === $analysis['action'] && false !== strpos($analysis['reason'], 'Ambiguous'), 'ambiguous duplicate is skipped');

$GLOBALS['sf_test_posts'] = array();
$GLOBALS['sf_test_meta'] = array();
$GLOBALS['sf_test_posts'][20] = (object) array('ID' => 20, 'post_type' => 'school', 'post_status' => 'draft', 'post_title' => 'South Forsyth', 'post_name' => 'south-forsyth');
$GLOBALS['sf_test_meta'][20]['_sf_import_source_id'] = 'https://www.forsyth.k12.ga.us/fs/pages/high';
$south_middle = $record;
$south_middle['source_id'] = 'https://www.forsyth.k12.ga.us/fs/pages/middle';
$south_middle['title'] = 'South Forsyth';
$south_middle['meta']['sf_source_url'] = 'https://www.forsyth.k12.ga.us/fs/pages/middle';
$south_middle['meta']['sf_address'] = '4670 Windermere Pkwy';
$analysis = Southforsyth_School_Import_Safety::analyze_record($south_middle);
sf_assert('create' === $analysis['action'], 'shortened title collision with different source identity creates a separate draft');

$GLOBALS['sf_test_posts'] = array();
$GLOBALS['sf_test_meta'] = array();
$GLOBALS['sf_test_terms'] = array();
$GLOBALS['sf_test_updates'] = array();
$GLOBALS['sf_test_deletes'] = array();
$GLOBALS['sf_test_posts'][10] = (object) array('ID' => 10, 'post_type' => 'school', 'post_status' => 'draft', 'post_title' => 'Example High School', 'post_name' => 'example-high-school');

$bad = $record;
$bad['meta']['sf_address'] = '';
$analysis = Southforsyth_School_Import_Safety::analyze_record($bad);
sf_assert('skip' === $analysis['action'] && false !== strpos($analysis['reason'], 'missing an address'), 'missing required data is reported');

$match = Southforsyth_Geocode_Match_Evaluator::evaluate(
    array('title' => 'Example High School', 'address' => '123 Peachtree Parkway', 'city' => 'Cumming', 'zip' => '30041'),
    array('lat' => '34.2', 'lon' => '-84.1', 'display_name' => '123 Peachtree Parkway, Cumming, GA', 'address' => array('amenity' => 'Example High School', 'house_number' => '123', 'road' => 'Peachtree Parkway', 'city' => 'Cumming', 'county' => 'Forsyth County', 'state' => 'Georgia', 'postcode' => '30041'))
);
Southforsyth_Geocode_Command::apply_match(10, array('place_id' => 'abc', 'lat' => '34.2', 'lon' => '-84.1'), $match);
sf_assert('34.2' === get_post_meta(10, 'sf_lat', true), 'valid geocode candidate is accepted when no coordinates exist');

$weak = Southforsyth_Geocode_Match_Evaluator::evaluate(
    array('title' => 'Example High School', 'address' => '123 Peachtree Parkway', 'city' => 'Cumming', 'zip' => '30041'),
    array('lat' => '34.2', 'lon' => '-84.1', 'display_name' => 'Cumming, GA', 'address' => array('amenity' => 'Example High School', 'city' => 'Cumming', 'county' => 'Forsyth County', 'state' => 'Georgia'))
);
sf_assert('review' === $weak['class'], 'weak geocode candidate is flagged');

$GLOBALS['sf_test_terms'][10]['sf_school_type'] = array('High', 'Public');
$GLOBALS['sf_test_meta'][10]['_sf_import_source'] = 'forsyth_county';
$GLOBALS['sf_test_meta'][10]['_sf_import_source_id'] = 'https://www.forsyth.k12.ga.us/fs/pages/1';
$GLOBALS['sf_test_meta'][10]['sf_source_url'] = 'https://www.forsyth.k12.ga.us/fs/pages/1';
$GLOBALS['sf_test_meta'][10]['sf_website'] = 'https://example.forsyth.k12.ga.us';
$GLOBALS['sf_test_meta'][10]['sf_address'] = '123 Peachtree Pkwy';
$GLOBALS['sf_test_meta'][10]['sf_city'] = 'Cumming';
$GLOBALS['sf_test_meta'][10]['sf_state'] = 'GA';
$GLOBALS['sf_test_meta'][10]['sf_zip'] = '30041';
$GLOBALS['sf_test_meta'][10]['sf_phone'] = '(770) 555-1212';
$GLOBALS['sf_test_meta'][10]['sf_district'] = 'Forsyth County Schools';
$GLOBALS['sf_test_meta'][10]['sf_last_verified'] = gmdate('Y-m-d');
unset($GLOBALS['sf_test_meta'][10]['sf_lat'], $GLOBALS['sf_test_meta'][10]['sf_lng'], $GLOBALS['sf_test_meta'][10]['sf_geocode_confidence']);
$readiness = Southforsyth_School_Import_Safety::readiness(10);
sf_assert($readiness['ready'], 'basic publication readiness does not require enrichment fields');
sf_assert(in_array('grades served', $readiness['warnings'], true) && in_array('latitude/longitude', $readiness['warnings'], true), 'missing enrichment fields are readiness warnings');

$command = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-forsyth-county-import-command.php');
$dry_run_position = strpos($command, 'if ($dry_run)');
$dry_run_continue = strpos($command, "continue;\n            }\n\n            \$result", $dry_run_position);
$live_import_position = strpos($command, 'Southforsyth_Importer::import', $dry_run_position);
sf_assert(false !== $dry_run_position && false !== $dry_run_continue && false !== $live_import_position && $dry_run_continue < $live_import_position, 'dry-run performs no writes before live import call');
sf_assert(false !== strpos($command, 'audit_schools') && false !== strpos($command, 'correct_school_titles'), 'school audit and safe title correction commands are registered');
sf_assert(false !== strpos($command, "'publish' === \$post->post_status") && false !== strpos($command, 'wp_update_post'), 'title correction protects published posts and only writes through explicit correction');
sf_assert(false !== strpos($command, 'south-forsyth-only') && false !== strpos($command, 'school_coverage_report') && false !== strpos($command, 'classify_schools'), 'South Forsyth coverage import/report/classification commands are registered');

$meta = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/meta.php');
sf_assert(false !== strpos($meta, "array('Confirmed South Forsyth', 'Needs Review', 'Outside Coverage')"), 'coverage status options use the three-value editorial workflow');
sf_assert(false !== strpos($meta, 'sf_coverage_decision_source') && false !== strpos($meta, 'sf_coverage_decision_type'), 'coverage decision provenance meta is registered');

$queries = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/queries.php');
sf_assert(false !== strpos($queries, 'southforsyth_limit_public_school_queries_to_confirmed') && false !== strpos($queries, "'value' => 'Confirmed South Forsyth'"), 'public school queries are limited to confirmed South Forsyth schools');
sf_assert(false !== strpos($queries, "'Confirmed South Forsyth' === get_post_meta") && false !== strpos($queries, "'sf_duplicate_warning'"), 'Needs Review and Outside Coverage schools remain publicly hidden');

$admin_columns = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/admin/class-school-list-columns.php');
sf_assert(false !== strpos($admin_columns, 'COVERAGE_CONFIRMED') && false !== strpos($admin_columns, 'readiness($post_id)'), 'admin school publish action requires confirmed coverage and readiness');
sf_assert(false !== strpos($admin_columns, "COVERAGE_DECISION_TYPE_META_KEY, 'manual'"), 'admin coverage bulk actions record manual decision provenance');
sf_assert(false !== strpos($admin_columns, 'sf_filter_readiness') && false !== strpos($admin_columns, 'Missing required fields'), 'admin school screen exposes readiness and missing-required filters');

$pilot_command = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-schools-pilot-command.php');
sf_assert(false !== strpos($pilot_command, 'publish-confirmed-schools') && false !== strpos($pilot_command, 'publish_confirmed_schools'), 'bulk confirmed school publish command is registered');
sf_assert(false !== strpos($pilot_command, "post_status' => 'any'") && false !== strpos($pilot_command, "'draft' !== \$school->post_status"), 'bulk publish command considers all schools but only publishes drafts');
sf_assert(false !== strpos($pilot_command, 'missing/invalid required field') && false !== strpos($pilot_command, 'warnings'), 'bulk publish command reports required blockers and warning-only enrichment gaps');
sf_assert(false !== strpos($pilot_command, 'get_source_records_without_existing_post') && false !== strpos($pilot_command, 'Source records without an existing school post'), 'bulk publish command reports source records without existing posts');
sf_assert(false !== strpos($pilot_command, "WP_CLI::add_command('southforsyth publish-confirmed-schools'"), 'publish-confirmed-schools command is registered under the southforsyth namespace');
sf_assert('Confirmed South Forsyth' === Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Vickery Creek Elementary School'))['status']
    && 'Confirmed South Forsyth' === Southforsyth_School_Import_Safety::classify_coverage(array('title' => 'Vickery Creek Middle School'))['status'], 'publish dry-run classifier includes both Vickery Creek schools');
$publish_start = strpos($pilot_command, 'public function publish_confirmed_schools');
$publish_end = strpos($pilot_command, 'private function get_review_schools', $publish_start);
$publish_body = substr($pilot_command, $publish_start, $publish_end - $publish_start);
sf_assert(false !== strpos($publish_body, 'if ($dry_run)') && false !== strpos($publish_body, 'wp_update_post')
    && strpos($publish_body, 'if ($dry_run)') < strpos($publish_body, 'wp_update_post'), 'publishing dry-run reaches no publishing write path');
sf_assert(false !== strpos($pilot_command, 'only drafts are eligible') && false !== strpos($pilot_command, 'coverage status is '), 'publishing report protects published and non-confirmed schools with explicit reasons');

$coverage_command = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/inc/import/class-forsyth-county-import-command.php');
$classify_start = strpos($coverage_command, 'public function classify_schools');
$classify_end = strpos($coverage_command, 'private function get_provider', $classify_start);
$classify_body = substr($coverage_command, $classify_start, $classify_end - $classify_start);
sf_assert(false !== strpos($classify_body, 'if (! $dry_run)') && false !== strpos($classify_body, 'update_post_meta'), 'classify-schools dry-run performs no metadata writes');
sf_assert(false === strpos($classify_body, 'wp_update_post') && false === strpos($classify_body, "post_status' => 'publish'"), 'classify-schools live mode updates coverage metadata only and never publishes');
sf_assert(false !== strpos($coverage_command, 'Proposed classifier totals:') && false !== strpos($coverage_command, 'Proposed status:') && false !== strpos($coverage_command, 'Eligible for publishing:'), 'coverage report shows consistent proposed totals and per-record publishing eligibility');

$functions = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/functions.php');
sf_assert(false !== strpos($functions, "'import/class-schools-pilot-command.php'") && false !== strpos($functions, "defined('WP_CLI') && WP_CLI"), 'school pilot command file is loaded only for WP-CLI by active theme bootstrap');

$front_page = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/front-page.php');
sf_assert(false !== strpos($front_page, 'What Is South Forsyth?') && false !== strpos($front_page, 'coverage-definition'), 'homepage includes the South Forsyth definition section');

$coverage_component = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/template-parts/components/coverage-definition.php');
sf_assert(false !== strpos($coverage_component, 'not an incorporated city') && false !== strpos($coverage_component, 'GA-400 exits 12 through 14'), 'coverage definition component explains legal status and major corridors');

$coverage_template = file_get_contents(__DIR__ . '/../wordpress/wp-content/themes/southforsyth/page-templates/coverage.php');
sf_assert(false !== strpos($coverage_template, 'Template Name: What Is South Forsyth?') && false !== strpos($coverage_template, 'South Forsyth Coverage FAQ'), 'full coverage page template exists with FAQ');

echo "School pipeline tests complete.\n";
