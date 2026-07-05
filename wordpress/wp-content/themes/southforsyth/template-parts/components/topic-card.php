<?php

/**
 * Topic card component.
 * Use for pillar "topic cluster" pages (see the `topic` post type and the
 * sf_topic taxonomy in inc/taxonomies.php) that pull related Guides and
 * Articles together under one umbrella subject.
 * Data-agnostic: fed real Topic posts or placeholder data via query vars —
 * see inc/queries.php and docs/content-platform-architecture.md.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Topic';
$title = get_query_var('title') ?: 'South Forsyth topic';
$description = get_query_var('description') ?: 'Summarize the topic and link to related guides here.';
$link = get_query_var('link') ?: '#';
?>
<article class="card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><?php echo esc_html($title); ?></h3>
        <p><?php echo esc_html($description); ?></p>
        <a class="text-link" href="<?php echo esc_url($link); ?>">Explore topic</a>
    </div>
</article>
