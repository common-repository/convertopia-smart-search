<?php
// Function to update schedule based on AJAX request
function convertopia_update_schedule($action_hook, $feed_type, $nonce) {

    if (!check_ajax_referer($nonce, 'nonce', false)) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'convertopia_feed_configs';

    $cache_key = 'convertopia_feed_schedule_' . $feed_type;
    $cache_group = 'convertopia_feed';

    // Retrieve cached feed config if it exists
    $cached_feed_config = wp_cache_get($cache_key, $cache_group);
    if ($cached_feed_config !== false) {
        // Return cached data
        wp_send_json_success($cached_feed_config);
        return;
    }

    if ($feed_type == 'customer_feed') {
        $customer_frequency = isset($_POST['customer_frequency']) ? sanitize_text_field(wp_unslash($_POST['customer_frequency'])) : 'daily';
        $customer_hour = isset($_POST['customer_hour']) ? sanitize_text_field(wp_unslash($_POST['customer_hour'])) : '00';
        $customer_minute = isset($_POST['customer_minute']) ? sanitize_text_field(wp_unslash($_POST['customer_minute'])) : '00';
        $customer_second = isset($_POST['customer_second']) ? sanitize_text_field(wp_unslash($_POST['customer_second'])) : '00';

        $customer_hour = str_pad($customer_hour, 2, '0', STR_PAD_LEFT);
        $customer_minute = str_pad($customer_minute, 2, '0', STR_PAD_LEFT);
        $customer_second = str_pad($customer_second, 2, '0', STR_PAD_LEFT);

        $time = "$customer_hour:$customer_minute:$customer_second";
        $frequency = $customer_frequency;
        
    } else if ($feed_type == 'order_feed') {
        $order_frequency = isset($_POST['order_frequency']) ? sanitize_text_field(wp_unslash($_POST['order_frequency'])) : 'daily';
        $order_hour = isset($_POST['order_hour']) ? sanitize_text_field(wp_unslash($_POST['order_hour'])) : '00';
        $order_minute = isset($_POST['order_minute']) ? sanitize_text_field(wp_unslash($_POST['order_minute'])) : '00';
        $order_second = isset($_POST['order_second']) ? sanitize_text_field(wp_unslash($_POST['order_second'])) : '00';

        $order_hour = str_pad($order_hour, 2, '0', STR_PAD_LEFT);
        $order_minute = str_pad($order_minute, 2, '0', STR_PAD_LEFT);
        $order_second = str_pad($order_second, 2, '0', STR_PAD_LEFT);

        $time = "$order_hour:$order_minute:$order_second";
        $frequency = $order_frequency;

    } else if ($feed_type == 'product_feed') {
        $product_frequency = isset($_POST['product_frequency']) ? sanitize_text_field(wp_unslash($_POST['product_frequency'])) : 'daily';
        $product_hour = isset($_POST['product_hour']) ? sanitize_text_field(wp_unslash($_POST['product_hour'])) : '00';
        $product_minute = isset($_POST['product_minute']) ? sanitize_text_field(wp_unslash($_POST['product_minute'])) : '00';
        $product_second = isset($_POST['product_second']) ? sanitize_text_field(wp_unslash($_POST['product_second'])) : '00';

        $product_hour = str_pad($product_hour, 2, '0', STR_PAD_LEFT);
        $product_minute = str_pad($product_minute, 2, '0', STR_PAD_LEFT);
        $product_second = str_pad($product_second, 2, '0', STR_PAD_LEFT);

        $time = "$product_hour:$product_minute:$product_second";
        $frequency = $product_frequency;
    }

    if (!empty($frequency) && !empty($time)) {
        // Save the frequency and time in options or database
        if(get_option('convertopia_feed_frquency') && get_option('convertopia_feed_time')){
            update_option('convertopia_feed_frquency', $frequency);
            update_option('convertopia_feed_time', $time);
        } else {
            add_option('convertopia_feed_frquency', $frequency);
            add_option('convertopia_feed_time', $time);
        }


        // Cache the new feed schedule
        wp_cache_set($cache_key, array('frequency' => $frequency, 'time' => $time), $cache_group, 3600);

        // Save the frequency and time in the database
        // @codingStandardsIgnoreStart
        $wpdb->replace(
            $table_name,
            array(
                'action_hook' => esc_sql($action_hook),
                'feed_type'   => esc_sql($feed_type),
                'frequency'   => esc_sql($frequency),
                'time'        => esc_sql($time)
            ),
            array('%s', '%s', '%s', '%s')
        );
        // @codingStandardsIgnoreEnd

        if ($wpdb->last_error) {
            // Escaping the output for security
            echo esc_html($wpdb->last_error);
        }

        // Schedule script execution based on new settings
        wp_clear_scheduled_hook($action_hook);
        convertopia_schedule_script($action_hook, $frequency, $time);
    }
}

// Function to schedule script execution based on saved frequency and time
function convertopia_schedule_script($action_hook, $frequency, $time) {
    $frequency = get_option('convertopia_feed_frquency', $frequency);
    $time = get_option('convertopia_feed_time', $time);

    // Get the timezone offset in seconds
    $timezone = get_option('timezone_string');
    if ($timezone) {
        $datetime = new DateTime('now', new DateTimeZone($timezone));
        $offset = $datetime->getOffset();
    } else {
        $offset = 0; // If no timezone is set, default to 0
    }

    // Adjust the scheduled time according to the timezone offset
    $adjusted_time = strtotime('today ' . $time) - $offset;

    // Schedule the event based on frequency and adjusted time
    if ($frequency === 'daily') {
        if (!wp_next_scheduled($action_hook)) {
            wp_schedule_event($adjusted_time, 'daily', $action_hook);
        }
    } elseif ($frequency === 'weekly') {
        if (!wp_next_scheduled($action_hook)) {
            wp_schedule_event($adjusted_time, 'weekly', $action_hook);
        }
    } elseif ($frequency === 'monthly') {
        if (!wp_next_scheduled($action_hook)) {
            wp_schedule_event($adjusted_time, 'monthly', $action_hook);
        }
    }
}