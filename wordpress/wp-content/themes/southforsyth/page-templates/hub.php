<?php

/**
 * Template Name: South Forsyth Hub Page
 *
 * Reusable landing-page template for hub sections that don't have their own
 * custom post type archive: Things To Do, New Resident Guide, and Weekend
 * Guide (see inc/page-provisioning.php, which creates these three pages
 * automatically and assigns this template). Sections that DO have a custom
 * post type (Events, Restaurants, Parks, Schools, Churches, Neighborhoods,
 * Business Directory) use the richer archive.php instead, since that
 * template also needs to show live posts once they're published.
 *
 * All copy lives in inc/hub-content.php, keyed by page slug via
 * southforsyth_get_hub_content() — this file is a pure renderer.
 */

get_header();

$hub = southforsyth_get_hub_content(get_post_field('post_name', get_the_ID()));
?>

<main id="main-content" class="site-main">
    <section class="section">
        <div class="container">
            <header class="section-header">
                <h1 class="section-title"><?php echo esc_html($hub['title'] ?? get_the_title()); ?></h1>
                <?php if (! empty($hub['intro'])) : ?>
                    <div class="section-subtitle hub-intro">
                        <?php foreach ($hub['intro'] as $paragraph) : ?>
                            <p><?php echo esc_html($paragraph); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </header>

            <article class="card card-placeholder hub-empty-notice">
                <div class="card__body">
                    <span class="badge-soon">Coming soon</span>
                    <h2><?php echo esc_html($hub['empty_title'] ?? 'This guide is being built'); ?></h2>
                    <p><?php echo esc_html($hub['empty_description'] ?? 'Check back as real content is published.'); ?></p>
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

            <?php southforsyth_render_hub_links($hub); ?>

            <?php
            // No explicit have_posts()/the_post() loop here, matching
            // page.php's convention: WordPress has already set up the
            // global $post for this singular page before the template
            // loads, so the_content() renders whatever an editor adds to
            // this page in wp-admin (empty by default on auto-created pages).
            ?>
            <div class="hub-page-content flow">
                <?php the_content(); ?>
            </div>
        </div>
    </section>

    <?php southforsyth_render_hub_faq($hub); ?>

    <?php get_template_part('template-parts/components/newsletter'); ?>
</main>

<?php get_footer(); ?>
