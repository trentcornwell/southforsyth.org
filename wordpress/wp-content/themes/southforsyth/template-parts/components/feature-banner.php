<?php

/**
 * Feature banner component.
 * Use for promotional, editorial, or campaign-style highlight sections.
 * TODO: Replace with dynamic content when a featured campaign exists.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Featured';
$title = get_query_var('title') ?: 'A premium local experience';
$description = get_query_var('description') ?: 'Use this component to announce important stories, directories, or seasonal initiatives.';
$link_text = get_query_var('link_text') ?: 'Explore';
$link_url = get_query_var('link_url') ?: '#';
?>
<section class="feature-banner" aria-labelledby="feature-banner-title">
    <div class="container">
        <div class="feature-banner__content">
            <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
            <h2 id="feature-banner-title" class="section-title"><?php echo esc_html($title); ?></h2>
            <p class="section-subtitle"><?php echo esc_html($description); ?></p>
            <a class="btn btn-primary" href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($link_text); ?></a>
        </div>
    </div>
</section>