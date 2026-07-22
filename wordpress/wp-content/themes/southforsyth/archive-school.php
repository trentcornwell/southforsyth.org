<?php

/**
 * Verified South Forsyth schools directory.
 *
 * School profiles need level grouping and verification context that the
 * generic archive cannot provide. Queries still use the shared public-school
 * visibility rule; this template is presentation only, not a parallel data
 * path.
 */

get_header();

$level_groups = array(
    'Elementary Schools' => array('Elementary'),
    'Middle Schools'     => array('Middle', 'K-8'),
    'High Schools'       => array('High'),
);
$grouped_schools = array();
$private_schools = array();
$all_schools = get_posts(array(
    'post_type'      => 'school',
    'post_status'    => 'publish',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'meta_query'     => southforsyth_get_public_school_meta_query(),
));

foreach ($all_schools as $school) {
    $terms = wp_get_post_terms($school->ID, 'sf_school_type', array('fields' => 'names'));
    $terms = is_wp_error($terms) ? array() : $terms;

    if (in_array('Private', $terms, true)) {
        $private_schools[] = $school;
        continue;
    }

    foreach ($level_groups as $heading => $levels) {
        if (array_intersect($levels, $terms)) {
            $grouped_schools[$heading][] = $school;
            break;
        }
    }
}

if ($private_schools) {
    $grouped_schools['Private Schools'] = $private_schools;
}

$render_school_cards = static function ($schools) {
    echo '<div class="card-grid school-directory__grid">';
    foreach ($schools as $school) {
        $card = southforsyth_post_to_card($school);
        foreach ($card as $key => $value) {
            set_query_var($key, $value);
        }
        get_template_part('template-parts/components/school-card');
    }
    echo '</div>';
};
?>

<main id="main-content" class="site-main school-directory">
    <section class="section">
        <div class="container">
            <header class="section-header school-directory__header">
                <p class="eyebrow">Education directory</p>
                <h1 class="section-title">South Forsyth Schools</h1>
                <p class="section-subtitle">Browse verified schools that serve South Forsyth families, organized by school level.</p>
                <p>Only published schools confirmed through official addresses, attendance information, documented feeder relationships, or manual editorial review appear here. Information is checked against official school and district sources.</p>
                <?php if ($all_schools) : ?>
                    <p class="school-directory__count"><strong><?php echo esc_html(number_format_i18n(count($all_schools))); ?></strong> verified South Forsyth <?php echo 1 === count($all_schools) ? 'school' : 'schools'; ?></p>
                <?php endif; ?>
            </header>

            <?php if ($grouped_schools) : ?>
                <div class="school-directory__groups">
                    <?php foreach ($grouped_schools as $heading => $schools) : ?>
                        <section class="school-directory__group" aria-labelledby="<?php echo esc_attr(sanitize_title($heading)); ?>">
                            <h2 id="<?php echo esc_attr(sanitize_title($heading)); ?>" class="section-title"><?php echo esc_html($heading); ?></h2>
                            <?php $render_school_cards($schools); ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>Verified school profiles will appear here after editorial review.</p>
            <?php endif; ?>
        </div>
    </section>

    <?php get_template_part('template-parts/components/find-my-schools'); ?>

    <section class="section">
        <div class="container">
            <div class="cta school-directory__correction">
                <div class="stack">
                    <p class="eyebrow">Keep the directory accurate</p>
                    <h2 class="section-title">See something that needs a correction?</h2>
                    <p class="section-subtitle">Send the school name, the detail that changed, and an official source when possible.</p>
                </div>
                <a class="btn btn-primary" href="mailto:hello@southforsyth.org?subject=School%20directory%20correction">Submit a correction</a>
            </div>
            <?php
            $hub = southforsyth_get_hub_content('school');
            if ($hub) {
                southforsyth_render_hub_links($hub);
            }
            ?>
        </div>
    </section>

    <?php if (! empty($hub)) : southforsyth_render_hub_faq($hub); endif; ?>
</main>

<?php get_footer(); ?>
