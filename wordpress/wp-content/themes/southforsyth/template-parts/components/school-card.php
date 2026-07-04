<?php

/**
 * School card component.
 * TODO: Replace with dynamic school or district content.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Education';
$title = get_query_var('title') ?: 'Local school';
$description = get_query_var('description') ?: 'Share district details, information, or resources here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Read details</a>
    </div>
</article>