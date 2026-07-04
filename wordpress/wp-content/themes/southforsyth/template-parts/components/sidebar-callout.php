<?php

/**
 * Sidebar callout component.
 * Intended for related content, quick links, and featured announcements.
 * TODO: Replace with dynamic sidebar content from widgets or custom post types.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Featured';
$title = get_query_var('title') ?: 'Local spotlight';
$description = get_query_var('description') ?: 'Provide an editorial note, quick link, or community highlight here.';
$link_text = get_query_var('link_text') ?: 'Read more';
$link_url = get_query_var('link_url') ?: '#';
?>
<aside class="card card-feature sidebar-callout" aria-label="Sidebar callout">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="btn btn-outline" href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($link_text); ?></a>
    </div>
</aside>