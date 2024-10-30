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
        <h1 class="display-4 pt-3 pb-3"><?php echo esc_html(__('Convertopia Recommendations','convertopia-smart-search')) ?></h1>
        </div>
    </div><!-- End -->

    <div class="row">
        <div class="col-lg-9 mx-auto">
            <!-- Accordion -->
            <form class="simple-scheduled-export">
                <div class="convertopia-card card">
                    <div class="row justify-content-end">
                        <button class="save-feed-configs btn btn-success"><?php echo esc_html(__('Save Config', 'convertopia-smart-search')) ?></button>
                    </div>
                </div>
                <div id="convertopia-accordion" class="accordion shadow">

                    <!-- Accordion item 1 -->
                    <div class="convertopia-card card">
                        <div id="headingOne" class="card-header bg-white shadow-sm border-0">
                            <h2 class="mb-0">
                            <button type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true"
                                aria-controls="collapseOne"
                                class="btn btn-link text-dark font-weight-bold text-uppercase collapsible-link collapsible-feed-link"><?php echo esc_html(__('User Recommendations', 'convertopia-smart-search')) ?></button>
                            </h2>
                        </div>
                        <div id="collapseOne" aria-labelledby="headingOne" data-parent="#convertopia-accordion" class="collapse show">
                            <div class="card-body p-5">
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 ct-col">
                                                    <label class="ct-enable-lable" for="exampleInputEmail1"><?php echo esc_html(__('Enable', 'convertopia-smart-search')) ?></label>
                                                </div>
                                                <div class="col-sm-12 ct-col">
                                                    <label class="store-label" for=""><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select form-control" aria-label="Frequency">
                                                <option value="Yes" selected><?php echo esc_html(__('Yes', 'convertopia-smart-search')) ?></option>
                                                <option value="No"><?php echo esc_html(__('No', 'convertopia-smart-search')) ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 ct-col">
                                                    <label class="ct-rec-heading-lable" for="Heading"><?php echo esc_html(__('Heading', 'convertopia-smart-search')) ?></label>
                                                </div>
                                                <div class="col-sm-12 ct-col">
                                                    <label class="store-label" for=""><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control ct-rec-heading" id="Heading" aria-describedby="Heading">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 ct-col">
                                                    <label class="ct-product-tiles-row-lable" for="productTiles"><?php echo esc_html(__('Product Tiles in Row', 'convertopia-smart-search')) ?></label>
                                                </div>
                                                <div class="col-sm-12 ct-col">
                                                    <label class="store-label" for=""><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <select class="form-select form-control" aria-label="productTiles">
                                                <option value="1" selected>1</option>
                                                <option value="2">2</option>
                                                <option value="3">3</option>
                                                <option value="4">4</option>
                                                <option value="5">5</option>
                                                <option value="6">6</option>
                                                <option value="7">7</option>
                                                <option value="8">8</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <div class="row">
                                                <div class="col-sm-12 ct-col">
                                                    <label class="ct-pages-lable-one" for="pageSlection"><?php echo esc_html(__('Select pages where you want to show product tiles', 'convertopia-smart-search')) ?></label>
                                                </div>
                                                <div class="col-sm-12 ct-col">
                                                    <label class="store-label" for=""><?php echo esc_html(__('[store view]', 'convertopia-smart-search')) ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8">
                                            <select multiple class="form-control form-control" id="pageSlection">
                                                <option value="catalog_product_view" id="optIdRtW4JeVq"><?php echo esc_html(__('Product Page', 'convertopia-smart-search')) ?></option>
                                                <option value="catalog_category_view" id="optIdsMlvgse0"><?php echo esc_html(__('Category Page', 'convertopia-smart-search')) ?></option>
                                                <option value="cms_index_index" id="optId3N4gVqcU" selected="selected"><?php echo esc_html(__('Category Page', 'convertopia-smart-search')) ?></option>
                                                <option value="customer_acount_index" id="optIdZZPBRXdq"><?php echo esc_html(__('Account Info', 'convertopia-smart-search')) ?></option>
                                                <option value="customer_address_billing" id="optIdTGhvWB5z"><?php echo esc_html(__('Billing Info', 'convertopia-smart-search')) ?></option>
                                                <option value="customer_address_shipping" id="optId5Iaz92sP"><?php echo esc_html(__('Delivery Info', 'convertopia-smart-search')) ?></option>
                                                <option value="sales_order_history" id="optIdVl8idIfS"><?php echo esc_html(__('Recent Orders', 'convertopia-smart-search')) ?></option>
                                                <option value="customer_acount_login" id="optIdqiARqu8O"><?php echo esc_html(__('Login', 'convertopia-smart-search')) ?></option>
                                                <option value="customer_acount_create" id="optIdNRQkVEX4"><?php echo esc_html(__('Sign Up', 'convertopia-smart-search')) ?></option>
                                                <option value="contact_index_index" id="optIdjvVK0Wes"><?php echo esc_html(__('Contact Us', 'convertopia-smart-search')) ?></option>
                                                <option value="checkout_cart_index" id="optIdix23oQIf"><?php echo esc_html(__('Cart Page', 'convertopia-smart-search')) ?></option>
                                                <option value="checkout_onepage_success" id="optIdZ4hC9K4B"><?php echo esc_html(__('Order Success  Page', 'convertopia-smart-search')) ?></option>
                                                <option value="cms_noroute_index" id="optIdqwi2SJ9f"><?php echo esc_html(__('404 Page', 'convertopia-smart-search')) ?></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- End -->
            </form>
                </div><!-- End -->
        </div>
    </div>
</div>