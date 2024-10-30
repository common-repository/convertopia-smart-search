<?php
/**
 * @wordpress-plugin
 * Plugin Name:       Convertopia Smart Search
 * Plugin URI:        https://convertopia.com/
 * Description:       Integrate The Powerful Convertopia Search Service With WordPress
 * Version:           1.0.0
 * Author:            Convertopia
 * Author URI:        https://www.innovadeltech.com/
 * License:           GPL-2.0-or-later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0
 * Text Domain:       convertopia-smart-search
 * Domain Path:       /languages
 */


defined( 'ABSPATH' ) or die( 'UnAuthorized Access !' );

define( 'CONVERTOPIA_VERSION', '1.0.0' );
define( 'CONVERTOPIA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'CONVERTOPIA_PATH' ) ) {
    define( 'CONVERTOPIA_PATH', plugin_dir_path( __FILE__ ) );
}



require_once plugin_dir_path(__FILE__).'includes/class-convertopia-admin-notices.php';
require_once plugin_dir_path(__FILE__).'includes/csearch.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-convertopia.php';


// Include feature files
require_once plugin_dir_path(__FILE__) . 'includes/customer-feed.php';
require_once plugin_dir_path(__FILE__) . 'includes/product-feed.php';
require_once plugin_dir_path(__FILE__) . 'includes/order-feed.php';
require_once plugin_dir_path(__FILE__) . 'jobs/cron-scheduler.php';
require_once plugin_dir_path(__FILE__) . 'database/convertopia-feed-configs.php';
register_activation_hook(__FILE__, 'convertopia_create_feed_configs_table');
// register_activation_hook(__FILE__, 'convertopia_add_last_modified_column');


// Handle AJAX request to update all feed schedules
function convertopia_update_all_schedules() {
    convertopia_update_schedule('convertopia_cron_generate_customer_feed', 'customer_feed','convertopia_update_all_schedules_action');
    convertopia_update_schedule('convertopia_cron_generate_order_feed', 'order_feed', 'convertopia_update_all_schedules_action');
    convertopia_update_schedule('convertopia_cron_generate_product_feed', 'product_feed', 'convertopia_update_all_schedules_action');
    wp_send_json_success(array('message' => 'Schedules updated successfully'));
}
add_action('wp_ajax_convertopia_update_all_schedules', 'convertopia_update_all_schedules');


function convertopia_update_all_delta_schedules() {
    convertopia_update_schedule('convertopia_cron_generate_customer_feed_delta', 'customer_feed', 'convertopia_update_all_delta_schedules_action');
    convertopia_update_schedule('convertopia_cron_generate_order_feed_delta', 'order_feed', 'convertopia_update_all_delta_schedules_action');
    convertopia_update_schedule('convertopia_cron_generate_product_feed_delta', 'product_feed', 'convertopia_update_all_delta_schedules_action');
    wp_send_json_success(array('message' => 'Delta Schedules updated successfully'));
}
add_action('wp_ajax_convertopia_update_all_delta_schedules', 'convertopia_update_all_delta_schedules');

//function to add user_registered meta_key to users table 
if ( ! function_exists( 'convertopia_add_user_registered_meta' ) ) {
    function convertopia_add_user_registered_meta($user_id) {
        // Get the user registration time
        $user_info = get_userdata($user_id);
        $user_registered = $user_info->user_registered;

        // Add the meta key to wp_usermeta table
        update_user_meta($user_id, 'user_registered', strtotime($user_registered));
    }
    add_action('user_register', 'convertopia_add_user_registered_meta');
}

// Register uninstall hook to clean up on plugin removal
register_uninstall_hook(__FILE__, 'convertopia_uninstall');

function convertopia_uninstall() {
    global $wpdb;

    // Delete custom table
    $table_name = $wpdb->prefix . 'convertopia_feed_configs';
    // @codingStandardsIgnoreStart
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %s", $table_name));
    // @codingStandardsIgnoreEnd 

    // Clear scheduled events
    wp_clear_scheduled_hook('convertopia_cron_generate_customer_feed');
    wp_clear_scheduled_hook('convertopia_cron_generate_order_feed');
    wp_clear_scheduled_hook('convertopia_cron_generate_product_feed');
    wp_clear_scheduled_hook('convertopia_cron_generate_customer_feed_delta');
    wp_clear_scheduled_hook('convertopia_cron_generate_order_feed_delta');
    wp_clear_scheduled_hook('convertopia_cron_generate_product_feed_delta');

    // Delete plugin options
    delete_option('convertopia_settings');
    delete_option('convertopia_last_run_time_customers');
    delete_option('convertopia_feed_frquency');
    delete_option('convertopia_feed_time');

    // Clear cache if necessary
    wp_cache_delete('convertopia_settings');

    // If there are multiple cache keys, clear them all
    wp_cache_delete('convertopia_customer_data');
    wp_cache_delete('convertopia_order_data');
    wp_cache_delete('convertopia_product_data');

    wp_cache_delete('convertopia_feed_schedule_customer_feed');
    wp_cache_delete('convertopia_feed_schedule_order_feed');
    wp_cache_delete('convertopia_feed_schedule_product_feed');

    // Delete cached data
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete('convertopia_configs');
    }
}

// Add admin notices class
if (!class_exists('Convertopia_Admin_Notices')) {
    class Convertopia_Admin_Notices {
        public static function show_notices() {
            // Display admin notices if needed
        }
    }
}


// Include additional files only if class exists
if (class_exists('Convertopia')) {
    Convertopia::init();
}