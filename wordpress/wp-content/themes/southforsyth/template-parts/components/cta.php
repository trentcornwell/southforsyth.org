<?php

/**
 * CTA component.
 * Intended for campaign-style calls to action, newsletter capture, or feature linking.
 * TODO: Replace placeholder content with dynamic WordPress content when a campaign or landing page is available.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Stay in the loop';
$title = get_query_var('title') ?: 'Follow the story of South Forsyth';
$description = get_query_var('description') ?: 'Use this component for announcements, events, or premium local updates.';
$link_text = get_query_var('link_text') ?: 'Learn more';
$link_url = get_query_var('link_url') ?: '#';
?>
<section class="section" aria-labelledby="cta-title">
    <div class="container">
        <div class="cta">
            <div class="stack">
                <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
                <h2 id="cta-title" class="section-title"><?php echo esc_html($title); ?></h2>
                <p class="section-subtitle"><?php echo esc_html($description); ?></p>
            </div>
            <a class="btn btn-primary" href="<?php echo esc_url($link_url); ?>"><?php echo esc_html($link_text); ?></a>
        </div>
    </div>
</section>