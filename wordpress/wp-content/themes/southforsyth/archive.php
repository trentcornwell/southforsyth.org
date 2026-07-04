<?php

/**
 * Generic archive template shared by every custom post type.
 * Renders each post through the card component that matches its post type
 * (e.g. event-card for Events) so WordPress never needs a dedicated
 * archive-{post_type}.php file for a simple listing page.
 */

get_header();

$queried_post_type = get_query_var('post_type');
$card_template = southforsyth_get_card_template_for_post_type(is_array($queried_post_type) ? reset($queried_post_type) : $queried_post_type);
?>

<main id="main-content" class="site-main">
    <div class="container">
        <header class="section-header">
            <h1 class="section-title"><?php the_archive_title(); ?></h1>
            <?php the_archive_description('<div class="section-subtitle">', '</div>'); ?>
        </header>
        <div class="card-grid">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php if ($card_template) :
                        $card = southforsyth_post_to_card(get_post());
                        set_query_var('eyebrow', $card['eyebrow']);
                        set_query_var('title', $card['title']);
                        set_query_var('description', $card['description']);
                        set_query_var('link', $card['link']);
                        get_template_part($card_template);
                    else :
                        get_template_part('template-parts/content', 'card');
                    endif; ?>
                <?php endwhile;
            else : ?>
                <p>No results were found for this archive.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
