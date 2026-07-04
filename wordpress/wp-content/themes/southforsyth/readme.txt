South Forsyth Theme
===================

This theme is intentionally organized into focused modules so it can scale from a simple landing page into a large, high-traffic content platform. See docs/content-platform-architecture.md for the full rationale behind the content model.

Architecture notes
-----------------
- Core WordPress setup lives in inc/setup.php, inc/enqueue.php, inc/menus.php, inc/widgets.php.
- The content model — 9 custom post types, their taxonomies, post meta, and query helpers — lives in inc/post-types.php, inc/taxonomies.php, inc/meta.php, inc/queries.php.
- Presentation helpers (breadcrumbs, excerpts, the card-section renderer) live in inc/template-functions.php and inc/helpers.php.
- Performance-focused hooks live in inc/performance.php.
- SEO and schema helpers live in inc/schema.php.
- inc/architecture.php, inc/evergreen-content.php, and inc/community-platform.php hold content-strategy reference data (information architecture, evergreen guide plan, future platform roadmap). They are NOT auto-loaded by functions.php — require the specific file directly if something needs to read it programmatically.
- functions.php only bootstraps; it does not contain feature logic itself.

Implemented
-----------
- Custom post types: events, restaurants, parks, neighborhoods, schools, churches, businesses, guides, articles.
- Taxonomies including a cross-cutting "area" taxonomy shared by every location-bound post type.
- Homepage and archive/search/single templates that query live content per post type, falling back to clearly-labeled placeholder cards until real content exists.

Future expansion
----------------
- Add block patterns for richer content once the block editor is the primary authoring surface.
- Add object-cache-friendly transient layers as traffic grows.
- Build the community-submission, search/filtering, and interactive-map systems documented in inc/community-platform.php.
