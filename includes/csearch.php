<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// Hook the search processing to 'template_redirect' action
add_action('template_redirect', 'convertopia_process_search');

function convertopia_process_search() {
    if (!empty($_GET['q'])) {

        $nonce = isset($_GET['nonce']) ? sanitize_text_field(wp_unslash($_GET['nonce'])) : '';

        // Validate the nonce
        if (!wp_verify_nonce($nonce, 'convertopia_search_ajax_nonce')) {
            // Invalid nonce, handle error
            wp_die('Invalid request, nonce verification failed.');
        }

        // Sanitize search term
        $searchTerm = sanitize_text_field(wp_unslash($_GET['q']));


        try {
            // Encode search term into JSON
            $data = wp_json_encode(array('searchTerm' => $searchTerm));

            // Getting URL and credentials for Convertopia Service
            $convertopia_settings = get_option('convertopia_settings');
            $auth = 'Basic ' . base64_encode($convertopia_settings['cp_client_key'] . ':' . $convertopia_settings['cp_secret_key'] . ':' . $convertopia_settings['cp_store_id']);
            $serviceURL = "{$convertopia_settings['service_URL']}/product-search/extended";

            // Setup request arguments
            $args = array(
                'headers'   => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => $auth,
                ),
                'body'      => $data,
                'timeout'   => 60, // Adjust timeout as needed
            );

            // Perform the request using wp_remote_post
            $response = wp_remote_post($serviceURL, $args);

            // Check if the request was successful
            if (is_wp_error($response)) {
                throw new Exception('Request to Convertopia service failed.');
            }

            // Get the response body
            $result = wp_remote_retrieve_body($response);

            // Process search results
            $cSearch = array();
            //todo we need to add cSearch logic with service results

            // Construct safe base URL
            $baseUrl = esc_url(site_url()) . '/?s=';

            // Append search term and other parameters to base URL
            $finalUrl = add_query_arg(
                array(
                    's' => $cSearch,
                    'post_type' => 'product',
                    'searchTerm' => $searchTerm
                ),
                $baseUrl
            );

            if ( ! function_exists( 'wp_redirect' ) ) {
                require_once ABSPATH . WPINC . '/pluggable.php';
            }
            
            // Redirect to final URL
            wp_redirect($finalUrl, 302);
            exit();
        } catch (\Throwable $th) {
            // Handle exceptions or errors
            $baseUrl = esc_url(site_url()) . '/';

            if ( ! function_exists( 'wp_redirect' ) ) {
                require_once ABSPATH . WPINC . '/pluggable.php';
            }

            wp_redirect($baseUrl, 302);
            exit();
        }
    }
}