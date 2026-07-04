<?php

/**
 * Section header component.
 * Use this to keep consistent intro blocks across the design system.
 */

$eyebrow = get_query_var('eyebrow') ?: '';
$title = get_query_var('title') ?: '';
$subtitle = get_query_var('subtitle') ?: '';
$align = get_query_var('align') ?: 'left';
?>
<div class="section-header section-header--<?php echo esc_attr($align); ?>">
    <?php if (! empty($eyebrow)) : ?>
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
    <?php endif; ?>
    <?php if (! empty($title)) : ?>
        <h2 class="section-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
    <?php if (! empty($subtitle)) : ?>
        <p class="section-subtitle"><?php echo esc_html($subtitle); ?></p>
    <?php endif; ?>
</div>