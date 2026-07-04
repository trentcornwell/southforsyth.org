<?php

/**
 * Event card component.
 * Use for community events, markets, and recurring programming.
 * Data-agnostic: fed real Event posts or placeholder data via query vars —
 * see inc/queries.php and docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Event';
$title = get_query_var('title') ?: 'Upcoming event';
$description = get_query_var('description') ?: 'Use this component for community programming, markets, and happenings.';
$link = get_query_var('link') ?: '#';
$date = get_query_var('date') ?: 'Coming soon';
?>
<article class="card card-event">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <p class="card-event__date"><?php echo esc_html($date); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Reserve spot</a>
    </div>
</article>