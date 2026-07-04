<?php

/**
 * Park card component.
 * Use for parks, trails, and outdoor recreation listings.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Outdoors';
$title = get_query_var('title') ?: 'Local park';
$description = get_query_var('description') ?: 'Highlight amenities, parking, and family appeal here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Explore park</a>
    </div>
</article>
