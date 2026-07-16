<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Public suggestion-form handler. Registered for both logged-in
 * (admin_post_) and anonymous (admin_post_nopriv_) requests, since this
 * form is meant for any visitor, not just logged-in users. Order of
 * checks matters — nonce, then honeypot, then rate limit, then
 * validation/sanitization — cheapest and most bot-revealing checks first.
 * Never creates anything but a `pending` sf_suggestion post; never touches
 * the target post directly. That only ever happens through
 * Southforsyth_Suggestion_Moderation, after a human approves it.
 */
class Southforsyth_Suggestion_Handler
{
    const RATE_LIMIT_SECONDS = 60;

    public static function register()
    {
        add_action('admin_post_southforsyth_submit_suggestion', array(__CLASS__, 'handle'));
        add_action('admin_post_nopriv_southforsyth_submit_suggestion', array(__CLASS__, 'handle'));
    }

    public static function handle()
    {
        $redirect_to = ! empty($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : home_url('/');

        if (! isset($_POST['southforsyth_suggestion_nonce']) || ! wp_verify_nonce($_POST['southforsyth_suggestion_nonce'], 'southforsyth_submit_suggestion')) {
            self::redirect($redirect_to, 'error');
        }

        // Honeypot: a real visitor never fills this (it's visually hidden,
        // not type="hidden", which some bots specifically skip). A bot
        // that does gets a fake success — no signal that it was caught.
        if (! empty($_POST['sf_hp_website'])) {
            self::redirect($redirect_to, 'success');
        }

        $ip_hash = self::get_ip_hash();
        $rate_limit_key = 'sf_suggest_rl_' . $ip_hash;
        if (get_transient($rate_limit_key)) {
            self::redirect($redirect_to, 'ratelimited');
        }

        $target_post_id = isset($_POST['target_post_id']) ? (int) $_POST['target_post_id'] : 0;
        $target_post = $target_post_id ? get_post($target_post_id) : null;

        if (! $target_post || ! in_array($target_post->post_type, southforsyth_get_directory_meta_post_types(), true)) {
            self::redirect($redirect_to, 'error');
        }

        $requested_field = isset($_POST['requested_field']) ? sanitize_key($_POST['requested_field']) : '';
        $suggested_value = isset($_POST['suggested_value']) ? sanitize_textarea_field(wp_unslash($_POST['suggested_value'])) : '';
        $explanation = isset($_POST['explanation']) ? sanitize_textarea_field(wp_unslash($_POST['explanation'])) : '';
        $consent = ! empty($_POST['consent']);

        if ('' === $suggested_value || '' === $explanation || ! $consent) {
            self::redirect($redirect_to, 'error');
        }

        $suggestible_fields = southforsyth_get_suggestible_fields();
        if (! isset($suggestible_fields[$requested_field])) {
            $requested_field = 'other';
        }

        // Snapshot the current value server-side — never trust a
        // client-submitted "current value," which could be spoofed to make
        // a diff look different than reality.
        $current_value_snapshot = ('other' !== $requested_field)
            ? (string) get_post_meta($target_post_id, $requested_field, true)
            : '';

        $source_url = ! empty($_POST['source_url']) ? esc_url_raw(wp_unslash($_POST['source_url'])) : '';
        $submitter_name = ! empty($_POST['submitter_name']) ? sanitize_text_field(wp_unslash($_POST['submitter_name'])) : '';
        $submitter_email = ! empty($_POST['submitter_email']) ? sanitize_email(wp_unslash($_POST['submitter_email'])) : '';
        $credit_consent = ! empty($_POST['credit_consent']);

        $suggestion_id = wp_insert_post(array(
            'post_type'   => 'sf_suggestion',
            'post_status' => 'pending',
            'post_title'  => sprintf('Suggestion for #%d (%s): %s', $target_post_id, $suggestible_fields[$requested_field], wp_trim_words($suggested_value, 8)),
        ));

        if (is_wp_error($suggestion_id)) {
            self::redirect($redirect_to, 'error');
        }

        update_post_meta($suggestion_id, 'sf_target_post_id', $target_post_id);
        update_post_meta($suggestion_id, 'sf_target_post_type', $target_post->post_type);
        update_post_meta($suggestion_id, 'sf_requested_field', $requested_field);
        update_post_meta($suggestion_id, 'sf_current_value_snapshot', $current_value_snapshot);
        update_post_meta($suggestion_id, 'sf_suggested_value', $suggested_value);
        update_post_meta($suggestion_id, 'sf_explanation', $explanation);
        update_post_meta($suggestion_id, 'sf_source_url', $source_url);
        update_post_meta($suggestion_id, 'sf_submitter_name', $submitter_name);
        update_post_meta($suggestion_id, 'sf_submitter_email', $submitter_email);
        update_post_meta($suggestion_id, 'sf_ip_hash', $ip_hash);
        update_post_meta($suggestion_id, 'sf_credit_consent', $credit_consent);

        set_transient($rate_limit_key, 1, self::RATE_LIMIT_SECONDS);

        self::redirect($redirect_to, 'success');
    }

    private static function get_ip_hash()
    {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        return hash('sha256', $ip . wp_salt());
    }

    private static function redirect($redirect_to, $result)
    {
        wp_safe_redirect(add_query_arg('sf_suggestion', $result, $redirect_to));
        exit;
    }
}

Southforsyth_Suggestion_Handler::register();
