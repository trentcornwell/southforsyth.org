<?php

/**
 * Community spotlight component.
 * Use to highlight a resident, volunteer, or local organization.
 * No post type backs this yet — it's plain placeholder copy, not a real
 * quote. TODO: connect to a real submission/editorial-pick workflow (see
 * the "community-recommendations" entry in inc/community-platform.php)
 * once one exists.
 */

$quote = get_query_var('quote') ?: 'This is where a resident, volunteer, or local organization spotlight will appear once one is selected.';
$name = get_query_var('name') ?: 'Featured resident';
$role = get_query_var('role') ?: 'Example placeholder';
?>
<section class="section" aria-labelledby="spotlight-title">
    <div class="container">
        <div class="card card-spotlight">
            <h2 class="eyebrow" id="spotlight-title">Community spotlight</h2>
            <p class="card-spotlight__quote">&ldquo;<?php echo esc_html($quote); ?>&rdquo;</p>
            <div>
                <p class="card-spotlight__name"><?php echo esc_html($name); ?></p>
                <p class="card-spotlight__role"><?php echo esc_html($role); ?></p>
            </div>
        </div>
    </div>
</section>
