<?php

/**
 * Generic archive template shared by every custom post type.
 * Renders each post through the card component that matches its post type
 * (e.g. event-card for Events) so WordPress never needs a dedicated
 * archive-{post_type}.php file for a simple listing page.
 *
 * Also doubles as each post type's "hub page": intro copy, a coming-soon
 * notice with sample category cards (only shown while the post type has
 * zero published posts), related-section links, an FAQ, and a newsletter
 * CTA — all pulled from inc/hub-content.php via southforsyth_get_hub_content()
 * so this stays one template instead of one per post type. The live-post
 * grid below is untouched: the moment a post type has published content it
 * takes over automatically, exactly as before.
 */

get_header();

$queried_post_type = get_query_var('post_type');
$post_type_key = is_array($queried_post_type) ? reset($queried_post_type) : $queried_post_type;

// On a taxonomy term archive (e.g. /school-type/elementary/, which the
// Schools hub's level-filter links point to), WordPress's post_type query
// var is empty -- it's only set for actual post-type archives. Without
// this, $card_template below resolves to nothing and every post falls
// back to the generic content-card template instead of school-card,
// silently dropping level/sector/address/phone/website/profile-link from
// exactly the pages meant to showcase them by level.
if (! $post_type_key && is_tax()) {
    $queried_term = get_queried_object();
    if ($queried_term instanceof WP_Term) {
        $term_taxonomy = get_taxonomy($queried_term->taxonomy);
        if ($term_taxonomy && ! empty($term_taxonomy->object_type[0])) {
            $post_type_key = $term_taxonomy->object_type[0];
        }
    }
}

$card_template = southforsyth_get_card_template_for_post_type($post_type_key);
$hub = southforsyth_get_hub_content($post_type_key);

// A taxonomy term archive (e.g. /school-type/elementary/) is a *filtered
// view* of a post type's hub, not the hub itself -- $post_type_key now
// resolves correctly for card rendering (see above), but the page's own
// title/intro/empty-state should still reflect the specific term (e.g.
// "Elementary" via get_the_archive_title()), not the generic hub copy
// ("Schools"), or every level filter would look identical and a filter
// that happens to match zero posts would misleadingly claim the entire
// section is empty.
$is_filtered_taxonomy_view = is_tax();
?>

<main id="main-content" class="site-main">
    <section class="section">
        <div class="container">
            <header class="section-header">
                <h1 class="section-title">
                    <?php
                    // get_the_archive_title() intentionally returns HTML (a
                    // <span> wrapper around the term/prefix, for CSS hooks)
                    // -- it's meant to be echoed raw, the same as
                    // the_title(). esc_html() here would double-escape it
                    // into visible literal tag text.
                    echo ($hub && ! empty($hub['title']) && ! $is_filtered_taxonomy_view) ? esc_html($hub['title']) : wp_kses_post(get_the_archive_title());
                    ?>
                </h1>
                <?php if ($hub && ! empty($hub['intro']) && ! $is_filtered_taxonomy_view) : ?>
                    <div class="section-subtitle hub-intro">
                        <?php foreach ($hub['intro'] as $paragraph) : ?>
                            <p><?php echo esc_html($paragraph); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <?php the_archive_description('<div class="section-subtitle">', '</div>'); ?>
                <?php endif; ?>
            </header>

            <?php if ('school' === $post_type_key) : get_template_part('template-parts/components/find-my-schools'); ?>
                <h2 class="section-title" style="margin-top: var(--space-8);">Browse Schools</h2>
                <p class="section-subtitle">
                    <?php
                    $matching_count = (int) $wp_query->found_posts;
                    echo esc_html(sprintf('%d %s', $matching_count, 1 === $matching_count ? 'school' : 'schools'));
                    echo $is_filtered_taxonomy_view ? esc_html(' matching this filter.') : esc_html(' published.');
                    ?>
                </p>
            <?php endif; ?>

            <?php if (have_posts()) : ?>
                <div class="card-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php if ($card_template) :
                            $card = southforsyth_post_to_card(get_post());
                            set_query_var('eyebrow', $card['eyebrow']);
                            set_query_var('title', $card['title']);
                            set_query_var('description', $card['description']);
                            set_query_var('link', $card['link']);
                            set_query_var('date', $card['date'] ?? '');
                            set_query_var('address', $card['address'] ?? '');
                            set_query_var('area', $card['area'] ?? '');
                            set_query_var('city', $card['city'] ?? '');
                            set_query_var('location', $card['location'] ?? '');
                            set_query_var('grades', $card['grades'] ?? '');
                            set_query_var('level', $card['level'] ?? '');
                            set_query_var('sector', $card['sector'] ?? '');
                            set_query_var('city_meta', $card['city_meta'] ?? '');
                            set_query_var('state', $card['state'] ?? '');
                            set_query_var('zip', $card['zip'] ?? '');
                            set_query_var('phone', $card['phone'] ?? '');
                            set_query_var('website', $card['website'] ?? '');
                            get_template_part($card_template);
                        else :
                            get_template_part('template-parts/content', 'card');
                        endif; ?>
                    <?php endwhile; ?>
                </div>
            <?php elseif ($hub && ! $is_filtered_taxonomy_view) : ?>
                <article class="card card-placeholder hub-empty-notice">
                    <div class="card__body">
                        <span class="badge-soon">Coming soon</span>
                        <h2><?php echo esc_html($hub['empty_title'] ?? 'No listings published yet'); ?></h2>
                        <p><?php echo esc_html($hub['empty_description'] ?? 'This section is being built.'); ?></p>
                    </div>
                </article>

                <?php if (! empty($hub['samples'])) : ?>
                    <div class="card-grid hub-samples">
                        <?php foreach ($hub['samples'] as $sample) :
                            set_query_var('icon', $sample['icon'] ?? '•');
                            set_query_var('title', $sample['title'] ?? '');
                            set_query_var('description', $sample['description'] ?? '');
                            set_query_var('link', '');
                            get_template_part('template-parts/components/coming-soon-card');
                        endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <p>No results were found for this archive.</p>
            <?php endif; ?>

            <?php if ($hub) : southforsyth_render_hub_level_links($hub); southforsyth_render_hub_links($hub); endif; ?>
        </div>
    </section>

    <?php if ($hub) : southforsyth_render_hub_faq($hub); ?>
        <?php get_template_part('template-parts/components/newsletter'); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
