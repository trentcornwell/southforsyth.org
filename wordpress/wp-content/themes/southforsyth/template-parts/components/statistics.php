<?php

/**
 * Statistics section component.
 * Use to highlight numbers, metrics, or community facts.
 * TODO: Replace with real statistics from future WordPress data sources.
 */

$stats = get_query_var('stats') ?: array(
    array('label' => 'Neighborhood guides', 'value' => '30+'),
    array('label' => 'Local favorites', 'value' => '120+'),
    array('label' => 'Community stories', 'value' => '50+'),
);
?>
<section class="section" aria-labelledby="stats-title">
    <div class="container">
        <div class="section-header">
            <p class="eyebrow">At a glance</p>
            <h2 id="stats-title" class="section-title">A growing local publication</h2>
            <p class="section-subtitle">This section is ready to showcase key metrics, community reach, or local highlights as the site expands.</p>
        </div>
        <div class="stats-grid">
            <?php foreach ($stats as $stat) : ?>
                <div class="stat-card">
                    <strong><?php echo esc_html($stat['value'] ?? ''); ?></strong>
                    <span><?php echo esc_html($stat['label'] ?? ''); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>