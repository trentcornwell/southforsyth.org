<?php get_header(); ?>

<main id="main-content" class="site-main">
    <?php get_template_part('template-parts/components/hero'); ?>

    <section class="section section--soft">
        <div class="container">
            <div class="section-intro">
                <p class="eyebrow">Local guide</p>
                <h2>Everything you need for a great weekend in South Forsyth</h2>
                <p>From family-friendly outings to new neighborhood favorites, this homepage is designed to feel like a trusted local magazine for residents and visitors alike.</p>
            </div>

            <div class="feature-strip">
                <article class="card card--feature">
                    <div class="card__body">
                        <p class="eyebrow">This weekend</p>
                        <h3>Farmers market, park play, and local brunch picks</h3>
                        <p>TODO: Replace this with a dynamic events query once the site starts pulling live community listings.</p>
                    </div>
                </article>
                <article class="card card--feature">
                    <div class="card__body">
                        <p class="eyebrow">Families</p>
                        <h3>School calendar, youth activities, and neighborhood resources</h3>
                        <p>TODO: Add curated family guides and school info as content expands.</p>
                    </div>
                </article>
                <article class="card card--feature">
                    <div class="card__body">
                        <p class="eyebrow">Business spotlight</p>
                        <h3>Trusted shops, salons, services, and local favorites</h3>
                        <p>TODO: Turn this into a featured business section with categories and map links.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <?php
    $weekend_cards = array(
        array('eyebrow' => 'Events', 'title' => 'Community Events', 'description' => 'A curated look at concerts, markets, and pop-up happenings around town.', 'link' => '#events'),
        array('eyebrow' => 'Outdoors', 'title' => 'Parks & Trails', 'description' => 'Quick ideas for walking, biking, and easy outdoor family plans.', 'link' => '#parks'),
        array('eyebrow' => 'Food', 'title' => 'Coffee & Brunch', 'description' => 'Neighborhood favorites for a relaxed weekend morning.', 'link' => '#restaurants'),
    );
    set_query_var('title', 'Weekend guide');
    set_query_var('intro', 'TODO: Replace these cards with a live weekend roundup as soon as events content is available.');
    set_query_var('cards', $weekend_cards);
    set_query_var('id', 'events');
    get_template_part('template-parts/components/card-grid');
    ?>

    <section class="section">
        <div class="container split-panel">
            <div class="card card--editorial">
                <div class="card__body">
                    <p class="eyebrow">New resident essentials</p>
                    <h2>Moving to South Forsyth?</h2>
                    <p>From school zones and local favorites to parks, services, and weekend ideas, this guide will become a dependable starting point for newcomers who want to feel at home quickly.</p>
                    <a class="btn btn--primary" href="#">Read the guide</a>
                </div>
            </div>
            <div class="card">
                <div class="card__body">
                    <p class="eyebrow">Need to know</p>
                    <ul class="list">
                        <li>TODO: Add local service recommendations and practical moving tips.</li>
                        <li>TODO: Highlight school district updates and family-friendly resources.</li>
                        <li>TODO: Build a searchable directory for businesses, restaurants, and churches.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <?php
    $parks = array(
        array('eyebrow' => 'Outdoors', 'title' => 'Parks & Trails', 'description' => 'Local green spaces, walking loops, and family-friendly spots.', 'link' => '#'),
        array('eyebrow' => 'Nature', 'title' => 'Lake Lanier Access', 'description' => 'A favorite place for weekend outings and scenic views.', 'link' => '#'),
        array('eyebrow' => 'Adventure', 'title' => 'Community Trails', 'description' => 'Quick routes for walkers, runners, and kids.', 'link' => '#'),
    );
    set_query_var('title', 'Parks & trails');
    set_query_var('intro', 'TODO: Replace this with dynamic park listings and trail highlights.');
    set_query_var('cards', $parks);
    set_query_var('id', 'parks');
    get_template_part('template-parts/components/card-grid');
    ?>

    <?php
    $schools = array(
        array('eyebrow' => 'Education', 'title' => 'Schools', 'description' => 'A quick overview of the local school landscape.', 'link' => '#'),
        array('eyebrow' => 'Families', 'title' => 'Family Resources', 'description' => 'Helpful information for parents and new residents.', 'link' => '#'),
        array('eyebrow' => 'Local', 'title' => 'Student Life', 'description' => 'Community activities and neighborhood connections.', 'link' => '#'),
    );
    set_query_var('title', 'Schools');
    set_query_var('intro', 'TODO: Connect this block to school listings and district resources.');
    set_query_var('cards', $schools);
    set_query_var('id', 'schools');
    get_template_part('template-parts/components/card-grid');
    ?>

    <?php
    $restaurants = array(
        array('eyebrow' => 'Dining', 'title' => 'Restaurants & Coffee', 'description' => 'Local favorites for breakfast, brunch, lunch, and coffee.', 'link' => '#'),
        array('eyebrow' => 'Coffee', 'title' => 'Neighborhood Cafes', 'description' => 'Reliable spots to work, meet friends, or unwind.', 'link' => '#'),
        array('eyebrow' => 'Places', 'title' => 'Weekend Dining', 'description' => 'Easy recommendations for family meals and date nights.', 'link' => '#'),
    );
    set_query_var('title', 'Restaurants & coffee');
    set_query_var('intro', 'TODO: Replace with curated local business listings and neighborhood picks.');
    set_query_var('cards', $restaurants);
    set_query_var('id', 'restaurants');
    get_template_part('template-parts/components/card-grid');
    ?>

    <?php
    $churches = array(
        array('eyebrow' => 'Community', 'title' => 'Churches', 'description' => 'Local congregations and community gatherings.', 'link' => '#'),
        array('eyebrow' => 'Service', 'title' => 'Volunteer Opportunities', 'description' => 'Ways to connect and contribute locally.', 'link' => '#'),
        array('eyebrow' => 'Faith', 'title' => 'Community Events', 'description' => 'Family-focused gatherings and seasonal celebrations.', 'link' => '#'),
    );
    set_query_var('title', 'Churches & community');
    set_query_var('intro', 'TODO: Replace this with dynamic church directory content and service listings.');
    set_query_var('cards', $churches);
    set_query_var('id', 'churches');
    get_template_part('template-parts/components/card-grid');
    ?>

    <section class="section">
        <div class="container grid grid--2">
            <article class="card">
                <div class="card__body">
                    <p class="eyebrow">Local businesses</p>
                    <h2>Support the people behind South Forsyth</h2>
                    <p>TODO: Build this into a searchable business directory with categories, service descriptions, and map links.</p>
                    <a class="btn" href="#">Browse directory</a>
                </div>
            </article>
            <article class="card card--editorial">
                <div class="card__body">
                    <p class="eyebrow">Community calendar</p>
                    <h2>What’s coming next</h2>
                    <p>TODO: Replace this block with a dynamic event calendar or upcoming events query once content is ready.</p>
                </div>
            </article>
        </div>
    </section>

    <?php get_template_part('template-parts/components/newsletter'); ?>
</main>

<?php get_footer(); ?>