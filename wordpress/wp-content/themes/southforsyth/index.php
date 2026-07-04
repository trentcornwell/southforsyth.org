<?php get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container layout-content">
        <div class="stack">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <article class="card card-post">
                        <header class="card__header">
                            <p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        </header>
                        <div class="card__body">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile;
            else : ?>
                <p>No content found.</p>
            <?php endif; ?>
        </div>
        <aside class="sidebar">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    </div>
</main>

<?php get_footer(); ?>