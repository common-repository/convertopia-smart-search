<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (isset($_SESSION['productIds']) && !empty($_SESSION['productIds'])) {

    $sanitized_product_ids = array_map('absint', $_SESSION['productIds']);

    // Only proceed if sanitized array is not empty
    if (!empty($sanitized_product_ids)) {
        $args = array(
            'post_type' => 'product',
            'post__in' => $sanitized_product_ids, // Use sanitized product IDs
        );
    }
}

$query = new WP_Query($args);

if ($query->have_posts()) {
    ?>
    <div class="search-results">
        <h2><?php echo esc_html(__('Search Results', 'convertopia-smart-search')) ?></h2>
        <ul>
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                ?>
                <li>
                    <h3><?php the_title(); ?></h3>
                    <div class="product-content">
                        <?php the_content(); ?>
                    </div>
                </li>
                <?php
            }
            ?>
        </ul>
    </div>
    <?php
} else {
    ?>
    <div class="no-results">
        <h2><?php echo esc_html(__('No Products Found', 'convertopia-smart-search')) ?></h2>
        <p><?php echo esc_html(__('Sorry, no products were found matching your search criteria.','convertopia-smart-search')) ?></p>
    </div>
    <?php
}

wp_reset_postdata();