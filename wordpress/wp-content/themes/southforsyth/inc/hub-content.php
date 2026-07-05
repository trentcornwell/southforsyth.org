<?php

/**
 * Hub page content.
 *
 * A "hub" is any top-level section page: the seven custom post type
 * archives (events, restaurants, parks, schools, churches, neighborhoods,
 * business directory) rendered by archive.php, plus three sections that
 * have no custom post type of their own (Things To Do, New Resident Guide,
 * Weekend Guide) rendered by page-templates/hub.php. Both templates pull
 * from the same southforsyth_get_hub_content() lookup so the intro copy,
 * FAQ, sample cards, and related links live in one place instead of being
 * duplicated per template.
 *
 * Every "samples" entry below describes a content *category*, not a real
 * business/church/event — matching the placeholder content policy in
 * docs/content-platform-architecture.md. Samples only render when a section
 * has no published posts yet (see archive.php); once real content exists,
 * live posts take over automatically and the samples disappear.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_hub_definitions')) {
    function southforsyth_get_hub_definitions()
    {
        return array(
            'event' => array(
                'title' => 'Events',
                'intro' => array(
                    'What\'s happening around South Forsyth — markets, festivals, school and church programs, and recurring community events.',
                    'Every event here is a real, published listing with a date, time, and venue. Nothing on this page is sponsored placement.',
                ),
                'empty_title' => 'No events published yet',
                'empty_description' => 'We haven\'t published any Events yet. Once real events are added in wp-admin, they\'ll appear here automatically, newest first.',
                'samples' => array(
                    array('icon' => 'M', 'title' => 'Markets & Festivals', 'description' => 'Farmers markets, seasonal festivals, and outdoor community gatherings.'),
                    array('icon' => 'F', 'title' => 'Family Programming', 'description' => 'Story times, youth sports kickoffs, and family-friendly meetups.'),
                    array('icon' => 'C', 'title' => 'Civic & School Events', 'description' => 'School district calendar items, town halls, and public meetings.'),
                    array('icon' => 'H', 'title' => 'Holiday & Seasonal', 'description' => 'Tree lightings, egg hunts, fall festivals, and other seasonal happenings.'),
                ),
                'links' => array(
                    array('label' => 'Weekend Guide', 'key' => 'weekend-guide'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'Restaurants & Coffee', 'key' => 'restaurant'),
                    array('label' => 'Parks & Trails', 'key' => 'park'),
                ),
                'faq' => array(
                    array('question' => 'Is this every event happening in South Forsyth?', 'answer' => 'No — this page only lists events that have been published on SouthForsyth.org. It will never be complete, but every listing here is real.'),
                    array('question' => 'Can I submit an event?', 'answer' => 'Public event submissions aren\'t open yet. A submission form is planned — see the site\'s data integration roadmap for the current status.'),
                    array('question' => 'How often is this page updated?', 'answer' => 'New events appear the moment they\'re published, sorted newest first — there\'s no delay or manual sync step.'),
                ),
            ),
            'restaurant' => array(
                'title' => 'Restaurants & Coffee',
                'intro' => array(
                    'A guide to local dining and coffee — restaurants, brunch spots, and coffee shops in and around South Forsyth.',
                    'Listings are organized by cuisine and neighborhood so it\'s easy to find something close by or worth the drive.',
                ),
                'empty_title' => 'No restaurants published yet',
                'empty_description' => 'We haven\'t published any Restaurant listings yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'B', 'title' => 'Breakfast & Brunch', 'description' => 'Morning and weekend brunch spots worth getting up for.'),
                    array('icon' => 'C', 'title' => 'Coffee Shops', 'description' => 'Local coffee shops for a quick stop, a study session, or a slow morning.'),
                    array('icon' => 'F', 'title' => 'Family Dining', 'description' => 'Kid-friendly restaurants that make weeknight dinners easy.'),
                    array('icon' => 'D', 'title' => 'Date Night', 'description' => 'A quieter, dress-up-a-little pick for date night.'),
                ),
                'links' => array(
                    array('label' => 'Weekend Guide', 'key' => 'weekend-guide'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'Business Directory', 'key' => 'business'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                ),
                'faq' => array(
                    array('question' => 'How do I get my restaurant listed?', 'answer' => 'A business submission form is planned but not live yet. Until then, listings are added directly in wp-admin.'),
                    array('question' => 'Are these listings sponsored?', 'answer' => 'No. Every listing on this page is a normal editorial entry, not paid placement. A separate, clearly labeled "featured" system is planned for later.'),
                    array('question' => 'Do you cover coffee shops too?', 'answer' => 'Yes — coffee shops share this same Restaurants listing, filterable by cuisine once filtering is built.'),
                ),
            ),
            'park' => array(
                'title' => 'Parks',
                'intro' => array(
                    'Parks and playgrounds across South Forsyth — where to let kids run, walk the dog, or find some shade.',
                    'Each listing is meant to answer the practical questions: parking, amenities, and whether it\'s worth the drive. Looking for a trail or greenway specifically? See Trails.',
                ),
                'empty_title' => 'No parks published yet',
                'empty_description' => 'We haven\'t published any Park listings yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'P', 'title' => 'Neighborhood Parks', 'description' => 'Smaller parks worth a quick visit close to home.'),
                    array('icon' => 'K', 'title' => 'Playgrounds', 'description' => 'Play spaces organized by age range and amenities.'),
                    array('icon' => 'D', 'title' => 'Dog-Friendly Spaces', 'description' => 'Parks with dog-friendly rules or dedicated off-leash areas.'),
                    array('icon' => 'R', 'title' => 'Picnic & Reservable Spaces', 'description' => 'Pavilions and spaces worth reserving ahead for a group.'),
                ),
                'links' => array(
                    array('label' => 'Trails', 'key' => 'trail'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'Weekend Guide', 'key' => 'weekend-guide'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                ),
                'faq' => array(
                    array('question' => 'Are these county parks or private amenities?', 'answer' => 'This section is meant to cover public parks. Private, HOA-only amenities generally won\'t be listed here.'),
                    array('question' => 'Do you cover trails and greenways here too?', 'answer' => 'Trails have their own section — see the Trails link above — so each can be browsed and filtered on its own terms.'),
                    array('question' => 'Can I suggest a park to add?', 'answer' => 'Not through a public form yet — that\'s part of the planned submission workflow. For now, reach out through the site\'s normal contact channels.'),
                ),
            ),
            'trail' => array(
                'title' => 'Trails',
                'intro' => array(
                    'Walking, biking, and greenway trails across South Forsyth — routes worth lacing up for.',
                    'Split out from Parks so distance, surface, and difficulty can be first-class details instead of buried in a park listing.',
                ),
                'empty_title' => 'No trails published yet',
                'empty_description' => 'We haven\'t published any Trail listings yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'G', 'title' => 'Greenways', 'description' => 'Paved, multi-use greenway connections like the Big Creek Greenway corridor.'),
                    array('icon' => 'N', 'title' => 'Nature Trails', 'description' => 'Unpaved, natural-surface trails through wooded areas.'),
                    array('icon' => 'F', 'title' => 'Family-Friendly Routes', 'description' => 'Shorter, flatter routes that work well with a stroller or young kids.'),
                    array('icon' => 'B', 'title' => 'Bike Routes', 'description' => 'Routes worth riding, not just walking.'),
                ),
                'links' => array(
                    array('label' => 'Parks', 'key' => 'park'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'Weekend Guide', 'key' => 'weekend-guide'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                ),
                'faq' => array(
                    array('question' => 'Do you list distance and difficulty?', 'answer' => 'That\'s the plan for each Trail listing once real content is published — the post type already has fields ready for it.'),
                    array('question' => 'Is this the same as the Parks section?', 'answer' => 'No — Trails is its own section now, specifically for walking/biking routes, separate from park amenities like playgrounds and picnic areas.'),
                    array('question' => 'Can I suggest a trail to add?', 'answer' => 'Not through a public form yet — that\'s part of the planned submission workflow. For now, reach out through the site\'s normal contact channels.'),
                ),
            ),
            'school' => array(
                'title' => 'Schools',
                'intro' => array(
                    'A clear, practical overview of schools serving South Forsyth families — public and private, by level.',
                    'This page is meant to help new and prospective residents get oriented quickly, not to replace the school district\'s own site.',
                ),
                'empty_title' => 'No school profiles published yet',
                'empty_description' => 'We haven\'t published any School profiles yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'E', 'title' => 'Elementary Schools', 'description' => 'Elementary schools serving South Forsyth neighborhoods.'),
                    array('icon' => 'M', 'title' => 'Middle Schools', 'description' => 'Middle schools and their feeder patterns.'),
                    array('icon' => 'H', 'title' => 'High Schools', 'description' => 'High schools, including the area\'s public high school.'),
                    array('icon' => 'P', 'title' => 'Private & Independent Schools', 'description' => 'Private and independent school options in the area.'),
                ),
                'links' => array(
                    array('label' => 'New Resident Guide', 'key' => 'new-resident-guide'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'Events', 'key' => 'event'),
                ),
                'faq' => array(
                    array('question' => 'Is this an official school district resource?', 'answer' => 'No. SouthForsyth.org is an independent community guide, not affiliated with Forsyth County Schools. Always confirm attendance zones and enrollment directly with the district.'),
                    array('question' => 'Will you list school boundaries?', 'answer' => 'District boundary lines change and are best confirmed directly with Forsyth County Schools — this page will link out to the official source rather than republish boundary maps.'),
                    array('question' => 'Do you cover private schools?', 'answer' => 'Yes, once profiles are published — private and independent schools are part of this same listing.'),
                ),
            ),
            'church' => array(
                'title' => 'Churches',
                'intro' => array(
                    'A respectful directory of congregations and faith communities across South Forsyth.',
                    'Meant to help newcomers find a church home — not to rank, endorse, or compare denominations.',
                ),
                'empty_title' => 'No church profiles published yet',
                'empty_description' => 'We haven\'t published any Church profiles yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'S', 'title' => 'Service Times', 'description' => 'Congregations organized by typical service day and time.'),
                    array('icon' => 'F', 'title' => 'Family & Youth Programs', 'description' => 'Churches with dedicated children\'s and youth ministries.'),
                    array('icon' => 'V', 'title' => 'Volunteer Opportunities', 'description' => 'Congregations with active community service and volunteer programs.'),
                    array('icon' => 'D', 'title' => 'Denominations', 'description' => 'A cross-section of denominations represented in the area.'),
                ),
                'links' => array(
                    array('label' => 'Business Directory', 'key' => 'business'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                    array('label' => 'New Resident Guide', 'key' => 'new-resident-guide'),
                    array('label' => 'Events', 'key' => 'event'),
                ),
                'faq' => array(
                    array('question' => 'How are churches chosen for this list?', 'answer' => 'Inclusion isn\'t an endorsement of any denomination or theology — this is meant to be a broad, respectful directory, not an editorial ranking.'),
                    array('question' => 'Can a church submit or update its listing?', 'answer' => 'A church submission workflow is planned but not live yet. Until then, listings are added and updated directly in wp-admin.'),
                    array('question' => 'Do you list volunteer opportunities separately?', 'answer' => 'Not yet as their own listing type — for now, volunteer programs are noted within each church\'s profile.'),
                ),
            ),
            'neighborhood' => array(
                'title' => 'Neighborhoods',
                'intro' => array(
                    'Profiles of the neighborhoods and communities that make up South Forsyth — Halcyon, Big Creek, Denmark, Vickery, Windermere, Polo Fields, and more.',
                    'Each profile is meant to answer "what\'s it like to live here?" with lifestyle, schools, and nearby amenities.',
                ),
                'empty_title' => 'No neighborhood profiles published yet',
                'empty_description' => 'We haven\'t published any Neighborhood profiles yet. Real profiles will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'H', 'title' => 'Halcyon', 'description' => 'The mixed-use development along Post Road.'),
                    array('icon' => 'V', 'title' => 'Vickery', 'description' => 'The Vickery Village area near Vickery Creek.'),
                    array('icon' => 'W', 'title' => 'Windermere', 'description' => 'A residential community off the Post Road / GA-400 corridor.'),
                    array('icon' => 'P', 'title' => 'Polo Fields', 'description' => 'A residential community near the Polo Golf & Country Club.'),
                ),
                'links' => array(
                    array('label' => 'New Resident Guide', 'key' => 'new-resident-guide'),
                    array('label' => 'Schools', 'key' => 'school'),
                    array('label' => 'Restaurants & Coffee', 'key' => 'restaurant'),
                    array('label' => 'Parks & Trails', 'key' => 'park'),
                ),
                'faq' => array(
                    array('question' => 'Is "South Forsyth" an official name for these neighborhoods?', 'answer' => 'No — South Forsyth isn\'t an incorporated city, so none of these neighborhoods have an official "South Forsyth" designation. It\'s the name residents use for this part of Forsyth County.'),
                    array('question' => 'Will you cover HOA-specific details?', 'answer' => 'Neighborhood profiles focus on lifestyle, schools, and amenities generally — not HOA rules, which vary and change independently of this site.'),
                    array('question' => 'Can I suggest a neighborhood that\'s missing?', 'answer' => 'Yes, informally for now — a public suggestion/submission form is on the roadmap but not live yet.'),
                ),
            ),
            'business' => array(
                'title' => 'Business Directory',
                'intro' => array(
                    'A trusted directory for finding and supporting South Forsyth businesses and service providers.',
                    'Business isn\'t the focus of this site — it\'s one section among many — but a good directory matters for a real community guide.',
                ),
                'empty_title' => 'No businesses published yet',
                'empty_description' => 'We haven\'t published any Business listings yet. Real listings will appear here automatically once they\'re added.',
                'samples' => array(
                    array('icon' => 'S', 'title' => 'Services', 'description' => 'Home services, professional services, and everyday needs.'),
                    array('icon' => 'R', 'title' => 'Retail', 'description' => 'Local shops, boutiques, and retail businesses.'),
                    array('icon' => 'H', 'title' => 'Health & Wellness', 'description' => 'Local health, wellness, and personal-care providers.'),
                    array('icon' => 'P', 'title' => 'Professional Services', 'description' => 'Legal, financial, and other professional services.'),
                ),
                'links' => array(
                    array('label' => 'Restaurants & Coffee', 'key' => 'restaurant'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                    array('label' => 'New Resident Guide', 'key' => 'new-resident-guide'),
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                ),
                'faq' => array(
                    array('question' => 'How do I add my business?', 'answer' => 'A business submission form is planned but not live yet — see the site\'s data integration roadmap for the current phase.'),
                    array('question' => 'Is there a cost to be listed?', 'answer' => 'Standard listings are intended to be free. A separate, clearly labeled featured/sponsored placement system is planned for later, but nothing like that exists yet.'),
                    array('question' => 'How are businesses categorized?', 'answer' => 'By category and neighborhood/area, so residents can browse by what they need or what\'s nearby.'),
                ),
            ),
            'things-to-do' => array(
                'title' => 'Things To Do',
                'intro' => array(
                    'A starting point for exploring South Forsyth — events, parks, dining, and family activities in one place.',
                    'This page pulls together the highlights from every other section rather than duplicating a full listing of its own.',
                ),
                'empty_title' => 'This overview is being built',
                'empty_description' => 'This page is meant to summarize highlights from Events, Parks & Trails, and Restaurants & Coffee once those sections have real published content.',
                'samples' => array(
                    array('icon' => 'O', 'title' => 'Outdoors', 'description' => 'Parks, trails, and greenways for an afternoon outside.'),
                    array('icon' => 'F', 'title' => 'Family Activities', 'description' => 'Kid-friendly outings and rainy-day ideas.'),
                    array('icon' => 'E', 'title' => 'Events This Weekend', 'description' => 'A quick look at what\'s coming up on the community calendar.'),
                    array('icon' => 'S', 'title' => 'Seasonal Picks', 'description' => 'Farmers markets, festivals, and holiday events by season.'),
                ),
                'links' => array(
                    array('label' => 'Events', 'key' => 'event'),
                    array('label' => 'Parks & Trails', 'key' => 'park'),
                    array('label' => 'Restaurants & Coffee', 'key' => 'restaurant'),
                    array('label' => 'Weekend Guide', 'key' => 'weekend-guide'),
                ),
                'faq' => array(
                    array('question' => 'How is this different from the Weekend Guide?', 'answer' => 'Things To Do is a broad overview of the area; the Weekend Guide is a more curated, single "here\'s what to do this weekend" plan.'),
                    array('question' => 'Will this page ever have its own content, or just links?', 'answer' => 'The plan is a mix — some original framing content plus live highlights pulled from Events, Parks, and Restaurants as those sections fill in.'),
                ),
            ),
            'new-resident-guide' => array(
                'title' => 'New Resident Guide',
                'intro' => array(
                    'A practical starting point for anyone relocating to South Forsyth — schools, neighborhoods, utilities, and everyday essentials.',
                    'South Forsyth isn\'t an incorporated city, so a lot of "getting oriented" here means understanding neighborhoods and school zones rather than a single city government.',
                ),
                'empty_title' => 'This guide is being built',
                'empty_description' => 'The full move-in checklist and neighborhood breakdowns aren\'t published yet. This page will grow into a complete relocation guide over time.',
                'samples' => array(
                    array('icon' => 'N', 'title' => 'Neighborhood Overview', 'description' => 'A starting map of Halcyon, Vickery, Windermere, Polo Fields, and more.'),
                    array('icon' => 'S', 'title' => 'Schools & Zoning', 'description' => 'How to find your school zone and what to ask before you move.'),
                    array('icon' => 'U', 'title' => 'Utilities & Services', 'description' => 'Getting set up with water, power, trash, and internet.'),
                    array('icon' => 'C', 'title' => 'Community & Churches', 'description' => 'Finding a church home and getting plugged into community life.'),
                ),
                'links' => array(
                    array('label' => 'Neighborhoods', 'key' => 'neighborhood'),
                    array('label' => 'Schools', 'key' => 'school'),
                    array('label' => 'Churches', 'key' => 'church'),
                    array('label' => 'Business Directory', 'key' => 'business'),
                ),
                'faq' => array(
                    array('question' => 'Is South Forsyth a city I can look up on a map?', 'answer' => 'Not exactly — it\'s a community identity for the southern part of Forsyth County, not an incorporated city with its own boundary or government. See the "What is South Forsyth?" section on the homepage for the full explanation.'),
                    array('question' => 'What school district serves South Forsyth?', 'answer' => 'Forsyth County Schools. Always confirm your specific attendance zone directly with the district, since boundaries can change.'),
                    array('question' => 'Where should I start if I\'m moving here?', 'answer' => 'Neighborhoods and Schools are the two sections most new residents check first — both are linked above.'),
                ),
            ),
            'weekend-guide' => array(
                'title' => 'Weekend Guide',
                'intro' => array(
                    'A curated plan for making the most of a weekend in South Forsyth — events, food, and outdoor time.',
                    'Unlike Things To Do, this page is meant to read like a single suggested itinerary rather than a full directory.',
                ),
                'empty_title' => 'This guide is being built',
                'empty_description' => 'A real, published weekend itinerary isn\'t live yet. Once Events and Restaurants have real content, this page will pull from them automatically.',
                'samples' => array(
                    array('icon' => 'S', 'title' => 'Saturday Morning', 'description' => 'Coffee, a farmers market, or a walk on a local trail.'),
                    array('icon' => 'A', 'title' => 'Saturday Afternoon', 'description' => 'A park visit, a playground stop, or a local event.'),
                    array('icon' => 'E', 'title' => 'Saturday Evening', 'description' => 'Dinner out or a family-friendly evening activity.'),
                    array('icon' => 'U', 'title' => 'Sunday', 'description' => 'Church, a slower pace, and getting ready for the week ahead.'),
                ),
                'links' => array(
                    array('label' => 'Events', 'key' => 'event'),
                    array('label' => 'Restaurants & Coffee', 'key' => 'restaurant'),
                    array('label' => 'Parks & Trails', 'key' => 'park'),
                    array('label' => 'Things To Do', 'key' => 'things-to-do'),
                ),
                'faq' => array(
                    array('question' => 'Does this change every week?', 'answer' => 'That\'s the intent once it\'s live — a refreshed plan tied to real upcoming Events rather than a static, never-updated itinerary.'),
                    array('question' => 'Is this only for families?', 'answer' => 'No — the plan is to keep this broad enough for couples, individuals, and groups, not just family outings.'),
                ),
            ),
        );
    }
}

if (! function_exists('southforsyth_get_hub_content')) {
    function southforsyth_get_hub_content($key)
    {
        $hubs = southforsyth_get_hub_definitions();

        return $hubs[$key] ?? null;
    }
}

if (! function_exists('southforsyth_get_hub_url')) {
    /**
     * Resolve a hub key (a post type name or a static hub page slug) to its
     * front-end URL, so nav items, related-links pill rows, and the
     * homepage's coming-soon cards all point at the same place without
     * hardcoding URLs in more than one file.
     */
    function southforsyth_get_hub_url($key)
    {
        if (! $key) {
            return '';
        }

        if (post_type_exists($key)) {
            $link = get_post_type_archive_link($key);
            return $link ?: '';
        }

        $page = get_page_by_path($key);
        if ($page) {
            return get_permalink($page);
        }

        // Fall back to the conventional URL even if the page hasn't been
        // created yet (see inc/page-provisioning.php, which normally
        // creates it automatically).
        return home_url('/' . $key . '/');
    }
}

if (! function_exists('southforsyth_render_hub_links')) {
    function southforsyth_render_hub_links($hub)
    {
        if (empty($hub['links'])) {
            return;
        }

        echo '<div class="pill-row pill-row--start" aria-label="Related sections">' . PHP_EOL;
        foreach ($hub['links'] as $link) {
            $url = southforsyth_get_hub_url($link['key'] ?? '');
            if (! $url) {
                continue;
            }
            echo '<a class="pill-link" href="' . esc_url($url) . '">' . esc_html($link['label'] ?? '') . '</a>' . PHP_EOL;
        }
        echo '</div>' . PHP_EOL;
    }
}

if (! function_exists('southforsyth_render_hub_faq')) {
    function southforsyth_render_hub_faq($hub)
    {
        if (empty($hub['faq'])) {
            return;
        }

        set_query_var('title', 'Frequently Asked Questions');
        set_query_var('items', $hub['faq']);
        get_template_part('template-parts/components/faq-block');
    }
}
