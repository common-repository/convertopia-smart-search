<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//function to start the session
if ( ! function_exists( 'convertopia_start_session' ) ) {
    function convertopia_start_session() {
        if (!session_id()) {
            session_start();
        }
    }
    add_action('init', 'convertopia_start_session', 1);
}


//function to load convertopia textdomains
if ( ! function_exists( 'convertopia_load_textdomain' ) ) {
    function convertopia_load_textdomain() {
        load_plugin_textdomain( 'convertopia-smart-search', false, plugin_dir_path(__FILE__) . '../languages' );
    }
    add_action( 'plugins_loaded', 'convertopia_load_textdomain' );
}

//function to enqueue admin scripts
if ( ! function_exists( 'convertopia_frontend_enqueue_scripts' ) ) {
    function convertopia_frontend_enqueue_scripts() {
        wp_enqueue_style('bootstrap', plugin_dir_url(__FILE__) . '../assets/css/bootstrap/css/bootstrap.min.css', array(), '4.6.2');
        wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . '../assets/js/bootstrap/js/bootstrap.min.js', array('jquery'), '4.6.2', true);
    }

    add_action('admin_enqueue_scripts', 'convertopia_frontend_enqueue_scripts');
}

//function to Enqueue styles and scripts
if ( ! function_exists( 'convertopia_enqueue_scripts' ) ) {
    function convertopia_enqueue_scripts() {

        // Enqueue styles
        wp_enqueue_style('font-awesome', plugin_dir_url(__FILE__) . '../assets/css/font-awesome-4.7.0/css/font-awesome.min.css', array(), '4.7.0');
        wp_enqueue_style('convertopia-style', plugin_dir_url(__FILE__) . '../assets/css/style.css', array(), '1.0');

        // Enqueue scripts
        wp_enqueue_script('jquery');

        // Define your plugin version
        $plugin_version = '1.0.0'; // Replace with your plugin version
        wp_enqueue_script('convertopia-spinner', plugin_dir_url(__FILE__) . '../assets/js/spinner.js', array('jquery'), $plugin_version, array('strategy'  => 'defer'));
        wp_enqueue_script('convertopia-script', plugin_dir_url(__FILE__) . '../assets/js/convertopia.js', array('jquery'), $plugin_version, array('strategy'  => 'defer'));
        wp_enqueue_script('convertopiaSettingScript', plugin_dir_url(__FILE__) . '../assets/js/convertopia-setting.js', array(), $plugin_version, array('strategy'  => 'defer'));
        wp_enqueue_script('convertopiaAutocomplete', plugin_dir_url(__FILE__) . '../assets/js/search-autocomplete.js', array(), $plugin_version, array('strategy'  => 'defer'));

        // Localize script
        wp_localize_script('convertopia-script', 'convertopia_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));

        // Pass settings data to scripts
        $convertopia_settings = get_option('convertopia_settings');
        $scriptData = convertopia_prepare_script_data($convertopia_settings);

        wp_localize_script('convertopiaSettingScript', 'convertopia_settings', $scriptData);
    }

    // Hook the function to both frontend and admin area
    add_action('wp_enqueue_scripts', 'convertopia_enqueue_scripts');
    add_action('admin_enqueue_scripts', 'convertopia_enqueue_scripts');
}

//function to Enqueue tracking scripts
if ( ! function_exists( 'convertopia_tracking_enqueue_scripts' ) ) {
    function convertopia_tracking_enqueue_scripts() {

        // Define your plugin version
        $plugin_version = '1.0.0'; // Replace with your plugin version
        wp_enqueue_script('convertopiaTrackingScript', plugin_dir_url(__FILE__) . '../assets/js/convertopia-tracking.js', array(), $plugin_version, array('strategy'  => 'defer'));

        wp_localize_script('convertopiaTrackingScript', 'convertopia_custom_ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('convertopia_custom_ajax_nonce')
        ));

        $convertopia_settings = get_option('convertopia_settings');
        $scriptData = convertopia_prepare_script_data($convertopia_settings);

        wp_localize_script('convertopiaTrackingScript', 'convertopia_settings', $scriptData);
    }

    add_action('wp_enqueue_scripts', 'convertopia_tracking_enqueue_scripts');
}

// Get product attributes
if ( ! function_exists( 'convertopia_get_product_attributes' ) ) {
    function convertopia_get_product_attributes($product) {
        $attributes = array();
        foreach ($product->get_attributes() as $attribute_name => $attribute) {
            $attributes[$attribute_name] = wc_get_product_terms($product->get_id(), $attribute['name'], array('fields' => 'names'));
        }
        return $attributes;
    }
}

// Prepare script data
if ( ! function_exists( 'convertopia_prepare_script_data' ) ) {
    function convertopia_prepare_script_data( $convertopia_settings ) {
        // Ensure $convertopia_settings is an array
        if ( ! is_array( $convertopia_settings ) ) {
            // Handle the error, either by returning default values or logging the issue
            error_log( 'convertopia_prepare_script_data: $convertopia_settings is not an array.' );
            return array(); // or return default values if needed
        }

        // Safely retrieve the store ID and CDN URL, or use default values if not set
        $site_id = isset($convertopia_settings['cp_store_id']) ? absint($convertopia_settings['cp_store_id']) : '';
        $cdn_url = isset($convertopia_settings['cdn_URL']) ? esc_url($convertopia_settings['cdn_URL']) : '';

        $session_id = sanitize_text_field( session_id() );
        $user_id = is_user_logged_in() ? get_current_user_id() : 'guest';
        $page_type = convertopia_get_page_type();
        $line_items = convertopia_get_cart_items();
        $istrackUserData = convertopia_is_user_consent_given();
        $search_nonce = wp_create_nonce( 'convertopia_search_ajax_nonce' );

        return array(
            'cdnURL'          => $cdn_url,
            'istrackUserData' => $istrackUserData,
            'StoreID'         => $site_id,
            'session_id'      => $session_id,
            'page_type'       => $page_type,
            'user_id'         => $user_id,
            'lineItem'        => $line_items,
            'search_nonce'    => $search_nonce,
        );
    }
}

// Function to Get current page type
if ( ! function_exists( 'convertopia_get_page_type' ) ) {
    function convertopia_get_page_type() {
        if (is_front_page()) return 'HOME';
        
        if (function_exists('is_product_category') && is_product_category()) return 'CLP';
        if (function_exists('is_shop') && is_shop()) return 'PLP';
        if (function_exists('is_product') && is_product()) return 'PDP';
        if (function_exists('is_cart') && is_cart()) return 'CART';
        if (function_exists('is_checkout') && is_checkout()) {
            if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) return 'CHECKOUT_PAYMENT';
            if (function_exists('is_order_received_page') && is_order_received_page()) return 'ORDER_CONFIRMATION';
            return 'CHECKOUT_REVIEW';
        }
        if (function_exists('is_account_page') && is_account_page()) return is_user_logged_in() ? 'ACCOUNT_DASHBOARD' : 'ACCOUNT_LOGIN';

        // Retrieve custom slugs from settings
        $custom_registration_slug = get_option('convertopia_custom_registration_slug');
        if ($custom_registration_slug) return is_user_logged_in() ? 'ACCOUNT_DASHBOARD' : 'ACCOUNT_SIGNUP';
        
        return 'OTHER';
    }
}

// Function Get cart items
if ( ! function_exists( 'convertopia_get_cart_items' ) ) {
    function convertopia_get_cart_items() {
        if (!class_exists('WooCommerce') || !function_exists('WC') || !WC()->cart) return array();

        $cart_items = WC()->cart->get_cart();
        $line_items = array();

        foreach ($cart_items as $cart_item) {
            $product = $cart_item['data'];
            $line_items[] = array(
                'id' => $product->get_id(),
                'name' => esc_html($product->get_name()),
                'price' => wc_format_decimal($product->get_price()),
                'category' => wp_strip_all_tags(wc_get_product_category_list($product->get_id())),
                'url' => esc_url(get_permalink($product->get_id(), 'full')),
            );
        }
        return $line_items;
    }
}

// Function to track add to cart
if ( ! function_exists( 'convertopia_track_add_to_cart' ) ) {
    function convertopia_track_add_to_cart() {

        if (!check_ajax_referer('convertopia_custom_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        // Sanitize the input
        $product_id = isset($_POST['product_id']) ? sanitize_text_field(wp_unslash($_POST['product_id'])) : '';

        if ($product_id) {
            $product_id = intval($product_id);
            $product = wc_get_product($product_id);
            $istrackUserData = convertopia_is_user_consent_given();

            if ($product) {
                $current_user = wp_get_current_user();
                $user_id = ($current_user->exists()) ? $current_user->ID : 'guest';
                $page_type = convertopia_get_page_type(); // Ensure this function exists and returns a value

                $line_items[] = array(
                    'id' => $product->get_id(),
                    'name' => esc_html($product->get_name()),
                    'price' => wc_format_decimal($product->get_price()),
                    'category' => wp_strip_all_tags(wc_get_product_category_list($product->get_id())),
                    'url' => esc_url(get_permalink($product->get_id(), 'full')),
                );

                $event_data = array(
                    'addToCart' => true,
                    'event' => 'add_to_cart',
                    'istrackUserData' => $istrackUserData,
                    'page_type' => $page_type,
                    'line_items' => $line_items,
                    'user_id' => $user_id,
                );

                // Ensure no additional output
                wp_send_json($event_data);
            } else {
                wp_send_json_error(array('message' => 'Product not found'));
            }
        } else {
            wp_send_json_error(array('message' => 'Invalid product ID'));
        }
    }
    add_action('wp_ajax_convertopia_track_add_to_cart', 'convertopia_track_add_to_cart');
    add_action('wp_ajax_nopriv_convertopia_track_add_to_cart', 'convertopia_track_add_to_cart');
}

// Add menu item to admin dashboard
if ( ! function_exists( 'convertopia_menu' ) ) {
    function convertopia_menu() {
        add_menu_page('Convertopia Settings Page', 'Convertopia', 'manage_options', 'convertopia_plugin_menu', 'convertopia_init', plugin_dir_url(__FILE__) . '../assets/images/favicon.png');
        add_submenu_page( 'convertopia_plugin_menu', 'Convertopia Settings Page', 'General Settings', 'manage_options', 'convertopia_plugin_menu','convertopia_init' );
        add_submenu_page( 'convertopia_plugin_menu', 'Feed', 'Feed', 'manage_options', 'convertopia_plugin_sub_menu_feed','convertopia_feed');
        add_options_page(
            'convertopia_plugin_menu',
            'Convertopia slug Setting',
            'manage_options',
            'convertopia-page-slug-settings',
            'covertopia_settings_slug_page'
        );

    }
    add_action('admin_menu', 'convertopia_menu');
}

// Main page content
if ( ! function_exists( 'convertopia_feed' ) ) {
    function convertopia_feed() {
        require_once plugin_dir_path(__FILE__) . '../templates/convertopia-feeds.php'; 
    }
}

// Convertopia settings init 
if ( ! function_exists( 'convertopia_init' ) ) {
    function convertopia_init() {
        require_once plugin_dir_path(__FILE__) . '../templates/convertopia-settings.php';
    }
}

// Convertopia recommendation Content
if ( ! function_exists( 'convertopia_recommendation' ) ) {
    function convertopia_recommendation() {
        require_once plugin_dir_path(__FILE__) . '../templates/convertopia-recommendations.php'; 
    }
}


// Add order confirmation event and attributes 
if ( ! function_exists( 'convertopia_order_confirmation_event' ) ) {
    function convertopia_order_confirmation_event($order_id) {
        // Get the order object
        $order = wc_get_order($order_id);
        $convertopia_settings = get_option('convertopia_settings');
        $site_id = isset($convertopia_settings['cp_store_id']) ? absint($convertopia_settings['cp_store_id']) : '';
        $session_id = sanitize_text_field(session_id());

        $option_key = 'convertopia_custom_consent' . $session_id;
        $istrackUserData = get_option($option_key);


        // Check if the order object is valid
        if (!$order) {
            return;
        }

        // Get order details
        $line_items = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $product_id = absint($product->get_id());
            $product_name = sanitize_text_field($product->get_name());
            $product_price = floatval($product->get_price());
            $product_category = wp_strip_all_tags(wc_get_product_category_list($product_id));
            $product_url = esc_url(get_permalink($product_id));

            $line_items[] = array(
                'id' => $product_id,
                'name' => $product_name,
                'price' => $product_price,
                'category' => wp_strip_all_tags($product_category),
                'url' => $product_url,
            );
        }

        // Get user details
        $user_id = $order->get_user_id() ? absint($order->get_user_id()) : 'guest';
        $user_email = sanitize_email($order->get_billing_email());
        $user_email_hashed = base64_encode($user_email);
        $user_billing_address = array(
            'first_name' => sanitize_text_field($order->get_billing_first_name()),
            'last_name' => sanitize_text_field($order->get_billing_last_name()),
            'address1' => sanitize_text_field($order->get_billing_address_1()),
            'address2' => sanitize_text_field($order->get_billing_address_2()),
            'city' => sanitize_text_field($order->get_billing_city()),
            'state' => sanitize_text_field($order->get_billing_state()),
            'zip' => sanitize_text_field($order->get_billing_postcode()),
            'country' => sanitize_text_field($order->get_billing_country())
        );
        $user_shipping_address = array(
            'first_name' => sanitize_text_field($order->get_shipping_first_name()),
            'last_name' => sanitize_text_field($order->get_shipping_last_name()),
            'address1' => sanitize_text_field($order->get_shipping_address_1()),
            'address2' => sanitize_text_field($order->get_shipping_address_2()),
            'city' => sanitize_text_field($order->get_shipping_city()),
            'state' => sanitize_text_field($order->get_shipping_state()),
            'zip' => sanitize_text_field($order->get_shipping_postcode()),
            'country' => sanitize_text_field($order->get_shipping_country()),
        );

        // Get order total and currency
        $order_total = floatval($order->get_total());
        $currency_code = sanitize_text_field($order->get_currency());
        $page_type = convertopia_get_page_type();

        // Prepare the event data
        $event_data = array(
            'event' => 'order_confirmation',
            'istrackUserData' => $istrackUserData,
            'page_type' => sanitize_text_field($page_type),
            'line_items' => $line_items,
            'user_email_hashed' => $user_email_hashed,
            'user_billing_address' => $user_billing_address,
            'user_shipping_address' => $user_shipping_address,
            'order_total' => $order_total,
            'currency_code' => $currency_code,
            'user_id' => $user_id,
            'StoreID' => $site_id,
            'session_id' => sanitize_text_field($session_id)
        );

        // Localize the event data for JavaScript
        wp_localize_script('convertopiaTrackingScript', 'convertopia_settings', $event_data);
    }
    add_action('woocommerce_thankyou', 'convertopia_order_confirmation_event', 10, 1);
}

// Add Shipping Address 
if ( ! function_exists( 'convertopia_custom_capture_checkout_shipping_info' ) ) {
    function convertopia_custom_capture_checkout_shipping_info($checkout) {

        // Check if WooCommerce is active
        if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'WC' ) || ! WC()->cart ) {
            return;
        }

        // Localize script with required data
        $convertopia_settings = get_option('convertopia_settings');
        $current_user = wp_get_current_user();
        $user_id = ($current_user->exists()) ? absint($current_user->ID) : 'guest';
        $user_email = ($current_user->exists()) ? sanitize_email($current_user->user_email) : '';
        $user_email_hashed = base64_encode($user_email);
        $site_id = isset($convertopia_settings['cp_store_id']) ? absint($convertopia_settings['cp_store_id']) : '';
        $session_id =sanitize_text_field(session_id());

        $option_key = 'convertopia_custom_consent' . $session_id;
        $istrackUserData = convertopia_is_user_consent_given();

        $shipping_address = array(
            'first_name' => sanitize_text_field($checkout->get_value('shipping_first_name')),
            'last_name' => sanitize_text_field($checkout->get_value('shipping_last_name')),
            'address1' => sanitize_text_field($checkout->get_value('shipping_address_1')),
            'address2' => sanitize_text_field($checkout->get_value('shipping_address_2')),
            'city' => sanitize_text_field($checkout->get_value('shipping_city')),
            'state' => sanitize_text_field($checkout->get_value('shipping_state')),
            'postcode' => sanitize_text_field($checkout->get_value('shipping_postcode')),
            'country' => sanitize_text_field($checkout->get_value('shipping_country'))
        );

        // Get line items from the cart
        $line_items = array();
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $line_items[] = array(
                'id' => absint($product->get_id()),
                'name' => sanitize_text_field($product->get_name()),
                'price' => floatval($product->get_price()),
                'category' => wp_kses_post(wc_get_product_category_list($product->get_id())),
                'url' => esc_url(get_permalink($product->get_id()))
            );
        }

        $page_type = convertopia_get_page_type();

        // Prepare the event data
        $event_data = array(
            'event' => 'checkout_shipping',
            'istrackUserData' => $istrackUserData,
            'page_type' => sanitize_text_field($page_type),
            'line_items' => $line_items,
            'user_email_hashed' => sanitize_text_field($user_email_hashed),
            'user_shipping_address' => $shipping_address,
            'user_id' => $user_id,
            'StoreID' => $site_id,
            'session_id' => sanitize_text_field($session_id)
        );

        // Localize the event data for JavaScript
        wp_localize_script('convertopiaTrackingScript', 'convertopia_settings', $event_data);

    }
    add_action('woocommerce_after_checkout_billing_form', 'convertopia_custom_capture_checkout_shipping_info');
}

// Function to Track Checkout data
if ( ! function_exists( 'covertopia_custom_function_before_checkout_form' ) ) {
    function covertopia_custom_function_before_checkout_form() {
        $convertopia_settings = get_option('convertopia_settings');
        $current_user = wp_get_current_user();
        $user_id = ($current_user->exists()) ? absint($current_user->ID) : 'guest';
        $page_type = convertopia_get_page_type();
        $line_items = convertopia_get_cart_items();
        $site_id = isset($convertopia_settings['cp_store_id']) ? absint($convertopia_settings['cp_store_id']) : '';
        $session_id = sanitize_text_field(session_id());
        $istrackUserData = convertopia_is_user_consent_given();

        $event_data = array(
            'event' => 'checkout_start',
            'istrackUserData' => $istrackUserData,
            'page_type' => 'CHECKOUT_REVIEW',
            'line_items' => $line_items,
            'user_id' => $user_id,
            'StoreID' => $site_id,
            'session_id' => $session_id,
        );

        // Localize the event data for JavaScript
        wp_localize_script('convertopiaTrackingScript', 'convertopia_settings', $event_data);
    }
    add_action('woocommerce_before_checkout_form', 'covertopia_custom_function_before_checkout_form', 10);
}

// function to register settings
if ( ! function_exists( 'convertopia_plugin_register_settings' ) ) {
    function convertopia_plugin_register_settings() {
        register_setting('convertopia_plugin_slug_settings_group', 'convertopia_custom_registration_slug');
    }
    add_action('admin_init', 'convertopia_plugin_register_settings');
}


// function to render page slug setting page
if ( ! function_exists( 'covertopia_settings_slug_page' ) ) {
    function covertopia_settings_slug_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html(__('My Plugin Settings', 'convertopia-smart-search')) ?></h1>
            <form method="post" action="options.php">
                <?php
                    settings_fields('convertopia_plugin_slug_settings_group');
                    do_settings_sections('convertopia_plugin_slug_settings_group');

                    // Add a nonce field for security
                    wp_nonce_field('convertopia_custom_slug_action', 'convertopia_custom_slug_nonce');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php esc_html(__('Custom Registration Page Slug', 'convertopia-smart-search')) ?></th>
                        <td><input type="text" name="convertopia_custom_registration_slug" value="<?php echo esc_attr(get_option('convertopia_custom_registration_slug')); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Add custom consent checkbox to checkout
if ( ! function_exists( 'convertopia_add_consent_checkout_field' ) ) {
    add_action('woocommerce_after_order_notes', 'convertopia_add_consent_checkout_field');

    function convertopia_add_consent_checkout_field($checkout) {
        echo '<div id="custom_consent_checkout_field"><h3>' . esc_html(__('Additional Consent', 'convertopia-smart-search')) . '</h3>';
        woocommerce_form_field('convertopia_custom_consent', array(
            'type'          => 'checkbox',
            'class'         => array('form-row-wide'),
            'label'         => esc_html(__('I agree to the convertopia consent terms', 'convertopia-smart-search')),
            'required'      => false,
            ), $checkout->get_value('convertopia_custom_consent'));

            // Add a nonce field for security
            wp_nonce_field('convertopia_add_consent_checkout_field_action', 'convertopia_add_consent_checkout_field_nonce');

        echo '</div>';
    }
}

// Save custom consent checkbox value
if ( ! function_exists( 'convertopia_save_consent_checkout_field' ) ) {
    add_action('woocommerce_checkout_update_order_meta', 'convertopia_save_consent_checkout_field');

    function convertopia_save_consent_checkout_field($order_id) {
        // Verify the nonce
        if (isset($_POST['convertopia_add_consent_checkout_field_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['convertopia_add_consent_checkout_field_nonce'])), 'convertopia_add_consent_checkout_field_action')) {
            
            // Check if consent checkbox was checked
            if (!empty($_POST['convertopia_custom_consent'])) {
                $session_id = sanitize_text_field(session_id());
                
                if ($session_id) {
                    // Save to options table with session ID as part of the key
                    $option_key = 'convertopia_custom_consent_' . $session_id;
                    update_option($option_key, sanitize_text_field(wp_unslash($_POST['convertopia_custom_consent'])));
                }
            }
        } else {
            // Invalid nonce, handle the error accordingly
            wp_die('Nonce verification failed. Please try again.');
        }
    }
}

if ( ! function_exists( 'convertopia_save_user_consent' ) ) {
    function convertopia_save_user_consent() {

        if (!check_ajax_referer('convertopia_custom_ajax_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }


        if (isset($_POST['consent'])) {
            $session_id = sanitize_text_field(session_id());
            $consent_given = sanitize_text_field(wp_unslash($_POST['consent']));

            if (is_user_logged_in()) {
                $option_key = 'convertopia_site_custom_consent' . $session_id;
                update_option($option_key, $consent_given);
            } else {
                setcookie('convertopia_site_custom_consent', $consent_given, time() + (86400 * 30), "/"); // 30 days
            }

            wp_send_json_success();
        } else {
            wp_send_json_error('Consent not provided');
        }
    }
    add_action('wp_ajax_convertopia_save_user_consent', 'convertopia_save_user_consent');
    add_action('wp_ajax_nopriv_convertopia_save_user_consent', 'convertopia_save_user_consent');
}

if ( ! function_exists( 'convertopia_is_user_consent_given' ) ) {
    function convertopia_is_user_consent_given() {
        if (is_user_logged_in()) {
            $session_id = sanitize_text_field(session_id());
            $option_key = 'convertopia_site_custom_consent' . $session_id;
            return get_option($option_key);
        } else {
            return isset($_COOKIE['convertopia_site_custom_consent']) && filter_var(sanitize_text_field(wp_unslash($_COOKIE['convertopia_site_custom_consent'])), FILTER_VALIDATE_BOOLEAN);
        }
    }
}