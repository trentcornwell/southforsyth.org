<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Creates the two small custom tables the import engine needs: a job queue
 * and an event log. Custom tables (not CPT rows) on purpose — see
 * docs/platform-architecture.md ("Why custom tables") — this project is
 * explicitly scoped to scale to hundreds of thousands of content pages, and
 * every import attempt logging a row would make wp_posts the wrong tool for
 * the job. Runs via dbDelta(), the same WordPress-native schema-migration
 * mechanism core and virtually every serious plugin uses, gated by a
 * version option so it only actually runs when the schema changes.
 */
class Southforsyth_Import_Install
{
    const SCHEMA_VERSION = '1.0.0';
    const VERSION_OPTION = 'southforsyth_import_schema_version';

    public static function queue_table()
    {
        global $wpdb;
        return $wpdb->prefix . 'sf_import_queue';
    }

    public static function log_table()
    {
        global $wpdb;
        return $wpdb->prefix . 'sf_import_log';
    }

    public static function maybe_install()
    {
        if (get_option(self::VERSION_OPTION) === self::SCHEMA_VERSION) {
            return;
        }

        self::install();
        update_option(self::VERSION_OPTION, self::SCHEMA_VERSION);
    }

    public static function install()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $queue_table = self::queue_table();
        $log_table = self::log_table();

        $sql = "CREATE TABLE {$queue_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider VARCHAR(64) NOT NULL DEFAULT '',
            source_id VARCHAR(191) NOT NULL DEFAULT '',
            post_type VARCHAR(32) NOT NULL DEFAULT '',
            payload LONGTEXT NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY provider_source (provider, source_id)
        ) {$charset_collate};

        CREATE TABLE {$log_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider VARCHAR(64) NOT NULL DEFAULT '',
            level VARCHAR(20) NOT NULL DEFAULT 'info',
            message TEXT NOT NULL,
            context LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY provider (provider),
            KEY level (level),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }
}
