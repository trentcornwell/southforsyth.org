<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Thin wrapper around WordPress's own slug-collision handling — no need to
 * reinvent uniqueness logic core already gets right.
 */
class Southforsyth_Slug_Generator
{
    public static function unique_slug($title, $post_type, $post_id = 0)
    {
        $slug = sanitize_title($title);
        return wp_unique_post_slug($slug, $post_id, 'publish', $post_type, 0);
    }
}
