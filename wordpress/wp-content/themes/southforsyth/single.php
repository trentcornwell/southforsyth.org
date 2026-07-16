<?php
get_header();

$is_directory_type = in_array(get_post_type(), southforsyth_get_directory_meta_post_types(), true);
$mission = $is_directory_type ? get_post_meta(get_the_ID(), 'sf_mission', true) : '';
?>

<main id="main-content" class="site-main">
    <div class="container layout-content">
        <article class="card card-post">
            <header class="card__header">
                <p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p>
                <h1><?php the_title(); ?></h1>
            </header>
            <div class="card__body">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('southforsyth-hero', array('class' => 'card__media-image')); ?>
                <?php endif; ?>

                <?php if ($mission) : // A sourced fact (see inc/meta.php's sf_mission doc comment), not generated prose — only ever shown when the source page actually stated one. ?>
                    <p class="section-subtitle"><?php echo esc_html($mission); ?></p>
                <?php endif; ?>

                <?php get_template_part('template-parts/components/post-meta'); ?>

                <?php if ($is_directory_type) :
                    set_query_var('lat', get_post_meta(get_the_ID(), 'sf_lat', true));
                    set_query_var('lng', get_post_meta(get_the_ID(), 'sf_lng', true));
                    get_template_part('template-parts/components/map-embed');
                endif; ?>

                <?php the_content(); ?>

                <?php if ($is_directory_type) :
                    $community_updated = get_post_meta(get_the_ID(), 'sf_community_updated', true);
                    $contributor = get_post_meta(get_the_ID(), 'sf_contributor_credit', true);
                    if ($community_updated) : ?>
                        <p class="card-location">
                            Community-updated as of <?php echo esc_html($community_updated); ?>
                            <?php if ($contributor) : ?>
                                — thanks to <?php echo esc_html($contributor); ?>
                            <?php endif; ?>
                        </p>
                    <?php endif;
                endif; ?>
            </div>
        </article>
        <aside class="sidebar">
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    </div>

    <?php
    // FAQs + related/nearby entities — both part of the ingestion-framework
    // work, and both generic across every directory-type post type (see
    // inc/queries.php), not school-specific, even though Schools is the
    // reference implementation.
    if ($is_directory_type) :
        $current_post = get_post();

        set_query_var('title', get_the_title() . ' FAQ');
        set_query_var('items', southforsyth_get_post_faqs(get_the_ID()));
        get_template_part('template-parts/components/faq-block');

        set_query_var('related', southforsyth_get_related_entities($current_post));
        set_query_var('nearby', southforsyth_get_nearby_places($current_post));
        get_template_part('template-parts/components/related-entities');

        if (southforsyth_get_directory_completeness(get_the_ID()) < 60) : ?>
            <section class="section section--soft">
                <div class="container">
                    <p class="section-subtitle">Help us improve this school guide.</p>
                </div>
            </section>
        <?php endif;

        set_query_var('post_id', get_the_ID());
        get_template_part('template-parts/components/suggestion-form');
    endif;
    ?>
</main>

<?php get_footer(); ?>