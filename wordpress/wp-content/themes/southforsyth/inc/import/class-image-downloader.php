<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Downloads and attaches a featured image for an imported post — but only
 * when the caller explicitly confirms image rights, per
 * docs/data-integration-roadmap.md's "Images require rights confirmation"
 * rule ("never a scraped image of unknown provenance"). There is
 * deliberately no code path that downloads an image from just an
 * image_url; $rights_confirmed must be true, which Southforsyth_Importer
 * only ever passes through from an explicit caller argument, never a
 * default.
 */
class Southforsyth_Image_Downloader
{
    /** @return int|false attachment ID on success, false otherwise */
    public static function download_and_attach($post_id, $image_url, $rights_confirmed = false, $license = '')
    {
        if (! $rights_confirmed || empty($image_url)) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_sideload_image($image_url, $post_id, null, 'id');
        if (is_wp_error($attachment_id)) {
            Southforsyth_Import_Logger::warning('image_downloader', 'Failed to sideload image for post #' . $post_id . ': ' . $attachment_id->get_error_message());
            return false;
        }

        set_post_thumbnail($post_id, $attachment_id);

        if ($license) {
            update_post_meta($attachment_id, '_sf_image_license', sanitize_text_field($license));
        }

        return $attachment_id;
    }
}
