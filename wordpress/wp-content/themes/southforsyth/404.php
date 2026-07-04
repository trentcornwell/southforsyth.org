<?php get_header(); ?>

<main id="main-content" class="site-main">
    <div class="container">
        <section class="card card--post">
            <header class="card__header">
                <h1>Page not found</h1>
            </header>
            <div class="card__body">
                <p>The page you requested could not be found. Try using the navigation or search to find what you need.</p>
                <?php get_search_form(); ?>
            </div>
        </section>
    </div>
</main>

<?php get_footer(); ?>