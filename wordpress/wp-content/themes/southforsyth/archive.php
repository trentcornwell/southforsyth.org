<?php get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container">
        <header class="section__heading">
            <h1><?php the_archive_title(); ?></h1>
            <?php the_archive_description('<p>', '</p>'); ?>
        </header>
        <div class="grid grid--3">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/content', 'card'); ?>
                <?php endwhile;
            else : ?>
                <p>No results were found for this archive.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>