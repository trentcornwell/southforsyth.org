<?php

/**
 * Homepage: PREVIEW / LAUNCHING-SOON VERSION.
 *
 * This intentionally does not query the custom post types yet. The full
 * content-platform architecture (post types, taxonomies, post meta, and
 * the southforsyth_get_latest_items() / southforsyth_render_card_section()
 * query helpers) is still in place and untouched — see inc/post-types.php,
 * inc/queries.php, and docs/content-platform-architecture.md. Nothing has
 * been published yet, so this page is a static, honest "here's what we're
 * building" preview instead of a live portal showing empty sections.
 *
 * To bring back the live, data-driven homepage once real content exists:
 * replace a "Coming Soon" section below with a
 * southforsyth_render_card_section() call fed by
 * southforsyth_get_latest_items('event', 3, $fallback) (etc.) — the exact
 * pattern used by archive.php and search.php already. Each spot where that
 * applies is marked with a TODO comment.
 */

get_header();

// TODO: once Event posts exist, replace with
// southforsyth_get_latest_items('event', 3, $fallback) and an
// event-card-based southforsyth_render_card_section() call.
//
// Each card below links to its real hub page (a live CPT archive via
// archive.php, or a standalone page via page-templates/hub.php) rather than
// nowhere — every one of these is a real, working URL today, even before
// any post type has published content, because the hub page itself
// explains what's coming and shows sample categories. See
// inc/hub-content.php.
$what_were_building = array(
    array('icon' => 'E', 'title' => 'Weekend Events', 'description' => 'Markets, festivals, school happenings, and things to do, organized around how families plan weekends.', 'link' => southforsyth_get_hub_url('event'), 'status' => 'Building'),
    array('icon' => 'R', 'title' => 'Restaurants & Coffee', 'description' => 'Local dining, coffee shops, brunch spots, and openings, presented as useful guides instead of hype.', 'link' => southforsyth_get_hub_url('restaurant'), 'status' => 'Building'),
    array('icon' => 'P', 'title' => 'Parks & Trails', 'description' => 'Greenway access points, playgrounds, parks, and outdoor routes for everyday use.', 'link' => southforsyth_get_hub_url('park'), 'status' => 'Building'),
    array('icon' => 'S', 'title' => 'Schools & Family Resources', 'description' => 'Practical orientation for school zones, family routines, and kid-friendly local resources.', 'link' => southforsyth_get_hub_url('school'), 'status' => 'Building'),
    array('icon' => 'N', 'title' => 'Neighborhood Guides', 'description' => 'Clear area-by-area context for Halcyon, Vickery, Windermere, Denmark, and nearby communities.', 'link' => southforsyth_get_hub_url('neighborhood'), 'status' => 'Building'),
    array('icon' => 'C', 'title' => 'Churches & Community', 'description' => 'A respectful local directory for congregations, nonprofits, service groups, and gathering places.', 'link' => southforsyth_get_hub_url('church'), 'status' => 'Building'),
    array('icon' => 'B', 'title' => 'Local Business Directory', 'description' => 'A trusted discovery layer for nearby businesses once real listings are ready to review.', 'link' => southforsyth_get_hub_url('business'), 'status' => 'Building'),
    array('icon' => 'W', 'title' => 'Weekly Local Briefing', 'description' => 'A future weekly roundup of new guides, weekend ideas, local openings, and community updates.', 'link' => '#newsletter', 'status' => 'Planned'),
);

// TODO: once Guide posts exist, replace with
// southforsyth_get_latest_items('guide', 6, $fallback) and a
// guide-card-based southforsyth_render_card_section() call.
$early_guides = array(
    array('icon' => 'S', 'title' => 'What Is South Forsyth?', 'description' => 'A plain-English starter guide to the area name, geography, and community identity.', 'status' => 'Starter guide'),
    array('icon' => 'G', 'title' => 'Big Creek Greenway', 'description' => 'An in-progress guide to access points, trail etiquette, parking, and family-friendly ways to use it.', 'status' => 'In progress'),
    array('icon' => 'H', 'title' => 'Halcyon Area Guide', 'description' => 'A starter overview for the shopping, dining, neighborhoods, and daily routines around Halcyon.', 'status' => 'Starter guide'),
    array('icon' => 'D', 'title' => 'Denmark High School Area', 'description' => 'An in-progress area guide for families orienting around Denmark High and nearby neighborhoods.', 'status' => 'In progress'),
    array('icon' => 'V', 'title' => 'Vickery Village Area', 'description' => 'A starter guide for the village center, nearby neighborhoods, restaurants, and walkable errands.', 'status' => 'Starter guide'),
    array('icon' => 'M', 'title' => 'Moving to South Forsyth', 'description' => 'An in-progress newcomer guide for getting oriented before and after a move.', 'status' => 'In progress'),
);
?>

<main id="main-content" class="site-main">
    <?php get_template_part('template-parts/components/hero'); ?>

    <section class="section section--soft" id="about">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'The area');
            set_query_var('title', 'What is South Forsyth?');
            set_query_var('subtitle', '');
            set_query_var('align', 'left');
            get_template_part('template-parts/components/section-header');
            ?>
            <div class="split-panel">
                <div class="stack">
                    <p>South Forsyth isn&rsquo;t an incorporated city &mdash; there&rsquo;s no city hall, mayor, or official municipal boundary. It&rsquo;s the name residents use for the southern part of Forsyth County, Georgia, stretching from the Big Creek area down to the Cumming and Alpharetta border.</p>
                    <p>SouthForsyth.org exists because even without a city limit sign, this is a real, connected community &mdash; and it deserves a single, trustworthy place to keep up with it.</p>
                </div>
                <?php
                set_query_var('eyebrow', 'Good to know');
                set_query_var('title', 'Not an official city');
                set_query_var('description', 'South Forsyth is a community identity, not a legal or municipal boundary. You won\'t find it on a city charter — but you\'ll find it in how residents describe where they live.');
                set_query_var('link_text', 'See what we\'re building');
                set_query_var('link_url', '#building');
                get_template_part('template-parts/components/sidebar-callout');
                ?>
            </div>
            <?php get_template_part('template-parts/components/local-definition-block'); ?>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/coming-soon-card', $what_were_building, array(
        'id' => 'building',
        'eyebrow' => 'What we\'re building',
        'title' => 'What We\'re Building',
        'intro' => 'A focused preview of the local guide sections being built first. These are not finished directories yet, but they show the shape of the site.',
    ));
    ?>

    <section class="section section--soft" id="why">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Our why');
            set_query_var('title', 'Why This Site Exists');
            set_query_var('subtitle', '');
            set_query_var('align', 'left');
            get_template_part('template-parts/components/section-header');
            ?>
            <div class="split-panel">
                <div class="stack">
                    <p>South Forsyth is growing fast, but there has never been one place to keep up with all of it &mdash; the restaurant that just opened, which park has the best playground, what&rsquo;s happening this weekend, or which neighborhood might be the right fit for your family.</p>
                    <p>An independent, community-minded guide to life in South Forsyth, created and maintained by Vision Baptist Church as a service to the community we love.</p>
                </div>
                <?php
                set_query_var('quote', 'A single, trustworthy place to answer the question: what\'s happening in South Forsyth, and where should we go?');
                set_query_var('attribution', 'The SouthForsyth.org team');
                get_template_part('template-parts/components/quote-block');
                ?>
            </div>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/coming-soon-card', $early_guides, array(
        'id' => 'early-guides',
        'eyebrow' => 'Starter guides',
        'title' => 'Early Local Guides',
        'intro' => 'These guide concepts are in progress. They are listed here as the first editorial priorities, not as completed guides.',
        'soft' => true,
    ));

    get_template_part('template-parts/components/newsletter');
    ?>

    <section class="section" id="community">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Built for');
            set_query_var('title', 'Built for the Community');
            set_query_var('subtitle', 'SouthForsyth.org is designed to be useful for everyone who calls this area home.');
            set_query_var('align', 'center');
            get_template_part('template-parts/components/section-header');
            ?>
            <div class="audience-grid">
                <div class="audience-item">
                    <h3>Residents</h3>
                    <p>Stay in the loop on what&rsquo;s happening nearby.</p>
                </div>
                <div class="audience-item">
                    <h3>New Families</h3>
                    <p>Get oriented quickly in a new community.</p>
                </div>
                <div class="audience-item">
                    <h3>Local Businesses</h3>
                    <p>Get discovered by the neighbors around you.</p>
                </div>
                <div class="audience-item">
                    <h3>Churches</h3>
                    <p>Reach families looking for a church home.</p>
                </div>
                <div class="audience-item">
                    <h3>Schools</h3>
                    <p>Share resources with the families you serve.</p>
                </div>
                <div class="audience-item">
                    <h3>Community Organizations</h3>
                    <p>Reach residents who want to get involved.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section section--soft" id="explore">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Start exploring');
            set_query_var('title', 'Jump to a section');
            set_query_var('subtitle', 'Every section below is live today — most are still filling in with real content, but none of them are dead ends.');
            set_query_var('align', 'center');
            get_template_part('template-parts/components/section-header');
            ?>
            <div class="pill-row">
                <?php foreach (southforsyth_get_primary_nav_items() as $item) :
                    $url = southforsyth_get_hub_url($item['key']);
                    if (! $url) {
                        continue;
                    }
                    ?>
                    <a class="pill-link" href="<?php echo esc_url($url); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php
    set_query_var('eyebrow', 'Coming soon');
    set_query_var('title', 'SouthForsyth.org is launching soon.');
    set_query_var('description', 'We\'re building this guide one neighborhood, one guide, and one local favorite at a time. Check back soon — or get updates below.');
    set_query_var('link_text', 'Get Updates');
    set_query_var('link_url', '#newsletter');
    get_template_part('template-parts/components/cta');
    ?>
</main>

<?php get_footer(); ?>
