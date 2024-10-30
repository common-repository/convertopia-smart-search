<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once plugin_dir_path(__FILE__) . '../jobs/cron-scheduler.php';

// Handle AJAX request to generate customer feed
if ( ! function_exists( 'convertopia_ajax_generate_customer_feed' ) ) {
    function convertopia_ajax_generate_customer_feed($customerdelta, $nonce) {

        if (!empty($nonce)) {
            if (!check_ajax_referer($nonce, 'nonce', false)) {
                wp_send_json_error(array('message' => 'Invalid nonce'));
            }
        }

        $convertopia_settings = get_option('convertopia_settings');
        $delta = isset($_POST['delta']) ? sanitize_text_field(wp_unslash($_POST['delta'])) : "false";
        $last_run = get_option('convertopia_last_run_time_customers');
        $last_run_time = strtotime($last_run);

        if ($customerdelta || $delta == "true" && $last_run_time) {

            // @codingStandardsIgnoreStart
            $args = array(
                'role__in' => array('customer', 'subscriber'), // Adjust roles as needed
                'number' => -1, // Get all users
                'meta_query' => array(
                    'relation' => 'OR', // Use OR to get users modified or registered since $last_run_time
                    array(
                        'key' => 'last_update',
                        'value' => $last_run_time,
                        'compare' => '>='
                    ),
                    array(
                        'key' => 'user_registered',
                        'value' => $last_run_time,
                        'compare' => '>='
                    ),
                ),
            );
            // @codingStandardsIgnoreEnd

        } else {
            $args = array(
                'role__in' => array('customer', 'subscriber'), // Adjust roles as needed
                'number' => -1 // Get all users
            );
        }

        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();


        if (!empty($users)) {
            $ndjson_data = '';
            foreach ($users as $user) {
                // Get user profile data
                $profile_data = array(
                    'salutation' => sanitize_text_field(get_user_meta($user->ID, 'salutation', true)) ?: "",
                    'title' => sanitize_text_field(get_user_meta($user->ID, 'title', true)) ?: "",
                    'company' => sanitize_text_field(get_user_meta($user->ID, 'billing_company', true)) ?: "",
                    'job_title' => sanitize_text_field(get_user_meta($user->ID, 'job_title', true)) ?: "",
                    'first_name' => sanitize_text_field(get_user_meta($user->ID, 'first_name', true)),
                    'last_name' => sanitize_text_field(get_user_meta($user->ID, 'last_name', true)),
                    'name_suffix' => sanitize_text_field(get_user_meta($user->ID, 'name_suffix', true)) ?: "",
                    'gender' => sanitize_text_field(get_user_meta($user->ID, 'gender', true)),
                    'birthday' => sanitize_text_field(get_user_meta($user->ID, 'birthday', true)),
                    'email' => sanitize_email($user->user_email),
                    'next_birthday' => sanitize_text_field(get_user_meta($user->ID, 'next_birthday', true)) ?: "",
                    'second_name' => sanitize_text_field(get_user_meta($user->ID, 'second_name', true)) ?: ""
                );

                // Get user phone data
                $phone_data = array(
                    'home_phone' => sanitize_text_field(get_user_meta($user->ID, 'billing_phone', true)),
                    'business_phone' => sanitize_text_field(get_user_meta($user->ID, 'business_phone', true)) ?: "",
                    'mobile_phone' => sanitize_text_field(get_user_meta($user->ID, 'mobile_phone', true)),
                    'fax_number' => sanitize_text_field(get_user_meta($user->ID, 'fax_number', true)) ?: ""
                );

                // Get user address data
                $address_data = array(
                    array(
                        'address_id' => $user->ID,
                        'title' => sanitize_text_field(get_user_meta($user->ID, 'address_title', true)),
                        'company' => sanitize_text_field(get_user_meta($user->ID, 'address_company', true)) ?: "",
                        'salutation' => sanitize_text_field(get_user_meta($user->ID, 'address_salutation', true)) ?: "",
                        'first_name' => sanitize_text_field(get_user_meta($user->ID, 'billing_first_name', true)),
                        'last_name' => sanitize_text_field(get_user_meta($user->ID, 'billing_last_name', true)),
                        'second_name' => sanitize_text_field(get_user_meta($user->ID, 'address_second_name', true)) ?: "",
                        'suffix' => sanitize_text_field(get_user_meta($user->ID, 'address_suffix', true)) ?: "",
                        'address_1' => sanitize_text_field(get_user_meta($user->ID, 'billing_address_1', true)),
                        'address_2' => sanitize_text_field(get_user_meta($user->ID, 'billing_address_2', true)),
                        'suite_no' => sanitize_text_field(get_user_meta($user->ID, 'suite_no', true)) ?: "",
                        'postal_box' => sanitize_text_field(get_user_meta($user->ID, 'postal_box', true)),
                        'city' => sanitize_text_field(get_user_meta($user->ID, 'billing_city', true)),
                        'postal_code' => sanitize_text_field(get_user_meta($user->ID, 'billing_postcode', true)),
                        'country' => sanitize_text_field(get_user_meta($user->ID, 'billing_country', true)),
                        'state' => sanitize_text_field(get_user_meta($user->ID, 'billing_state', true)),
                        'contact_phone' => sanitize_text_field(get_user_meta($user->ID, 'billing_phone', true))
                    )
                );

                // Get user custom data
                $customs_data = array(
                    array(
                        'custom_attribute_name' => sanitize_text_field(get_user_meta($user->ID, 'custom_attribute_name', true))?: "",
                        'custom_attribute_value' => sanitize_text_field(get_user_meta($user->ID, 'custom_attribute_value', true))?: ""
                    )
                );

                // Get user order data
                $orders_data = array();
                $orders = wc_get_orders(array('customer_id' => $user->ID));
                foreach ($orders as $order) {
                    $order_data = array(
                        'order_id' => sanitize_text_field($order->get_id()),
                        'order_date' => $order->get_date_created()->date('Y-m-d\TH:i:sP')
                    );
                    $orders_data[] = $order_data;
                }

                // Prepare user data array
                $user_data = array(
                    'customer_no' => sanitize_text_field($user->ID),
                    'created' => sanitize_text_field($user->user_registered),
                    'last_visited_date_time' => sanitize_text_field(get_user_meta($user->ID, 'last_visited_date_time', true))?: "",
                    'login' => sanitize_text_field($user->user_login),
                    'profile' => $profile_data,
                    'phone' => $phone_data,
                    'addresses' => $address_data,
                    'placed_orders_count' => wc_get_customer_order_count($user->ID),
                    'orders' => $orders_data,
                    'customs' => $customs_data
                );

                // Convert user data to NDJSON format
                $ndjson_data.=  wp_json_encode($user_data). "\n";
            }

            // Define file name and path
            $path = wp_upload_dir();
            $currentDate = gmdate("Ymds");
            $ctStoreId = $convertopia_settings['cp_store_id'] ? sanitize_text_field($convertopia_settings['cp_store_id']) : '';

            if ($delta == "true" || $customerdelta) {
            $fName = "delta_{$ctStoreId}_customer_{$currentDate}.json";
            } else {
                $fName = "{$ctStoreId}_customer_feed{$currentDate}.json";
            }
            
            $fileName = "{$path['path']}/{$fName}";
            $fileUrl = "{$path['url']}/{$fName}";

            if ( ! function_exists( 'write_json_to_file' ) ) {
                function write_json_to_file( $fileName, $ndjson_data ) {
                    if ( ! function_exists( 'WP_Filesystem' ) ) {
                        require_once( ABSPATH . 'wp-admin/includes/file.php' );
                    }
            
                    // Initialize the WP_Filesystem API.
                    $creds = request_filesystem_credentials( '', '', false, false, array() );
                    if ( ! WP_Filesystem( $creds ) ) {
                        return new WP_Error( 'filesystem_error', esc_html(__( 'Could not access filesystem.', 'convertopia-smart-search' )) );
                    }
            
                    global $wp_filesystem;
            
                    // Write the JSON data to the file.
                    if ( ! $wp_filesystem->put_contents( $fileName, $ndjson_data, FS_CHMOD_FILE ) ) {
                        error_log( 'Failed to write JSON data to file: ' . $fileName );
                        echo wp_json_encode( array( 'message' => 'Failed to write JSON data to file.' ) );
                    }
                }
            }
            
            write_json_to_file( $fileName, $ndjson_data);

            // Upload file to FTP
            $ftpHost = isset($convertopia_settings['ftp_host']) && !empty($convertopia_settings['ftp_host']) ? sanitize_text_field($convertopia_settings['ftp_host']) : null;
            $ftpUsername = isset($convertopia_settings['ftp_user']) && !empty($convertopia_settings['ftp_user']) ? sanitize_text_field($convertopia_settings['ftp_user']) : null;
            $ftpPassword = isset($convertopia_settings['ftp_password']) && !empty($convertopia_settings['ftp_password']) ? sanitize_text_field($convertopia_settings['ftp_password']) : null;
            $ftp_path = isset($convertopia_settings['ftp_path']) && !empty($convertopia_settings['ftp_path']) ? sanitize_text_field($convertopia_settings['ftp_path']) : null;

            // Check if any of the required credentials are missing
            if ( !$ftpHost || !$ftpUsername || !$ftpPassword || !$ftp_path ) {
                error_log('Missing FTP credentials.'); // Log error
                echo wp_json_encode(array('message' => 'FTP credentials are missing.'));
                return;
            }
            
            $connId = ftp_connect($ftpHost);
            $ftpLogin = ftp_login($connId, $ftpUsername, $ftpPassword);
            ftp_pasv($connId, true);
            if (!$connId || !$ftpLogin) {
                error_log('FTP connection failed.'); // Log error
                echo wp_json_encode(array('message' => 'FTP connection failed.'));
            }

            $localFilePath = $fileName;
            $remoteFilePath = "{$ftp_path}/{$fName}";

            if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_ASCII)) {
                echo wp_json_encode(array(
                    'message' => "customer feed exported successfully to server {$ftp_path} with file name {$fName}",
                    'file_url' => esc_url($fileUrl)
                ));
            } else {
                error_log('Error uploading customer feed to FTP server.'); // Log error
                echo wp_json_encode(array('message' => 'Error uploading customer feed to FTP server.'));
            }
            ftp_close($connId);

            update_option('convertopia_last_run_time_customers', gmdate('Y-m-d H:i:s'));
        } else {
            echo  wp_json_encode(array('message' => 'No users found.'));
        }
    }
}

// Handle AJAX request to generate customer feed
if ( ! function_exists( 'convertopia_generate_customer_feed' ) ) {
    function convertopia_generate_customer_feed() {
        $customerdelta = false;
        convertopia_ajax_generate_customer_feed($customerdelta, 'convertopia_generate_customer_feed_action');
        wp_die(); // Always end with wp_die() to avoid extra output
    }
    add_action('wp_ajax_convertopia_generate_customer_feed', 'convertopia_generate_customer_feed');
}

// Schedule cron job to generate customer feed
if ( ! function_exists( 'convertopia_cron_generate_customer_feed' ) ) {
    function convertopia_cron_generate_customer_feed() {
        // Log for debugging
        error_log('convertopia_cron_generate_customer_feed triggered');
        $customerdelta = false;
        convertopia_ajax_generate_customer_feed($customerdelta, null);
        wp_die(); // Always end with wp_die() to avoid extra output
    }
    add_action('convertopia_cron_generate_customer_feed', 'convertopia_cron_generate_customer_feed');
}

// Schedule cron job to generate customer delta feed
if ( ! function_exists( 'convertopia_cron_generate_customer_feed_delta' ) ) {
    function convertopia_cron_generate_customer_feed_delta() {
        // Log for debugging
        error_log('convertopia_cron_generate_customer_feed_delta triggered');
        $customerdelta = true;
        convertopia_ajax_generate_customer_feed($customerdelta, null);
        wp_die(); // Always end with wp_die() to avoid extra output
    }
    add_action('convertopia_cron_generate_customer_feed_delta', 'convertopia_cron_generate_customer_feed_delta');
}