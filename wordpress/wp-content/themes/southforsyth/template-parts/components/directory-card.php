<?php

/**
 * Directory card component.
 * Use for directory-style listings such as businesses, organizations, and
 * cross-post-type listings (e.g. the "Popular Places" homepage section).
 * Data-agnostic: fed real Business posts (or a featured-post mix) or
 * placeholder data via query vars — see inc/queries.php and
 * docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Directory';
$title = get_query_var('title') ?: 'Local business';
$description = get_query_var('description') ?: 'Add details, categories, and contact cues here.';
$link = get_query_var('link') ?: '#';
$location = get_query_var('location') ?: '';
?>
<article class="card card-directory">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <?php if ($location) : ?>
            <p class="card-location"><?php echo esc_html($location); ?></p>
        <?php endif; ?>
        <a class="text-link" href="<?php echo esc_url($link); ?>">View profile</a>
    </div>
</article>
