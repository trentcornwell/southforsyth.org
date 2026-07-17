<?php

/**
 * Template Name: What Is South Forsyth?
 *
 * Editorial coverage definition page. Assign this template manually to a
 * WordPress Page with slug `what-is-south-forsyth`; this file does not
 * create or publish that page automatically.
 */

get_header();

$faqs = array(
    array(
        'question' => 'Is South Forsyth a city?',
        'answer' => 'No. South Forsyth is not an incorporated city, official postal area, or municipality. It is a commonly used community identity for the southern part of Forsyth County, Georgia.',
    ),
    array(
        'question' => 'What ZIP codes are in South Forsyth?',
        'answer' => 'ZIP codes are clues, not boundaries. Parts of 30005, 30024, 30040, and 30041 may be relevant depending on the exact place, but no ZIP code alone decides coverage.',
    ),
    array(
        'question' => 'Is Halcyon in South Forsyth?',
        'answer' => 'Yes. Halcyon is one of the clearest everyday anchors for South Forsyth community life and is inside this guide\'s editorial coverage.',
    ),
    array(
        'question' => 'Is Vickery Village in South Forsyth?',
        'answer' => 'Yes. Vickery Village is part of the local community context this guide covers, especially for residents near the Vickery, Post Road, and South Forsyth school areas.',
    ),
    array(
        'question' => 'Why are some nearby Alpharetta, Johns Creek, or Suwanee places included?',
        'answer' => 'Some border-area places are part of daily life for South Forsyth residents even when their mailing city is Alpharetta, Johns Creek, or Suwanee. Inclusion is based on community relevance, not city name alone.',
    ),
);
?>

<main id="main-content" class="site-main">
    <section class="section section--accent">
        <div class="container">
            <header class="section-header section-header--center">
                <p class="eyebrow">Coverage guide</p>
                <h1 class="section-title">What Is South Forsyth?</h1>
                <p class="section-subtitle">The working editorial definition SouthForsyth.org uses for local guides, directories, schools, parks, churches, businesses, and gathering places.</p>
            </header>

            <?php
            set_query_var('variant', 'full');
            get_template_part('template-parts/components/coverage-definition');
            ?>
        </div>
    </section>

    <?php
    set_query_var('title', 'South Forsyth Coverage FAQ');
    set_query_var('items', $faqs);
    get_template_part('template-parts/components/faq-block');
    ?>

    <?php get_template_part('template-parts/components/newsletter'); ?>
</main>

<?php get_footer(); ?>
