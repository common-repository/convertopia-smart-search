<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once plugin_dir_path(__FILE__) . '../jobs/cron-scheduler.php';

if ( ! function_exists( 'convertopia_ajax_generate_product_feed' ) ) {
    function convertopia_ajax_generate_product_feed($productdelta, $nonce) {
        if (!class_exists('WooCommerce')) {
            error_log('WooCommerce not activated.'); // Log error
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

        // Get the last run time
        $last_run_time = get_option('convertopia_last_run_time_products');

        // Check if delta export is enabled
        if ($productdelta || $delta == "true" && $last_run_time) {
            // If delta flag is true, query products since the last run time
            $args = array(
                'date_query' => array(
                    'relation' => 'OR',
                    array(
                        'column' => 'post_date',
                        'after'     => $last_run_time,
                        'inclusive' => true,
                    ),
                    array(
                        'column' => 'post_modified',
                        'after'     => $last_run_time,
                        'inclusive' => true,
                    ),
                ),
                'limit' => -1, // Get all products
                'status' => 'publish', // Ensure products are published
            );
        } else {
            $args = array(
                'limit' => -1, // Get all products
                'status' => 'publish', // Ensure products are published
            );
        }

        $products = wc_get_products($args);

        if (!empty($products)) {
            // Define CSV file path and name
            $upload_dir = wp_upload_dir();
            $current_date = gmdate("Ymds");
            $store_id =  $convertopia_settings['cp_store_id'] ? esc_attr($convertopia_settings['cp_store_id']) : '';
            
            if ($delta == "true" || $productdelta) {
                $file_name = "delta_{$store_id}_convertopia_feed{$current_date}.csv";
            } else {
                $file_name = "{$store_id}_convertopia_feed{$current_date}.csv";
            }

            $file_path = trailingslashit($upload_dir['path']) . $file_name;

            // Open file for writing
            global $wp_filesystem;

            // Initialize the WP_Filesystem
            if ( ! function_exists( 'request_filesystem_credentials' ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
            }
            
            if ( ! WP_Filesystem() ) {
                request_filesystem_credentials( site_url() );
            }
            
            // Check if WP_Filesystem is initialized correctly
            if ( ! $wp_filesystem || is_wp_error( $wp_filesystem ) ) {
                return new WP_Error( 'filesystem_error', esc_html(__( 'Unable to initialize WP_Filesystem', 'convertopia-smart-search' )) );
            }
            
            // Prepare CSV data
            $csv_data = [];

            // Prepare header row
            $header = 'category_id|categories|category_url|image|sku|url|name|short_description|description|price|id';
            $csv_data[] = $header;
            
            // Prepare product data
            foreach ($products as $product) {
                // Prepare product data
                $product_data = array(
                    convertopia_get_primary_category_id($product->get_id()),
                    convertopia_get_product_categories($product->get_id()),
                    esc_url(get_category_link(convertopia_get_primary_category_id($product->get_id()))),
                    esc_url(wp_get_attachment_url($product->get_image_id())),
                    $product->get_sku(),
                    esc_url(get_permalink($product->get_id())),
                    $product->get_name(),
                    esc_html($product->get_short_description()),
                    esc_html($product->get_description()),
                    $product->get_price(),
                    $product->get_id()
                );
            
                // Concatenate product data into a single string with | separator
                $csv_row = implode('|', $product_data);
                $csv_data[] = $csv_row;
            }
            
            // Convert CSV data to string with no additional delimiter (just single column)
            $csv_string = implode("\n", $csv_data) . "\n";
            
            // Write the CSV string to file
            if (!$wp_filesystem->put_contents($file_path, $csv_string, FS_CHMOD_FILE)) {
                return new WP_Error('write_error', esc_html(__('Unable to write CSV file', 'convertopia-smart-search')));
            }

            // Upload file to FTP
            $ftp_host = isset($convertopia_settings['ftp_host']) && !empty($convertopia_settings['ftp_host']) ? sanitize_text_field($convertopia_settings['ftp_host']) : null;
            $ftp_user = isset($convertopia_settings['ftp_user']) && !empty($convertopia_settings['ftp_user']) ? sanitize_text_field($convertopia_settings['ftp_user']) : null;
            $ftp_password = isset($convertopia_settings['ftp_password']) && !empty($convertopia_settings['ftp_password']) ? sanitize_text_field($convertopia_settings['ftp_password']) : null;
            $ftp_path = isset($convertopia_settings['ftp_path']) && !empty($convertopia_settings['ftp_path']) ? sanitize_text_field($convertopia_settings['ftp_path']) : null;

            // Check if any of the required credentials are missing
            if ( !$ftp_host || !$ftp_user || !$ftp_password || !$ftp_path ) {
                error_log('Missing FTP credentials.'); // Log error
                echo wp_json_encode(array('message' => 'FTP credentials are missing.'));
                return;
            }

            $conn_id = ftp_connect($ftp_host);
            $ftp_login = ftp_login($conn_id, $ftp_user, $ftp_password);
            ftp_pasv($conn_id, true);

            if (!$conn_id || !$ftp_login) {
                error_log('FTP connection failed.'); // Log error
                echo wp_json_encode(array('message' => 'FTP connection failed.'));
                wp_die();
            }

            $remote_file_path = "{$ftp_path}/{$file_name}";

            if (ftp_put($conn_id, $remote_file_path, $file_path, FTP_ASCII)) {
                echo wp_json_encode(array(
                    'message' => "Products exported successfully into server {$ftp_path} with file name {$file_name}",
                ));
            } else {
                error_log('Error uploading product feed to FTP server.'); // Log error
                echo wp_json_encode(array('message' => 'Error uploading product feed to FTP server.'));
            }
            ftp_close($conn_id);

            // Update the last run time
            update_option('convertopia_last_run_time_products', current_time('mysql'));

        } else {
            error_log('No products found.'); // Log error
            echo wp_json_encode(array('message' => 'No products found.'));
        }
    }
}

//function to get the product primary category
if ( ! function_exists( 'convertopia_get_primary_category_id' ) ) {
    function convertopia_get_primary_category_id($product_id) {
        $terms = wp_get_post_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms) && isset($terms[0])) {
            // Assuming the first category is the primary one
            return $terms[0]->term_id;
        }
        return '';
    }
}

//function to get the product categories
if ( ! function_exists( 'convertopia_get_product_categories' ) ) {
    function convertopia_get_product_categories($product_id) {
        $terms = wp_get_post_terms($product_id, 'product_cat');
        if ($terms && !is_wp_error($terms)) {
            $categories = array();
            foreach ($terms as $term) {
                $categories[] = $term->name;
            }
            return implode(' > ', $categories);
        }
        return '';
    }
}

// function to run the quick products feed
if ( ! function_exists( 'convertopia_generate_product_feed' ) ) {
    function convertopia_generate_product_feed() {
        $productdelta = false;
        convertopia_ajax_generate_product_feed($productdelta, 'convertopia_generate_product_feed_action');
        // Ensure the script ends properly
        wp_die(); // Always end with wp_die() to avoid extra output
    }

    add_action('wp_ajax_convertopia_generate_product_feed', 'convertopia_generate_product_feed');
}

// function to run the cron job for products feed
if ( ! function_exists( 'convertopia_cron_generate_product_feed' ) ) {
    function convertopia_cron_generate_product_feed() {
        // Log for debugging
        error_log('convertopia_cron_generate_product_feed triggered');
        $productdelta = false;
        convertopia_ajax_generate_product_feed($productdelta, null);
        // Ensure the script ends properly
        wp_die(); // Always end with wp_die() to avoid extra output
    }

    add_action('convertopia_cron_generate_product_feed', 'convertopia_cron_generate_product_feed');
}

// function to run the cron job for products feed delta
if ( ! function_exists( 'convertopia_cron_generate_product_feed_delta' ) ) {
    function convertopia_cron_generate_product_feed_delta() {
        // Log for debugging
        error_log('convertopia_cron_generate_product_feed_delta triggered');
        $productdelta = true;
        convertopia_ajax_generate_product_feed($productdelta, null);
        // Ensure the script ends properly
        wp_die(); // Always end with wp_die() to avoid extra output
    }

    add_action('convertopia_cron_generate_product_feed_delta', 'convertopia_cron_generate_product_feed_delta');
}