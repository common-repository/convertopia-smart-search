<?php

function convertopia_create_feed_configs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'convertopia_feed_configs';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        action_hook varchar(255) NOT NULL,
        feed_type varchar(20) NOT NULL,
        frequency varchar(50) NOT NULL,
        time varchar(10) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}