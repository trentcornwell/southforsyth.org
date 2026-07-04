<?php

/**
 * Evergreen content strategy for South Forsyth.
 * This plan organizes the site around repeatedly searched, high-intent local guides.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_evergreen_content_plan')) {
    function southforsyth_get_evergreen_content_plan()
    {
        return array(
            'best-parks' => array(
                'title' => 'Best Parks',
                'priority' => 1,
                'url' => '/best-parks/',
                'hero' => 'Find the best parks in South Forsyth for family outings, walks, picnics, and weekday breaks.',
                'introduction' => 'This evergreen guide helps residents and visitors discover the most useful parks for play, relaxation, and outdoor time.',
                'table_of_contents' => array('Best parks for families', 'Best parks for walking', 'Picnic-friendly parks', 'Planning tips'),
                'suggested_sections' => array('Best parks for kids', 'Best parks for walking', 'Picnic-friendly options', 'Amenities to look for'),
                'internal_links' => array('Every Playground', 'Walking Trails', 'Family Activities', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'What is the best park for families?', 'answer' => 'Use this guide to compare playgrounds, shade, parking, and amenities.'),
                    array('question' => 'Are there parks with walking trails?', 'answer' => 'Yes, this guide will connect readers to the best walking-friendly outdoor spaces.')
                ),
                'related_guides' => array('Every Playground', 'Walking Trails', 'Family Activities'),
                'newsletter_cta' => 'Get local guide updates and seasonal outdoor ideas delivered to your inbox.',
                'schema_todo' => 'TODO: Add Place or Park schema when park profiles are created.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with dynamic park data, photos, and map links.'
            ),
            'every-playground' => array(
                'title' => 'Every Playground',
                'priority' => 2,
                'url' => '/every-playground/',
                'hero' => 'A family-first guide to every playground and play space worth knowing about in South Forsyth.',
                'introduction' => 'This guide is designed for parents looking for safe, convenient, and fun play options near home.',
                'table_of_contents' => array('Best playgrounds for toddlers', 'Best playgrounds for older kids', 'Shade and amenities', 'How to plan a play date'),
                'suggested_sections' => array('Toddler-friendly spaces', 'Big-kid play areas', 'Restroom and parking notes', 'Nearby snacks and coffee'),
                'internal_links' => array('Best Parks', 'Rainy Day Activities', 'Family Activities', 'Schools'),
                'faq' => array(
                    array('question' => 'Which playgrounds are best for toddlers?', 'answer' => 'This guide will highlight the newest and most accessible playgrounds for younger kids.'),
                    array('question' => 'Which locations have shade and bathrooms?', 'answer' => 'These details will be added as structured data and page content grows.')
                ),
                'related_guides' => array('Best Parks', 'Rainy Day Activities', 'Family Activities'),
                'newsletter_cta' => 'Stay updated with family-friendly local guides and seasonal outings.',
                'schema_todo' => 'TODO: Add Place schema and playground-specific structured data once listings are available.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with real playground listings and photos.'
            ),
            'walking-trails' => array(
                'title' => 'Walking Trails',
                'priority' => 3,
                'url' => '/walking-trails/',
                'hero' => 'Explore the best walking trails in and around South Forsyth for easy fitness and scenic routes.',
                'introduction' => 'This evergreen page will become a trusted resource for walkers, runners, and families looking for local routes.',
                'table_of_contents' => array('Easy walking loops', 'Longer trail routes', 'Trail amenities', 'Best times to go'),
                'suggested_sections' => array('Short loops', 'Trail difficulty', 'Parking and access', 'Best for sunset walks'),
                'internal_links' => array('Best Parks', 'Every Playground', 'Weekend Guide', 'Things To Do'),
                'faq' => array(
                    array('question' => 'Are there easy trails for beginners?', 'answer' => 'Yes, this page will group beginner-friendly routes and simple walking loops.'),
                    array('question' => 'Do the trails work for strollers?', 'answer' => 'This content will be expanded with accessibility notes and route details.')
                ),
                'related_guides' => array('Best Parks', 'Family Activities', 'Weekend Guide'),
                'newsletter_cta' => 'Get outdoor planning ideas and local trail updates in your inbox.',
                'schema_todo' => 'TODO: Add Trail or Place schema when route data is available.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with trail maps, length, difficulty, and traffic notes.'
            ),
            'restaurants' => array(
                'title' => 'Restaurants',
                'priority' => 4,
                'url' => '/restaurants/',
                'hero' => 'Discover the best restaurants in South Forsyth for family meals, casual outings, and special occasions.',
                'introduction' => 'This page will evolve into a local dining hub with category-based recommendations and restaurant profiles.',
                'table_of_contents' => array('Best family-friendly restaurants', 'Date-night spots', 'Fast casual favorites', 'How to choose'),
                'suggested_sections' => array('Neighborhood favorites', 'Price guides', 'Kid-friendly choices', 'Reservations and hours'),
                'internal_links' => array('Breakfast', 'Pizza', 'BBQ', 'Coffee Shops', 'Date Night'),
                'faq' => array(
                    array('question' => 'Which restaurants are best for families?', 'answer' => 'The guide will highlight family-friendly options with convenient parking and kid-friendly menus.'),
                    array('question' => 'Where should I go for date night?', 'answer' => 'This section will recommend more relaxed, elevated, and cozy dining options.')
                ),
                'related_guides' => array('Breakfast', 'Coffee Shops', 'Date Night', 'Weekend Guide'),
                'newsletter_cta' => 'Receive local dining roundups and weekend food plans straight to your inbox.',
                'schema_todo' => 'TODO: Add Restaurant or LocalBusiness schema when listings are published.',
                'dynamic_content_todo' => 'TODO: Replace placeholder content with restaurant profiles, menus, and availability details.'
            ),
            'coffee-shops' => array(
                'title' => 'Coffee Shops',
                'priority' => 5,
                'url' => '/coffee-shops/',
                'hero' => 'Find the best coffee shops for work, study, and relaxing mornings in South Forsyth.',
                'introduction' => 'This guide will support local coffee discovery for remote workers, students, and neighbors.',
                'table_of_contents' => array('Best coffee shops for work', 'Best places with Wi-Fi', 'Weekend coffee stops', 'Pet-friendly spots'),
                'suggested_sections' => array('Study-friendly spaces', 'Drive-through and quick stop options', 'Best pastries and breakfast pairings', 'Neighborhood favorites'),
                'internal_links' => array('Breakfast', 'Restaurants', 'Rainy Day Activities', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'Which coffee shops have Wi-Fi?', 'answer' => 'This guide will identify work-friendly cafés with reliable Wi-Fi and seating.'),
                    array('question' => 'Are there coffee shops near family activities?', 'answer' => 'Yes, the guide will connect coffee stops to nearby neighborhood plans.')
                ),
                'related_guides' => array('Breakfast', 'Restaurants', 'Rainy Day Activities'),
                'newsletter_cta' => 'Get coffee shop picks and local morning favorites delivered to your inbox.',
                'schema_todo' => 'TODO: Add CafeOrCoffeeShop or LocalBusiness schema for each listing.',
                'dynamic_content_todo' => 'TODO: Replace this page with dynamic coffee shop profiles and map links.'
            ),
            'breakfast' => array(
                'title' => 'Breakfast',
                'priority' => 6,
                'url' => '/breakfast/',
                'hero' => 'Start your day with the best breakfast options in South Forsyth.',
                'introduction' => 'This evergreen guide will help readers find family-friendly breakfasts, quick stops, and weekend brunch favorites.',
                'table_of_contents' => array('Best brunch spots', 'Quick breakfast options', 'Kid-friendly breakfast places', 'What to order'),
                'suggested_sections' => array('Family breakfast spots', 'Healthy breakfast picks', 'Weekend brunch favorites', 'Coffee pairings'),
                'internal_links' => array('Coffee Shops', 'Restaurants', 'Weekend Guide', 'Family Activities'),
                'faq' => array(
                    array('question' => 'Where can I get breakfast for the whole family?', 'answer' => 'This page will highlight broader breakfast options with kid-friendly menus and space for groups.'),
                    array('question' => 'Are there good brunch spots?', 'answer' => 'Yes, the guide will organize brunch options by occasion and neighborhood.')
                ),
                'related_guides' => array('Coffee Shops', 'Restaurants', 'Weekend Guide'),
                'newsletter_cta' => 'Get local breakfast and brunch suggestions sent to your inbox.',
                'schema_todo' => 'TODO: Add Restaurant or LocalBusiness schema once breakfast listings are added.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with real breakfast spot profiles and hours.'
            ),
            'pizza' => array(
                'title' => 'Pizza',
                'priority' => 7,
                'url' => '/pizza/',
                'hero' => 'Find the best pizza spots in South Forsyth for families, quick dinners, and casual nights out.',
                'introduction' => 'This guide will become a practical local resource for pizza lovers looking for quick, reliable dinner options.',
                'table_of_contents' => array('Best pizza for families', 'Best slices', 'Pizza for delivery', 'Pizza night ideas'),
                'suggested_sections' => array('Cheese pizza favorites', 'Gourmet options', 'Takeout and delivery picks', 'Kid-friendly choices'),
                'internal_links' => array('Restaurants', 'Family Activities', 'Rainy Day Activities', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'Where can I get good pizza for takeout?', 'answer' => 'This page will feature quick takeout-friendly pizza options.'),
                    array('question' => 'What are the best family pizza spots?', 'answer' => 'The guide will include family-friendly picks with easy parking and casual atmosphere.')
                ),
                'related_guides' => array('Restaurants', 'Family Activities', 'Rainy Day Activities'),
                'newsletter_cta' => 'Receive pizza and dinner picks for busy weeknights.',
                'schema_todo' => 'TODO: Add Restaurant schema when pizza listings are added.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with actual restaurant info, ratings, and menus.'
            ),
            'bbq' => array(
                'title' => 'BBQ',
                'priority' => 8,
                'url' => '/bbq/',
                'hero' => 'Discover the best BBQ options in South Forsyth for casual dinners, takeout, and weekend cravings.',
                'introduction' => 'This guide will help local readers find barbecue favorites and family-friendly smokehouse-style meals.',
                'table_of_contents' => array('Best BBQ for families', 'Best brisket and ribs', 'Quick takeout', 'Budget-friendly picks'),
                'suggested_sections' => array('Takeout favorites', 'Family meal deals', 'Sauce and sides', 'Neighborhood picks'),
                'internal_links' => array('Restaurants', 'Weekend Guide', 'Family Activities', 'Shopping'),
                'faq' => array(
                    array('question' => 'Where can I get good BBQ for dinner?', 'answer' => 'The guide will recommend local BBQ options by neighborhood and meal type.'),
                    array('question' => 'Is BBQ a good option for families?', 'answer' => 'Yes, this guide will highlight family-friendly barbecue choices and easy ordering options.')
                ),
                'related_guides' => array('Restaurants', 'Weekend Guide', 'Family Activities'),
                'newsletter_cta' => 'Get weekly dining picks including BBQ and local comfort food.',
                'schema_todo' => 'TODO: Add Restaurant schema once BBQ listings are ready.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with real business details and photos.'
            ),
            'family-activities' => array(
                'title' => 'Family Activities',
                'priority' => 9,
                'url' => '/family-activities/',
                'hero' => 'A practical guide to family-friendly activities in South Forsyth for every season.',
                'introduction' => 'This evergreen page will help parents choose from local ideas for weekends, after-school time, and quality family time.',
                'table_of_contents' => array('Weekend family ideas', 'After-school options', 'Rainy day activities', 'Seasonal family guides'),
                'suggested_sections' => array('Free activities', 'Low-cost options', 'Indoor ideas', 'Outdoor adventures'),
                'internal_links' => array('Best Parks', 'Every Playground', 'Rainy Day Activities', 'Schools'),
                'faq' => array(
                    array('question' => 'What are good family activities on a budget?', 'answer' => 'This guide will highlight low-cost ideas and community opportunities.'),
                    array('question' => 'What should we do on rainy days?', 'answer' => 'The guide will point to indoor-friendly ideas and flexible plans.')
                ),
                'related_guides' => array('Rainy Day Activities', 'Best Parks', 'Schools'),
                'newsletter_cta' => 'Get family activity ideas and local recommendations in your inbox.',
                'schema_todo' => 'TODO: Add ThingToDo or Event schema when family activity listings are published.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with real family activity content and seasonal updates.'
            ),
            'rainy-day-activities' => array(
                'title' => 'Rainy Day Activities',
                'priority' => 10,
                'url' => '/rainy-day-activities/',
                'hero' => 'Find the best rainy day ideas for families, kids, and couples in South Forsyth.',
                'introduction' => 'This page will make bad-weather days easier to plan by organizing indoor-friendly options and nearby alternatives.',
                'table_of_contents' => array('Indoor family ideas', 'Rainy day outings', 'Quick backup ideas', 'What to pack'),
                'suggested_sections' => array('Indoor play options', 'Coffee and treat breaks', 'Museum or cultural ideas', 'Last-minute backup plans'),
                'internal_links' => array('Family Activities', 'Coffee Shops', 'Restaurants', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'What can we do indoors with kids?', 'answer' => 'This guide will make indoor family options easy to browse and compare.'),
                    array('question' => 'What are good rainy-day date ideas?', 'answer' => 'This section will expand into indoor date ideas and cozy local options.')
                ),
                'related_guides' => array('Family Activities', 'Date Night', 'Coffee Shops'),
                'newsletter_cta' => 'Get rainy-day plans and local indoor ideas sent to your inbox.',
                'schema_todo' => 'TODO: Add Event or ThingToDo schema when activity listings are ready.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with curated local indoor options.'
            ),
            'date-night' => array(
                'title' => 'Date Night',
                'priority' => 11,
                'url' => '/date-night/',
                'hero' => 'Plan an easy, memorable date night in South Forsyth with dining, strolls, and local favorites.',
                'introduction' => 'This evergreen guide will help couples find romantic, low-stress date ideas for different budgets and moods.',
                'table_of_contents' => array('Casual date ideas', 'Dinner and drinks', 'Weekend date plans', 'Budget-friendly choices'),
                'suggested_sections' => array('Dinner date ideas', 'Coffee and dessert dates', 'Outdoor walks', 'Special-occasion options'),
                'internal_links' => array('Restaurants', 'Coffee Shops', 'Weekend Guide', 'Rainy Day Activities'),
                'faq' => array(
                    array('question' => 'What is a good low-key date night idea?', 'answer' => 'This guide will highlight simple local plans such as coffee, dessert, and an evening walk.'),
                    array('question' => 'What are good date options in bad weather?', 'answer' => 'This content will be expanded into cozy indoor and weather-proof options.')
                ),
                'related_guides' => array('Restaurants', 'Coffee Shops', 'Rainy Day Activities'),
                'newsletter_cta' => 'Receive date-night ideas and local weekend inspiration.',
                'schema_todo' => 'TODO: Add Event or ThingToDo schema for curated date-night experiences.',
                'dynamic_content_todo' => 'TODO: Replace with dynamic date ideas and partner business links.'
            ),
            'summer-camps' => array(
                'title' => 'Summer Camps',
                'priority' => 12,
                'url' => '/summer-camps/',
                'hero' => 'Find trusted summer camp ideas and local programming for kids in South Forsyth.',
                'introduction' => 'This guide will become a go-to resource for parents planning summer activities for their children.',
                'table_of_contents' => array('Day camps', 'Sports camps', 'Arts and learning camps', 'Enrollment tips'),
                'suggested_sections' => array('Camp types', 'Age group notes', 'What to pack', 'Registration reminders'),
                'internal_links' => array('Family Activities', 'Youth Sports', 'Schools', 'Events'),
                'faq' => array(
                    array('question' => 'What types of camps are available?', 'answer' => 'This guide will organize camps by activity, age, and schedule.'),
                    array('question' => 'When should parents start planning?', 'answer' => 'This content will include timing guidance for booking and registration.')
                ),
                'related_guides' => array('Family Activities', 'Youth Sports', 'Schools'),
                'newsletter_cta' => 'Get summer planning tips and camp reminders straight to your inbox.',
                'schema_todo' => 'TODO: Add Event schema once summer camp listings are created.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with live camp listings and registration links.'
            ),
            'christmas-events' => array(
                'title' => 'Christmas Events',
                'priority' => 13,
                'url' => '/christmas-events/',
                'hero' => 'Plan for Christmas events, lights, markets, and family traditions in South Forsyth.',
                'introduction' => 'This seasonal guide will become a helpful hub for holiday-specific planning and local celebrations.',
                'table_of_contents' => array('Holiday events', 'Light displays', 'Family events', 'Planning tips'),
                'suggested_sections' => array('Santa and family events', 'Holiday markets', 'Christmas lights routes', 'Parking and timing tips'),
                'internal_links' => array('Christmas Lights', 'Halloween', 'Holiday Guide', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'What holiday events should families look for?', 'answer' => 'This guide will highlight the best seasonal options for families and visitors.'),
                    array('question' => 'When should we plan for Christmas lights?', 'answer' => 'This section will include timing and route suggestions.')
                ),
                'related_guides' => array('Christmas Lights', 'Halloween', 'Seasonal Guides'),
                'newsletter_cta' => 'Receive holiday guides and event reminders for the season.',
                'schema_todo' => 'TODO: Add Event schema for Christmas-related listings.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with event listings, dates, and map links.'
            ),
            'pumpkin-patches' => array(
                'title' => 'Pumpkin Patches',
                'priority' => 14,
                'url' => '/pumpkin-patches/',
                'hero' => 'Explore the best pumpkin patches and fall activities in and around South Forsyth.',
                'introduction' => 'This guide will help readers plan family-friendly fall outings with photos, timing, and what to expect.',
                'table_of_contents' => array('Best pumpkin patches', 'Family-friendly fall activities', 'What to bring', 'Timing tips'),
                'suggested_sections' => array('Farm visits', 'Corn mazes', 'Photo opportunities', 'Weekend planning'),
                'internal_links' => array('Halloween', 'Christmas Events', 'Family Activities', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'When is the best time to visit a pumpkin patch?', 'answer' => 'This guide will include timing recommendations and seasonal tips.'),
                    array('question' => 'Are pumpkin patches good for younger kids?', 'answer' => 'Yes, this guide will help parents match activities to age range and energy level.')
                ),
                'related_guides' => array('Halloween', 'Family Activities', 'Seasonal Guides'),
                'newsletter_cta' => 'Get fall family guide updates and seasonal events in your inbox.',
                'schema_todo' => 'TODO: Add Event or Place schema when pumpkin patch listings are published.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with dynamic seasonal listings and photos.'
            ),
            'farmers-markets' => array(
                'title' => 'Farmers Markets',
                'priority' => 15,
                'url' => '/farmers-markets/',
                'hero' => 'Find local farmers markets and weekend produce stops in South Forsyth.',
                'introduction' => 'This evergreen guide will become a practical family and food resource for market visits and weekend errands.',
                'table_of_contents' => array('Best farmers markets', 'What to expect', 'Family-friendly visits', 'Best times to go'),
                'suggested_sections' => array('Produce and local goods', 'Food trucks and breakfast', 'Parking and access', 'Weekend planning'),
                'internal_links' => array('Restaurants', 'Breakfast', 'Weekend Guide', 'Shopping'),
                'faq' => array(
                    array('question' => 'Where are the best farmers markets nearby?', 'answer' => 'This guide will list the best local options and what each location offers.'),
                    array('question' => 'Are farmers markets good for families?', 'answer' => 'Yes, this page will highlight comfortable and family-friendly visits.')
                ),
                'related_guides' => array('Restaurants', 'Breakfast', 'Weekend Guide'),
                'newsletter_cta' => 'Get local food and weekend market updates in your inbox.',
                'schema_todo' => 'TODO: Add Event or Place schema as market listings are introduced.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with live market dates and vendor listings.'
            ),
            'fourth-of-july' => array(
                'title' => 'Fourth of July',
                'priority' => 16,
                'url' => '/fourth-of-july/',
                'hero' => 'Plan for the Fourth of July in South Forsyth with local events, family activities, and holiday tips.',
                'introduction' => 'This guide will become a seasonal hub for Independence Day festivities and family-friendly planning.',
                'table_of_contents' => array('Fourth of July events', 'Best family plans', 'Parking and timing', 'Holiday logistics'),
                'suggested_sections' => array('Fireworks and celebrations', 'Easy outdoor plans', 'Food ideas', 'Traffic and weather notes'),
                'internal_links' => array('Events', 'Weather', 'Traffic', 'Weekend Guide'),
                'faq' => array(
                    array('question' => 'What family activities happen on the Fourth?', 'answer' => 'This guide will target family-friendly holiday activities and event planning.'),
                    array('question' => 'Should we plan around traffic and weather?', 'answer' => 'Yes, the guide will include practical planning notes for travel and comfort.')
                ),
                'related_guides' => array('Events', 'Weather', 'Traffic'),
                'newsletter_cta' => 'Get seasonal holiday planning updates and event news.',
                'schema_todo' => 'TODO: Add Event schema when Fourth of July content is published.',
                'dynamic_content_todo' => 'TODO: Replace with dynamic holiday event listings and travel notes.'
            ),
            'halloween' => array(
                'title' => 'Halloween',
                'priority' => 17,
                'url' => '/halloween/',
                'hero' => 'Explore the best Halloween events, pumpkin patches, and family fun in South Forsyth.',
                'introduction' => 'This seasonal guide will help families and visitors plan for autumn traditions and events.',
                'table_of_contents' => array('Halloween events', 'Pumpkin patch trips', 'Kid-friendly ideas', 'Neighborhood planning'),
                'suggested_sections' => array('Trick-or-treat planning', 'Haunted houses and fall events', 'Costumes and logistics', 'Local traditions'),
                'internal_links' => array('Pumpkin Patches', 'Christmas Events', 'Family Activities', 'Events'),
                'faq' => array(
                    array('question' => 'What are the best Halloween activities for kids?', 'answer' => 'This guide will highlight family-friendly options and style-specific recommendations.'),
                    array('question' => 'What should we plan around?', 'answer' => 'This section will include timing, traffic, and weather notes for the season.')
                ),
                'related_guides' => array('Pumpkin Patches', 'Family Activities', 'Events'),
                'newsletter_cta' => 'Get Halloween event lists and seasonal ideas for your family.',
                'schema_todo' => 'TODO: Add Event schema when Halloween events are published.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with event calendars and local recommendations.'
            ),
            'christmas-lights' => array(
                'title' => 'Christmas Lights',
                'priority' => 18,
                'url' => '/christmas-lights/',
                'hero' => 'Find the best Christmas light displays and holiday driving routes near South Forsyth.',
                'introduction' => 'This guide will become a seasonal destination for readers planning neighborhood light tours and holiday outings.',
                'table_of_contents' => array('Best light displays', 'Drive-through options', 'Family viewing tips', 'Weather planning'),
                'suggested_sections' => array('Neighborhood displays', 'Best evening routes', 'Hot cocoa and treat stops', 'Parking tips'),
                'internal_links' => array('Christmas Events', 'Holiday Guide', 'Weekend Guide', 'Weather'),
                'faq' => array(
                    array('question' => 'Where are the best Christmas lights?', 'answer' => 'This guide will recommend the most festive and easiest-to-visit displays.'),
                    array('question' => 'Are there good holiday routes for families?', 'answer' => 'Yes, the guide will organize routes by distance and comfort level.')
                ),
                'related_guides' => array('Christmas Events', 'Holiday Guide', 'Weekend Guide'),
                'newsletter_cta' => 'Get seasonal holiday guides and Christmas light updates.',
                'schema_todo' => 'TODO: Add Event or Place schema for display listings.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with curated display pages and route maps.'
            ),
            'neighborhood-guides' => array(
                'title' => 'Neighborhood Guides',
                'priority' => 19,
                'url' => '/neighborhood-guides/',
                'hero' => 'Discover the neighborhoods of South Forsyth through lifestyle, amenities, and local character.',
                'introduction' => 'This guide will help new residents and visitors understand where to live, play, eat, and explore.',
                'table_of_contents' => array('Neighborhood profiles', 'Best amenities by area', 'Schools and family life', 'Move-in planning'),
                'suggested_sections' => array('Lifestyle overviews', 'Dining and shopping by area', 'School proximity', 'Quick local tips'),
                'internal_links' => array('Moving Guide', 'School Guide', 'Business Guide', 'Restaurants'),
                'faq' => array(
                    array('question' => 'Which neighborhoods are best for families?', 'answer' => 'This guide will compare family-friendliness by school access, parks, and amenities.'),
                    array('question' => 'How do I choose a neighborhood?', 'answer' => 'This content will help readers compare lifestyle, commute, and convenience factors.')
                ),
                'related_guides' => array('Moving Guide', 'School Guide', 'Business Guide'),
                'newsletter_cta' => 'Get neighborhood insights and local move-in planning updates.',
                'schema_todo' => 'TODO: Add Place schema for neighborhood profiles.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with neighborhood landing pages and local maps.'
            ),
            'church-guide' => array(
                'title' => 'Church Guide',
                'priority' => 20,
                'url' => '/church-guide/',
                'hero' => 'A practical guide to churches and community groups in South Forsyth.',
                'introduction' => 'This page will make it easier for residents to find faith communities, service opportunities, and family-centered groups.',
                'table_of_contents' => array('Church listings', 'Service times', 'Volunteer opportunities', 'Community programs'),
                'suggested_sections' => array('Faith communities', 'Youth and family ministries', 'Volunteer links', 'Community outreach'),
                'internal_links' => array('Volunteer Guide', 'Community Organizations', 'Events', 'Neighborhood Guides'),
                'faq' => array(
                    array('question' => 'How do I find a church that fits my family?', 'answer' => 'This guide will organize churches by denomination, family programs, and service times.'),
                    array('question' => 'How can I get involved?', 'answer' => 'The guide will link to volunteer and community participation opportunities.')
                ),
                'related_guides' => array('Volunteer Guide', 'Community Organizations', 'Neighborhood Guides'),
                'newsletter_cta' => 'Receive community guide updates and local service opportunities.',
                'schema_todo' => 'TODO: Add Organization or Place schema for church listings.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with live church directory data.'
            ),
            'moving-guide' => array(
                'title' => 'Moving Guide',
                'priority' => 21,
                'url' => '/moving-guide/',
                'hero' => 'A step-by-step moving guide for anyone relocating to South Forsyth.',
                'introduction' => 'This evergreen resource will help new residents understand the essentials of moving to the area.',
                'table_of_contents' => array('Before you move', 'Neighborhood selection', 'Schools and family setup', 'Local essentials'),
                'suggested_sections' => array('Utilities and local services', 'Neighborhood comparisons', 'School planning', 'First-week checklist'),
                'internal_links' => array('Neighborhood Guides', 'School Guide', 'Business Guide', 'Government'),
                'faq' => array(
                    array('question' => 'What should I do before moving?', 'answer' => 'This guide will outline the best first steps for settling in.'),
                    array('question' => 'Where should I start when choosing a neighborhood?', 'answer' => 'The guide will connect readers to neighborhood and school planning resources.')
                ),
                'related_guides' => array('Neighborhood Guides', 'School Guide', 'Business Guide'),
                'newsletter_cta' => 'Get moving tips and local newcomer resources sent to your inbox.',
                'schema_todo' => 'TODO: Add FAQPage or Article schema as the guide grows.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with relocation checklists and related links.'
            ),
            'school-guide' => array(
                'title' => 'School Guide',
                'priority' => 22,
                'url' => '/school-guide/',
                'hero' => 'Help families understand schools, programs, and local education resources in South Forsyth.',
                'introduction' => 'This page will become a family-friendly education guide with school information and practical resources.',
                'table_of_contents' => array('School overview', 'School options', 'Family resources', 'Education-related activities'),
                'suggested_sections' => array('School profiles', 'After-school offerings', 'Parent resources', 'Local programs'),
                'internal_links' => array('Schools', 'Moving Guide', 'Family Activities', 'Youth Sports'),
                'faq' => array(
                    array('question' => 'How do I learn about local schools?', 'answer' => 'This guide will summarize school options and family-relevant details.'),
                    array('question' => 'What resources are available for parents?', 'answer' => 'The guide will connect to local education resources and community programs.')
                ),
                'related_guides' => array('Moving Guide', 'Family Activities', 'Youth Sports'),
                'newsletter_cta' => 'Get school and family resource updates from South Forsyth.',
                'schema_todo' => 'TODO: Add EducationalOrganization schema once school listings are available.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with dynamic school profiles and district details.'
            ),
            'business-guide' => array(
                'title' => 'Business Guide',
                'priority' => 23,
                'url' => '/business-guide/',
                'hero' => 'A trusted guide to local businesses, services, and community partners in South Forsyth.',
                'introduction' => 'This guide will support local discovery and help residents find the services they need quickly.',
                'table_of_contents' => array('Business categories', 'Local services', 'Featured businesses', 'How to submit a listing'),
                'suggested_sections' => array('Popular business categories', 'Service providers', 'Neighborhood businesses', 'New listings'),
                'internal_links' => array('Business Directory', 'Restaurants', 'Shopping', 'Community Organizations'),
                'faq' => array(
                    array('question' => 'How do I find local businesses?', 'answer' => 'This guide will organize local business listings by category and neighborhood.'),
                    array('question' => 'How can a business be featured?', 'answer' => 'This section will describe the eventual submission and listing process.')
                ),
                'related_guides' => array('Business Directory', 'Restaurants', 'Shopping'),
                'newsletter_cta' => 'Receive local business highlights and community updates.',
                'schema_todo' => 'TODO: Add LocalBusiness schema for future directory entries.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with live business listings and categories.'
            ),
            'volunteer-guide' => array(
                'title' => 'Volunteer Guide',
                'priority' => 24,
                'url' => '/volunteer-guide/',
                'hero' => 'Find simple, meaningful ways to volunteer and give back in South Forsyth.',
                'introduction' => 'This evergreen guide will help local volunteers discover opportunities that match their interests and schedule.',
                'table_of_contents' => array('Volunteer opportunities', 'How to get involved', 'Family volunteering', 'Community partner links'),
                'suggested_sections' => array('One-time events', 'Ongoing opportunities', 'Youth and family volunteering', 'Partner organizations'),
                'internal_links' => array('Volunteer', 'Church Guide', 'Community Organizations', 'Events'),
                'faq' => array(
                    array('question' => 'How do I find volunteer opportunities?', 'answer' => 'The guide will connect readers to community organizations and local events.'),
                    array('question' => 'Are there family-friendly opportunities?', 'answer' => 'Yes, this content will be organized around volunteer options that fit different ages and schedules.')
                ),
                'related_guides' => array('Volunteer', 'Church Guide', 'Community Organizations'),
                'newsletter_cta' => 'Get volunteer opportunities and community updates in your inbox.',
                'schema_todo' => 'TODO: Add Organization or Event schema when volunteer listings are added.',
                'dynamic_content_todo' => 'TODO: Replace this placeholder with live volunteer opportunities and contact details.'
            )
        );
    }
}

if (! function_exists('southforsyth_get_evergreen_content_page')) {
    function southforsyth_get_evergreen_content_page($slug)
    {
        $pages = southforsyth_get_evergreen_content_plan();

        return $pages[$slug] ?? array(
            'title' => 'Evergreen Guide',
            'priority' => 99,
            'url' => '/',
            'hero' => 'A future evergreen guide for South Forsyth.',
            'introduction' => 'Add content for this page when the topic is ready.',
            'table_of_contents' => array('Overview'),
            'suggested_sections' => array('Suggested section one', 'Suggested section two'),
            'internal_links' => array('Home'),
            'faq' => array(),
            'related_guides' => array('Home'),
            'newsletter_cta' => 'Stay connected for future local guide updates.',
            'schema_todo' => 'TODO: Add schema when this page becomes a published guide.',
            'dynamic_content_todo' => 'TODO: Replace this placeholder with dynamic content once the topic is live.'
        );
    }
}
