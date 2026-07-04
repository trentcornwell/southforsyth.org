<?php get_header(); ?>

<main id="main-content" class="site-main">
    <?php get_template_part('template-parts/components/hero'); ?>

    <section class="section">
        <div class="container">
            <div class="section-heading">
                <h2>This Weekend</h2>
                <p>Curated local picks and community happenings for residents and visitors.</p>
            </div>
            <div class="card-grid">
                <?php
                $weekend_cards = array(
                    array('eyebrow' => 'Events', 'title' => 'Farmers Market', 'description' => 'Fresh produce, handmade goods, and local favorites.', 'link' => '#'),
                    array('eyebrow' => 'Outdoors', 'title' => 'Parks & Trails', 'description' => 'Plan a walk, picnic, or afternoon bike ride.', 'link' => '#'),
                    array('eyebrow' => 'Food', 'title' => 'Coffee & Brunch', 'description' => 'Neighborhood stops for a relaxed weekend morning.', 'link' => '#'),
                );
                foreach ($weekend_cards as $card) : ?>
                    <article class="card">
                        <div class="card__body">
                            <p class="eyebrow"><?php echo esc_html($card['eyebrow']); ?></p>
                            <h3><?php echo esc_html($card['title']); ?></h3>
                            <p><?php echo esc_html($card['description']); ?></p>
                            <a class="text-link" href="<?php echo esc_url($card['link']); ?>">Explore</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php
    $guides = array(
        array('eyebrow' => 'Guide', 'title' => 'Popular Guides', 'description' => 'Neighborhood tips, family favorites, and local recommendations.', 'link' => '#guides'),
        array('eyebrow' => 'Parks', 'title' => 'Parks & Trails', 'description' => 'Easy outings for walking, biking, and weekend play.', 'link' => '#parks'),
        array('eyebrow' => 'Schools', 'title' => 'Schools', 'description' => 'Quick facts and key local school information.', 'link' => '#schools'),
    );
    southforsyth_render_card_grid($guides, 'Popular Guides', 'A starter set of community-focused guides for new and longtime residents.', 'guides');
    ?>

    <?php
    $parks = array(
        array('eyebrow' => 'Outdoors', 'title' => 'Parks & Trails', 'description' => 'Local green spaces, walking loops, and family-friendly spots.', 'link' => '#'),
        array('eyebrow' => 'Nature', 'title' => 'Lake Lanier Access', 'description' => 'A favorite place for weekend outings and scenic views.', 'link' => '#'),
        array('eyebrow' => 'Adventure', 'title' => 'Community Trails', 'description' => 'Quick routes for walkers, runners, and kids.', 'link' => '#'),
    );
    southforsyth_render_card_grid($parks, 'Parks & Trails', 'TODO: Replace this placeholder section with dynamic park content.', 'parks');
    ?>

    <?php
    $schools = array(
        array('eyebrow' => 'Education', 'title' => 'Schools', 'description' => 'A quick overview of the local school landscape.', 'link' => '#'),
        array('eyebrow' => 'Families', 'title' => 'Family Resources', 'description' => 'Helpful information for parents and new residents.', 'link' => '#'),
        array('eyebrow' => 'Local', 'title' => 'Student Life', 'description' => 'Community activities and neighborhood connections.', 'link' => '#'),
    );
    southforsyth_render_card_grid($schools, 'Schools', 'TODO: Connect this block to school listings and district resources.', 'schools');
    ?>

    <?php
    $restaurants = array(
        array('eyebrow' => 'Dining', 'title' => 'Restaurants & Coffee', 'description' => 'Local favorites for breakfast, brunch, lunch, and coffee.', 'link' => '#'),
        array('eyebrow' => 'Coffee', 'title' => 'Neighborhood Cafes', 'description' => 'Reliable spots to work, meet friends, or unwind.', 'link' => '#'),
        array('eyebrow' => 'Places', 'title' => 'Weekend Dining', 'description' => 'Easy recommendations for family meals and date nights.', 'link' => '#'),
    );
    southforsyth_render_card_grid($restaurants, 'Restaurants & Coffee', 'TODO: Replace with curated local business listings.', 'restaurants');
    ?>

    <?php
    $churches = array(
        array('eyebrow' => 'Community', 'title' => 'Churches', 'description' => 'Local congregations and community gatherings.', 'link' => '#'),
        array('eyebrow' => 'Service', 'title' => 'Volunteer Opportunities', 'description' => 'Ways to connect and contribute locally.', 'link' => '#'),
        array('eyebrow' => 'Faith', 'title' => 'Community Events', 'description' => 'Family-focused gatherings and seasonal celebrations.', 'link' => '#'),
    );
    southforsyth_render_card_grid($churches, 'Churches', 'TODO: Replace this with dynamic church directory content.', 'churches');
    ?>

    <section class="section">
        <div class="container grid grid--2">
            <div class="card">
                <div class="card__body">
                    <p class="eyebrow">New residents</p>
                    <h2>New Resident Guide</h2>
                    <p>From schools and utility basics to parks and neighborhood tips, this section will become a go-to guide for residents arriving in South Forsyth.</p>
                    <a class="btn btn--primary" href="#">Read the guide</a>
                </div>
            </div>
            <div class="card">
                <div class="card__body">
                    <p class="eyebrow">Directory</p>
                    <h2>Local Business Directory</h2>
                    <p>TODO: Build this into a searchable business directory with categories, map links, and contact information.</p>
                    <a class="btn" href="#">Browse directory</a>
                </div>
            </div>
        </div>
    </section>

    <?php get_template_part('template-parts/components/newsletter'); ?>

    <section class="section">
        <div class="container card">
            <div class="card__body">
                <p class="eyebrow">Community calendar</p>
                <h2>Community Calendar Placeholder</h2>
                <p>TODO: Replace this block with a dynamic event calendar or upcoming events query once content is ready.</p>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>