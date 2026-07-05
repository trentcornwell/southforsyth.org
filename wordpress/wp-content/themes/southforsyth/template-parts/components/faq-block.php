<?php

/**
 * FAQ block component.
 * Renders a list of question/answer pairs as native <details>/<summary>
 * disclosures — accessible and keyboard-operable with no JS required.
 * Used by archive.php and page-templates/hub.php via
 * southforsyth_render_hub_faq(); can also be called directly by setting
 * the `title` and `items` query vars (items: array of {question, answer}).
 */

$title = get_query_var('title') ?: 'Frequently Asked Questions';
$items = get_query_var('items') ?: array();

if (empty($items)) {
    return;
}
?>
<section class="section faq-section" aria-labelledby="faq-title">
    <div class="container">
        <h2 id="faq-title" class="section-title"><?php echo esc_html($title); ?></h2>
        <div class="faq-block">
            <?php foreach ($items as $item) : ?>
                <details class="faq-item">
                    <summary><?php echo esc_html($item['question'] ?? ''); ?></summary>
                    <p><?php echo esc_html($item['answer'] ?? ''); ?></p>
                </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>
