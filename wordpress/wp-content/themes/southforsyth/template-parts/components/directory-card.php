<?php

/**
 * Directory card component.
 * Use for directory-style listings such as businesses, organizations, and local services.
 * TODO: Replace placeholder values with dynamic WordPress data.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Directory';
$title = get_query_var('title') ?: 'Local business';
$description = get_query_var('description') ?: 'Add details, categories, and contact cues here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card card-directory">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View profile</a>
    </div>
</article>