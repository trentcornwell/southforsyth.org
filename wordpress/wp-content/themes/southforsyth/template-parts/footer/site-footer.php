<footer class="site-footer">
    <div class="container site-footer__inner">
        <div>
            <h2>South Forsyth</h2>
            <p>Trusted local guidance for life in South Forsyth, Georgia.</p>
        </div>
        <div>
            <?php if (is_active_sidebar('footer-1')) : dynamic_sidebar('footer-1');
            endif; ?>
        </div>
        <div>
            <?php if (is_active_sidebar('footer-2')) : dynamic_sidebar('footer-2');
            endif; ?>
        </div>
    </div>
</footer>