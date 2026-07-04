<?php

/**
 * Homepage: the South Forsyth community portal.
 *
 * Every section below queries its matching custom post type first
 * (southforsyth_get_latest_items / southforsyth_get_featured_places) and
 * only falls back to the placeholder array when that post type has no
 * published content yet. As soon as real Events, Restaurants, Parks, etc.
 * are published, this template starts rendering them automatically.
 */

get_header();

$upcoming_events = southforsyth_get_latest_items('event', 3, array(
    array('eyebrow' => 'This weekend', 'title' => 'Farmers Market & Live Music', 'description' => 'A recurring Saturday morning market with local vendors, produce, and family activities.', 'link' => home_url('/events/'), 'date' => 'Saturdays'),
    array('eyebrow' => 'Community', 'title' => 'Movie Night in the Park', 'description' => 'A free outdoor screening with lawn seating, food trucks, and pre-show games.', 'link' => home_url('/events/'), 'date' => 'Monthly'),
    array('eyebrow' => 'Family', 'title' => 'Fall Festival & Craft Fair', 'description' => 'Seasonal vendors, food, and activities for the whole family.', 'link' => home_url('/events/'), 'date' => 'Seasonal'),
), 'Event');

$newest_guides = southforsyth_get_latest_items('guide', 3, array(
    array('eyebrow' => 'Guide', 'title' => 'Best Parks in South Forsyth', 'description' => 'A roundup of the best parks for families, walkers, and weekend outings.', 'link' => home_url('/guides/')),
    array('eyebrow' => 'Guide', 'title' => 'Every Playground Worth Knowing', 'description' => 'A parent-first guide to the area\'s best play spaces.', 'link' => home_url('/guides/')),
    array('eyebrow' => 'Guide', 'title' => 'New Resident Moving Guide', 'description' => 'A practical starting point for anyone relocating to the area.', 'link' => home_url('/guides/')),
), 'Guide');

$popular_places = southforsyth_get_featured_places(6, array(
    array('eyebrow' => 'Outdoors', 'title' => 'Big Creek Greenway', 'description' => 'A favorite paved trail for walking, running, and biking.', 'link' => home_url('/parks/')),
    array('eyebrow' => 'Nature', 'title' => 'Lake Lanier Access', 'description' => 'A popular spot for boating, fishing, and scenic weekend outings.', 'link' => home_url('/parks/')),
    array('eyebrow' => 'Dining', 'title' => 'Neighborhood Coffee & Brunch', 'description' => 'A reliable local favorite for weekend mornings.', 'link' => home_url('/restaurants/')),
    array('eyebrow' => 'Community', 'title' => 'Vickery Village', 'description' => 'A walkable neighborhood hub with shops, dining, and events.', 'link' => home_url('/neighborhoods/')),
));

$featured_restaurants = southforsyth_get_latest_items('restaurant', 3, array(
    array('eyebrow' => 'Dining', 'title' => 'Neighborhood Bistro', 'description' => 'A relaxed spot for family dinners and weekend brunch.', 'link' => home_url('/restaurants/')),
    array('eyebrow' => 'Coffee', 'title' => 'Corner Coffee House', 'description' => 'A cozy cafe with reliable Wi-Fi and weekend pastries.', 'link' => home_url('/restaurants/')),
    array('eyebrow' => 'Casual', 'title' => 'Family Pizza & Pasta', 'description' => 'An easy weeknight option with a kid-friendly menu.', 'link' => home_url('/restaurants/')),
), 'Dining');

$parks = southforsyth_get_latest_items('park', 3, array(
    array('eyebrow' => 'Outdoors', 'title' => 'Fowler Park', 'description' => 'Ballfields, playgrounds, and open green space for the whole family.', 'link' => home_url('/parks/')),
    array('eyebrow' => 'Nature', 'title' => 'Big Creek Greenway', 'description' => 'Miles of paved trail connecting neighborhoods and parks.', 'link' => home_url('/parks/')),
    array('eyebrow' => 'Recreation', 'title' => 'Central Community Park', 'description' => 'Sports fields, walking paths, and picnic areas.', 'link' => home_url('/parks/')),
), 'Outdoors');

$neighborhoods = southforsyth_get_latest_items('neighborhood', 3, array(
    array('eyebrow' => 'Neighborhood', 'title' => 'Vickery', 'description' => 'A walkable, amenity-rich neighborhood near shops and dining.', 'link' => home_url('/neighborhoods/')),
    array('eyebrow' => 'Neighborhood', 'title' => 'River Club', 'description' => 'A golf-course community with family-friendly amenities.', 'link' => home_url('/neighborhoods/')),
    array('eyebrow' => 'Neighborhood', 'title' => 'Windermere', 'description' => 'An established neighborhood known for its schools and parks.', 'link' => home_url('/neighborhoods/')),
), 'Neighborhood');

$schools = southforsyth_get_latest_items('school', 3, array(
    array('eyebrow' => 'Education', 'title' => 'South Forsyth High School', 'description' => 'Serving families across the southern part of the county.', 'link' => home_url('/schools/')),
    array('eyebrow' => 'Education', 'title' => 'Vickery Creek Middle School', 'description' => 'A neighborhood middle school with strong community involvement.', 'link' => home_url('/schools/')),
    array('eyebrow' => 'Education', 'title' => 'Local Elementary Schools', 'description' => 'An overview of nearby elementary options and boundaries.', 'link' => home_url('/schools/')),
), 'Education');

$churches = southforsyth_get_latest_items('church', 3, array(
    array('eyebrow' => 'Community', 'title' => 'South Forsyth Community Church', 'description' => 'A welcoming congregation with family and youth programs.', 'link' => home_url('/churches/')),
    array('eyebrow' => 'Faith', 'title' => 'Lakeside Fellowship', 'description' => 'A close-knit congregation active in local volunteer work.', 'link' => home_url('/churches/')),
    array('eyebrow' => 'Service', 'title' => 'Neighborhood Worship Center', 'description' => 'Multiple weekend services and community outreach programs.', 'link' => home_url('/churches/')),
), 'Community');

$businesses = southforsyth_get_latest_items('business', 3, array(
    array('eyebrow' => 'Services', 'title' => 'Local Home Services Co.', 'description' => 'A trusted provider for repairs, maintenance, and installations.', 'link' => home_url('/business-directory/')),
    array('eyebrow' => 'Retail', 'title' => 'Main Street Boutique', 'description' => 'A locally owned shop for gifts and everyday essentials.', 'link' => home_url('/business-directory/')),
    array('eyebrow' => 'Professional', 'title' => 'South Forsyth Family Dentistry', 'description' => 'A friendly practice welcoming new patients of all ages.', 'link' => home_url('/business-directory/')),
), 'Business');

$latest_articles = southforsyth_get_latest_items('article', 3, array(
    array('eyebrow' => 'Local News', 'title' => 'New Sidewalk Project Connects Two Neighborhoods', 'description' => 'A look at the latest infrastructure project and what it means for walkability.', 'link' => home_url('/articles/')),
    array('eyebrow' => 'Community', 'title' => 'Volunteers Rebuild Playground After Storm Damage', 'description' => 'Neighbors came together to restore a favorite family gathering spot.', 'link' => home_url('/articles/')),
    array('eyebrow' => 'Local News', 'title' => 'County Announces New Farmers Market Schedule', 'description' => 'Updated days, hours, and vendor lineup for the coming season.', 'link' => home_url('/articles/')),
), 'Story');
?>

<main id="main-content" class="site-main">
    <?php get_template_part('template-parts/components/hero'); ?>

    <section class="section section--soft" id="search">
        <div class="container">
            <?php
            set_query_var('eyebrow', 'Start here');
            set_query_var('title', 'Search South Forsyth');
            set_query_var('subtitle', 'Find guides, schools, parks, restaurants, events, and businesses in one place.');
            set_query_var('align', 'center');
            get_template_part('template-parts/components/section-header');
            get_template_part('template-parts/components/search');
            ?>
            <div class="pill-row" aria-label="Browse by category">
                <a class="pill-link" href="<?php echo esc_url(home_url('/events/')); ?>">Events</a>
                <a class="pill-link" href="<?php echo esc_url(home_url('/restaurants/')); ?>">Restaurants</a>
                <a class="pill-link" href="<?php echo esc_url(home_url('/parks/')); ?>">Parks</a>
                <a class="pill-link" href="<?php echo esc_url(home_url('/schools/')); ?>">Schools</a>
                <a class="pill-link" href="<?php echo esc_url(home_url('/churches/')); ?>">Churches</a>
                <a class="pill-link" href="<?php echo esc_url(home_url('/business-directory/')); ?>">Businesses</a>
            </div>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/event-card', $upcoming_events, array(
        'id' => 'events',
        'eyebrow' => 'Calendar',
        'title' => 'Upcoming events',
        'intro' => 'Markets, festivals, and community programming happening soon.',
        'cta_text' => 'View all events',
        'cta_link' => home_url('/events/'),
    ));

    southforsyth_render_card_section('template-parts/components/guide-card', $newest_guides, array(
        'id' => 'guides',
        'eyebrow' => 'Local guides',
        'title' => 'Newest local guides',
        'intro' => 'Evergreen guides built to help residents and visitors plan with confidence.',
        'cta_text' => 'Browse all guides',
        'cta_link' => home_url('/guides/'),
        'soft' => true,
    ));

    southforsyth_render_card_section('template-parts/components/directory-card', $popular_places, array(
        'id' => 'popular',
        'eyebrow' => 'Trending',
        'title' => 'Popular places',
        'intro' => 'A mix of parks, restaurants, and neighborhoods residents keep coming back to.',
    ));
    ?>

    <section class="section">
        <div class="container split-panel">
            <div class="card card-editorial">
                <div class="card__body">
                    <p class="eyebrow">New resident essentials</p>
                    <h2>Moving to South Forsyth?</h2>
                    <p>From school zones and local favorites to parks, services, and weekend ideas, this guide is a dependable starting point for newcomers who want to feel at home quickly.</p>
                    <a class="btn btn-primary" href="<?php echo esc_url(home_url('/guides/')); ?>">Read the moving guide</a>
                </div>
            </div>
            <div class="card">
                <div class="card__body">
                    <p class="eyebrow">Need to know</p>
                    <ul class="list">
                        <li>Local service recommendations and practical moving tips.</li>
                        <li>School district boundaries and family-friendly resources.</li>
                        <li>A searchable directory for businesses, restaurants, and churches.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/restaurant-card', $featured_restaurants, array(
        'id' => 'restaurants',
        'eyebrow' => 'Dining',
        'title' => 'Featured restaurants',
        'intro' => 'Local favorites for breakfast, brunch, lunch, and coffee.',
        'cta_text' => 'View all restaurants',
        'cta_link' => home_url('/restaurants/'),
        'soft' => true,
    ));

    southforsyth_render_card_section('template-parts/components/park-card', $parks, array(
        'id' => 'parks',
        'eyebrow' => 'Outdoors',
        'title' => 'Parks',
        'intro' => 'Green space, trails, and playgrounds across South Forsyth.',
        'cta_text' => 'View all parks',
        'cta_link' => home_url('/parks/'),
    ));

    southforsyth_render_card_section('template-parts/components/neighborhood-card', $neighborhoods, array(
        'id' => 'neighborhoods',
        'eyebrow' => 'Where to live',
        'title' => 'Neighborhoods',
        'intro' => 'Lifestyle, schools, and amenities across the area\'s neighborhoods.',
        'cta_text' => 'View all neighborhoods',
        'cta_link' => home_url('/neighborhoods/'),
        'soft' => true,
    ));

    southforsyth_render_card_section('template-parts/components/school-card', $schools, array(
        'id' => 'schools',
        'eyebrow' => 'Education',
        'title' => 'Schools',
        'intro' => 'A quick overview of the local school landscape.',
        'cta_text' => 'View all schools',
        'cta_link' => home_url('/schools/'),
    ));

    southforsyth_render_card_section('template-parts/components/church-card', $churches, array(
        'id' => 'churches',
        'eyebrow' => 'Faith & community',
        'title' => 'Churches',
        'intro' => 'Local congregations, service times, and volunteer opportunities.',
        'cta_text' => 'View all churches',
        'cta_link' => home_url('/churches/'),
        'soft' => true,
    ));

    southforsyth_render_card_section('template-parts/components/directory-card', $businesses, array(
        'id' => 'business-directory',
        'eyebrow' => 'Support local',
        'title' => 'Business directory',
        'intro' => 'Trusted shops, services, and professionals in South Forsyth.',
        'cta_text' => 'Browse the directory',
        'cta_link' => home_url('/business-directory/'),
    ));
    ?>

    <section class="section section--soft" aria-labelledby="conditions-title">
        <div class="container">
            <div class="section-header">
                <p class="eyebrow">Local conditions</p>
                <h2 id="conditions-title" class="section-title">Weather &amp; traffic</h2>
                <p class="section-subtitle">A quick-glance snapshot for planning your day.</p>
            </div>
            <div class="grid-2">
                <?php
                get_template_part('template-parts/components/weather-placeholder');
                get_template_part('template-parts/components/traffic-placeholder');
                ?>
            </div>
        </div>
    </section>

    <?php
    southforsyth_render_card_section('template-parts/components/article-card', $latest_articles, array(
        'id' => 'articles',
        'eyebrow' => 'Editorial',
        'title' => 'Latest articles',
        'intro' => 'Local news and community stories from South Forsyth.',
        'cta_text' => 'Read more articles',
        'cta_link' => home_url('/articles/'),
    ));

    get_template_part('template-parts/components/newsletter');
    get_template_part('template-parts/components/community-spotlight');
    ?>
</main>

<?php get_footer(); ?>
