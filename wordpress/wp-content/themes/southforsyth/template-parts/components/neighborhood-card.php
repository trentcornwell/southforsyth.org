<?php

/**
 * Neighborhood card component.
 * Use for neighborhood profiles covering lifestyle, schools, and amenities.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Neighborhood';
$title = get_query_var('title') ?: 'South Forsyth neighborhood';
$description = get_query_var('description') ?: 'Summarize lifestyle, schools, and nearby amenities here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card card-directory">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View neighborhood</a>
    </div>
</article>
