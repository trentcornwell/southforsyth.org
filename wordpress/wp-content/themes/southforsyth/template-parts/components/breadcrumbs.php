<?php

/**
 * Breadcrumbs component.
 * Intended for interior pages once WordPress content hierarchy is in place.
 */

if (! function_exists('southforsyth_breadcrumbs')) {
    function southforsyth_breadcrumbs()
    {
        echo '<nav class="breadcrumbs" aria-label="Breadcrumb">';
        echo '<ol class="breadcrumbs__list">';
        echo '<li class="breadcrumbs__item"><a href="' . esc_url(home_url('/')) . '">Home</a></li>';

        if (is_single()) {
            echo '<li class="breadcrumbs__item"><span>' . esc_html(get_the_title()) . '</span></li>';
        } elseif (is_page()) {
            echo '<li class="breadcrumbs__item"><span>' . esc_html(get_the_title()) . '</span></li>';
        }

        echo '</ol>';
        echo '</nav>';
    }
}

if (! is_front_page()) : ?>
    <div class="container">
        <?php southforsyth_breadcrumbs(); ?>
    </div>
<?php endif; ?>