<?php

/**
 * Restaurant card component.
 * Use for restaurants, coffee shops, and dining listings.
 * Data-agnostic: fed real Restaurant posts or placeholder data via query
 * vars — see inc/queries.php and docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Dining';
$title = get_query_var('title') ?: 'Neighborhood restaurant';
$description = get_query_var('description') ?: 'Showcase cuisine, hours, and neighborhood appeal here.';
$link = get_query_var('link') ?: '#';
$location = get_query_var('location') ?: '';
?>
<article class="card card-feature">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <?php if ($location) : ?>
            <p class="card-location"><?php echo esc_html($location); ?></p>
        <?php endif; ?>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View menu</a>
    </div>
</article>
