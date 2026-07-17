<?php

/**
 * School card component.
 * Data-agnostic: fed real School posts or placeholder data via query vars —
 * see inc/queries.php and docs/content-platform-architecture.md. Every
 * section below only renders when the underlying field actually has data —
 * no empty labels, no generic filler copy.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Education';
$title = get_query_var('title') ?: 'Local school';
$description = get_query_var('description') ?: '';
$link = get_query_var('link') ?: '#';
$grades = get_query_var('grades') ?: '';
$level = get_query_var('level') ?: '';
$sector = get_query_var('sector') ?: '';
$address = get_query_var('address') ?: '';
$city = get_query_var('city_meta') ?: '';
$state = get_query_var('state') ?: '';
$zip = get_query_var('zip') ?: '';
$phone = get_query_var('phone') ?: '';
$website = get_query_var('website') ?: '';

$city_state_zip = trim(implode(', ', array_filter(array($city, trim($state . ' ' . $zip)))));
$type_parts = array_filter(array($sector, $level));
?>
<article class="card school-card">
    <div class="card__body">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <h3><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($title); ?></a></h3>

        <?php if (! empty($type_parts) || $grades) : ?>
            <p class="card-location">
                <?php echo esc_html(implode(' · ', $type_parts)); ?>
                <?php if ($grades) : ?>
                    <?php echo $type_parts ? ' · ' : ''; ?>Grades <?php echo esc_html($grades); ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if ($description) : ?>
            <p><?php echo esc_html($description); ?></p>
        <?php endif; ?>

        <?php if ($address || $city_state_zip) : ?>
            <p class="card-location"><?php echo esc_html(trim($address . ($address && $city_state_zip ? ', ' : '') . $city_state_zip)); ?></p>
        <?php endif; ?>

        <?php if ($phone) : ?>
            <p class="card-location"><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
        <?php endif; ?>

        <div class="card__links">
            <?php if ($website) : ?>
                <a class="text-link" href="<?php echo esc_url($website); ?>" rel="noopener" target="_blank">Official website</a>
            <?php endif; ?>
            <a class="text-link" href="<?php echo esc_url($link); ?>">View school profile</a>
        </div>
    </div>
</article>
