<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * "Community Platform" admin menu (Phase 6). Deliberately unstyled beyond
 * WordPress's own admin CSS classes (.wrap, .widefat, .button) — these are
 * working operational tools for whoever maintains the site, not a
 * public-facing UI, so custom design effort isn't the priority here.
 */
class Southforsyth_Admin_Menu
{
    const CAPABILITY = 'manage_options';
    const SLUG = 'southforsyth-platform';

    public static function register()
    {
        add_action('admin_menu', array(__CLASS__, 'add_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_action('admin_post_southforsyth_process_next_job', array(__CLASS__, 'handle_process_next_job'));
        add_action('admin_post_southforsyth_clear_logs', array(__CLASS__, 'handle_clear_logs'));
    }

    public static function add_menu()
    {
        add_menu_page('Community Platform', 'Community Platform', self::CAPABILITY, self::SLUG, array(__CLASS__, 'render_providers'), 'dashicons-networking', 58);

        add_submenu_page(self::SLUG, 'Providers', 'Providers', self::CAPABILITY, self::SLUG, array(__CLASS__, 'render_providers'));
        add_submenu_page(self::SLUG, 'Imports', 'Imports', self::CAPABILITY, self::SLUG . '-imports', array(__CLASS__, 'render_imports'));
        add_submenu_page(self::SLUG, 'Queues', 'Queues', self::CAPABILITY, self::SLUG . '-queues', array(__CLASS__, 'render_queues'));
        add_submenu_page(self::SLUG, 'Logs', 'Logs', self::CAPABILITY, self::SLUG . '-logs', array(__CLASS__, 'render_logs'));
        add_submenu_page(self::SLUG, 'Content Status', 'Content Status', self::CAPABILITY, self::SLUG . '-content-status', array(__CLASS__, 'render_content_status'));
        add_submenu_page(self::SLUG, 'Statistics', 'Statistics', self::CAPABILITY, self::SLUG . '-statistics', array(__CLASS__, 'render_statistics'));
        add_submenu_page(self::SLUG, 'Settings', 'Settings', self::CAPABILITY, self::SLUG . '-settings', array(__CLASS__, 'render_settings'));
    }

    // ---------------------------------------------------------------
    // Providers
    // ---------------------------------------------------------------

    public static function render_providers()
    {
        $descriptions = array(
            'google_places'  => 'Businesses & restaurants via the Google Places API. Requires a billing-enabled API key.',
            'census'         => 'U.S. Census/ACS demographic context (population, income, age) for Neighborhood profiles. Requires a free Census API key.',
            'openstreetmap'  => 'Geocoding via OpenStreetMap Nominatim. Keyless, rate-limited to ~1 req/sec.',
            'forsyth_county' => 'Forsyth County government/schools/parks/library. Reads a configurable feed URL — no confirmed public API yet.',
            'nces'           => 'NCES public school data (grade span, address, lat/lng) for enrichment/verification, not narrative content. Reads a configurable feed URL — no confirmed keyless API yet.',
            'weather'        => 'Forecasts via the free, keyless National Weather Service API (api.weather.gov).',
            'traffic'        => 'Road conditions via GDOT/511 Georgia. Reads a configurable feed URL + optional API key.',
            'rss'            => 'News/article excerpts via WordPress core\'s built-in feed parser. Excerpt + link only, per policy.',
            'events_ics'     => 'Calendar events via a dependency-free ICS (iCalendar) parser — church, school, library, Chamber calendars.',
        );

        self::render_wrap('Providers', function () use ($descriptions) {
            echo '<p>Every provider implements <code>search()</code>, <code>fetch()</code>, <code>normalize()</code>, and <code>cache()</code> — see <code>inc/providers/</code> and <code>docs/platform-architecture.md</code>.</p>';
            echo '<table class="widefat striped"><thead><tr><th>Slug</th><th>Class</th><th>Status</th><th>Description</th></tr></thead><tbody>';

            foreach (southforsyth_get_providers() as $slug => $provider) {
                if (! $provider) {
                    continue;
                }
                $configured = method_exists($provider, 'is_configured') ? $provider->is_configured() : true;
                $status = $configured ? '<span style="color:#2f7d4f;">Ready</span>' : '<span style="color:#c97a00;">Needs setup</span>';

                echo '<tr>';
                echo '<td><code>' . esc_html($slug) . '</code></td>';
                echo '<td><code>' . esc_html(get_class($provider)) . '</code></td>';
                echo '<td>' . $status . '</td>';
                echo '<td>' . esc_html($descriptions[$slug] ?? '') . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        });
    }

    // ---------------------------------------------------------------
    // Imports
    // ---------------------------------------------------------------

    public static function render_imports()
    {
        self::render_wrap('Imports', function () {
            $counts = Southforsyth_Import_Queue::counts();

            echo '<p>No scheduled jobs run automatically yet (Phase 9 is infrastructure-only — see <code>inc/automation.php</code>). Use the button below to manually process the next queued job, one at a time, while building/testing a provider.</p>';

            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            wp_nonce_field('southforsyth_process_next_job');
            echo '<input type="hidden" name="action" value="southforsyth_process_next_job">';
            submit_button('Process next queued job (' . (int) $counts['pending'] . ' pending)', 'primary', 'submit', false);
            echo '</form>';

            echo '<h2>Recent import activity</h2>';
            self::render_log_table(Southforsyth_Import_Logger::recent(20));
        });
    }

    public static function handle_process_next_job()
    {
        check_admin_referer('southforsyth_process_next_job');

        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('Insufficient permissions.', 'southforsyth'));
        }

        Southforsyth_Importer::process_next();

        wp_safe_redirect(admin_url('admin.php?page=' . self::SLUG . '-imports&processed=1'));
        exit;
    }

    // ---------------------------------------------------------------
    // Queues
    // ---------------------------------------------------------------

    public static function render_queues()
    {
        self::render_wrap('Queues', function () {
            $counts = Southforsyth_Import_Queue::counts();

            echo '<ul>';
            foreach ($counts as $status => $count) {
                echo '<li><strong>' . esc_html(ucfirst($status)) . ':</strong> ' . (int) $count . '</li>';
            }
            echo '</ul>';

            echo '<h2>Recent jobs</h2>';
            $jobs = Southforsyth_Import_Queue::recent(50);

            if (empty($jobs)) {
                echo '<p>No jobs queued yet. Providers push jobs onto this queue via <code>Southforsyth_Import_Queue::push()</code> once a real fetch is wired up.</p>';
                return;
            }

            echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Provider</th><th>Post type</th><th>Status</th><th>Attempts</th><th>Created</th></tr></thead><tbody>';
            foreach ($jobs as $job) {
                echo '<tr>';
                echo '<td>' . (int) $job['id'] . '</td>';
                echo '<td>' . esc_html($job['provider']) . '</td>';
                echo '<td>' . esc_html($job['post_type']) . '</td>';
                echo '<td>' . esc_html($job['status']) . '</td>';
                echo '<td>' . (int) $job['attempts'] . '</td>';
                echo '<td>' . esc_html($job['created_at']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        });
    }

    // ---------------------------------------------------------------
    // Logs
    // ---------------------------------------------------------------

    public static function render_logs()
    {
        self::render_wrap('Logs', function () {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom:1em;">';
            wp_nonce_field('southforsyth_clear_logs');
            echo '<input type="hidden" name="action" value="southforsyth_clear_logs">';
            submit_button('Clear logs', 'delete', 'submit', false);
            echo '</form>';

            self::render_log_table(Southforsyth_Import_Logger::recent(200));
        });
    }

    public static function handle_clear_logs()
    {
        check_admin_referer('southforsyth_clear_logs');

        if (! current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('Insufficient permissions.', 'southforsyth'));
        }

        Southforsyth_Import_Logger::clear();

        wp_safe_redirect(admin_url('admin.php?page=' . self::SLUG . '-logs&cleared=1'));
        exit;
    }

    private static function render_log_table(array $logs)
    {
        if (empty($logs)) {
            echo '<p>No log entries yet.</p>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr><th>Time</th><th>Level</th><th>Provider</th><th>Message</th></tr></thead><tbody>';
        foreach ($logs as $log) {
            $color = array('error' => '#b33a3a', 'warning' => '#c97a00', 'info' => '#2f3740');
            echo '<tr>';
            echo '<td>' . esc_html($log['created_at']) . '</td>';
            echo '<td style="color:' . esc_attr($color[$log['level']] ?? '#2f3740') . ';">' . esc_html($log['level']) . '</td>';
            echo '<td>' . esc_html($log['provider']) . '</td>';
            echo '<td>' . esc_html($log['message']) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    // ---------------------------------------------------------------
    // Content status
    // ---------------------------------------------------------------

    public static function render_content_status()
    {
        self::render_wrap('Content Status', function () {
            echo '<table class="widefat striped"><thead><tr><th>Post type</th><th>Published</th><th>Draft</th><th>Pending review</th></tr></thead><tbody>';

            foreach (southforsyth_get_post_type_definitions() as $post_type => $definition) {
                $counts = wp_count_posts($post_type);
                echo '<tr>';
                echo '<td>' . esc_html($definition['plural']) . '</td>';
                echo '<td>' . (int) ($counts->publish ?? 0) . '</td>';
                echo '<td>' . (int) ($counts->draft ?? 0) . '</td>';
                echo '<td>' . (int) ($counts->pending ?? 0) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        });
    }

    // ---------------------------------------------------------------
    // Statistics
    // ---------------------------------------------------------------

    public static function render_statistics()
    {
        self::render_wrap('Statistics', function () {
            $total_published = 0;
            $total_imported = 0;

            foreach (array_keys(southforsyth_get_post_type_definitions()) as $post_type) {
                $counts = wp_count_posts($post_type);
                $total_published += (int) ($counts->publish ?? 0);

                $imported = get_posts(array(
                    'post_type'   => $post_type,
                    'post_status' => 'any',
                    'numberposts' => -1,
                    'fields'      => 'ids',
                    'meta_key'    => Southforsyth_Duplicate_Detector::SOURCE_META_KEY,
                ));
                $total_imported += count($imported);
            }

            $queue_counts = Southforsyth_Import_Queue::counts();

            echo '<ul>';
            echo '<li><strong>Total published content posts (all platform types):</strong> ' . (int) $total_published . '</li>';
            echo '<li><strong>Posts created via the import engine:</strong> ' . (int) $total_imported . '</li>';
            echo '<li><strong>Queue — pending:</strong> ' . (int) $queue_counts['pending'] . '</li>';
            echo '<li><strong>Queue — done:</strong> ' . (int) $queue_counts['done'] . '</li>';
            echo '<li><strong>Queue — failed:</strong> ' . (int) $queue_counts['failed'] . '</li>';
            echo '</ul>';
        });
    }

    // ---------------------------------------------------------------
    // Settings
    // ---------------------------------------------------------------

    public static function register_settings()
    {
        $settings = array(
            'southforsyth_google_places_api_key',
            'southforsyth_census_api_key',
            'southforsyth_forsyth_county_feed_url',
            'southforsyth_nces_feed_url',
            'southforsyth_traffic_feed_url',
            'southforsyth_traffic_api_key',
        );

        foreach ($settings as $setting) {
            register_setting('southforsyth_platform_settings', $setting, array('sanitize_callback' => 'sanitize_text_field'));
        }
    }

    public static function render_settings()
    {
        self::render_wrap('Settings', function () {
            echo '<p>Provider credentials/endpoints, stored in the WordPress options table — never in <code>.env</code> (that file is reserved for DreamHost deployment credentials only).</p>';
            echo '<form method="post" action="' . esc_url(admin_url('options.php')) . '">';
            settings_fields('southforsyth_platform_settings');

            echo '<table class="form-table"><tbody>';
            self::render_settings_field('southforsyth_google_places_api_key', 'Google Places API key');
            self::render_settings_field('southforsyth_census_api_key', 'U.S. Census API key');
            self::render_settings_field('southforsyth_forsyth_county_feed_url', 'Forsyth County feed URL');
            self::render_settings_field('southforsyth_nces_feed_url', 'NCES public school data feed URL');
            self::render_settings_field('southforsyth_traffic_feed_url', 'Traffic (GDOT/511 Georgia) feed URL');
            self::render_settings_field('southforsyth_traffic_api_key', 'Traffic API key');
            echo '</tbody></table>';

            submit_button('Save Settings');
            echo '</form>';
        });
    }

    private static function render_settings_field($option, $label)
    {
        echo '<tr><th><label for="' . esc_attr($option) . '">' . esc_html($label) . '</label></th><td>';
        echo '<input type="text" class="regular-text" id="' . esc_attr($option) . '" name="' . esc_attr($option) . '" value="' . esc_attr(get_option($option, '')) . '">';
        echo '</td></tr>';
    }

    // ---------------------------------------------------------------
    // Shared wrapper
    // ---------------------------------------------------------------

    private static function render_wrap($title, callable $body)
    {
        echo '<div class="wrap">';
        echo '<h1>Community Platform — ' . esc_html($title) . '</h1>';
        $body();
        echo '</div>';
    }
}
