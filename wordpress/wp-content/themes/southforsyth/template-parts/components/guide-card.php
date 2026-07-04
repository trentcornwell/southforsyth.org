<?php

/**
 * Guide card component.
 * Use for curated guides, explainer content, and how-to posts.
 * TODO: Replace with dynamic guide content once custom post types or categories are ready.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Guide';
$title = get_query_var('title') ?: 'Neighborhood guide';
$description = get_query_var('description') ?: 'Summarize the guide and link to more detail here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Open guide</a>
    </div>
</article>