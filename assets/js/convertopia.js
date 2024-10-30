
jQuery(document).ready(function($) {
    $('#generate-customer-feed-btn, #generate-customer-feed-btn-delta').click(function(e) {
        e.preventDefault();
        $('#loading-spinner').removeClass('d-none');
        deltaFlag = $(this).data('delta') ? true : false;
        var nonce = $('#convertopia_generate_customer_feed_nonce').val();


        $.ajax({
            url: convertopia_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convertopia_generate_customer_feed',
                nonce: nonce,
                delta: deltaFlag
            },
            success: function(response) {
                var data = JSON.parse(response);
                $('.feed-alert-message').removeClass('d-none');
                $('.feed-alert-message').text(data.message); 
                $('html, body').animate({
                    scrollTop: $('#wpbody-content').offset().top
                }, 1000);
                $('#loading-spinner').addClass('d-none');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error generating customer feed. Please try again.');

                $('#loading-spinner').addClass('d-none');
            }
        });
    });

    $('#generate-product-feed-btn, #generate-product-feed-btn-delta').click(function(e) {
        e.preventDefault();
        // Show loader
        $('#loading-spinner').removeClass('d-none');
        deltaFlag = $(this).data('delta') ? true : false;
        var nonce = $('#convertopia_generate_product_feed_nonce').val();

    
        $.ajax({
            url: convertopia_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convertopia_generate_product_feed',
                nonce: nonce,
                delta: deltaFlag
            },
            success: function(response) {
                var data = JSON.parse(response);
                $('.feed-alert-message').removeClass('d-none');
                $('.feed-alert-message').text(data.message);
                $('html, body').animate({
                    scrollTop: $('#wpbody-content').offset().top
                }, 1000);
                // Hide loader
                $('#loading-spinner').addClass('d-none');
    
                // If file_url is provided, you may choose to download the file automatically
                if (data.file_url) {
                    // For CSV files, you can trigger a download
                    var link = document.createElement('a');
                    link.setAttribute('href', data.file_url);
                    link.setAttribute('download', 'product-feed.csv');
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error generating product feed. Please try again.');
    
                // Hide loader
                $('#loading-spinner').addClass('d-none');
            }
        });
    });

    $('#generate-order-feed-btn, #generate-order-feed-btn-delta').click(function(e) {
        e.preventDefault();
        $('#loading-spinner').removeClass('d-none');
        deltaFlag = $(this).data('delta') ? true : false;
        var nonce = $('#convertopia_generate_order_feed_nonce').val();
        
        $.ajax({
            url: convertopia_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convertopia_generate_order_feed',
                nonce: nonce,
                delta: deltaFlag
            },
            success: function(response) {
                var data = JSON.parse(response);
                $('.feed-alert-message').removeClass('d-none');
                $('.feed-alert-message').text(data.message);
                $('html, body').animate({
                    scrollTop: $('#wpbody-content').offset().top
                }, 1000);
                $('#loading-spinner').addClass('d-none');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert('Error generating order feed. Please try again.');

                $('#loading-spinner').addClass('d-none');
            }
        });
    });

    $('.save-feed-configs').on('click', function(e) {
        e.preventDefault();
        $('#loading-spinner').removeClass('d-none');
    
        var nonce = $('#convertopia_update_all_schedules_nonce').val();
        var customerFrequency = $('.customer-feed-frequency').val();
        var customerHour = $('.customer-feed-hours').val(); // Corrected selector
        var customerMinute = $('.customer-feed-minute').val(); // Corrected selector
        var customerSecond = $('.customer-feed-second').val(); // Corrected selector
    
        // Get values for order feed
        var orderFrequency = $('.order-feed-frequency').val();
        var orderHour = $('.order-feed-hour').val(); // Corrected selector
        var orderMinute = $('.order-feed-minute').val(); // Corrected selector
        var orderSecond = $('.order-feed-second').val(); // Corrected selector
    
        // Get values for product feed
        var productFrequency = $('.product-feed-frequency').val();
        var productHour = $('.product-feed-hour').val(); // Corrected selector
        var productMinute = $('.product-feed-minute').val(); // Corrected selector
        var productSecond = $('.product-feed-second').val(); // Corrected selector
    
        // Ensure leading zero for single digits
        if (customerHour && customerMinute && customerSecond) {
            customerHour = customerHour.length === 1 ? '0' + customerHour : customerHour;
            customerMinute = customerMinute.length === 1 ? '0' + customerMinute : customerMinute;
            customerSecond = customerSecond.length === 1 ? '0' + customerSecond : customerSecond;
        }
    
        if (orderHour && orderMinute && orderSecond) {
            orderHour = orderHour.length === 1 ? '0' + orderHour : orderHour;
            orderMinute = orderMinute.length === 1 ? '0' + orderMinute : orderMinute;
            orderSecond = orderSecond.length === 1 ? '0' + orderSecond : orderSecond;
        }
    
        if (productHour && productMinute && productSecond) {
            productHour = productHour.length === 1 ? '0' + productHour : productHour;
            productMinute = productMinute.length === 1 ? '0' + productMinute : productMinute;
            productSecond = productSecond.length === 1 ? '0' + productSecond : productSecond;
        }
    
        // Send data to server via AJAX
        $.ajax({
            url: convertopia_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'convertopia_update_all_schedules',
                nonce: nonce,
                customer_frequency: customerFrequency,
                customer_hour: customerHour,
                customer_minute: customerMinute,
                customer_second: customerSecond,
                order_frequency: orderFrequency,
                order_hour: orderHour,
                order_minute: orderMinute,
                order_second: orderSecond,
                product_frequency: productFrequency,
                product_hour: productHour,
                product_minute: productMinute,
                product_second: productSecond
            },
            success: function(response) {
                $('.feed-alert-message').removeClass('d-none');
                $('.feed-alert-message').text(response.data.message); 
                $('html, body').animate({
                    scrollTop: $('#wpbody-content').offset().top
                }, 1000);
                
                $('#loading-spinner').addClass('d-none');
            },
            error: function(xhr, status, error) {
                $('.feed-alert-message').text('Error updating schedule', error); 
                $('#loading-spinner').addClass('d-none');
            }
        });
    });

    $('.collapsible-delta-link').on('click', function(e) {
        e.preventDefault();
        var isOpenDeltaAccordion = $(this).attr('aria-expanded');
        if (isOpenDeltaAccordion == "false") {
            $('.save-feed-configs').addClass('d-none');
            $('.save-feed-configs-delta').removeClass('d-none');
        } else {
            $('.save-feed-configs').removeClass('d-none');
            $('.save-feed-configs-delta').addClass('d-none');
        }
    })


    $('.collapsible-feed-link').on('click', function(e) {
        e.preventDefault();
        var isOpenDeltaAccordion = $(this).attr('aria-expanded');
        if (isOpenDeltaAccordion == "false") {
            $('.save-feed-configs').removeClass('d-none');
            $('.save-feed-configs-delta').addClass('d-none');
        } else {
            $('.save-feed-configs').addClass('d-none');
            $('.save-feed-configs-delta').removeClass('d-none');
        }
    })

    $('.save-feed-configs-delta').on('click', function(e) {
        e.preventDefault();
        $('#loading-spinner').removeClass('d-none');
        deltaFlag = true;
        var nonce = $('#convertopia_update_all_delta_schedules_nonce').val();
        var customerFrequency = $('.customer-feed-frequency-delta').val();
        var customerHour = $('.customer-feed-hours-delta').val(); // Corrected selector
        var customerMinute = $('.customer-feed-minute-delta').val(); // Corrected selector
        var customerSecond = $('.customer-feed-second-delta').val(); // Corrected selector


        // Get values for order feed

        var orderFrequency = $('.order-feed-frequency-delta').val();
        var orderHour = $('.order-feed-hour-delta').val(); // Corrected selector
        var orderMinute = $('.order-feed-minute-delta').val(); // Corrected selector
        var orderSecond = $('.order-feed-second-delta').val(); // Corrected selector

        // Get values for product feed

        var productFrequency = $('.product-feed-frequency-delta').val();
        var productHour = $('.product-feed-hour-delta').val(); // Corrected selector
        var productMinute = $('.product-feed-minute-delta').val(); // Corrected selector
        var productSecond = $('.product-feed-second-delta').val(); // Corrected selector


        // Ensure leading zero for single digits
        if (customerHour && customerMinute && customerSecond) {
            customerHour = customerHour.length === 1 ? '0' + customerHour : customerHour;
            customerMinute = customerMinute.length === 1 ? '0' + customerMinute : customerMinute;
            customerSecond = customerSecond.length === 1 ? '0' + customerSecond : customerSecond;
        }

        if (orderHour && orderMinute && orderSecond) {
            orderHour = orderHour.length === 1 ? '0' + orderHour : orderHour;
            orderMinute = orderMinute.length === 1 ? '0' + orderMinute : orderMinute;
            orderSecond = orderSecond.length === 1 ? '0' + orderSecond : orderSecond;
        }

        if (productHour && productMinute && productSecond) {
            productHour = productHour.length === 1 ? '0' + productHour : productHour;
            productMinute = productMinute.length === 1 ? '0' + productMinute : productMinute;
            productSecond = productSecond.length === 1 ? '0' + productSecond : productSecond;
        }

        // var time = hour + ':' + minute + ':' + second;

            // Send data to server via AJAX
            $.ajax({
                url: convertopia_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convertopia_update_all_delta_schedules',
                    nonce: nonce,
                    customer_frequency: customerFrequency,
                    customer_hour: customerHour,
                    customer_minute: customerMinute,
                    customer_second: customerSecond,
                    order_frequency: orderFrequency,
                    order_hour: orderHour,
                    order_minute: orderMinute,
                    order_second: orderSecond,
                    product_frequency: productFrequency,
                    product_hour: productHour,
                    product_minute: productMinute,
                    product_second: productSecond,
                    delta: deltaFlag
                },
                success: function(response) {
                    // You can display a success message if needed
                    $('.feed-alert-message').removeClass('d-none');
                    $('.feed-alert-message').text(response.data.message); 
                    $('html, body').animate({
                        scrollTop: $('#wpbody-content').offset().top
                    }, 1000);
                    
                    $('#loading-spinner').addClass('d-none');
                },
                error: function(xhr, status, error) {
                    console.error('Error updating schedule:', error);
                    // Handle error here
                    $('#loading-spinner').addClass('d-none');
                }
            });
    });
    
});
