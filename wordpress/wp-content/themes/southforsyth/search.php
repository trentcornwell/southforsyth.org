<?php get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container">
        <header class="section__heading">
            <h1>Search results for “<?php echo esc_html(get_search_query()); ?>”</h1>
        </header>
        <div class="grid grid--3">
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <?php get_template_part('template-parts/content', 'card'); ?>
                <?php endwhile;
            else : ?>
                <p>No matching content was found.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php get_footer(); ?>