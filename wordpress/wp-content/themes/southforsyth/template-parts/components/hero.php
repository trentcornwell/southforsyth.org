<section class="hero">
    <div class="container hero__inner">
        <div class="hero__content stack">
            <p class="eyebrow">South Forsyth, Georgia</p>
            <h1>Your Guide to Life in South Forsyth</h1>
            <p class="hero__lede">Find upcoming events, parks, schools, churches, restaurants, local guides, and trusted community resources in one place.</p>
            <form class="hero__search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="visually-hidden" for="site-search">Search the site</label>
                <input id="site-search" type="search" name="s" placeholder="Search guides, schools, parks, and events" value="<?php echo esc_attr(get_search_query()); ?>">
                <button class="btn btn--primary" type="submit">Search</button>
            </form>
        </div>
    </div>
</section>