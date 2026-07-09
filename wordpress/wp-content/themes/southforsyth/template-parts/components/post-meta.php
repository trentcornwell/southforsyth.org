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

// School-specific detail, in addition to the shared directory fields above
// (address/phone/hours/website already applied since 'school' is one of
// southforsyth_get_directory_meta_post_types()). See inc/meta.php.
if ('school' === $post_type) {
    $grades = get_post_meta($post_id, 'sf_grades_served', true);
    $principal = get_post_meta($post_id, 'sf_principal_name', true);
    $boundary_url = get_post_meta($post_id, 'sf_boundary_url', true);
    $programs = get_post_meta($post_id, 'sf_notable_programs', true);

    if ($grades) {
        $items[] = array('label' => 'Grades served', 'value' => $grades);
    }
    if ($principal) {
        $items[] = array('label' => 'Principal', 'value' => $principal);
    }
    if ($programs) {
        $items[] = array('label' => 'Notable programs', 'value' => $programs);
    }
    if ($boundary_url) {
        $items[] = array(
            'label' => 'Attendance zone',
            'value' => '<a href="' . esc_url($boundary_url) . '">View official boundary map</a>',
            'raw'   => true,
        );
    }
}

// Source/verification, shared across every directory-style post type (see
// inc/meta.php) — a trust signal for any listing, not just schools.
if (in_array($post_type, southforsyth_get_directory_meta_post_types(), true)) {
    $source_url = get_post_meta($post_id, 'sf_source_url', true);
    $last_verified = get_post_meta($post_id, 'sf_last_verified', true);

    if ($last_verified) {
        $items[] = array('label' => 'Last verified', 'value' => $last_verified);
    }
    if ($source_url) {
        $items[] = array(
            'label' => 'Source',
            'value' => '<a href="' . esc_url($source_url) . '">Official source</a>',
            'raw'   => true,
        );
    }
}

if (empty($items)) {
    return;
}
?>
<ul class="card-meta">
    <?php foreach ($items as $item) : ?>
        <li><strong><?php echo esc_html($item['label']); ?>:</strong> <?php echo ! empty($item['raw']) ? $item['value'] : esc_html($item['value']); ?></li>
    <?php endforeach; ?>
</ul>
