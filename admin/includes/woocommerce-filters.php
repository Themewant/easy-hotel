<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// change product permalink to accomodation permalink at shop page
add_filter('woocommerce_product_get_permalink', function($url, $product) {
    if (!$product || !is_object($product)) {
        return $url;
    }

    $accomodation_id = get_post_meta($product->get_id(), '_accomodation_id', true);
    $accomodation_permalink = get_permalink($accomodation_id);

    if (!empty($accomodation_id) && $accomodation_permalink) {
        return $accomodation_permalink;
    }

    return $url;
}, 10, 2);

add_filter('post_type_link', 'eshb_custom_product_permalink', 10, 2);

function eshb_custom_product_permalink($url, $post) {
    if (!$post || !is_object($post) || $post->post_type !== 'product') {
        return $url;
    }

    $accomodation_id = get_post_meta($post->ID, '_accomodation_id', true);

    if (!empty($accomodation_id)) {
        // Temporarily remove the filter to prevent infinite loop
        remove_filter('post_type_link', 'eshb_custom_product_permalink', 10);
        
        $accomodation_permalink = get_permalink($accomodation_id);
        
        // Add the filter back
        add_filter('post_type_link', 'eshb_custom_product_permalink', 10, 2);

        if ($accomodation_permalink) {
            return $accomodation_permalink;
        }
    }

    return $url;
}



// change product permalink to accomodation permalink at cart table
add_filter('woocommerce_cart_item_permalink', function($url, $cart_item, $cart_item_key) {
    if (!isset($cart_item['product_id']) || empty($cart_item['product_id'])) {
        return $url;
    }

    $accomodation_id = get_post_meta($cart_item['product_id'], '_accomodation_id', true);
    $accomodation_permalink = get_permalink($accomodation_id);

    if (!empty($accomodation_id) && $accomodation_permalink) {
        return $accomodation_permalink;
    }
    
    return $url;
}, 10, 3);


// change product permalink to accomodation permalink at checkout page
add_filter('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key) {
    if (!isset($cart_item['product_id']) || empty($cart_item['product_id']) || !isset($cart_item['data'])) {
        return $name;
    }

    $accomodation_id = get_post_meta($cart_item['product_id'], '_accomodation_id', true);
    $accomodation_permalink = get_permalink($accomodation_id);

    if (!empty($accomodation_id) && $accomodation_permalink) {
        $product_name = $cart_item['data']->get_name();
        return '<a href="' . esc_url($accomodation_permalink) . '">' . esc_html($product_name) . '</a>';
    }
    
    return $name;
}, 10, 3);


// change product permalink to accomodation permalink at order page
add_filter('woocommerce_order_item_permalink', function($url, $item, $order) {
    if (!$item || !is_object($item)) {
        return $url;
    }

    $product_id = $item->get_product_id();
    if (!$product_id) {
        return $url;
    }

    $accomodation_id = get_post_meta($product_id, '_accomodation_id', true);
    $accomodation_permalink = get_permalink($accomodation_id);

    if (!empty($accomodation_id) && $accomodation_permalink) {
        return $accomodation_permalink;
    }

    return $url;
}, 10, 3);


// hide from woocommece product query
add_action('woocommerce_product_query', 'eshb_hide_products_with_accommodation_id');
function eshb_hide_products_with_accommodation_id($q) {
    $meta_query = $q->get('meta_query');

    $meta_query[] = array(
        'key'     => '_accomodation_id',
        'compare' => 'NOT EXISTS',
    );

    $q->set('meta_query', $meta_query);
}

add_filter('woocommerce_related_products', 'eshb_remove_related_products_with_accommodation_id', 10, 3);

function eshb_remove_related_products_with_accommodation_id($related_posts, $product_id, $args) {
    $filtered = [];

    foreach ($related_posts as $related_product_id) {
        $accommodation_id = get_post_meta($related_product_id, '_accomodation_id', true);
        
        if (empty($accommodation_id)) {
            $filtered[] = $related_product_id;
        }
    }

    return $filtered;
}

// Remove default WooCommerce coupon form and render a custom inline coupon field
// inside the order review (no nested <form> — uses a plain button + JS AJAX)
add_action( 'init', function() {
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
} );
add_action( 'woocommerce_review_order_before_payment', function() {
    $archive_url = get_post_type_archive_link( 'eshb_accomodation' );
    ?>
    <div class="eshb-order-review-actions">
        <div class="eshb-order-review-actions__left">
            <?php if ( $archive_url ) : ?>
            <a href="<?php echo esc_url( $archive_url ); ?>" class="button eshb-add-more-btn">
                <?php esc_html_e( 'Add more accommodation', 'easy-hotel' ); ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="eshb-order-review-actions__right">
            <?php if ( wc_coupons_enabled() ) : ?>
            <div class="eshb-coupon-wrap">
                <p class="eshb-coupon-toggle">
                    <?php esc_html_e( 'Have a coupon?', 'easy-hotel' ); ?>
                    <a href="#" class="eshb-showcoupon"><?php esc_html_e( 'Click here to enter your code', 'easy-hotel' ); ?></a>
                </p>
                <div class="eshb-coupon-fields" style="display:none;">
                    <p class="form-row">
                        <input type="text" class="eshb-coupon-code input-text" placeholder="<?php esc_attr_e( 'Coupon code', 'easy-hotel' ); ?>" />
                        <button type="button" class="eshb-apply-coupon button"><?php esc_html_e( 'Apply coupon', 'easy-hotel' ); ?></button>
                    </p>
                    <div class="eshb-coupon-message"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}, 10 );

// Remove "Order again" button from WooCommerce Thank You page
add_action( 'woocommerce_thankyou', function( $order_id ) {
    remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button', 10 );
}, 1 );

// Hide the entire Additional Information section heading/wrapper when disabled
add_filter( 'woocommerce_enable_order_notes_field', function( $enabled ) {
    $settings = get_option( 'eshb_settings', array() );
    if ( empty( $settings['checkout-additional-fields'] ) ) {
        return false;
    }
    return $enabled;
} );

// Filter checkout fields based on plugin settings
add_filter( 'woocommerce_checkout_fields', function( $fields ) {
    $settings = get_option( 'eshb_settings', array() );

    // Rename address labels
    if ( isset( $fields['billing']['billing_address_1']['label'] ) ) {
        $fields['billing']['billing_address_1']['label'] = __( 'Address', 'easy-hotel' );
    }
    if ( isset( $fields['shipping']['shipping_address_1']['label'] ) ) {
        $fields['shipping']['shipping_address_1']['label'] = __( 'Address', 'easy-hotel' );
    }

    // Billing fields
    $allowed_billing = isset( $settings['checkout-billing-fields'] ) ? (array) $settings['checkout-billing-fields'] : null;
    if ( is_array( $allowed_billing ) && ! empty( $allowed_billing ) ) {
        foreach ( array_keys( $fields['billing'] ?? array() ) as $key ) {
            if ( ! in_array( $key, $allowed_billing, true ) ) {
                unset( $fields['billing'][ $key ] );
            }
        }
    }

    // Shipping fields
    $allowed_shipping = isset( $settings['checkout-shipping-fields'] ) ? (array) $settings['checkout-shipping-fields'] : null;
    if ( is_array( $allowed_shipping ) && ! empty( $allowed_shipping ) ) {
        foreach ( array_keys( $fields['shipping'] ?? array() ) as $key ) {
            if ( ! in_array( $key, $allowed_shipping, true ) ) {
                unset( $fields['shipping'][ $key ] );
            }
        }
    }

    // Additional information section
    $additional_enabled = ! empty( $settings['checkout-additional-fields'] );
    if ( ! $additional_enabled ) {
        $fields['order'] = array();
    } elseif ( isset( $settings['checkout-order-notes'] ) && ! $settings['checkout-order-notes'] ) {
        unset( $fields['order']['order_comments'] );
    }

    return $fields;
}, 20 );

