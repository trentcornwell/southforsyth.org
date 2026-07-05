<?php

/**
 * Local definition block.
 * Establishes what "South Forsyth" means: a community identity used for the
 * southern end of Forsyth County, Georgia, not an incorporated city or
 * legal boundary. Areas/corridors are data-driven via query vars so this
 * block can be reused anywhere that needs to answer "what is South
 * Forsyth?" (currently the homepage; the New Resident Guide hub links back
 * to the homepage section rather than duplicating it).
 *
 * Area names are unambiguous public geography (developments, greenways,
 * roads) — see the placeholder content policy in
 * docs/content-platform-architecture.md for why that distinction matters.
 */

$eyebrow = get_query_var('eyebrow') ?: 'Not an official city';
$note = get_query_var('note') ?: 'There is no city hall, mayor, or municipal boundary for South Forsyth. It is the name residents use for the southern end of Forsyth County, Georgia - a community identity, not a legal one.';
$areas = get_query_var('areas') ?: array(
    array('name' => 'Southern Forsyth County', 'description' => 'The broad geographic area south of Cumming along the GA-400 corridor - the area most people mean by "South Forsyth."'),
    array('name' => 'Cumming / Alpharetta border', 'description' => 'The stretch where South Forsyth blends into the City of Cumming to the north and Alpharetta/Milton to the south.'),
    array('name' => 'Halcyon', 'description' => 'The mixed-use development along Post Road, anchoring shopping, dining, and newer residential growth.'),
    array('name' => 'Big Creek', 'description' => 'The area around the Big Creek Greenway, one of the region\'s main walking and biking corridors.'),
    array('name' => 'Denmark area', 'description' => 'The growing residential area around Denmark High School and Ronald Reagan Parkway.'),
    array('name' => 'Vickery', 'description' => 'The Vickery Village area near Vickery Creek, known for its walkable town-center layout.'),
    array('name' => 'Windermere', 'description' => 'A residential community off the Post Road / GA-400 corridor.'),
    array('name' => 'Polo Fields', 'description' => 'A residential community near the Polo Golf & Country Club.'),
    array('name' => 'McFarland / Union Hill / Shiloh corridors', 'description' => 'The connecting roads - McFarland Road, Union Hill Road, and Shiloh Road - that tie South Forsyth\'s neighborhoods together.'),
);
?>
<div class="local-definition-block">
    <div class="area-grid">
        <?php foreach ($areas as $area) : ?>
            <div class="area-card">
                <h3><?php echo esc_html($area['name'] ?? ''); ?></h3>
                <p><?php echo esc_html($area['description'] ?? ''); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="area-note">
        <p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
        <p><?php echo esc_html($note); ?></p>
    </div>
</div>
