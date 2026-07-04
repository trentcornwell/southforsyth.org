<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content stack">
            <p class="eyebrow">South Forsyth, Georgia</p>
            <h1>Your trusted guide to local life in South Forsyth</h1>
            <p class="hero__lede">Find weekend plans, school and family resources, parks, restaurants, churches, local businesses, and community stories in one polished place.</p>
            <div class="hero__meta" aria-label="Featured local categories">
                <span class="pill">Events</span>
                <span class="pill">Schools</span>
                <span class="pill">Parks</span>
                <span class="pill">Restaurants</span>
                <span class="pill">Churches</span>
                <span class="pill">Businesses</span>
            </div>
            <form class="hero__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="visually-hidden" for="site-search">Search the site</label>
                <input id="site-search" type="search" name="s" placeholder="Search guides, schools, parks, and events" value="<?php echo esc_attr(get_search_query()); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
        </div>
    </div>
</section>