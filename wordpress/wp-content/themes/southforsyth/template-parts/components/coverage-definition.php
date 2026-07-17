<?php

/**
 * Reusable South Forsyth coverage definition.
 *
 * Used by the homepage in compact mode and by the "What Is South Forsyth?"
 * page template in full mode. This is editorial guidance, not a legal map.
 */

$variant = get_query_var('variant') ?: 'compact';
$is_full = 'full' === $variant;

$communities = array('Halcyon', 'Big Creek', 'Denmark', 'Lambert', 'South Forsyth', 'Vickery', 'Windermere', 'Sharon Springs', 'Alpharetta/Cumming border');
$corridors = array('GA-400 exits 12 through 14', 'Peachtree Parkway', 'McFarland Parkway', 'Ronald Reagan Boulevard', 'McGinnis Ferry Road', 'Old Atlanta Road', 'Mathis Airport Parkway', 'Shiloh Road', 'Union Hill Road', 'Windermere Parkway');
$school_anchors = array('South Forsyth High School', 'Denmark High School', 'Lambert High School');
?>
<div class="coverage-definition coverage-definition--<?php echo esc_attr($variant); ?>">
    <div class="coverage-definition__intro">
        <p>South Forsyth is the southern part of Forsyth County, Georgia, centered around the communities and school areas near Halcyon, Big Creek, Denmark, Lambert, South Forsyth, Vickery, Windermere, Sharon Springs, and the Alpharetta/Cumming border.</p>
        <p>It is not an incorporated city or official postal area. It is a community identity commonly used for the neighborhoods, schools, businesses, churches, parks, and gathering places in the southern part of the county.</p>
    </div>

    <div class="coverage-definition__note">
        <p class="eyebrow">How we define our coverage</p>
        <p>Editorial coverage focuses on places that people living near Halcyon, Denmark, Lambert, South Forsyth, Vickery, and Windermere would reasonably consider part of their community.</p>
    </div>

    <?php if ($is_full) : ?>
        <div class="coverage-definition__grid">
            <section class="coverage-panel">
                <h2>Primary Communities</h2>
                <ul class="coverage-list">
                    <?php foreach ($communities as $community) : ?>
                        <li><?php echo esc_html($community); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="coverage-panel">
                <h2>Major Corridors</h2>
                <ul class="coverage-list">
                    <?php foreach ($corridors as $corridor) : ?>
                        <li><?php echo esc_html($corridor); ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>

            <section class="coverage-panel">
                <h2>School Anchors</h2>
                <ul class="coverage-list">
                    <?php foreach ($school_anchors as $anchor) : ?>
                        <li><?php echo esc_html($anchor); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p>Elementary and middle schools are included only when official evidence or manual editorial review connects them to the South Forsyth coverage area.</p>
            </section>

            <section class="coverage-panel">
                <h2>ZIP Code Cautions</h2>
                <p>ZIP codes are useful clues, not boundaries. Cumming, Alpharetta, Johns Creek, and Suwanee mailing addresses can sit near the edge of South Forsyth without automatically being inside or outside this guide's coverage.</p>
            </section>

            <section class="coverage-panel">
                <h2>Border-Area Rule</h2>
                <p>Border-area places may be included when they are part of daily life for South Forsyth residents, especially near Halcyon, McFarland, Windward/McGinnis Ferry, Johns Creek Parkway, and the Alpharetta/Cumming border.</p>
            </section>

            <section class="coverage-panel">
                <h2>Editorial Inclusion Test</h2>
                <p>If a resident near Halcyon, Denmark, Lambert, South Forsyth, Vickery, or Windermere would reasonably say "this is part of our local community," the place belongs in review. If not, it stays out of public coverage even if it appears in a countywide source.</p>
            </section>
        </div>
    <?php else : ?>
        <div class="coverage-definition__chips" aria-label="Primary South Forsyth corridors">
            <?php foreach (array_slice($corridors, 0, 6) as $corridor) : ?>
                <span class="tag"><?php echo esc_html($corridor); ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
