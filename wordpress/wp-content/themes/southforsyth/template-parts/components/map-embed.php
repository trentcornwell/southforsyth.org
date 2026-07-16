<?php

/**
 * Map embed component.
 * Renders nothing unless both lat and lng are set — never a placeholder
 * map, never a guessed location. Uses OpenStreetMap's own embeddable
 * iframe (openstreetmap.org/export/embed.html) — no JS mapping library, no
 * API key, matching the "no plugins/frameworks" constraint. Pairs with
 * Southforsyth_Openstreetmap_Provider, which is what populates sf_lat/
 * sf_lng in the first place for geocoded posts.
 *
 * Usage: set_query_var('lat', $lat); set_query_var('lng', $lng);
 * get_template_part('template-parts/components/map-embed');
 */

$lat = get_query_var('lat') ?: '';
$lng = get_query_var('lng') ?: '';

if ('' === $lat || '' === $lng) {
    return;
}

$lat = (float) $lat;
$lng = (float) $lng;
$delta = 0.01;
$bbox = ($lng - $delta) . ',' . ($lat - $delta) . ',' . ($lng + $delta) . ',' . ($lat + $delta);
$embed_url = 'https://www.openstreetmap.org/export/embed.html?bbox=' . rawurlencode($bbox) . '&layer=mapnik&marker=' . rawurlencode($lat . ',' . $lng);
$link_url = 'https://www.openstreetmap.org/?mlat=' . rawurlencode($lat) . '&mlon=' . rawurlencode($lng) . '#map=16/' . rawurlencode($lat) . '/' . rawurlencode($lng);
?>
<div class="map-embed">
    <iframe title="Map" src="<?php echo esc_url($embed_url); ?>" loading="lazy" style="width:100%;height:320px;border:1px solid var(--color-border);border-radius:var(--radius-md);"></iframe>
    <p class="map-embed__link"><a href="<?php echo esc_url($link_url); ?>" target="_blank" rel="noopener">View larger map</a></p>
</div>
