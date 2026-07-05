<header class="site-header">
    <div class="container site-header__inner">
        <?php
        /**
         * .brand is a plain container, not a link, because the_custom_logo()
         * already renders its own <a href="home_url">...</a> around the
         * uploaded logo (WordPress core behavior) — wrapping that in a
         * second <a> here would produce invalid nested anchors. When no
         * custom logo is set, the bundled optimized header logo gets one
         * home link, with text shown only if the image file is missing.
         */
        ?>
        <div class="brand">
            <?php if (has_custom_logo()) : ?>
                <?php the_custom_logo(); ?>
            <?php else : ?>
                <?php $fallback_logo = get_template_directory() . '/assets/images/logo/southforsyth-logo-header.png'; ?>
                <?php $site_name = get_bloginfo('name'); ?>
                <a class="brand__link brand__link--image" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php echo esc_attr($site_name); ?>">
                    <?php if (file_exists($fallback_logo)) : ?>
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo/southforsyth-logo-header.png'); ?>" alt="<?php echo esc_attr($site_name); ?>" width="48" height="48">
                    <?php else : ?>
                        <span class="brand__text">
                            <strong><?php bloginfo('name'); ?></strong>
                            <span class="brand__tagline">Discover &bull; Connect &bull; Volunteer</span>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
        </div>

        <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-navigation" aria-label="Toggle navigation">
            <span class="visually-hidden">Toggle navigation</span>
            <span></span><span></span><span></span>
        </button>

        <nav id="site-navigation" class="site-navigation" data-nav aria-label="Primary navigation">
            <?php wp_nav_menu(array('theme_location' => 'primary', 'menu_class' => 'site-navigation__list', 'container' => false, 'fallback_cb' => 'southforsyth_primary_nav_fallback')); ?>
        </nav>

        <div class="header-search" aria-label="Search coming soon">
            <span class="header-search__icon" aria-hidden="true"></span>
            <span>Search coming soon</span>
        </div>
    </div>
</header>
