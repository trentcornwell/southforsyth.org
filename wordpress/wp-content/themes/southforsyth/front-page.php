<?php

/**
 * Homepage for SouthForsyth.org.
 *
 * This page introduces the site as a useful community guide while keeping
 * the implementation lightweight and ready for future dynamic content.
 */

get_header();

$featured_places = southforsyth_get_featured_places(3);
$latest_events = southforsyth_get_latest_items('event', 3);
$latest_guides = southforsyth_get_latest_items('guide', 3);
$featured_schools = southforsyth_get_latest_items('school', 3, array(), 'Verified school');

$guide_sections = array(
    array(
        'icon' => 'E',
        'title' => 'Events',
        'description' => 'Find local happenings, family activities, school events, church programs, markets, and seasonal gatherings.',
        'link' => southforsyth_get_hub_url('event'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'P',
        'title' => 'Parks & Recreation',
        'description' => 'Explore parks, playgrounds, trails, greenway access points, and outdoor places worth knowing.',
        'link' => southforsyth_get_hub_url('park'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'S',
        'title' => 'Schools',
        'description' => 'Get oriented to public, private, and family education resources serving South Forsyth.',
        'link' => southforsyth_get_hub_url('school'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'C',
        'title' => 'Churches',
        'description' => 'Discover congregations, ministries, service opportunities, and faith communities across the area.',
        'link' => southforsyth_get_hub_url('church'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'R',
        'title' => 'Restaurants',
        'description' => 'Browse local restaurants, coffee shops, casual family spots, and places for a night out.',
        'link' => southforsyth_get_hub_url('restaurant'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'B',
        'title' => 'Local Businesses',
        'description' => 'Support nearby shops, services, organizations, and trusted local businesses.',
        'link' => southforsyth_get_hub_url('business'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'F',
        'title' => 'Family Resources',
        'description' => 'Find practical help for parents, kids, students, seniors, and families building life here.',
        'link' => southforsyth_get_hub_url('community_resource'),
        'status' => 'Guide',
    ),
    array(
        'icon' => 'N',
        'title' => 'New to South Forsyth',
        'description' => 'Start with essentials for newcomers: neighborhoods, schools, services, and everyday local rhythms.',
        'link' => southforsyth_get_hub_url('new-resident-guide'),
        'status' => 'Start here',
    ),
);
?>

<main id="main-content" class="site-main">
    <?php get_template_part('template-parts/components/hero'); ?>

    <section class="section section--accent" id="what-is-south-forsyth">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Community definition');
            set_query_var('title', 'What Is South Forsyth?');
            set_query_var('subtitle', 'A clear working definition for what this guide covers - and what it does not claim to be.');
            set_query_var('align', 'center');
            get_template_part('template-parts/components/section-header');
            set_query_var('variant', 'compact');
            get_template_part('template-parts/components/coverage-definition');
            ?>
            <p class="coverage-definition__cta">
                <a class="btn btn-primary" href="<?php echo esc_url(home_url('/what-is-south-forsyth/')); ?>">Read the Full Coverage Guide</a>
            </p>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/coming-soon-card', $guide_sections, array(
        'id' => 'guide',
        'eyebrow' => 'Explore the guide',
        'title' => 'Helpful Local Sections',
        'intro' => 'Use SouthForsyth.org as a starting point for finding places to go, people to meet, resources to share, and organizations serving our community.',
        'align' => 'center',
    ));

    if ($featured_schools) {
        southforsyth_render_card_section('template-parts/components/school-card', $featured_schools, array(
            'id' => 'schools',
            'eyebrow' => 'Verified South Forsyth schools',
            'title' => 'Explore Local Schools',
            'intro' => 'Start with verified school profiles, then browse the complete directory by elementary, middle, and high school level.',
            'align' => 'center',
            'soft' => true,
            'cta_link' => southforsyth_get_hub_url('school'),
            'cta_text' => 'Browse all verified schools',
        ));
    }

    get_template_part('template-parts/components/find-my-schools');

    southforsyth_render_card_section('template-parts/components/directory-card', $featured_places, array(
        'id' => 'featured',
        'eyebrow' => 'Featured locally',
        'title' => 'Featured Places',
        'intro' => 'A quick look at featured places and resources from the guide.',
        'align' => 'center',
        'soft' => true,
    ));

    southforsyth_render_card_section('template-parts/components/event-card', $latest_events, array(
        'id' => 'latest-events',
        'eyebrow' => 'Latest events',
        'title' => 'Recently Added Events',
        'intro' => 'Newly published events from around South Forsyth.',
        'align' => 'center',
    ));

    southforsyth_render_card_section('template-parts/components/guide-card', $latest_guides, array(
        'id' => 'latest-guides',
        'eyebrow' => 'Latest guides',
        'title' => 'Recently Added Guides',
        'intro' => 'Evergreen local guides as they are published.',
        'align' => 'center',
        'soft' => true,
    ));
    ?>

    <section class="section section--soft" id="purpose">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Community purpose');
            set_query_var('title', 'A Guide Built to Keep Neighbors Connected');
            set_query_var('subtitle', 'SouthForsyth.org exists to help families, neighbors, churches, schools, and local organizations stay connected to the people and places around them.');
            set_query_var('align', 'center');
            get_template_part('template-parts/components/section-header');
            ?>
            <div class="audience-grid">
                <div class="audience-item">
                    <h3>Families</h3>
                    <p>Find schools, parks, events, restaurants, and everyday resources that make local life easier.</p>
                </div>
                <div class="audience-item">
                    <h3>Neighbors</h3>
                    <p>Discover what is happening nearby and learn more about the communities that make up South Forsyth.</p>
                </div>
                <div class="audience-item">
                    <h3>Churches & Ministries</h3>
                    <p>Share service opportunities, programs, and welcoming places for people looking for connection.</p>
                </div>
                <div class="audience-item">
                    <h3>Schools & Organizations</h3>
                    <p>Help residents find trusted information, local programs, and ways to get involved.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="suggest-resource">
        <div class="container">
            <div class="cta cta--resource">
                <div class="stack">
                    <p class="eyebrow">Local resource CTA</p>
                    <h2 class="section-title">Suggest a Local Resource</h2>
                    <p class="section-subtitle">Know about an event, business, church, ministry, school resource, nonprofit, or community organization that belongs in the guide? Send it our way so this site can keep growing with the community.</p>
                </div>
                <a class="btn btn-primary" href="mailto:hello@southforsyth.org?subject=South%20Forsyth%20Guide%20Resource%20Suggestion">Submit a Local Resource</a>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/components/newsletter'); ?>
</main>

<?php get_footer(); ?>
