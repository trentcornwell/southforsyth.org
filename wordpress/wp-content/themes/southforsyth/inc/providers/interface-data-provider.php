<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Contract every external data provider must implement. See
 * docs/platform-architecture.md ("How to add a provider") before adding a
 * new one — the pattern is always: search() finds candidate records,
 * fetch() retrieves one record in full, normalize() maps the provider's own
 * raw shape onto the theme's common import shape (Southforsyth_Normalizer),
 * and cache() wraps read-through caching around any of the above.
 */
interface Southforsyth_Data_Provider
{
    /** A short, stable identifier used as the cache-key namespace and as
     *  the value stored in the import log's `provider` column. */
    public function get_slug();

    /** Find candidate records matching $query. Returns an array of raw,
     *  provider-shaped records (not yet normalized) — may be empty. */
    public function search($query, array $args = array());

    /** Retrieve one record in full by the provider's own ID. Returns a raw,
     *  provider-shaped record, or null if not found. */
    public function fetch($id, array $args = array());

    /** Map one raw provider record onto the common import shape (see
     *  Southforsyth_Normalizer::shape()) that Southforsyth_Importer expects. */
    public function normalize($raw);

    /** Read-through cache around any callable — see
     *  Southforsyth_Cache_Manager::remember(). */
    public function cache($key, $ttl, callable $resolver);
}
