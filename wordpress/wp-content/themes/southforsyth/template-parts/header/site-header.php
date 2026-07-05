<header class="site-header">
    <div class="container site-header__inner">
        <?php
        /**
         * .brand is a plain container, not a link, because the_custom_logo()
         * already renders its own <a href="home_url">...</a> around the
         * uploaded logo (WordPress core behavior) — wrapping that in a
         * second <a> here would produce invalid nested anchors and a
         * screen reader announcing "home" twice. The site name/tagline get
         * their own separate link to home instead.
         */
        ?>
        <div class="brand">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/icons/logo-mark.svg'); ?>" alt="South Forsyth" width="48" height="48">
                </a>
            <?php endif; ?>
            <a class="brand__link" href="<?php echo esc_url(home_url('/')); ?>">
                <span class="brand__text">
                    <strong><?php bloginfo('name'); ?></strong>
                    <span class="brand__tagline">Discover &bull; Connect &bull; Volunteer</span>
                </span>
            </a>
        </div>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-navigation" aria-label="Toggle navigation">
            <span class="visually-hidden">Toggle navigation</span>
            <span></span><span></span><span></span>
        </button>

        <nav id="site-navigation" class="site-navigation" data-nav aria-label="Primary navigation">
            <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'site-navigation__list', 'container' => false, 'fallback_cb' => 'southforsyth_primary_nav_fallback')); ?>
        </nav>
    </div>
</header>