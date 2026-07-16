<?php

/**
 * Small helper utilities for the theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_render_card_section')) {
    /**
     * Render a titled section of cards using a specific card component
     * template part (e.g. event-card, restaurant-card) so each content
     * type keeps its own visual treatment instead of a generic card.
     */
    function southforsyth_render_card_section($template_part, $cards, $args = array())
    {
        if (empty($cards)) {
            return;
        }

        $id = $args['id'] ?? '';
        $classes = 'section' . (! empty($args['soft']) ? ' section--soft' : '');

        echo '<section class="' . esc_attr($classes) . '"' . ($id ? ' id="' . esc_attr($id) . '"' : '') . '>' . PHP_EOL;
        echo '<div class="container">' . PHP_EOL;

        set_query_var('eyebrow', $args['eyebrow'] ?? '');
        set_query_var('title', $args['title'] ?? '');
        set_query_var('subtitle', $args['intro'] ?? '');
        set_query_var('align', $args['align'] ?? 'left');
        get_template_part('template-parts/components/section-header');

        echo '<div class="card-grid">' . PHP_EOL;
        foreach ($cards as $card) {
            set_query_var('eyebrow', $card['eyebrow'] ?? '');
            set_query_var('title', $card['title'] ?? '');
            set_query_var('description', $card['description'] ?? '');
            // No '#' fallback here on purpose: most card templates (event,
            // restaurant, etc.) supply their own '#' default for direct/
            // standalone use, but coming-soon-card.php deliberately treats
            // an empty link as "no link" — forcing '#' here would give every
            // coming-soon card a dead link even when no real page exists.
            set_query_var('link', $card['link'] ?? '');
            set_query_var('date', $card['date'] ?? '');
            set_query_var('address', $card['address'] ?? '');
            set_query_var('area', $card['area'] ?? '');
            set_query_var('city', $card['city'] ?? '');
            set_query_var('location', $card['location'] ?? '');
            set_query_var('icon', $card['icon'] ?? '');
            set_query_var('status', $card['status'] ?? '');
            get_template_part($template_part);
        }
        echo '</div>' . PHP_EOL;

        if (! empty($args['cta_text']) && ! empty($args['cta_link'])) {
            echo '<p style="margin-top: var(--space-6);"><a class="btn btn-outline" href="' . esc_url($args['cta_link']) . '">' . esc_html($args['cta_text']) . '</a></p>' . PHP_EOL;
        }

        echo '</div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;
    }
}

if (! function_exists('southforsyth_render_mixed_card_grid')) {
    /**
     * Like southforsyth_render_card_section(), but for a list of WP_Post
     * objects that may span more than one post type (e.g. related/nearby
     * entities — see template-parts/components/related-entities.php) —
     * each post routes through its own post type's card component via
     * southforsyth_get_card_template_for_post_type(), the same lookup
     * archive.php and search.php already use, so a related school still
     * renders as a school-card and a nearby restaurant still renders as a
     * restaurant-card instead of collapsing to one generic card.
     */
    function southforsyth_render_mixed_card_grid($title, array $posts)
    {
        if (empty($posts)) {
            return;
        }

        echo '<div class="related-entities__group">' . PHP_EOL;
        echo '<h3 class="related-entities__title">' . esc_html($title) . '</h3>' . PHP_EOL;
        echo '<div class="card-grid">' . PHP_EOL;

        foreach ($posts as $post) {
            $card_template = southforsyth_get_card_template_for_post_type($post->post_type);
            if (! $card_template) {
                continue;
            }

            $card = southforsyth_post_to_card($post);
            set_query_var('eyebrow', $card['eyebrow']);
            set_query_var('title', $card['title']);
            set_query_var('description', $card['description']);
            set_query_var('link', $card['link']);
            set_query_var('date', $card['date'] ?? '');
            set_query_var('address', $card['address'] ?? '');
            set_query_var('area', $card['area'] ?? '');
            set_query_var('city', $card['city'] ?? '');
            set_query_var('location', $card['location'] ?? '');
            set_query_var('grades', $card['grades'] ?? '');
            get_template_part($card_template);
        }

        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
}
