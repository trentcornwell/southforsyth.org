<?php

/**
 * Post meta component.
 * Renders the type-specific detail list (event date/venue, or directory
 * address/phone/hours/website) above the content on single templates.
 * Renders nothing for regular posts and pages.
 */

$post_id = get_the_ID();
$post_type = get_post_type();
$items = array();

if ('event' === $post_type) {
    $date = get_post_meta($post_id, 'sf_event_date', true);
    $time = get_post_meta($post_id, 'sf_event_time', true);
    $venue = get_post_meta($post_id, 'sf_event_venue', true);

    if ($date) {
        $items[] = array('label' => 'Date', 'value' => $date);
    }
    if ($time) {
        $items[] = array('label' => 'Time', 'value' => $time);
    }
    if ($venue) {
        $items[] = array('label' => 'Venue', 'value' => $venue);
    }
} elseif (in_array($post_type, southforsyth_get_directory_meta_post_types(), true)) {
    $address = get_post_meta($post_id, 'sf_address', true);
    $phone = get_post_meta($post_id, 'sf_phone', true);
    $hours = get_post_meta($post_id, 'sf_hours', true);
    $website = get_post_meta($post_id, 'sf_website', true);

    if ($address) {
        $items[] = array('label' => 'Address', 'value' => $address);
    }
    if ($phone) {
        $items[] = array('label' => 'Phone', 'value' => $phone);
    }
    if ($hours) {
        $items[] = array('label' => 'Hours', 'value' => $hours);
    }
    if ($website) {
        $items[] = array('label' => 'Website', 'value' => $website);
    }
}

if (empty($items)) {
    return;
}
?>
<ul class="card-meta">
    <?php foreach ($items as $item) : ?>
        <li><strong><?php echo esc_html($item['label']); ?>:</strong> <?php echo esc_html($item['value']); ?></li>
    <?php endforeach; ?>
</ul>
