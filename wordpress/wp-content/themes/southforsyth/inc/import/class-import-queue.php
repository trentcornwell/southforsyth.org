<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * FIFO job queue for import work, backed by the custom table from
 * Southforsyth_Import_Install. A "job" is one normalized record waiting to
 * be run through Southforsyth_Importer — see docs/platform-architecture.md
 * ("Import system").
 */
class Southforsyth_Import_Queue
{
    public static function push($provider, $post_type, array $normalized_record)
    {
        global $wpdb;
        $now = current_time('mysql');

        $wpdb->insert(Southforsyth_Import_Install::queue_table(), array(
            'provider'   => $provider,
            'source_id'  => $normalized_record['source_id'] ?? '',
            'post_type'  => $post_type,
            'payload'    => wp_json_encode($normalized_record),
            'status'     => 'pending',
            'attempts'   => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ));

        return $wpdb->insert_id;
    }

    /** Claims the oldest pending job by marking it "processing" and returning it. */
    public static function pop()
    {
        global $wpdb;
        $table = Southforsyth_Import_Install::queue_table();

        $job = $wpdb->get_row("SELECT * FROM {$table} WHERE status = 'pending' ORDER BY id ASC LIMIT 1", ARRAY_A);
        if (! $job) {
            return null;
        }

        $wpdb->update($table, array(
            'status'     => 'processing',
            'updated_at' => current_time('mysql'),
        ), array('id' => $job['id']));

        $job['payload'] = json_decode($job['payload'], true);
        return $job;
    }

    public static function mark_done($job_id)
    {
        self::update_status($job_id, 'done');
    }

    public static function mark_failed($job_id)
    {
        global $wpdb;
        $table = Southforsyth_Import_Install::queue_table();

        $wpdb->query($wpdb->prepare("UPDATE {$table} SET attempts = attempts + 1 WHERE id = %d", $job_id));
        self::update_status($job_id, 'failed');
    }

    private static function update_status($job_id, $status)
    {
        global $wpdb;
        $wpdb->update(Southforsyth_Import_Install::queue_table(), array(
            'status'     => $status,
            'updated_at' => current_time('mysql'),
        ), array('id' => $job_id));
    }

    /** @return array<string,int> counts keyed by status */
    public static function counts()
    {
        global $wpdb;
        $table = Southforsyth_Import_Install::queue_table();

        $rows = $wpdb->get_results("SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", ARRAY_A);
        $counts = array('pending' => 0, 'processing' => 0, 'done' => 0, 'failed' => 0);
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }
        return $counts;
    }

    public static function recent($limit = 50)
    {
        global $wpdb;
        $table = Southforsyth_Import_Install::queue_table();
        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table} ORDER BY id DESC LIMIT %d", $limit), ARRAY_A);
    }
}
