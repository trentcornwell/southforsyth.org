<?php

/**
 * Generic archive template shared by every custom post type.
 * Renders each post through the card component that matches its post type
 * (e.g. event-card for Events) so WordPress never needs a dedicated
 * archive-{post_type}.php file for a simple listing page.
 *
 * Also doubles as each post type's "hub page": intro copy, a coming-soon
 * notice with sample category cards (only shown while the post type has
 * zero published posts), related-section links, an FAQ, and a newsletter
 * CTA — all pulled from inc/hub-content.php via southforsyth_get_hub_content()
 * so this stays one template instead of one per post type. The live-post
 * grid below is untouched: the moment a post type has published content it
 * takes over automatically, exactly as before.
 */

get_header();

$queried_post_type = get_query_var('post_type');
$post_type_key = is_array($queried_post_type) ? reset($queried_post_type) : $queried_post_type;
$card_template = southforsyth_get_card_template_for_post_type($post_type_key);
$hub = southforsyth_get_hub_content($post_type_key);
?>

<main id="main-content" class="site-main">
    <section class="section">
        <div class="container">
            <header class="section-header">
                <h1 class="section-title">
                    <?php
                    // get_the_archive_title() (not post_type_archive_title(),
                    // which only handles post type archives) so this still
                    // works correctly on taxonomy archives that also render
                    // through this template (e.g. an sf_event_category term).
                    echo ($hub && ! empty($hub['title'])) ? esc_html($hub['title']) : esc_html(get_the_archive_title());
                    ?>
                </h1>
                <?php if ($hub && ! empty($hub['intro'])) : ?>
                    <div class="section-subtitle hub-intro">
                        <?php foreach ($hub['intro'] as $paragraph) : ?>
                            <p><?php echo esc_html($paragraph); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <?php the_archive_description('<div class="section-subtitle">', '</div>'); ?>
                <?php endif; ?>
            </header>

            <?php if (have_posts()) : ?>
                <div class="card-grid">
                    <?php while (have_posts()) : the_post(); ?>
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
                    <?php endwhile; ?>
                </div>
            <?php elseif ($hub) : ?>
                <article class="card card-placeholder hub-empty-notice">
                    <div class="card__body">
                        <span class="badge-soon">Coming soon</span>
                        <h2><?php echo esc_html($hub['empty_title'] ?? 'No listings published yet'); ?></h2>
                        <p><?php echo esc_html($hub['empty_description'] ?? 'This section is being built.'); ?></p>
                    </div>
                </article>

                <?php if (! empty($hub['samples'])) : ?>
                    <div class="card-grid hub-samples">
                        <?php foreach ($hub['samples'] as $sample) :
                            set_query_var('icon', $sample['icon'] ?? '•');
                            set_query_var('title', $sample['title'] ?? '');
                            set_query_var('description', $sample['description'] ?? '');
                            set_query_var('link', '');
                            get_template_part('template-parts/components/coming-soon-card');
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <p>No results were found for this archive.</p>
            <?php endif; ?>

            <?php if ($hub) : southforsyth_render_hub_links($hub); endif; ?>
        </div>
    </section>

    <?php if ($hub) : southforsyth_render_hub_faq($hub); ?>
        <?php get_template_part('template-parts/components/newsletter'); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
