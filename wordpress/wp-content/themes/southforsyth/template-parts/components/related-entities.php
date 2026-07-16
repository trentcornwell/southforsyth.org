<?php

/**
 * Related + nearby entities component.
 * Renders southforsyth_get_related_entities()/get_nearby_places() results
 * (inc/queries.php) via southforsyth_render_mixed_card_grid() (inc/helpers.php),
 * which routes each result through its own post type's card component —
 * a related school renders via school-card.php, a nearby restaurant via
 * restaurant-card.php. This is the "internal links" piece of the
 * ingestion-framework work: it's the one place a single template links out
 * to other entities, so it doesn't need a separate mechanism of its own.
 *
 * Usage: set_query_var('related', southforsyth_get_related_entities($post));
 * set_query_var('nearby', southforsyth_get_nearby_places($post));
 * get_template_part('template-parts/components/related-entities');
 */

$related = get_query_var('related') ?: array();
$nearby = get_query_var('nearby') ?: array();

if (empty($related) && empty($nearby)) {
    return;
}
?>
<section class="section related-entities" aria-labelledby="related-entities-title">
    <div class="container stack">
        <h2 id="related-entities-title" class="section-title visually-hidden">Related and nearby</h2>
        <?php
        southforsyth_render_mixed_card_grid('Related nearby', $related);
        southforsyth_render_mixed_card_grid('Nearby places', $nearby);
        ?>
    </div>
</section>
