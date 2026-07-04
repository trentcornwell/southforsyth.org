<?php

/**
 * Small helper utilities for the theme.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_render_card_grid')) {
    function southforsyth_render_card_grid($cards, $title = '', $intro = '', $id = '')
    {
        if (empty($cards)) {
            return;
        }

        echo '<section class="section"' . ($id ? ' id="' . esc_attr($id) . '"' : '') . '>' . PHP_EOL;
        echo '<div class="container">' . PHP_EOL;
        if ($title || $intro) {
            echo '<div class="section-heading">' . PHP_EOL;
            if ($title) {
                echo '<h2>' . esc_html($title) . '</h2>' . PHP_EOL;
            }
            if ($intro) {
                echo '<p>' . esc_html($intro) . '</p>' . PHP_EOL;
            }
            echo '</div>' . PHP_EOL;
        }

        echo '<div class="card-grid">' . PHP_EOL;
        foreach ($cards as $card) {
            $title_text = $card['title'] ?? '';
            $description = $card['description'] ?? '';
            $link = $card['link'] ?? '#';
            $eyebrow = $card['eyebrow'] ?? 'Local guide';

            echo '<article class="card">' . PHP_EOL;
            echo '<div class="card__body">' . PHP_EOL;
            echo '<p class="eyebrow">' . esc_html($eyebrow) . '</p>' . PHP_EOL;
            echo '<h3>' . esc_html($title_text) . '</h3>' . PHP_EOL;
            echo '<p>' . esc_html($description) . '</p>' . PHP_EOL;
            echo '<a class="text-link" href="' . esc_url($link) . '">Learn more</a>' . PHP_EOL;
            echo '</div>' . PHP_EOL;
            echo '</article>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
        echo '</section>' . PHP_EOL;
    }
}
