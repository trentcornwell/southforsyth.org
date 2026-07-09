<?php

/**
 * School card component.
 * Use for education-focused content: schools and district resources.
 * Data-agnostic: fed real School posts or placeholder data via query vars —
 * see inc/queries.php and docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Education';
$title = get_query_var('title') ?: 'Local school';
$description = get_query_var('description') ?: 'Share district details, information, or resources here.';
$link = get_query_var('link') ?: '#';
$location = get_query_var('location') ?: '';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <?php if ($location) : ?>
            <p class="card-location"><?php echo esc_html($location); ?></p>
        <?php endif; ?>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Read details</a>
    </div>
</article>
