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
     */
    function southforsyth_require_theme_files(array $files)
    {
        $theme_dir = get_template_directory();
        foreach ($files as $file) {
            require_once $theme_dir . '/inc/' . $file;
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
 */
southforsyth_require_theme_files(array(
    'cache/cache.php',
    'import/import.php',
    'providers/providers.php',
    'search/search.php',
    'automation.php',
));

// Admin-only tooling (the "Community Platform" wp-admin menu) — gated
// behind is_admin() so none of it parses on a front-end page request,
// matching this theme's existing performance philosophy (see
// inc/performance.php).
if (is_admin()) {
    southforsyth_require_theme_files(array(
        'admin/admin.php',
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
