<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Shared behavior for every provider: the slug and the cache() helper.
 * search()/fetch()/normalize() stay abstract — each provider's integration
 * is genuinely different and shouldn't be templated into a false-shared
 * implementation.
 */
abstract class Southforsyth_Abstract_Provider implements Southforsyth_Data_Provider
{
    protected $slug;

    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    public function get_slug()
    {
        return $this->slug;
    }

    public function cache($key, $ttl, callable $resolver)
    {
        return Southforsyth_Cache_Manager::remember($this->cache_key($key), $ttl, $resolver);
    }

    protected function cache_key($key)
    {
        return 'provider_' . $this->slug . '_' . md5($key);
    }

    /**
     * Shared HTTP GET helper: every real (non-stub) provider fetches from a
     * public HTTP endpoint, so this centralizes the wp_remote_get() call,
     * a descriptive User-Agent (several providers below — NWS, Nominatim —
     * require or strongly request one), and consistent error handling.
     * Returns the decoded body on success, or null on any failure.
     */
    protected function http_get($url, array $headers = array(), $as_json = true)
    {
        $response = wp_remote_get($url, array(
            'timeout' => 10,
            'headers' => array_merge(array(
                'User-Agent' => 'SouthForsyth.org/1.0 (community platform; ' . home_url('/') . ')',
            ), $headers),
        ));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        if (! $as_json) {
            return $body;
        }

        $decoded = json_decode($body, true);
        return (null === $decoded && JSON_ERROR_NONE !== json_last_error()) ? null : $decoded;
    }

    abstract public function search($query, array $args = array());
    abstract public function fetch($id, array $args = array());
    abstract public function normalize($raw);
}
