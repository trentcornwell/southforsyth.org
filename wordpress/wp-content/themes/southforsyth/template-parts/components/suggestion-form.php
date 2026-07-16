<?php

/**
 * "Improve this page" suggestion form.
 * Same no-JS <details>/<summary> disclosure pattern as faq-block.php — no
 * new JS needed to show/hide it. Submissions never modify published
 * content directly: this posts to admin_post_southforsyth_submit_suggestion
 * (inc/community/class-suggestion-handler.php), which only ever creates a
 * `pending` sf_suggestion post. Works for any directory-type post type,
 * not school-specific, even though Schools is where it first appears.
 *
 * Usage: set_query_var('post_id', get_the_ID());
 * get_template_part('template-parts/components/suggestion-form');
 */

$target_post_id = (int) (get_query_var('post_id') ?: get_the_ID());
if (! $target_post_id) {
    return;
}

$submitted = isset($_GET['sf_suggestion']) ? sanitize_key($_GET['sf_suggestion']) : ''; // phpcs:ignore -- read-only, display a one-time notice
?>
<section class="section suggestion-form" aria-labelledby="suggestion-form-title">
    <div class="container">
        <?php if ('success' === $submitted) : ?>
            <div class="notice-box"><p>Thanks — your suggestion was submitted and will be reviewed before anything changes.</p></div>
        <?php elseif ('error' === $submitted) : ?>
            <div class="notice-box"><p>Something wasn't right with that submission. Please check the form and try again.</p></div>
        <?php elseif ('ratelimited' === $submitted) : ?>
            <div class="notice-box"><p>Please wait a moment before submitting another suggestion.</p></div>
        <?php endif; ?>

        <details class="faq-item">
            <summary id="suggestion-form-title">Help us improve this page — suggest a correction</summary>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="stack" style="margin-top: var(--space-4);">
                <?php wp_nonce_field('southforsyth_submit_suggestion', 'southforsyth_suggestion_nonce'); ?>
                <input type="hidden" name="action" value="southforsyth_submit_suggestion">
                <input type="hidden" name="target_post_id" value="<?php echo esc_attr($target_post_id); ?>">
                <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink($target_post_id)); ?>">

                <!-- Honeypot: visually hidden from real visitors via the existing
                     .visually-hidden utility, never type="hidden" (some bots skip
                     those specifically). Left blank by humans; a bot that fills
                     every field trips this. -->
                <div class="visually-hidden">
                    <label for="sf_hp_website">Leave this field blank</label>
                    <input type="text" id="sf_hp_website" name="sf_hp_website" tabindex="-1" autocomplete="off">
                </div>

                <label for="sf_requested_field">What needs changing?</label>
                <select id="sf_requested_field" name="requested_field" required>
                    <?php foreach (southforsyth_get_suggestible_fields() as $key => $label) : ?>
                        <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="sf_suggested_value">Suggested correction</label>
                <textarea id="sf_suggested_value" name="suggested_value" required></textarea>

                <label for="sf_explanation">Why? (helps our reviewer verify it)</label>
                <textarea id="sf_explanation" name="explanation" required></textarea>

                <label for="sf_source_url">Supporting source URL (optional, but helps get this approved faster)</label>
                <input type="url" id="sf_source_url" name="source_url">

                <label for="sf_submitter_name">Your name (optional)</label>
                <input type="text" id="sf_submitter_name" name="submitter_name">

                <label for="sf_submitter_email">Your email (optional — never shown publicly, only used if we need to follow up)</label>
                <input type="email" id="sf_submitter_email" name="submitter_email">

                <label class="cluster">
                    <input type="checkbox" name="credit_consent" value="1">
                    <span>Credit me by name if this suggestion is approved</span>
                </label>

                <label class="cluster">
                    <input type="checkbox" name="consent" value="1" required>
                    <span>I understand a moderator will review this before anything on the site changes, and that submitting false information may result in my suggestion being rejected.</span>
                </label>

                <button type="submit" class="btn btn-primary">Submit suggestion</button>
            </form>
        </details>
    </div>
</section>
