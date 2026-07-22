<?php

/**
 * Theme bootstrap.
 *
 * Keeps the theme modular so it can scale from a local community homepage
 * into a larger content platform. Each require below is a single-purpose
 * module grouped by responsibility — see the section comments here for what
 * each group does, and the individual inc/*.php file headers for details.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_require_theme_files')) {
    /**
     * Require a list of inc/ files relative to the theme directory.
     * Keeps the section groups below a readable one-line-per-file list
     * instead of repeating get_template_directory() . '/inc/' everywhere.
     *
     * Reserved for core files the theme cannot function without (setup,
     * post types, hub content, etc.) — these are expected to always exist,
     * so this uses require_once with no existence check, matching "use
     * require_once only for files that definitely exist." For anything
     * newer/optional that shouldn't be able to take the whole site down if
     * it's ever missing (e.g. from a deploy/sync mistake), use
     * southforsyth_require_optional_theme_files() below instead.
     */
    function southforsyth_require_theme_files(array $files)
    {
        $theme_dir = get_template_directory();
        foreach ($files as $file) {
            require_once $theme_dir . '/inc/' . $file;
        }
    }
}

if (! function_exists('southforsyth_require_optional_theme_files')) {
    /**
     * Same idea as southforsyth_require_theme_files(), but for files that
     * are allowed to be missing without a fatal error taking down every
     * page on the site — e.g. the data-platform layer (inc/cache,
     * inc/import, inc/providers, inc/search, inc/automation.php,
     * inc/admin), which nothing else in the theme depends on at parse
     * time (see docs/platform-architecture.md). A missing file here is
     * logged via error_log() and skipped instead of a PHP fatal
     * ("Failed opening required ...") — this is exactly the class of bug
     * that took the live site down when a deploy script's rsync
     * --exclude pattern accidentally stripped out inc/cache/ (fixed
     * separately in deploy.sh; see its comment).
     */
    function southforsyth_require_optional_theme_files(array $files)
    {
        $theme_dir = get_template_directory();
        foreach ($files as $file) {
            $path = $theme_dir . '/inc/' . $file;
            if (file_exists($path)) {
                require_once $path;
            } else {
                error_log('South Forsyth theme: optional file missing, skipped: inc/' . $file);
            }
        }
    }
}

// Core WordPress setup: theme supports, asset enqueueing, navigation menus,
// widget/sidebar areas.
southforsyth_require_theme_files(array(
    'setup.php',
    'enqueue.php',
    'menus.php',
    'widgets.php',
));

// Content model: the custom post types, taxonomies, and post meta that make
// South Forsyth a structured content platform, plus the query helpers that
// read them back out for templates (see docs/content-platform-architecture.md).
southforsyth_require_theme_files(array(
    'post-types.php',
    'taxonomies.php',
    'meta.php',
    'queries.php',
));

// Hub pages: the intro/FAQ/sample-card content shared by every post type
// archive (archive.php) and the three standalone hub pages that have no
// custom post type of their own (page-templates/hub.php), plus the
// provisioning that creates those three pages automatically. Unlike
// inc/architecture.php and friends below, this content IS read by templates
// on every relevant request, so it's required eagerly like the rest of the
// content model.
southforsyth_require_theme_files(array(
    'hub-content.php',
    'page-provisioning.php',
    'school-provisioning.php',
    'resource-provisioning.php',
));

// Presentation: rendering helpers shared by template parts (breadcrumbs,
// excerpts, the card-section renderer), plus performance and SEO/schema
// output hooks.
southforsyth_require_theme_files(array(
    'template-functions.php',
    'helpers.php',
    'performance.php',
    'schema.php',
));

/**
 * Data platform (Phases 1–10 of the community-platform-scaling work — see
 * docs/platform-architecture.md for the full picture). Load order matters
 * a little here even though nothing calls these classes at parse time:
 * cache first (providers/automation call Southforsyth_Cache_Manager),
 * import second (Southforsyth_Normalizer is used by every provider's
 * normalize()), then providers, then search and automation, which both
 * depend on the two before them. All four loader files are class
 * definitions plus a couple of hook registrations — no request does real
 * work (an HTTP call, a DB write) just by these being required.
 *
 * Loaded via southforsyth_require_optional_theme_files(), not
 * southforsyth_require_theme_files(): this whole layer is optional
 * infrastructure that no core template depends on, so a missing file here
 * (e.g. a bad deploy) logs a warning and skips it instead of fatally
 * erroring the entire site — see that function's doc comment.
 */
southforsyth_require_optional_theme_files(array(
    'cache/cache.php',
    'import/import.php',
    'providers/providers.php',
    'search/search.php',
    'automation.php',
));

// Community suggestion system (this project's first public write-path) —
// the sf_suggestion post type must register on every request (front-end
// submissions and admin moderation both need it), so it's loaded here
// alongside the rest of the data platform, not gated behind is_admin() or
// WP_CLI. See docs/data-integration-roadmap.md, "South Forsyth
// classification policy," for how this interacts with editorial workflow.
southforsyth_require_optional_theme_files(array(
    'community/community.php',
));

// Address-to-school lookup ("Find My Schools") — the boundary service and
// the REST route registration both need to load on every front-end
// request (the REST route registers on rest_api_init regardless of which
// page is loading), not just WP-CLI. See docs/data-integration-roadmap.md
// for the official Forsyth County Schools GIS source this is built on.
southforsyth_require_optional_theme_files(array(
    'import/class-school-boundary-service.php',
    'school-locator.php',
));

// Admin-only tooling (the "Community Platform" wp-admin menu) — gated
// behind is_admin() so none of it parses on a front-end page request,
// matching this theme's existing performance philosophy (see
// inc/performance.php). Also optional-loaded, same reasoning as above.
if (is_admin()) {
    southforsyth_require_optional_theme_files(array(
        'import/class-geocode-match-evaluator.php',
        'import/class-geocode-command.php',
        'admin/admin.php',
        'admin/class-school-list-columns.php',
    ));
}

// WP-CLI-only commands — gated behind WP_CLI so nothing here parses on a
// normal web request. This theme's first WP-CLI command; see
// docs/platform-architecture.md, "How to add a new data source" for the
// research this command's provider is built from.
if (defined('WP_CLI') && WP_CLI) {
    southforsyth_require_optional_theme_files(array(
        'import/class-geocode-match-evaluator.php',
        'import/class-forsyth-county-import-command.php',
        'import/class-geocode-command.php',
        'import/class-schools-pilot-command.php',
        'import/class-school-boundary-command.php',
        'import/class-school-enrichment-command.php',
    ));
}

/**
 * Content strategy reference data — inc/architecture.php,
 * inc/evergreen-content.php, and inc/community-platform.php — is
 * intentionally NOT required here.
 *
 * Those files hold large, static planning arrays (the information
 * architecture, the evergreen guide plan, and future platform systems) that
 * no template currently reads; requiring them on every request would mean
 * parsing roughly 1,500 lines of unused PHP on every single page load. They
 * stay in the repo as reference material for planning new Guide/Article
 * content — require the specific file directly (e.g. from a WP-CLI command
 * or an admin-only tool) if something needs to read it programmatically.
 */
