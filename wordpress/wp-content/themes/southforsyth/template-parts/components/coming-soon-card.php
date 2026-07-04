<?php

/**
 * Coming soon card component.
 * Use for feature/category previews and sample guide titles that aren't
 * published yet. Deliberately has no link — nothing here is clickable
 * until real content exists behind it, so it never dead-ends on an empty
 * archive page.
 *
 * The icon is a single letter styled in CSS (see .coming-soon-card__icon
 * in assets/css/main.css) — no icon library involved.
 *
 * TODO: once a post type has published content, swap the matching card
 * out for a real one via southforsyth_get_latest_items() +
 * southforsyth_render_card_section() (see inc/queries.php and
 * docs/content-platform-architecture.md) instead of removing this
 * component — other still-unpublished categories will keep using it.
 */

$icon = get_query_var('icon') ?: '•';
$title = get_query_var('title') ?: 'Coming soon';
$description = get_query_var('description') ?: 'This section is being built.';
?>
<article class="card coming-soon-card">
    <div class="card__body">
        <div class="coming-soon-card__icon" aria-hidden="true"><?php echo esc_html($icon); ?></div>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <span class="badge-soon">Coming soon</span>
    </div>
</article>
