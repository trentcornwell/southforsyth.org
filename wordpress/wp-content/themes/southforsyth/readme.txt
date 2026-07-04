South Forsyth Theme
===================

This theme is intentionally organized into focused modules so it can scale from a simple landing page into a large, high-traffic content destination.

Architecture notes
-----------------
- Core WordPress setup lives in inc/setup.php.
- Asset loading is managed in inc/enqueue.php.
- Performance-focused hooks live in inc/performance.php.
- SEO and schema helpers live in inc/seo.php.
- Reusable template helpers live in inc/template-functions.php.

Future expansion
----------------
- Add custom post types for events, directories, and services.
- Add block patterns and ACF-backed templates for richer content.
- Add object-cache-friendly transient layers as traffic grows.
