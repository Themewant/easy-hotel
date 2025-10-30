<?php
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

add_filter('post_type_link', 'custom_product_permalink', 10, 2);

function custom_product_permalink($url, $post) {
    if (!$post || !is_object($post) || $post->post_type !== 'product') {
        return $url;
    }

    $accomodation_id = get_post_meta($post->ID, '_accomodation_id', true);

    if (!empty($accomodation_id)) {
        // Temporarily remove the filter to prevent infinite loop
        remove_filter('post_type_link', 'custom_product_permalink', 10);
        
        $accomodation_permalink = get_permalink($accomodation_id);
        
        // Add the filter back
        add_filter('post_type_link', 'custom_product_permalink', 10, 2);

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
add_action('woocommerce_product_query', 'hide_products_with_accommodation_id');
function hide_products_with_accommodation_id($q) {
    $meta_query = $q->get('meta_query');

    $meta_query[] = array(
        'key'     => '_accomodation_id',
        'compare' => 'NOT EXISTS',
    );

    $q->set('meta_query', $meta_query);
}

add_filter('woocommerce_related_products', 'remove_related_products_with_accommodation_id', 10, 3);

function remove_related_products_with_accommodation_id($related_posts, $product_id, $args) {
    $filtered = [];

    foreach ($related_posts as $related_product_id) {
        $accommodation_id = get_post_meta($related_product_id, '_accomodation_id', true);
        
        if (empty($accommodation_id)) {
            $filtered[] = $related_product_id;
        }
    }

    return $filtered;
}

// Remove "Order again" button from WooCommerce Thank You page
add_action( 'woocommerce_thankyou', function( $order_id ) {
    remove_action( 'woocommerce_order_details_after_order_table', 'woocommerce_order_again_button', 10 );
}, 1 );

