<?php

/**
 * Article card component.
 * Use for stories, editorial content, and publication-style listing cards.
 * TODO: Replace with dynamic post data when articles are available.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Story';
$title = get_query_var('title') ?: 'Featured article';
$description = get_query_var('description') ?: 'Add a concise summary and editorial lead here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card card-feature">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Read article</a>
    </div>
</article>