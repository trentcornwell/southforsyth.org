<?php get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container grid grid--sidebar">
        <article class="content-area card card--post">
            <header class="card__header">
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="card__body">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('southforsyth-hero', array('class' => 'card__media-image')); ?>
                <?php endif; ?>
                <?php the_content(); ?>
            </div>
        </article>
        <aside class="sidebar">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    </div>
</main>

<?php get_footer(); ?>