<?php

/**
 * Community spotlight component.
 * Use to highlight a resident, volunteer, or local organization.
 * TODO: Replace with a dynamic spotlight once submissions or editorial
 * picks are available.
 */

$quote = get_query_var('quote') ?: 'Moving here two years ago was easy because this community made it feel like home from day one.';
$name = get_query_var('name') ?: 'Community member';
$role = get_query_var('role') ?: 'South Forsyth resident';
?>
<section class="section" aria-labelledby="spotlight-title">
    <div class="container">
        <div class="card card-spotlight">
            <p class="eyebrow" id="spotlight-title">Community spotlight</p>
            <p class="card-spotlight__quote">&ldquo;<?php echo esc_html($quote); ?>&rdquo;</p>
            <div>
                <p class="card-spotlight__name"><?php echo esc_html($name); ?></p>
                <p class="card-spotlight__role"><?php echo esc_html($role); ?></p>
            </div>
        </div>
    </div>
</section>
