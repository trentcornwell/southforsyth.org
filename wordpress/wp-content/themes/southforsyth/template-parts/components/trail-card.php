<?php

/**
 * Trail card component.
 * Use for walking/biking trail and greenway listings — split out from
 * Parks so Trails can have its own hub (see inc/post-types.php).
 * Data-agnostic: fed real Trail posts or placeholder data via query vars —
 * see inc/queries.php and docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Trail';
$title = get_query_var('title') ?: 'Local trail';
$description = get_query_var('description') ?: 'Highlight distance, surface, and difficulty here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View trail</a>
    </div>
</article>
