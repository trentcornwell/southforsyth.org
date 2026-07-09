<?php

/**
 * Search results template.
 * Search results can mix post types, so each result is routed to its own
 * post type's card component individually rather than picking one template
 * for the whole page.
 */

get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container">
        <header class="section-header">
            <h1 class="section-title">Search results for &ldquo;<?php echo esc_html(get_search_query()); ?>&rdquo;</h1>
        </header>
        <div class="card-grid">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php
                    $card_template = southforsyth_get_card_template_for_post_type(get_post_type());
                    if ($card_template) :
                        $card = southforsyth_post_to_card(get_post());
                        set_query_var('eyebrow', $card['eyebrow']);
                        set_query_var('title', $card['title']);
                        set_query_var('description', $card['description']);
                        set_query_var('link', $card['link']);
                        set_query_var('date', $card['date'] ?? '');
                        set_query_var('address', $card['address'] ?? '');
                        set_query_var('area', $card['area'] ?? '');
                        set_query_var('city', $card['city'] ?? '');
                        set_query_var('location', $card['location'] ?? '');
                        set_query_var('grades', $card['grades'] ?? '');
                        get_template_part($card_template);
                    else :
                        get_template_part('template-parts/content', 'card');
                    endif;
                    ?>
                <?php endwhile;
            else : ?>
                <p>No matching content was found.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
