<?php

/**
 * Automation-ready hooks (Phase 9).
 *
 * IMPORTANT: no cron job is scheduled anywhere in this file, on purpose —
 * per the brief, this is infrastructure only. Every callback below is
 * registered against a plain WordPress action hook, not a cron schedule, so
 * nothing runs on its own. To actually automate one of these:
 *
 *     wp_schedule_event(time(), 'hourly', 'southforsyth_refresh_weather');
 *
 * ...added later (e.g. in a small "activate automation" admin action, or a
 * real WP-Cron setup once a source's reliability has been proven manually
 * first — see docs/data-integration-roadmap.md's "Future automation
 * phases", which this file's hooks map directly onto). Until then, every
 * hook below can still be triggered manually via `do_action(...)` — from
 * WP-CLI, from an admin action, or during development.
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('southforsyth_get_automation_hooks')) {
    /** @return array<string,string> hook name => human description, for admin/doc display */
    function southforsyth_get_automation_hooks()
    {
        return array(
            'southforsyth_refresh_weather'  => 'Re-fetch the current forecast and force-refresh the weather cache.',
            'southforsyth_refresh_traffic'  => 'Re-fetch current road/traffic conditions and force-refresh the cache.',
            'southforsyth_refresh_events'   => 'Re-fetch every configured ICS calendar and queue new/changed events for import.',
            'southforsyth_expire_stale_content' => 'Move past-dated Events to draft so the public site never shows an expired listing.',
        );
    }
}

/**
 * Forces a fresh weather fetch/cache for the site's configured point.
 * Reads sf_lat/sf_lng from options if set; otherwise no-ops (there's no
 * fabricated default coordinate — an admin must configure one on the
 * Settings page before this does anything).
 */
if (! function_exists('southforsyth_refresh_weather')) {
    function southforsyth_refresh_weather()
    {
        $lat = get_option('southforsyth_site_lat');
        $lng = get_option('southforsyth_site_lng');
        if (! $lat || ! $lng) {
            return;
        }

        $provider = southforsyth_get_provider('weather');
        if (! $provider) {
            return;
        }

        Southforsyth_Cache_Manager::refresh('provider_weather_' . md5('forecast_' . $lat . ',' . $lng), HOUR_IN_SECONDS, function () use ($provider, $lat, $lng) {
            return $provider->fetch($lat . ',' . $lng);
        });

        Southforsyth_Import_Logger::info('weather', 'Scheduled/manual weather refresh ran.');
    }
}
add_action('southforsyth_refresh_weather', 'southforsyth_refresh_weather');

if (! function_exists('southforsyth_refresh_traffic')) {
    function southforsyth_refresh_traffic()
    {
        $provider = southforsyth_get_provider('traffic');
        if (! $provider || ! $provider->is_configured()) {
            return;
        }

        Southforsyth_Cache_Manager::refresh('provider_traffic_' . md5('incidents_'), 10 * MINUTE_IN_SECONDS, function () use ($provider) {
            return $provider->search('');
        });

        Southforsyth_Import_Logger::info('traffic', 'Scheduled/manual traffic refresh ran.');
    }
}
add_action('southforsyth_refresh_traffic', 'southforsyth_refresh_traffic');

/**
 * Re-fetches every configured ICS feed (newline-separated URLs in the
 * `southforsyth_ics_feed_urls` option) and pushes each event onto the
 * import queue as a pending job — never straight to a published post,
 * matching the "nothing writes directly to publish" rule.
 */
if (! function_exists('southforsyth_refresh_events')) {
    function southforsyth_refresh_events()
    {
        $feed_urls = array_filter(array_map('trim', explode("\n", (string) get_option('southforsyth_ics_feed_urls', ''))));
        if (empty($feed_urls)) {
            return;
        }

        $provider = southforsyth_get_provider('events_ics');
        if (! $provider) {
            return;
        }

        $queued = 0;
        foreach ($feed_urls as $feed_url) {
            $events = $provider->search($feed_url);
            foreach ($events as $event) {
                $normalized = $provider->normalize($event);
                if (empty($normalized)) {
                    continue;
                }

                $exists = Southforsyth_Duplicate_Detector::find_existing(
                    $normalized['source'],
                    $normalized['source_id'],
                    $normalized['post_type']
                );

                if (! $exists) {
                    Southforsyth_Import_Queue::push($normalized['source'], $normalized['post_type'], $normalized);
                    $queued++;
                }
            }
        }

        Southforsyth_Import_Logger::info('events_ics', "Scheduled/manual events refresh queued {$queued} new event(s) across " . count($feed_urls) . ' feed(s).');
    }
}
add_action('southforsyth_refresh_events', 'southforsyth_refresh_events');

/**
 * Expires stale content: any published Event whose sf_event_date has
 * already passed gets moved to draft, so the public site never shows a
 * listing for an event that already happened.
 */
if (! function_exists('southforsyth_expire_stale_content')) {
    function southforsyth_expire_stale_content()
    {
        $events = get_posts(array(
            'post_type'      => 'event',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'sf_event_date',
                    'value'   => current_time('Y-m-d'),
                    'compare' => '<',
                    'type'    => 'DATE',
                ),
            ),
        ));

        foreach ($events as $event) {
            wp_update_post(array('ID' => $event->ID, 'post_status' => 'draft'));
        }

        Southforsyth_Import_Logger::info('automation', 'Expired ' . count($events) . ' past-dated event(s).');
    }
}
add_action('southforsyth_expire_stale_content', 'southforsyth_expire_stale_content');
