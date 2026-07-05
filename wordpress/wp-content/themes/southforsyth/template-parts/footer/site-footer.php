<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="footer-brand">
            <h2>South Forsyth</h2>
            <span class="footer-tagline">Discover &bull; Connect &bull; Volunteer</span>
            <p>An independent, community-minded guide to life in South Forsyth, Georgia &mdash; not a government site, and not run by or for any single church or organization.</p>
        </div>
        <div>
            <h2 class="widget__title">Explore</h2>
            <?php if (is_active_sidebar('footer-1')) :
                dynamic_sidebar('footer-1');
            else : ?>
                <ul class="footer-links">
                    <?php foreach (southforsyth_get_primary_nav_items() as $item) :
                        $url = southforsyth_get_hub_url($item['key']);
                        if (! $url) {
                            continue;
                        }
                        ?>
                        <li><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($item['label']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <div>
            <h2 class="widget__title">Stay Connected</h2>
            <?php if (is_active_sidebar('footer-2')) :
                dynamic_sidebar('footer-2');
            else : ?>
                <p>Get weekend events, new restaurants, and local guides in your inbox.</p>
                <a class="btn btn-outline" href="<?php echo esc_url(home_url('/#newsletter')); ?>">Get Updates</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container site-footer__bottom">
        <p>&copy; <?php echo esc_html(date('Y')); ?> South Forsyth.org. Community-built, not government-run.</p>
    </div>
</footer>
