<?php

/**
 * Breadcrumbs component.
 * Renders on every interior page (skipped on the front page). The actual
 * breadcrumb trail is built by southforsyth_the_breadcrumbs() in
 * inc/template-functions.php — this file only supplies the container.
 */

if (! is_front_page()) : ?>
    <div class="container">
        <?php southforsyth_the_breadcrumbs(); ?>
    </div>
<?php endif; ?>