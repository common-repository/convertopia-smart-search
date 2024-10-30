<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once plugin_dir_path(__FILE__) . '../jobs/cron-scheduler.php';

// Handle AJAX request to generate order feed
if ( ! function_exists( 'convertopia_ajax_generate_order_feed' ) ) {
    function convertopia_ajax_generate_order_feed($orderdelta, $nonce) {
        if (!class_exists('WooCommerce')) {
            echo wp_json_encode(array('message' => 'WooCommerce not activated.'));
            wp_die();
        }

        if (!empty($nonce)) {
            if (!check_ajax_referer($nonce, 'nonce', false)) {
                wp_send_json_error(array('message' => 'Invalid nonce'));
            }
        }

        $convertopia_settings = get_option('convertopia_settings');
        $delta = isset($_POST['delta']) ? sanitize_text_field(wp_unslash($_POST['delta'])) : "false";
        $last_run_time = get_option('convertopia_last_run_time_orders');

        if ($orderdelta || $delta == "true" && $last_run_time) {
            $last_run_time = gmdate("Y-m-d H:i:s", strtotime($last_run_time)); // Ensure last run time is in 24-hour format
            error_log('Last run time: ' . $last_run_time); // Debug log

            $args = array(
                'limit' => -1, // Get all orders
                'date_query' => array(
                    'relation' => 'OR',
                    array(
                        'after' => $last_run_time,
                        'inclusive' => true,
                        'column' => 'date_created_gmt',
                    ),
                    array(
                        'after' => $last_run_time,
                        'inclusive' => true,
                        'column' => 'date_modified_gmt',
                    ),
                ),
            );
            error_log('Date Query: ' . print_r($args['date_query'], true)); // Debug log
        } else {
            $args = array(
                'limit' => -1, // Get all orders
            );
        }

        $orders = wc_get_orders($args);

        if (!empty($orders)) {
            $ndjson_data = '';
            foreach ($orders as $order) {
                // Get customer information
                $customer = $order->get_user();
                $customer_data = array(
                    'customer_no' => $customer ? absint($customer->ID) : '',
                    'customer_name' => sanitize_text_field($order->get_billing_first_name()) . ' ' . esc_html($order->get_billing_last_name()),
                    'customer_email' => sanitize_email($order->get_billing_email()),
                    'billing_address' => array(
                        'first_name' => sanitize_text_field($order->get_billing_first_name()),
                        'last_name' => sanitize_text_field($order->get_billing_last_name()),
                        'address1' => sanitize_text_field($order->get_billing_address_1()),
                        'city' => sanitize_text_field($order->get_billing_city()),
                        'postal_code' => sanitize_text_field($order->get_billing_postcode()),
                        'state_code' => sanitize_text_field($order->get_billing_state()),
                        'country_code' => sanitize_text_field($order->get_billing_country()),
                        'phone' => sanitize_text_field($order->get_billing_phone())
                    )
                );

                // Get product line items
                $line_items = array();
                foreach ($order->get_items() as $item_id => $item) {
                    $product = $item->get_product();
                    $line_items[] = array(
                        'net_price' => floatval($product->get_price()),
                        'tax' => floatval($item->get_total_tax()),
                        'gross_price' => floatval($item->get_total() + $item->get_total_tax()),
                        'base_price' => floatval($item->get_total() + $item->get_total_tax()),
                        'line_item_text' => sanitize_text_field($product->get_name()),
                        'tax_basis' => floatval($product->get_price()),
                        'product_id' => absint($product->get_id()),
                        'quantity' => absint($item->get_quantity()),
                        'tax_rate' => floatval($item->get_total_tax() / $product->get_price()) // Calculate tax rate
                    );

                    $line_item_price_adjustment[] = array(
                        'net_price' => floatval($product->get_price()),
                        'tax' => floatval($item->get_total_tax()),
                        'gross_price' => floatval($item->get_total() + $item->get_total_tax()),
                        'base_price' => floatval($item->get_total() + $item->get_total_tax()),
                        'line_item_text' => sanitize_text_field($product->get_name()),
                        'promo_id'=> ''
                    );
                }

                // Get shipping line items
                $shipping_items = array();
                foreach ($order->get_shipping_methods() as $shipping_item_id => $shipping_item) {
                    $shipping_items[] = array(
                        'net_price' => floatval($shipping_item->get_total()),
                        'tax' => floatval($shipping_item->get_total_tax()), // Assuming shipping is not taxed
                        'gross_price' => floatval($shipping_item->get_total()),
                        'base_price' => floatval($shipping_item->get_total()),
                        'line_item_text' => sanitize_text_field($shipping_item->get_method_title())
                    );

                    $shipping_items_price_adjustments[] = array(
                        'net_price' => floatval($shipping_item->get_total()),
                        'tax' => floatval($shipping_item->get_total_tax()), // Assuming shipping is not taxed
                        'gross_price' => floatval($shipping_item->get_total()),
                        'base_price' => floatval($shipping_item->get_total()),
                        'line_item_text' => sanitize_text_field($shipping_item->get_method_title()),
                        'promo_id' => ''
                    );
                }

                // Get shipment information
                $shipment_data = array(
                    'shipping_status' => sanitize_text_field($order->get_status()),
                    'shipping_method' => sanitize_text_field($order->get_shipping_method()),
                    'tracking_number' => sanitize_text_field($order->get_meta('_tracking_number', true)),
                    'shipping_address' => array(
                        'first_name' => sanitize_text_field($order->get_shipping_first_name()),
                        'last_name' => sanitize_text_field($order->get_shipping_last_name()),
                        'address1' => sanitize_text_field($order->get_shipping_address_1()),
                        'city' => sanitize_text_field($order->get_shipping_city()),
                        'postal_code' => sanitize_text_field($order->get_shipping_postcode()),
                        'state_code' => sanitize_text_field($order->get_shipping_state()),
                        'country_code' => sanitize_text_field($order->get_shipping_country()),
                        'phone' => sanitize_text_field($order->get_billing_phone())
                    )
                );

                // Build the order data
                $order_data = array(
                    'order_no' => absint($order->get_order_number()),
                    'created_by' => sanitize_text_field($order), // Adjust as needed
                    'order_date' => sanitize_text_field($order->get_date_created()->format('Y-m-d\TH:i:sP')),
                    'currency' => sanitize_text_field($order->get_currency()),
                    'locale' => get_locale(),
                    'taxation' => convertopia_check_taxation_basis(),
                    'invoice' => absint($order->get_id()),
                    'customer' => $customer_data,
                    'status' => array(
                        'order_status' => sanitize_text_field($order->get_status()),
                        'shipping_status' => sanitize_text_field($order->get_status()),
                        'confirmation_status' => 'CONFIRMED', // Assuming all orders are confirmed
                        'payment_status' => sanitize_text_field($order->get_payment_method() ? 'PAID' : 'UNPAID') // Adjust as needed
                    ),
                    'product_line_items' => array(
                        'line_items' => $line_items,
                        'price_adjustment' => $line_item_price_adjustment // Assuming no price adjustments for line items
                    ),
                    'shipping_line_items' => array(
                        'line_items' => $shipping_items,
                        'price_adjustment' => $shipping_items_price_adjustments // Assuming no price adjustments for shipping
                    ),
                    'shipment' => $shipment_data,
                    'total' => array(
                        'sub_total' => floatval($order->get_subtotal()),
                        'shipping_total' => floatval($order->get_shipping_total()),
                        'tax_total' => floatval($order->get_total_tax()),
                        'order_total' => floatval($order->get_total())
                    ),
                );

                // Check for custom fields and add them if they exist
                $customs = array();
                $custom_fields = get_post_meta($order->get_id());
                foreach ($custom_fields as $key => $value) {
                    if (strpos($key, '_custom_') !== false) { // Assuming custom fields have a prefix '_custom_'
                        $customs[] = array(
                            'custom_attribute_name' => esc_html($key),
                            'custom_attribute_value' => esc_html($value[0])
                        );
                    }
                }
                if (!empty($customs)) {
                    $order_data['customs'] = $customs;
                }

                $ndjson_data.=  wp_json_encode($order_data). "\n";

            }

            // Convert the array to JSON

            // Check if JSON encoding was successful
            if ($json_data === false) {
                echo wp_json_encode(array('message' => 'Failed to generate valid JSON.'));
                wp_die();
            }

            // Define the file path and name
            $path = wp_upload_dir();
            $currentDate = gmdate("Ymds");
            $ctStoreId = $convertopia_settings['cp_store_id'] ? sanitize_text_field($convertopia_settings['cp_store_id']) : '';
            if ($delta == "true" || $orderdelta) {
                $fName = "delta_{$ctStoreId}_convertopia_order_feed{$currentDate}.json";
            } else {
                $fName = "{$ctStoreId}_convertopia_order_feed{$currentDate}.json";
            }
            $fileName = "{$path['path']}/{$fName}";
            $fileUrl = "{$path['url']}/{$fName}";

            // Write JSON data to file


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
                wp_die();
            }

            $localFilePath = $fileName;
            $remoteFilePath = "{$ftp_path}/{$fName}";

            if (ftp_put($connId, $remoteFilePath, $localFilePath, FTP_ASCII)) {
                echo wp_json_encode(array(
                    'message' => "Order feed exported successfully to server {$ftp_path} with file name {$fName}",
                    'file_url' => esc_url($fileUrl)
                ));
            } else {
                error_log('Error uploading order feed to FTP server.'); // Log error
                echo wp_json_encode(array('message' => 'Error uploading order feed to FTP server.'));
            }
            ftp_close($connId);
            
            update_option('convertopia_last_run_time_orders', gmdate('Y-m-d H:i:s')); // Update the last run time
        } else {
            echo wp_json_encode(array('message' => 'No orders found.'));
        }
    }
}

//function to get the source of orders
if ( ! function_exists( 'convertopia_get_order_source' ) ) {
    function convertopia_get_order_source($order) {
        $order_source = "";
        $user_id = $order->get_user_id();
        if ($user_id) {
            $user = get_user_by('id', $user_id);
            if ($user) {
                $order_source = implode(', ', $user->roles);
            } else {
                $order_source = "guest";
            }
        } else {
            $order_source = "guest";
        }

        return $order_source;
    }
}

// function to get the tax type
if ( ! function_exists( 'convertopia_check_taxation_basis' ) ) {
    function convertopia_check_taxation_basis() {
        // Get the setting that determines if prices include tax
        $prices_include_tax = get_option('woocommerce_prices_include_tax');

        // Check if the setting is configured
        if ($prices_include_tax === 'yes') {
            return 'gross';
        } elseif ($prices_include_tax === 'no') {
            return 'net';
        } else {
            return ''; // Empty string if not configured
        }
    }
}

// function to run the quick order feeds 
if ( ! function_exists( 'convertopia_generate_order_feed' ) ) {
    function convertopia_generate_order_feed() {
        $orderdelta = false;
        convertopia_ajax_generate_order_feed($orderdelta, 'convertopia_generate_order_feed_action');
        wp_die(); // Always end with wp_die() to avoid extra output
    }
    add_action('wp_ajax_convertopia_generate_order_feed', 'convertopia_generate_order_feed');
}

// function to run the cron job for order feed
if ( ! function_exists( 'convertopia_cron_generate_order_feed' ) ) {
function convertopia_cron_generate_order_feed() {
    // Log for debugging
    error_log('convertopia_cron_generate_order_feed triggered');
    $orderdelta = false;
    convertopia_ajax_generate_order_feed($orderdelta, null);
    wp_die(); // Always end with wp_die() to avoid extra output
}
add_action('convertopia_cron_generate_order_feed', 'convertopia_cron_generate_order_feed');
}

// function to run the cron job for order feed delta 
if ( ! function_exists( 'convertopia_cron_generate_order_feed_delta' ) ) {
    function convertopia_cron_generate_order_feed_delta() {
        // Log for debugging
        error_log('convertopia_cron_generate_order_feed_delta triggered');
        $orderdelta = true;
        convertopia_ajax_generate_order_feed($orderdelta, null);
        wp_die(); // Always end with wp_die() to avoid extra output
    }
    add_action('convertopia_cron_generate_order_feed_delta', 'convertopia_cron_generate_order_feed_delta');
}