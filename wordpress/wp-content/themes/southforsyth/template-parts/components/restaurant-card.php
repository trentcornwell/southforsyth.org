<?php

/**
 * Restaurant card component.
 * TODO: Replace with dynamic restaurant listings and ratings when content is available.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Dining';
$title = get_query_var('title') ?: 'Neighborhood restaurant';
$description = get_query_var('description') ?: 'Showcase cuisine, hours, and neighborhood appeal here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card card-feature">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View menu</a>
    </div>
</article>