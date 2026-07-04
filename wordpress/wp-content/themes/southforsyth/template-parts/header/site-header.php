<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/logo-mark.svg'); ?>" alt="South Forsyth" width="48" height="48">
            <?php endif; ?>
            <span class="brand__text">
                <strong><?php bloginfo('name'); ?></strong>
                <span><?php bloginfo('description'); ?></span>
            </span>
        </a>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-navigation" aria-label="Toggle navigation">
            <span class="visually-hidden">Toggle navigation</span>
            <span></span><span></span><span></span>
        </button>

        <nav id="site-navigation" class="site-navigation" data-nav aria-label="Primary navigation">
            <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'site-navigation__list', 'container' => false, 'fallback_cb' => false)); ?>
        </nav>
    </div>
</header>