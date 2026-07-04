<?php

/**
 * Community platform planning layer for South Forsyth.
 * This keeps the theme lightweight today while defining the future systems and data models needed for a scalable local platform.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_platform_systems')) {
    function southforsyth_get_platform_systems()
    {
        return array(
            'business-directory' => array(
                'title' => 'Business Directory',
                'summary' => 'A searchable directory for local businesses, service providers, and community partners.',
                'group' => 'directory',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Category browsing', 'Featured listings', 'Sponsored placements', 'Map links', 'Review-ready profiles'),
                'placeholder' => 'TODO: connect this to a custom post type with category, neighborhood, and pricing filters.'
            ),
            'church-directory' => array(
                'title' => 'Church Directory',
                'summary' => 'A welcoming directory for churches, worship communities, volunteer programs, and family ministries.',
                'group' => 'directory',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Denomination filters', 'Service-time browsing', 'Volunteer opportunities', 'Community event links'),
                'placeholder' => 'TODO: add church profiles and submission workflows once content volume grows.'
            ),
            'school-directory' => array(
                'title' => 'School Directory',
                'summary' => 'A family-focused school guide for education resources, school profiles, and local programs.',
                'group' => 'directory',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('School type filters', 'Program browsing', 'District references', 'Parent resources'),
                'placeholder' => 'TODO: replace placeholder content with verified school data and district links.'
            ),
            'community-calendar' => array(
                'title' => 'Community Calendar',
                'summary' => 'A lightweight calendar layer for recurring programs, markets, events, and seasonal happenings.',
                'group' => 'community',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Upcoming events', 'Date filters', 'Neighborhood grouping', 'Community highlights'),
                'placeholder' => 'TODO: connect calendar entries to an event post type and recurring scheduling logic.'
            ),
            'restaurant-directory' => array(
                'title' => 'Restaurant Directory',
                'summary' => 'A dining-focused discovery system for restaurants, brunch spots, coffee shops, bars, and family-friendly favorites.',
                'group' => 'directory',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Cuisine filters', 'Neighborhood browsing', 'Hours and delivery info', 'Recommended pairings'),
                'placeholder' => 'TODO: layer in menu details, photos, and reservation data over time.'
            ),
            'event-submission' => array(
                'title' => 'Event Submission',
                'summary' => 'A submission flow for organizers to publish events, share details, and request editorial review.',
                'group' => 'community',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Form-based submission', 'Moderation workflow', 'Organizer profile support', 'Featured event review'),
                'placeholder' => 'TODO: add a submission form and moderation queue once the site starts accepting community submissions.'
            ),
            'business-submission' => array(
                'title' => 'Business Submission',
                'summary' => 'A portal for local businesses to submit listings, update hours, and add service details.',
                'group' => 'community',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Claim a listing', 'Add photos', 'Create service categories', 'Flag outdated info'),
                'placeholder' => 'TODO: build a lightweight owner portal and verification workflow.'
            ),
            'church-submission' => array(
                'title' => 'Church Submission',
                'summary' => 'A friendly submission layer for churches to add directories, service times, and volunteer opportunities.',
                'group' => 'community',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Directory updates', 'Volunteer program details', 'Service schedule refreshes', 'Community links'),
                'placeholder' => 'TODO: add approval steps and verification notes for community outreach organizations.'
            ),
            'newsletter' => array(
                'title' => 'Newsletter',
                'summary' => 'A lightweight newsletter system for local updates, seasonal guides, and community announcements.',
                'group' => 'membership',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Email signup', 'Segmented content', 'Weekly digest support', 'Event reminders'),
                'placeholder' => 'TODO: connect to an email provider and define preference categories.'
            ),
            'member-accounts' => array(
                'title' => 'Member Accounts',
                'summary' => 'A future member layer for account sign-in, saved preferences, favorites, and personalized recommendations.',
                'group' => 'membership',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Account creation', 'Saved preferences', 'Profile editing', 'Saved searches'),
                'placeholder' => 'TODO: add authentication and account settings once the platform needs personalization.'
            ),
            'favorites' => array(
                'title' => 'Favorites',
                'summary' => 'A simple way for members to bookmark favorite listings, restaurants, parks, and events.',
                'group' => 'membership',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Bookmarking', 'Saved lists', 'Personal recommendations', 'Follow-up reminders'),
                'placeholder' => 'TODO: attach favorites to a user profile and allow export or sharing.'
            ),
            'saved-guides' => array(
                'title' => 'Saved Guides',
                'summary' => 'A content-saving experience for local guides, itineraries, and family planning resources.',
                'group' => 'membership',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Guide bookmarking', 'Personal planning', 'Suggested next reads', 'Shareable lists'),
                'placeholder' => 'TODO: pair saved guides with a personalized dashboard or profile panel.'
            ),
            'interactive-maps' => array(
                'title' => 'Interactive Maps',
                'summary' => 'A map-first layer for restaurants, parks, trails, churches, and local services.',
                'group' => 'discovery',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Map pins', 'Nearby suggestions', 'Route planning', 'Layered filters'),
                'placeholder' => 'TODO: integrate a mapping provider and add coordinates to directory content.'
            ),
            'search' => array(
                'title' => 'Search',
                'summary' => 'A discovery engine for finding guides, businesses, events, schools, and community places.',
                'group' => 'discovery',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Fast local search', 'Autocomplete', 'Ranked results', 'Suggested next actions'),
                'placeholder' => 'TODO: connect search to custom post types and structured metadata.'
            ),
            'filtering' => array(
                'title' => 'Filtering',
                'summary' => 'A flexible filtering layer for directories, guides, and listings by category, location, and intent.',
                'group' => 'discovery',
                'priority' => 'High',
                'status' => 'Planned',
                'goals' => array('Multi-select filters', 'Location-based queries', 'Audience filters', 'Saved filter presets'),
                'placeholder' => 'TODO: expose filters as reusable blocks for each directory view.'
            ),
            'bookmarks' => array(
                'title' => 'Bookmarks',
                'summary' => 'A lightweight bookmarking system for pages, guides, and saved community resources.',
                'group' => 'membership',
                'priority' => 'Low',
                'status' => 'Planned',
                'goals' => array('Quick save actions', 'Archive lists', 'Return visits', 'Content reminders'),
                'placeholder' => 'TODO: expand into a full “my saved content” experience when member accounts launch.'
            ),
            'nearby-suggestions' => array(
                'title' => 'Nearby Suggestions',
                'summary' => 'Context-aware suggestions that bring nearby businesses, parks, and activities into each destination page.',
                'group' => 'discovery',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Proximity lookup', 'Related place suggestions', 'Trip planning support', 'Context-aware discovery'),
                'placeholder' => 'TODO: use map data and geolocation to recommend relevant places around each listing.'
            ),
            'featured-listings' => array(
                'title' => 'Featured Listings',
                'summary' => 'A premium placement layer for standout directories, businesses, events, and community resources.',
                'group' => 'community',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Editorial highlights', 'Sponsored placement slots', 'Seasonal campaigns', 'Pinned listings'),
                'placeholder' => 'TODO: add a featured status taxonomy and editorial workflow.'
            ),
            'sponsored-listings' => array(
                'title' => 'Sponsored Listings',
                'summary' => 'A paid placement layer for businesses and partners that want increased visibility.',
                'group' => 'community',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Paid promotion', 'Local partner visibility', 'Campaign scheduling', 'Performance notes'),
                'placeholder' => 'TODO: pair this with payment processing and simple campaign management.'
            ),
            'community-recommendations' => array(
                'title' => 'Community Recommendations',
                'summary' => 'A trust-driven recommendation layer that highlights places, guides, and events favored by the local community.',
                'group' => 'community',
                'priority' => 'Medium',
                'status' => 'Planned',
                'goals' => array('Editorial recommendations', 'Community-verified picks', 'Tag-based curation', 'Seasonal rounds-ups'),
                'placeholder' => 'TODO: build this as a curation layer that can be moderated and promoted by editors.'
            )
        );
    }
}

if (! function_exists('southforsyth_get_platform_data_models')) {
    function southforsyth_get_platform_data_models()
    {
        return array(
            'businesses' => array(
                'title' => 'Businesses',
                'description' => 'Local businesses and service providers with directory profiles and featured placement support.',
                'fields' => array('Business name', 'Description', 'Category', 'Address', 'Phone', 'Website', 'Hours', 'Amenities', 'Featured image'),
                'relationships' => array('Belongs to a neighborhood', 'Can link to related events and restaurants', 'Can be featured or sponsored'),
                'suggested_cpt' => 'business',
                'suggested_taxonomies' => array('business_category', 'service_area', 'featured_status'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/businesses', '/wp-json/southforsyth/v1/businesses/{id}'),
                'future_ai_integrations' => array('Auto-generate summaries', 'Suggested tags', 'Nearby business recommendations'),
                'search_filters' => array('Category', 'Neighborhood', 'Open now', 'Rating', 'Service area'),
                'map_integration' => 'Geo coordinates, map pins, proximity search, and route-friendly directions.',
                'seo_opportunities' => array('LocalBusiness schema', 'Category landing pages', 'Review-friendly profiles')
            ),
            'churches' => array(
                'title' => 'Churches',
                'description' => 'Faith communities, ministries, and volunteer-oriented organizations that need a clear local directory view.',
                'fields' => array('Church name', 'Denomination', 'Service times', 'Description', 'Address', 'Contact', 'Volunteer programs', 'Featured image'),
                'relationships' => array('Linked to community organizations', 'Can support volunteer opportunities', 'May be associated with neighborhood guides'),
                'suggested_cpt' => 'church',
                'suggested_taxonomies' => array('denomination', 'community_program', 'volunteer_focus'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/churches', '/wp-json/southforsyth/v1/churches/{id}'),
                'future_ai_integrations' => array('Suggested volunteer matching', 'Community overview summaries', 'Service-time updates'),
                'search_filters' => array('Denomination', 'Neighborhood', 'Volunteering', 'Family programs'),
                'map_integration' => 'Pin placement for service centers, family programs, and volunteer events.',
                'seo_opportunities' => array('Organization schema', 'Neighborhood landing pages', 'Community and volunteer-focused content')
            ),
            'schools' => array(
                'title' => 'Schools',
                'description' => 'A structured data model for school listings, parent resources, and local education references.',
                'fields' => array('School name', 'Type', 'Grade level', 'Programs', 'Address', 'District', 'Website', 'Contact', 'Application info'),
                'relationships' => array('Linked to neighborhood pages', 'Supports family activity guides', 'Can connect to local sports and education programs'),
                'suggested_cpt' => 'school',
                'suggested_taxonomies' => array('school_type', 'grade_level', 'program_focus'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/schools', '/wp-json/southforsyth/v1/schools/{id}'),
                'future_ai_integrations' => array('Parent-friendly summaries', 'School comparison insights', 'Program recommendation support'),
                'search_filters' => array('School type', 'Grade level', 'Programs', 'District'),
                'map_integration' => 'Location pins, school boundary context, and family route planning support.',
                'seo_opportunities' => array('EducationalOrganization schema', 'Parent and relocation content', 'School comparison landing pages')
            ),
            'restaurants' => array(
                'title' => 'Restaurants',
                'description' => 'Dining-specific profiles with cuisine, pricing, hours, seating, and nearby guide context.',
                'fields' => array('Restaurant name', 'Cuisine', 'Price range', 'Hours', 'Address', 'Reservations', 'Delivery', 'Amenities', 'Menu link'),
                'relationships' => array('Connected to neighborhood guides', 'Useful for weekend plans and date-night content', 'Can be linked to featured listings'),
                'suggested_cpt' => 'restaurant',
                'suggested_taxonomies' => array('cuisine', 'price_range', 'dining_style'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/restaurants', '/wp-json/southforsyth/v1/restaurants/{id}'),
                'future_ai_integrations' => array('Dining recommendations', 'Personalized meal suggestions', 'Nearby restaurant discovery'),
                'search_filters' => array('Cuisine', 'Neighborhood', 'Price', 'Outdoor seating', 'Delivery'),
                'map_integration' => 'Geo-pinned restaurants, map-based discovery, and proximity suggestions.',
                'seo_opportunities' => array('Restaurant schema', 'Cuisine landing pages', 'Neighborhood and weekend guide links')
            ),
            'events' => array(
                'title' => 'Events',
                'description' => 'A robust event model for community activities, recurring programs, and seasonal happenings.',
                'fields' => array('Event title', 'Description', 'Date', 'Time', 'Venue', 'Category', 'Admission', 'Organizer', 'Featured image'),
                'relationships' => array('Can be linked to businesses and restaurants', 'Can appear in calendars and weekly guides', 'Can be sponsored or featured'),
                'suggested_cpt' => 'event',
                'suggested_taxonomies' => array('event_category', 'event_type', 'season'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/events', '/wp-json/southforsyth/v1/events/{id}'),
                'future_ai_integrations' => array('Event summarization', 'Suggested nearby activities', 'Personalized calendar reminders'),
                'search_filters' => array('Date', 'Category', 'Neighborhood', 'Free/Paid', 'Audience'),
                'map_integration' => 'Venue pins and event-day route planning support.',
                'seo_opportunities' => array('Event schema', 'Seasonal landing pages', 'Calendar-based content growth')
            ),
            'neighborhoods' => array(
                'title' => 'Neighborhoods',
                'description' => 'Neighborhood profiles that connect lifestyle, schools, parks, businesses, and newcomer content.',
                'fields' => array('Neighborhood name', 'Description', 'Highlights', 'Schools', 'Amenities', 'Commute time', 'Map area', 'Featured image'),
                'relationships' => array('Contains businesses and schools', 'Supports new resident guides', 'Can surface relevant parks and events'),
                'suggested_cpt' => 'neighborhood',
                'suggested_taxonomies' => array('neighborhood_type', 'lifestyle_tag', 'school_zone'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/neighborhoods', '/wp-json/southforsyth/v1/neighborhoods/{id}'),
                'future_ai_integrations' => array('Neighborhood summaries', 'Move-in guide suggestions', 'Lifestyle recommendations'),
                'search_filters' => array('Lifestyle', 'School access', 'Amenities', 'Commute'),
                'map_integration' => 'Neighborhood boundary layers, points of interest, and nearby route support.',
                'seo_opportunities' => array('Place schema', 'Move-in and relocation landing pages', 'Neighborhood-specific internal links')
            ),
            'parks' => array(
                'title' => 'Parks',
                'description' => 'Open-space and recreation listings for parks, playgrounds, and outdoor activity planning.',
                'fields' => array('Park name', 'Description', 'Amenities', 'Parking', 'Restrooms', 'Dog-friendly', 'Open hours', 'Address'),
                'relationships' => array('Linked to trails and playgrounds', 'Supports family and weekend guides', 'Can be surfaced in nearby suggestions'),
                'suggested_cpt' => 'park',
                'suggested_taxonomies' => array('park_type', 'amenity_tag', 'family_friendly'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/parks', '/wp-json/southforsyth/v1/parks/{id}'),
                'future_ai_integrations' => array('Park recommendation summaries', 'Family-friendly matching', 'Nearby outing suggestions'),
                'search_filters' => array('Amenities', 'Neighborhood', 'Dog-friendly', 'Shade', 'Accessibility'),
                'map_integration' => 'Map pins, route planning, and outdoor itinerary support.',
                'seo_opportunities' => array('Park or Place schema', 'Family and outdoor landing pages', 'Local guide and neighborhood links')
            ),
            'trails' => array(
                'title' => 'Trails',
                'description' => 'Trail data for walking, biking, and outdoor routes that can be searched and filtered by difficulty.',
                'fields' => array('Trail name', 'Distance', 'Difficulty', 'Surface', 'Parking', 'Accessibility', 'Route notes', 'Address'),
                'relationships' => array('Connected to parks', 'Supports family and fitness guides', 'Useful for nearby suggestions'),
                'suggested_cpt' => 'trail',
                'suggested_taxonomies' => array('trail_type', 'difficulty', 'accessibility'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/trails', '/wp-json/southforsyth/v1/trails/{id}'),
                'future_ai_integrations' => array('Trail summaries', 'Route matching', 'Fitness and family recommendations'),
                'search_filters' => array('Length', 'Difficulty', 'Accessibility', 'Neighborhood'),
                'map_integration' => 'Geo routes, turn-by-turn planning, and points of interest.',
                'seo_opportunities' => array('Trail or Place schema', 'Outdoor guide pages', 'Route and neighborhood links')
            ),
            'playgrounds' => array(
                'title' => 'Playgrounds',
                'description' => 'Family-friendly play space data with safe, practical details for caregivers and parents.',
                'fields' => array('Playground name', 'Age range', 'Shade', 'Restrooms', 'Parking', 'Amenities', 'Address', 'Accessibility'),
                'relationships' => array('Grouped under parks', 'Useful for family activity guides', 'Supports nearby suggestions for parents'),
                'suggested_cpt' => 'playground',
                'suggested_taxonomies' => array('age_group', 'amenity_tag', 'family_friendly'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/playgrounds', '/wp-json/southforsyth/v1/playgrounds/{id}'),
                'future_ai_integrations' => array('Best-fit playground recommendations', 'Parent-friendly summaries', 'Nearby snack and activity suggestions'),
                'search_filters' => array('Age range', 'Shade', 'Neighborhood', 'Accessibility'),
                'map_integration' => 'Map pins, short-route planning, and nearby play-stop context.',
                'seo_opportunities' => array('Place schema', 'Family guide pages', 'Local neighborhood and park links')
            ),
            'volunteer-opportunities' => array(
                'title' => 'Volunteer Opportunities',
                'description' => 'Volunteer listings that help residents contribute to community initiatives and local events.',
                'fields' => array('Opportunity title', 'Description', 'Organization', 'Schedule', 'Age range', 'Location', 'Contact', 'Volunteer type'),
                'relationships' => array('Linked to churches and community organizations', 'Can surface on event and community pages', 'Supports family or youth volunteer content'),
                'suggested_cpt' => 'volunteer_opportunity',
                'suggested_taxonomies' => array('volunteer_type', 'audience', 'organization_type'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/volunteer-opportunities', '/wp-json/southforsyth/v1/volunteer-opportunities/{id}'),
                'future_ai_integrations' => array('Interest matching', 'Volunteer opportunity summaries', 'Family-friendly suggestions'),
                'search_filters' => array('Volunteer type', 'Audience', 'Location', 'Availability'),
                'map_integration' => 'Volunteer site pins and nearby opportunity discovery.',
                'seo_opportunities' => array('Organization schema', 'Community service landing pages', 'Local partner links')
            ),
            'community-organizations' => array(
                'title' => 'Community Organizations',
                'description' => 'Groups, nonprofits, and local organizations that support civic life and community engagement.',
                'fields' => array('Organization name', 'Mission', 'Focus area', 'Contact', 'Website', 'Programs', 'Address', 'Volunteer opportunities'),
                'relationships' => array('Connected to volunteer listings', 'Supports church and civic pages', 'Useful for featured content and community recommendations'),
                'suggested_cpt' => 'community_organization',
                'suggested_taxonomies' => array('focus_area', 'organization_type', 'community_program'),
                'future_api_endpoints' => array('/wp-json/southforsyth/v1/community-organizations', '/wp-json/southforsyth/v1/community-organizations/{id}'),
                'future_ai_integrations' => array('Community matching', 'Program summaries', 'Partnership recommendations'),
                'search_filters' => array('Focus area', 'Organization type', 'Volunteer opportunities', 'Location'),
                'map_integration' => 'Association pins and local civic network mapping.',
                'seo_opportunities' => array('Organization schema', 'Civic and volunteer landing pages', 'Local directory and partner links')
            )
        );
    }
}
