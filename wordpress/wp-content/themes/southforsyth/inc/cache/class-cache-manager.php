<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Thin wrapper around WordPress transients so every provider and importer
 * reads/writes cache through one place. Swapping the storage backend later
 * (e.g. a persistent object cache backed by Redis) means changing the
 * methods below, not every call site — see docs/platform-architecture.md
 * ("Cache layer") for the migration note. WordPress transients already use
 * the object cache automatically when one is configured (e.g. a Redis
 * object-cache drop-in), so this class is Redis-ready today without any
 * code change — it only needs a persistent object cache plugin installed.
 */
class Southforsyth_Cache_Manager
{
    const DEFAULT_TTL = HOUR_IN_SECONDS;
    const PREFIX = 'sf_cache_';

    public static function get($key)
    {
        return get_transient(self::PREFIX . $key);
    }

    public static function set($key, $value, $ttl = null)
    {
        return set_transient(self::PREFIX . $key, $value, $ttl ?? self::DEFAULT_TTL);
    }

    public static function delete($key)
    {
        return delete_transient(self::PREFIX . $key);
    }

    /**
     * Read-through cache: return the cached value if present, otherwise
     * call $resolver(), cache its result, and return it. Every provider's
     * cache() method (see Southforsyth_Abstract_Provider) delegates here.
     */
    public static function remember($key, $ttl, callable $resolver)
    {
        $cached = self::get($key);
        if (false !== $cached) {
            return $cached;
        }

        $value = $resolver();
        self::set($key, $value, $ttl);
        return $value;
    }

    /**
     * Forces $resolver() to run and re-caches the result, regardless of
     * whether a cached value still exists. This is what "manual refresh"
     * (an admin action) and, later, "scheduled refresh" (the Phase 9
     * automation hooks in inc/automation.php) both call — scheduled refresh
     * is just this method invoked from a cron callback instead of a click.
     */
    public static function refresh($key, $ttl, callable $resolver)
    {
        $value = $resolver();
        self::set($key, $value, $ttl);
        return $value;
    }
}
