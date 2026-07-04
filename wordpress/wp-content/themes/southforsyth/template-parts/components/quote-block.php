<?php

/**
 * Quote block component.
 * Use for testimonials, editorial pulls, or featured voice sections.
 * TODO: Replace with editorial quotes or testimonials from WordPress content.
 */

$quote = get_query_var('quote') ?: 'Local life deserves a publication-quality home.';
$attribution = get_query_var('attribution') ?: 'South Forsyth community';
?>
<blockquote class="quote-block" aria-label="Featured quote">
    <p>“<?php echo esc_html($quote); ?>”</p>
    <footer><?php echo esc_html($attribution); ?></footer>
</blockquote>