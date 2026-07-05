<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Progress/event logger for the import engine, backed by the custom table
 * from Southforsyth_Import_Install. Matches
 * docs/data-integration-roadmap.md's "Failures are logged, not silent" rule
 * — every provider/importer call in this system should log through here
 * rather than PHP's error_log(), so the admin "Logs" page has one place to
 * read from.
 */
class Southforsyth_Import_Logger
{
    public static function log($provider, $level, $message, array $context = array())
    {
        global $wpdb;
        $wpdb->insert(Southforsyth_Import_Install::log_table(), array(
            'provider'   => $provider,
            'level'      => $level,
            'message'    => $message,
            'context'    => ! empty($context) ? wp_json_encode($context) : null,
            'created_at' => current_time('mysql'),
        ));
    }

    public static function info($provider, $message, array $context = array())
    {
        self::log($provider, 'info', $message, $context);
    }

    public static function warning($provider, $message, array $context = array())
    {
        self::log($provider, 'warning', $message, $context);
    }

    public static function error($provider, $message, array $context = array())
    {
        self::log($provider, 'error', $message, $context);
    }

    public static function recent($limit = 100, $level = null)
    {
        global $wpdb;
        $table = Southforsyth_Import_Install::log_table();

        if ($level) {
            return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} WHERE level = %s ORDER BY id DESC LIMIT %d", $level, $limit), ARRAY_A);
        }

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", $limit), ARRAY_A);
    }

    public static function clear()
    {
        global $wpdb;
        $wpdb->query('TRUNCATE TABLE ' . Southforsyth_Import_Install::log_table());
    }
}
