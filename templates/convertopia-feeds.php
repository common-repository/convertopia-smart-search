<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="loader d-none" id="loading-spinner">
    <div class="spinner-border" role="status">
        <span class="sr-only">Loading...</span>
    </div>
</div>
<div class="container-fluid feed-alert-container">
    <div class="alert alert-primary feed-alert-message d-none" role="alert">
    </div>
</div>

<div class="container mt-3">
    <!-- For demo purpose -->
    <div class="row py-5">
        <div class="col-lg-9 mx-auto text-white text-center">
        <h1 class="display-4 pt-3 pb-3"><?php echo esc_html(__('Convertopia Feeds', 'convertopia-smart-search')) ?></h1>
        </div>
    </div><!-- End -->

    <div class="row">
        <div class="col-lg-9 mx-auto">
            <!-- Accordion -->
            <form class="simple-scheduled-export">
                <?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
                wp_nonce_field('convertopia_generate_customer_feed_action', 'convertopia_generate_customer_feed_nonce'); ?>
                <?php wp_nonce_field('convertopia_generate_order_feed_action', 'convertopia_generate_order_feed_nonce'); ?>
                <?php wp_nonce_field('convertopia_generate_product_feed_action', 'convertopia_generate_product_feed_nonce'); ?>
                <?php wp_nonce_field('convertopia_update_all_schedules_action', 'convertopia_update_all_schedules_nonce'); ?>
                <?php wp_nonce_field('convertopia_update_all_delta_schedules_action', 'convertopia_update_all_delta_schedules_nonce'); ?>

                <div class="convertopia-card card">
                    <div class="row justify-content-end">
                            <button class="save-feed-configs btn btn-success"><?php echo esc_html(__('Save Config', 'convertopia-smart-search')) ?></button>
                            <button class="save-feed-configs-delta btn btn-primary d-none"><?php echo esc_html(__('Save Config', 'convertopia-smart-search')) ?></button>
                    </div>
                </div>
                <div id="convertopia-accordion" class="accordion shadow">

                    <!-- Accordion item 1 -->
                    <div class="convertopia-card card">
                        <div id="headingOne" class="card-header bg-white shadow-sm border-0">
                            <h2 class="mb-0">
                            <button type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                aria-controls="collapseOne"
                                class="btn btn-link text-dark font-weight-bold text-uppercase collapsible-link collapsible-feed-link"><?php echo esc_html(__('Scheduled Export', 'convertopia-smart-search')) ?></button>
                            </h2>
                        </div>
                        <div id="collapseOne" aria-labelledby="headingOne" data-parent="#convertopia-accordion" class="collapse show">

                            <div id="convertopia-accordion-feed" class="accordion shadow">

                                <!-- Accordion item 1 -->
                                <div class="convertopia-card card">
                                    <div id="headingOne" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseCustomer" aria-expanded="true"
                                            aria-controls="collapseCustomer"
                                            class="btn btn-link text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Customer', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseCustomer" aria-labelledby="headingOne" data-parent="#convertopia-accordion-feed" class="collapse show">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="ct-frequency" for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select customer-feed-frequency form-control" aria-label="Frequency">
                                                            <option value="daily" selected><?php echo esc_html(__('Daily', 'convertopia-smart-search')) ?></option>
                                                            <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                            <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select customer-feed-hours" name="hour" aria-label="hour">
                                                                <option value="0" selected>0</option>
                                                                <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select customer-feed-minute" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select customer-feed-second" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                                <!-- Accordion item 2 -->
                                <div class="convertopia-card card">
                                    <div id="headingTwo" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseOrder" aria-expanded="false"
                                            aria-controls="collapseOrder"
                                            class="btn btn-link collapsed text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Orders', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseOrder" aria-labelledby="headingTwo" data-parent="#convertopia-accordion-feed" class="collapse">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select order-feed-frequency form-control" aria-label="Frequency">
                                                            <option value="daily" selected><?php echo esc_html(__('Daily', 'convertopia-smart-search')) ?></option>
                                                            <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                            <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select order-feed-hour" name="hour" aria-label="hour">
                                                                <option value="0" selected>0</option>
                                                                <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                                <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select order-feed-minute" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select order-feed-second" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                                <!-- Accordion item 3 -->
                                <div class="convertopia-card card">
                                    <div id="headingThree" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseProducts" aria-expanded="false"
                                            aria-controls="collapseProducts"
                                            class="btn btn-link collapsed text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Products', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseProducts" aria-labelledby="headingThree" data-parent="#convertopia-accordion-feed" class="collapse">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select product-feed-frequency form-control" aria-label="Frequency">
                                                            <option value="daily" selected><?php echo esc_html(__('Daily', 'convertopia-smart-search')) ?></option>
                                                            <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                            <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select product-feed-hour" name="hour" aria-label="hour">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select product-feed-minute" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select product-feed-second" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                            </div><!-- End -->
                        </div>
                    </div><!-- End -->

                    <!-- Accordion item 2 -->
                    <div class="convertopia-card card">
                        <div id="headingTwo" class="card-header bg-white shadow-sm border-0">
                                <h2 class="mb-0">
                                <button type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true"
                                    aria-controls="collapseTwo"
                                    class="btn btn-link text-dark font-weight-bold text-uppercase collapsible-link collapsible-delta-link"><?php echo esc_html(__('Scheduled Delta Export', 'convertopia-smart-search')) ?></button>
                                </h2>
                        </div>
                        <div id="collapseTwo" aria-labelledby="headingTwo" data-parent="#convertopia-accordion" class="collapse show">

                            <div id="convertopia-accordion-feed" class="accordion shadow">

                                <!-- Accordion item 1 -->
                                <div class="convertopia-card card">
                                    <div id="delta-customer-feed" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseCustomer-delta" aria-expanded="true"
                                            aria-controls="collapseCustomer-delta"
                                            class="btn btn-link text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Customer', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseCustomer-delta" aria-labelledby="delta-customer-feed" data-parent="#convertopia-accordion-feed" class="collapse show">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select customer-feed-frequency-delta form-control" aria-label="Frequency">
                                                            <option value="daily" selected><?php echo esc_html(__('dAILY', 'convertopia-smart-search')) ?></option>
                                                            <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                            <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select customer-feed-hours-delta" name="hour" aria-label="hour">
                                                                <option value="0" selected>0</option>
                                                                <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select customer-feed-minute-delta" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select customer-feed-second-delta" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <label for="exampleInputEmail1"></label>

                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                                <!-- Accordion item 2 -->
                                <div class="convertopia-card card">
                                    <div id="delta-orders-feed" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseOrder-delta" aria-expanded="false"
                                            aria-controls="collapseOrder-delta"
                                            class="btn btn-link collapsed text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Orders', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseOrder-delta" aria-labelledby="delta-orders-feed" data-parent="#convertopia-accordion-feed" class="collapse">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select order-feed-frequency-delta form-control" aria-label="Frequency">
                                                            <option value="daily" selected><?php echo esc_html(__('Daily', 'convertopia-smart-search')) ?></option>
                                                            <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                            <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select order-feed-hour-delta" name="hour" aria-label="hour">
                                                                <option value="0" selected>0</option>
                                                                <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                                <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select order-feed-minute-delta" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select order-feed-second-delta" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                                <!-- Accordion item 3 -->
                                <div class="convertopia-card card">
                                    <div id="products-delta-feed" class="card-header bg-white shadow-sm border-0">
                                        <h2 class="mb-0">
                                        <button type="button" data-toggle="collapse" data-target="#collapseProducts-delta" aria-expanded="false"
                                            aria-controls="collapseProducts-delta"
                                            class="btn btn-link collapsed text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Products', 'convertopia-smart-search')) ?></button>
                                        </h2>
                                    </div>
                                    <div id="collapseProducts-delta" aria-labelledby="products-delta-feed" data-parent="#convertopia-accordion-feed" class="collapse">
                                        <div class="card-body p-5">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1"><?php echo esc_html(__('Frequency', 'convertopia-smart-search')) ?></label>
                                                <select class="form-select product-feed-frequency-delta" aria-label="Frequency">
                                                    <option value="daily" selected><?php echo esc_html(__('Daily', 'convertopia-smart-search')) ?></option>
                                                    <option value="weekly"><?php echo esc_html(__('Weekly', 'convertopia-smart-search')) ?></option>
                                                    <option value="monthly"><?php echo esc_html(__('Monthly', 'convertopia-smart-search')) ?></option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <div class="row">
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label-heading" for="exampleInputEmail1"><?php echo esc_html(__('Start Time', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                            <div class="col-sm-12 ct-col">
                                                                <label class="store-label" for="exampleInputEmail1"><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <select class="form-select product-feed-hour-delta" name="hour" aria-label="hour">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 23; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select> :

                                                        <select class="form-select product-feed-minute-delta" name="minute" aria-label="minute">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>  :

                                                        <select class="form-select product-feed-second-delta" name="second" aria-label="second">
                                                            <option value="0" selected>0</option>
                                                            <?php for ($i = 1; $i <= 59; $i++): ?>
                                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div><!-- End -->

                            </div><!-- End -->
                        </div>
                    </div><!-- End -->
            </form>
                    <!-- Accordion item 3 -->
                    <div class="convertopia-card card">
                        <div id="headingThree" class="card-header bg-white shadow-sm border-0">
                            <h2 class="mb-0">
                            <button type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false"
                                aria-controls="collapseThree"
                                class="btn btn-link collapsed text-dark font-weight-bold text-uppercase collapsible-link"><?php echo esc_html(__('Feeds', 'convertopia-smart-search')) ?></button>
                            </h2>
                        </div>
                        <div id="collapseThree" aria-labelledby="headingThree" data-parent="#convertopia-accordion" class="collapse">
                            <div class="card-body p-5">
                                <button class="btn d-block mt-2 btn-primary" id="generate-customer-feed-btn"><?php echo esc_html(__('Generate Customer Feed', 'convertopia-smart-search')) ?></button>
                                <button class="btn d-block mt-2 btn-primary" id="generate-product-feed-btn"><?php echo esc_html(__('Generate Product Feed', 'convertopia-smart-search')) ?></button>
                                <button class="btn d-block mt-2 btn-primary" id="generate-order-feed-btn"><?php echo esc_html(__('Generate Order Feed', 'convertopia-smart-search')) ?></button>
                                <button class="btn d-block mt-2 btn-primary" id="generate-customer-feed-btn-delta" data-delta="true"><?php echo esc_html(__('Generate Customer Feed Delta', 'convertopia-smart-search')) ?></button>
                                <button class="btn d-block mt-2 btn-primary" id="generate-product-feed-btn-delta" data-delta="true"><?php echo esc_html(__('Generate Product Feed Delta', 'convertopia-smart-search')) ?></button>
                                <button class="btn d-block mt-2 btn-primary" id="generate-order-feed-btn-delta" data-delta="true"><?php echo esc_html(__('Generate Order Feed Delta', 'convertopia-smart-search')) ?></button>
                            </div>
                        </div>
                    </div><!-- End -->

                </div><!-- End -->
        </div>
    </div>
</div>