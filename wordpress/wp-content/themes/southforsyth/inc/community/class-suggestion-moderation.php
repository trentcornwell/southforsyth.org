<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * The moderation screen: a meta box on sf_suggestion's native edit screen
 * (not a new custom page) plus the action handler that actually applies an
 * approved change. Attached under the existing "Community Platform" admin
 * menu (Southforsyth_Admin_Menu, inc/admin/class-admin-menu.php) rather
 * than a new top-level menu.
 */
class Southforsyth_Suggestion_Moderation
{
    public static function register()
    {
        add_action('admin_menu', array(__CLASS__, 'attach_to_community_platform_menu'), 20);
        add_action('add_meta_boxes_sf_suggestion', array(__CLASS__, 'add_meta_box'));
        add_action('admin_post_southforsyth_moderate_suggestion', array(__CLASS__, 'handle'));

        // sf_suggestion has no title support UI on purpose (the handler
        // sets post_title programmatically) — a plain summary list column
        // in its place is more useful to a moderator scanning the queue.
        add_filter('manage_sf_suggestion_posts_columns', array(__CLASS__, 'add_columns'));
        add_action('manage_sf_suggestion_posts_custom_column', array(__CLASS__, 'render_column'), 10, 2);
    }

    public static function attach_to_community_platform_menu()
    {
        if (! class_exists('Southforsyth_Admin_Menu')) {
            return;
        }

        add_submenu_page(
            Southforsyth_Admin_Menu::SLUG,
            'Suggestions',
            'Suggestions',
            'edit_others_posts',
            'edit.php?post_type=sf_suggestion'
        );
    }

    public static function add_columns($columns)
    {
        return array(
            'cb'         => $columns['cb'],
            'title'      => 'Suggestion',
            'sf_target'  => 'Target page',
            'sf_field'   => 'Field',
            'sf_status'  => 'Status',
            'date'       => $columns['date'],
        );
    }

    public static function render_column($column, $post_id)
    {
        switch ($column) {
            case 'sf_target':
                $target_id = (int) get_post_meta($post_id, 'sf_target_post_id', true);
                $target = $target_id ? get_post($target_id) : null;
                echo $target ? '<a href="' . esc_url(get_edit_post_link($target_id)) . '">' . esc_html(get_the_title($target_id)) . '</a>' : '—';
                break;

            case 'sf_field':
                $fields = southforsyth_get_suggestible_fields();
                $field = get_post_meta($post_id, 'sf_requested_field', true);
                echo esc_html($fields[$field] ?? $field);
                break;

            case 'sf_status':
                $statuses = southforsyth_get_suggestion_statuses();
                $status = get_post_status($post_id);
                echo esc_html($statuses[$status] ?? $status);
                break;
        }
    }

    public static function add_meta_box()
    {
        add_meta_box(
            'southforsyth_suggestion_review',
            'Review this suggestion',
            array(__CLASS__, 'render_meta_box'),
            'sf_suggestion',
            'normal',
            'high'
        );
    }

    public static function render_meta_box($post)
    {
        $target_id = (int) get_post_meta($post->ID, 'sf_target_post_id', true);
        $target = $target_id ? get_post($target_id) : null;
        $field_key = get_post_meta($post->ID, 'sf_requested_field', true);
        $fields = southforsyth_get_suggestible_fields();
        $field_label = $fields[$field_key] ?? $field_key;
        $is_structured = 'other' !== $field_key && array_key_exists($field_key, $fields);

        $current_value = get_post_meta($post->ID, 'sf_current_value_snapshot', true);
        $suggested_value = get_post_meta($post->ID, 'sf_suggested_value', true);
        $explanation = get_post_meta($post->ID, 'sf_explanation', true);
        $source_url = get_post_meta($post->ID, 'sf_source_url', true);
        $submitter_name = get_post_meta($post->ID, 'sf_submitter_name', true);
        $submitter_email = get_post_meta($post->ID, 'sf_submitter_email', true);
        $notes = get_post_meta($post->ID, 'sf_moderator_notes', true);
        $status = get_post_status($post->ID);

        wp_nonce_field('southforsyth_moderate_suggestion_' . $post->ID, 'southforsyth_moderation_nonce');
        ?>
        <table class="form-table">
            <tr><th>Target page</th><td><?php echo $target ? '<a href="' . esc_url(get_edit_post_link($target_id)) . '" target="_blank">' . esc_html(get_the_title($target_id)) . '</a> (' . esc_html($target->post_type) . ')' : '<em>Target post no longer exists (#' . (int) $target_id . ')</em>'; ?></td></tr>
            <tr><th>Field</th><td><?php echo esc_html($field_label); ?><?php echo $is_structured ? '' : ' <em>(freeform — not auto-applied on approval)</em>'; ?></td></tr>
            <tr><th>Current value</th><td><?php echo $current_value ? esc_html($current_value) : '<em>(empty)</em>'; ?></td></tr>
            <tr>
                <th><label for="sf_final_value">Proposed value</label></th>
                <td>
                    <textarea name="final_value" id="sf_final_value" rows="3" style="width:100%;"><?php echo esc_textarea($suggested_value); ?></textarea>
                    <p class="description">Editable — what's applied on Approve is whatever is in this box, not necessarily the original submission.</p>
                </td>
            </tr>
            <tr><th>Submitter's explanation</th><td><?php echo esc_html($explanation); ?></td></tr>
            <tr><th>Source URL</th><td><?php echo $source_url ? '<a href="' . esc_url($source_url) . '" target="_blank" rel="noopener">' . esc_html($source_url) . '</a>' : '<em>(none provided)</em>'; ?></td></tr>
            <tr><th>Submitter</th><td><?php echo esc_html($submitter_name ?: '(anonymous)'); ?><?php echo $submitter_email ? ' — ' . esc_html($submitter_email) : ''; ?></td></tr>
            <tr><th>Current status</th><td><strong><?php echo esc_html(southforsyth_get_suggestion_statuses()[$status] ?? $status); ?></strong></td></tr>
            <tr>
                <th><label for="sf_moderator_notes">Moderator notes</label></th>
                <td><textarea name="moderator_notes" id="sf_moderator_notes" rows="2" style="width:100%;"><?php echo esc_textarea($notes); ?></textarea></td>
            </tr>
        </table>

        <p class="submitbox" style="margin-top:1em;">
            <?php foreach (array('approved' => 'Approve' . ($is_structured ? ' & Apply' : ''), 'rejected' => 'Reject', 'needs-more-info' => 'Needs More Info', 'duplicate' => 'Mark Duplicate') as $action => $label) : ?>
                <button type="submit" name="sf_moderation_action" value="<?php echo esc_attr($action); ?>" formaction="<?php echo esc_url(admin_url('admin-post.php')); ?>" formmethod="post" class="button <?php echo 'approved' === $action ? 'button-primary' : ''; ?>" style="margin-right:0.5em;">
                    <?php echo esc_html($label); ?>
                </button>
            <?php endforeach; ?>
        </p>
        <input type="hidden" name="action" value="southforsyth_moderate_suggestion">
        <input type="hidden" name="suggestion_id" value="<?php echo (int) $post->ID; ?>">
        <?php
        // These buttons live inside the normal post edit <form>, which
        // already posts to post.php — formaction/formmethod above redirect
        // just these specific submit buttons to admin-post.php instead,
        // without needing a second, separate <form>.
    }

    public static function handle()
    {
        $suggestion_id = isset($_POST['suggestion_id']) ? (int) $_POST['suggestion_id'] : 0;
        $action = isset($_POST['sf_moderation_action']) ? sanitize_key($_POST['sf_moderation_action']) : '';
        $suggestion = $suggestion_id ? get_post($suggestion_id) : null;

        if (! $suggestion || 'sf_suggestion' !== $suggestion->post_type) {
            wp_die(esc_html__('Suggestion not found.', 'southforsyth'), '', array('response' => 404));
        }

        if (! isset($_POST['southforsyth_moderation_nonce']) || ! wp_verify_nonce($_POST['southforsyth_moderation_nonce'], 'southforsyth_moderate_suggestion_' . $suggestion_id)) {
            wp_die(esc_html__('Security check failed.', 'southforsyth'), '', array('response' => 403));
        }

        // The capability check that actually matters: never bypassable by
        // reaching this handler directly, regardless of what the UI shows.
        if (! current_user_can('edit_others_posts')) {
            wp_die(esc_html__('You do not have permission to moderate suggestions.', 'southforsyth'), '', array('response' => 403));
        }

        $valid_actions = array('approved', 'rejected', 'needs-more-info', 'duplicate');
        if (! in_array($action, $valid_actions, true)) {
            wp_die(esc_html__('Unknown moderation action.', 'southforsyth'), '', array('response' => 400));
        }

        $notes = isset($_POST['moderator_notes']) ? sanitize_textarea_field(wp_unslash($_POST['moderator_notes'])) : '';
        $final_value = isset($_POST['final_value']) ? sanitize_textarea_field(wp_unslash($_POST['final_value'])) : '';

        if ('approved' === $action) {
            self::apply_approval($suggestion_id, $final_value);
        }

        wp_update_post(array('ID' => $suggestion_id, 'post_status' => $action));
        update_post_meta($suggestion_id, 'sf_moderator_notes', $notes);
        update_post_meta($suggestion_id, 'sf_approving_moderator', get_current_user_id());
        update_post_meta($suggestion_id, 'sf_resolution_date', current_time('Y-m-d'));

        wp_safe_redirect(add_query_arg('sf_moderated', $action, get_edit_post_link($suggestion_id, 'raw')));
        exit;
    }

    /**
     * Only ever writes the exact field named in sf_requested_field — never
     * a blind overwrite of a different field than the one the moderator
     * was shown. Freeform ("other") suggestions have no structured field
     * to write to, so approving one records the decision (and still
     * counts as a community update for the trust-signal below) without
     * touching any specific meta value — the moderator applies freeform
     * feedback to the target post manually, in the normal editor.
     */
    private static function apply_approval($suggestion_id, $final_value)
    {
        $target_id = (int) get_post_meta($suggestion_id, 'sf_target_post_id', true);
        $field = get_post_meta($suggestion_id, 'sf_requested_field', true);
        $suggestible_fields = southforsyth_get_suggestible_fields();

        if (! $target_id || ! get_post($target_id)) {
            return;
        }

        if ('other' !== $field && array_key_exists($field, $suggestible_fields)) {
            update_post_meta($target_id, $field, $final_value);
        }

        update_post_meta($target_id, 'sf_last_verified', current_time('Y-m-d'));
        update_post_meta($target_id, 'sf_community_updated', current_time('Y-m-d'));

        if (! empty(get_post_meta($suggestion_id, 'sf_credit_consent', true))) {
            $name = get_post_meta($suggestion_id, 'sf_submitter_name', true);
            if ($name) {
                update_post_meta($target_id, 'sf_contributor_credit', $name);
            }
        }
    }
}

Southforsyth_Suggestion_Moderation::register();
