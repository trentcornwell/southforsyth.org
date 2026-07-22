<?php

/**
 * Single School template.
 *
 * A dedicated template (rather than single.php's generic directory
 * handling) because schools need several distinct, school-only sections
 * (attendance/feeder, programs) that don't apply to any other directory
 * type. Reuses the same shared helpers, meta fields, and components as
 * every other directory-type single page (FAQ block, related/nearby
 * entities, suggestion form) — see docs/content-platform-architecture.md.
 *
 * Every section below renders only when the underlying data exists. No
 * empty labels, no placeholder claims, no invented facts.
 */

get_header();

while (have_posts()) :
    the_post();
    $post_id = get_the_ID();

    $terms = wp_get_post_terms($post_id, 'sf_school_type', array('fields' => 'names'));
    $terms = (! empty($terms) && ! is_wp_error($terms)) ? $terms : array();
    $level = '';
    foreach (array('Elementary', 'Middle', 'High', 'K-8') as $key) {
        if (in_array($key, $terms, true)) {
            $level = $key;
            break;
        }
    }
    $sector = '';
    foreach (array('Public', 'Private', 'Charter', 'Homeschool Resource') as $key) {
        if (in_array($key, $terms, true)) {
            $sector = $key;
            break;
        }
    }

    $grades = get_post_meta($post_id, 'sf_grades_served', true);
    $address = get_post_meta($post_id, 'sf_address', true);
    $city = get_post_meta($post_id, 'sf_city', true);
    $state = get_post_meta($post_id, 'sf_state', true);
    $zip = get_post_meta($post_id, 'sf_zip', true);
    $full_address = trim(implode(', ', array_filter(array($address, trim($city . ' ' . $state . ' ' . $zip)))));
    $phone = get_post_meta($post_id, 'sf_phone', true);
    $website = get_post_meta($post_id, 'sf_website', true);
    $staff_directory_url = get_post_meta($post_id, 'sf_staff_directory_url', true);
    $principal = get_post_meta($post_id, 'sf_principal_name', true);
    $hours = get_post_meta($post_id, 'sf_hours', true);
    $district = get_post_meta($post_id, 'sf_district', true);
    $source_url = get_post_meta($post_id, 'sf_source_url', true);
    $last_verified = get_post_meta($post_id, 'sf_last_verified', true);
    $boundary_url = get_post_meta($post_id, 'sf_boundary_url', true);
    $feeder_pattern = get_post_meta($post_id, 'sf_feeder_pattern', true);
    $programs = get_post_meta($post_id, 'sf_notable_programs', true);
    $mission = get_post_meta($post_id, 'sf_mission', true);
    $editorial_summary = get_post_meta($post_id, 'sf_editorial_summary', true);
    $activities = get_post_meta($post_id, 'sf_extracurricular_activities', true);
    $athletics = get_post_meta($post_id, 'sf_athletics', true);
    $enrollment_url = get_post_meta($post_id, 'sf_enrollment_information_url', true);
    $parent_resources_url = get_post_meta($post_id, 'sf_parent_resources_url', true);
    $transportation_url = get_post_meta($post_id, 'sf_transportation_information_url', true);
    $enrichment_checked = get_post_meta($post_id, 'sf_enrichment_last_checked', true);
    $field_sources = json_decode((string) get_post_meta($post_id, 'sf_enrichment_source_notes', true), true);
    $field_sources = is_array($field_sources) ? $field_sources : array();
    $lat = get_post_meta($post_id, 'sf_lat', true);
    $lng = get_post_meta($post_id, 'sf_lng', true);
    $directions_url = $full_address
        ? 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($full_address)
        : '';

    $type_parts = array_filter(array($sector, $level ? $level . ' School' : ''));
    ?>

    <main id="main-content" class="site-main">
        <div class="container container-narrow school-profile">
            <article class="card card-post">
                <header class="card__header">
                    <p class="eyebrow"><?php echo esc_html($district ?: 'School'); ?></p>
                    <h1><?php the_title(); ?></h1>
                    <?php if (! empty($type_parts) || $grades) : ?>
                        <p class="section-subtitle">
                            <?php echo esc_html(implode(' · ', $type_parts)); ?>
                            <?php if ($grades) : ?>
                                <?php echo $type_parts ? ' · ' : ''; ?>Grades <?php echo esc_html($grades); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <div class="card__links">
                        <?php if ($website) : ?>
                            <a class="btn btn-primary" href="<?php echo esc_url($website); ?>" rel="noopener" target="_blank">Official website</a>
                        <?php endif; ?>
                        <?php if ($phone) : ?>
                            <a class="text-link" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a>
                        <?php endif; ?>
                        <?php if ($directions_url) : ?>
                            <a class="text-link" href="<?php echo esc_url($directions_url); ?>" rel="noopener" target="_blank">Directions</a>
                        <?php endif; ?>
                    </div>
                    <?php if ($full_address) : ?>
                        <p class="card-location"><?php echo esc_html($full_address); ?></p>
                    <?php endif; ?>
                </header>

                <div class="card__body">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('southforsyth-hero', array('class' => 'card__media-image')); ?>
                    <?php endif; ?>

                    <?php
                    // Overview: a real official statement if the source stated one
                    // (sf_mission), otherwise a factual summary built only from
                    // stored fields (southforsyth_get_school_factual_summary) —
                    // never promotional language, never a placeholder.
                    $overview = $editorial_summary ?: ($mission ?: southforsyth_get_excerpt($post_id, 40));
                    ?>
                    <?php if ($overview) : ?>
                        <section class="section-block">
                            <h2>Overview</h2>
                            <p><?php echo esc_html($overview); ?></p>
                        </section>
                    <?php endif; ?>

                    <?php
                    $info_items = array_filter(array(
                        'District'       => $district,
                        'Principal'      => $principal,
                        'Grades'         => $grades,
                        'School type'    => implode(' · ', $type_parts),
                        'Designation'    => $sector,
                        'Address'        => $full_address,
                        'Phone'          => $phone,
                        'School hours'   => $hours,
                    ));
                    ?>
                    <?php if (! empty($info_items)) : ?>
                        <section class="section-block">
                            <h2>School Information</h2>
                            <ul class="card-meta">
                                <?php foreach ($info_items as $label => $value) : ?>
                                    <li><strong><?php echo esc_html($label); ?>:</strong> <?php echo esc_html($value); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if ($boundary_url || $feeder_pattern) : ?>
                        <section class="section-block">
                            <h2>Attendance &amp; Feeder Information</h2>
                            <ul class="card-meta">
                                <?php if ($feeder_pattern) : ?>
                                    <li><strong>Feeder pattern:</strong> <?php echo esc_html($feeder_pattern); ?></li>
                                <?php endif; ?>
                                <?php if ($boundary_url) : ?>
                                    <li><strong>Attendance boundary:</strong> <a href="<?php echo esc_url($boundary_url); ?>" rel="noopener" target="_blank">Official boundary map</a></li>
                                <?php endif; ?>
                            </ul>
                            <p class="card-location">Attendance boundaries and feeder patterns can change. Families should confirm current assignments directly with Forsyth County Schools.</p>
                        </section>
                    <?php endif; ?>

                    <?php if ($programs) : ?>
                        <section class="section-block">
                            <h2>Programs</h2>
                            <p><?php echo esc_html($programs); ?></p>
                        </section>
                    <?php endif; ?>

                    <?php if ($activities || $athletics) : ?>
                        <section class="section-block">
                            <h2>Student Life</h2>
                            <?php if ($activities) : ?>
                                <h3>Activities</h3>
                                <p><?php echo esc_html($activities); ?></p>
                            <?php endif; ?>
                            <?php if ($athletics) : ?>
                                <h3>Athletics</h3>
                                <p><?php echo esc_html($athletics); ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if ($enrollment_url || $parent_resources_url || $transportation_url) : ?>
                        <section class="section-block">
                            <h2>Family Resources</h2>
                            <ul class="card-meta">
                                <?php if ($enrollment_url) : ?><li><a href="<?php echo esc_url($enrollment_url); ?>" rel="noopener" target="_blank">Official enrollment information</a></li><?php endif; ?>
                                <?php if ($parent_resources_url) : ?><li><a href="<?php echo esc_url($parent_resources_url); ?>" rel="noopener" target="_blank">Official parent resources</a></li><?php endif; ?>
                                <?php if ($transportation_url) : ?><li><a href="<?php echo esc_url($transportation_url); ?>" rel="noopener" target="_blank">Official transportation information</a></li><?php endif; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if ($lat && $lng) :
                        set_query_var('lat', $lat);
                        set_query_var('lng', $lng);
                        get_template_part('template-parts/components/map-embed');
                    endif; ?>

                    <?php if ($staff_directory_url || $phone || $directions_url || $boundary_url) : ?>
                        <section class="section-block">
                            <h2>Contact &amp; Links</h2>
                            <ul class="card-meta">
                                <?php if ($staff_directory_url) : ?>
                                    <li><a href="<?php echo esc_url($staff_directory_url); ?>" rel="noopener" target="_blank">Staff directory</a></li>
                                <?php endif; ?>
                                <?php if ($phone) : ?>
                                    <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></li>
                                <?php endif; ?>
                                <?php if ($directions_url) : ?>
                                    <li><a href="<?php echo esc_url($directions_url); ?>" rel="noopener" target="_blank">Directions</a></li>
                                <?php endif; ?>
                                <?php if ($boundary_url) : ?>
                                    <li><a href="<?php echo esc_url($boundary_url); ?>" rel="noopener" target="_blank">Boundary information</a></li>
                                <?php endif; ?>
                            </ul>
                        </section>
                    <?php endif; ?>

                    <?php if ($source_url || $last_verified) : ?>
                        <section class="section-block school-profile__source" aria-labelledby="school-source-heading">
                            <h2 id="school-source-heading">Source &amp; verification</h2>
                            <p class="card-location">
                            <?php if ($source_url) : ?>
                                Source: <a href="<?php echo esc_url($source_url); ?>" rel="noopener" target="_blank">Official Forsyth County Schools page</a>.
                            <?php endif; ?>
                            <?php if ($last_verified) : ?>
                                Last verified <?php echo esc_html($last_verified); ?>.
                            <?php endif; ?>
                            <?php if ($enrichment_checked) : ?>
                                Enrichment sources checked <?php echo esc_html($enrichment_checked); ?>.
                            <?php endif; ?>
                            </p>
                            <?php if ($field_sources) : ?>
                                <details>
                                    <summary>Field-level official sources</summary>
                                    <ul class="card-meta">
                                        <?php foreach ($field_sources as $field => $source) :
                                            if (empty($source['source_url'])) {
                                                continue;
                                            }
                                            ?>
                                            <li><strong><?php echo esc_html(ucwords(str_replace('_', ' ', preg_replace('/^sf_/', '', $field)))); ?>:</strong> <a href="<?php echo esc_url($source['source_url']); ?>" rel="noopener" target="_blank">official source</a><?php echo ! empty($source['checked_at']) ? esc_html(' · checked ' . $source['checked_at']) : ''; ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </details>
                            <?php endif; ?>
                            <p><a href="mailto:hello@southforsyth.org?subject=<?php echo esc_attr(rawurlencode('Correction: ' . get_the_title())); ?>">Report a correction to this profile</a></p>
                        </section>
                    <?php endif; ?>

                    <?php
                    $community_updated = get_post_meta($post_id, 'sf_community_updated', true);
                    $contributor = get_post_meta($post_id, 'sf_contributor_credit', true);
                    if ($community_updated) : ?>
                        <p class="card-location">
                            Community-updated as of <?php echo esc_html($community_updated); ?>
                            <?php if ($contributor) : ?>
                                — thanks to <?php echo esc_html($contributor); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <?php get_template_part('template-parts/components/find-my-schools'); ?>

        <?php
        $current_post = get_post();

        set_query_var('title', get_the_title() . ' FAQ');
        set_query_var('items', southforsyth_get_post_faqs($post_id));
        get_template_part('template-parts/components/faq-block');

        set_query_var('related', southforsyth_get_related_entities($current_post));
        set_query_var('nearby', southforsyth_get_nearby_places($current_post));
        get_template_part('template-parts/components/related-entities');

        if (southforsyth_get_school_completeness($post_id) < 60) : ?>
            <section class="section section--soft">
                <div class="container">
                    <p class="section-subtitle">Help us improve this school guide.</p>
                </div>
            </section>
        <?php endif;

        set_query_var('post_id', $post_id);
        get_template_part('template-parts/components/suggestion-form');
        ?>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>
