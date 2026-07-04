<?php

/**
 * Search component.
 * Usage: include this partial inside a hero or landing section and supply a query string if needed.
 */

$search_action = home_url('/');
$search_value = get_search_query();
?>
<form class="search" role="search" method="get" action="<?php echo esc_url($search_action); ?>" aria-label="Site search">
    <label class="visually-hidden" for="site-search">Search the site</label>
    <input id="site-search" class="search__field" type="search" name="s" placeholder="Search guides, schools, parks, and events" value="<?php echo esc_attr($search_value); ?>">
    <button class="btn btn-primary" type="submit">Search</button>
</form>