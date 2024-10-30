// custom-script.js

// Ensure convertopia is defined globally
window.convertopia = window.convertopia || [];

(function($) {
    $(document).ready(function() {
        if (typeof convertopia_settings !== 'undefined') {



            if (convertopia_settings.istrackUserData) {
                // Push merchant_identify event
                window.convertopia.push({
                    'event': 'merchant_identify',
                    'public_site_id': convertopia_settings.StoreID,
                    'session_id': convertopia_settings.session_id
                });

                // Push page_view event
                window.convertopia.push({
                    'event': 'page_view',
                    'page_type': convertopia_settings.page_type,
                    'user_id': convertopia_settings.user_id
                });
            }



            if (convertopia_settings.page_type == 'CART') {
                if (convertopia_settings.istrackUserData) {
                    window.convertopia.push({
                        'event': 'view_cart',
                        'line_items': convertopia_settings.lineItem, // array of products in cart with object {id:'', name:'', price:'', catgory:'', url:''}
                        'user_id': convertopia_settings.user_id
                    })
                }
            }

            if (convertopia_settings.page_type == 'CHECKOUT_BEGIN' || convertopia_settings.page_type == 'CHECKOUT_REVIEW') {
                if (convertopia_settings.istrackUserData) {
                    window.convertopia.push({
                        'event': 'checkout_start',
                        'line_items': convertopia_settings.line_items,
                        'user_id': convertopia_settings.user_id
                    });
                }
            }

            if (convertopia_settings.page_type == 'ORDER_CONFIRMATION' || convertopia_settings.order_total) {
                if (convertopia_settings.istrackUserData) {
                    window.convertopia.push({
                        'event': 'checkout_shipping',
                        'line_items': convertopia_settings.line_items,
                        'user_shipping_address': convertopia_settings.user_shipping_address,
                        'user_email_hashed': convertopia_settings.user_email_hashed,
                        'user_id': convertopia_settings.user_id
                    });
    
                    window.convertopia.push({
                        'event': 'checkout_payment',
                        'line_items': convertopia_settings.line_items, // array of products with object {id:'', name:'', price:'', catgory:'', url: ''}
                        'user_billing_address': convertopia_settings.user_billing_address, // object having {'first_name: '', last_name: '',address1': '', address1: '', cit
                        'user_email_hashed': convertopia_settings.user_email_hashed,
                        'user_id': convertopia_settings.user_id
                    });
                    
                    window.convertopia.push({
                        'event': 'order_confirmation',
                        'line_items': convertopia_settings.line_items,
                        'user_email_hashed': convertopia_settings.user_email_hashed,
                        'user_billing_address': convertopia_settings.user_billing_address,
                        'user_shipping_address': convertopia_settings.user_shipping_address,
                        'order_total': convertopia_settings.order_total,
                        'currency_code': convertopia_settings.currency_code,
                        'user_id': convertopia_settings.user_id
                    });
                }
            }

        } else {
            console.error('convertopia_settings is not defined.');
        }
    });

    $(document).on('added_to_cart', function(event, fragments, cart_hash, $button) {
        // Send tracking data here, e.g., using AJAX to your server or analytics service
        var product = $button.attr('href');
        var productObject = product.split('=');
        var product_id = productObject[1];

        // Example: Sending data via AJAX to your server
        $.ajax({
            url: convertopia_custom_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'convertopia_track_add_to_cart',
                product_id: product_id,
                nonce: convertopia_custom_ajax_object.nonce
            },
            success: function(response) {
                window.convertopia.push({
                    'event': 'add_to_cart',
                    'line_items': response.line_items, // You can modify this if you have multiple items
                    'user_id': response.user_id // Modify as needed for user_id
                })
            }
        });
    });


        // Check if the consent has already been given
        if (!sessionStorage.getItem('consentGiven')) {
            // Display the popup
            $('body').append(`
                <div id="ct-consent-popup" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 10000;">
                    <div style="background: #fff; padding: 20px; border-radius: 5px; text-align: center;">
                        <p>We use cookies to track your data. Please provide your consent.</p>
                        <button id="ct-consent-accept" style="margin-right: 10px;">Accept</button>
                        <button id="ct-consent-decline">Decline</button>
                    </div>
                </div>
            `);
    
            // Handle the accept button
            $('#ct-consent-accept').on('click', function() {
                $.ajax({
                    type: 'post',
                    url: convertopia_custom_ajax_object.ajax_url,
                    data: {
                        action: 'convertopia_save_user_consent',
                        consent: true,
                        nonce: convertopia_custom_ajax_object.nonce,
                    },
                    success: function(response) {
                        if (response.success) {
                            sessionStorage.setItem('consentGiven', 'true');
                            $('#ct-consent-popup').remove();
                        }
                        location.reload();
                    }
                });
            });
    
            // Handle the decline button
            $('#ct-consent-decline').on('click', function() {
                sessionStorage.setItem('consentGiven', 'false');
                $('#ct-consent-popup').remove();
            });
        }

})(jQuery);