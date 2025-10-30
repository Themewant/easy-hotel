<?php
use SureCart\Models\Coupon;
class ESHB_Coupon {

    public function __construct() {
        // Initialize the save coupon action 
        add_action( 'save_post_eshb_coupon', [$this, 'save_coupon'], 10, 3 );
    }

    public function create_coupon($post_id) {
        $settings       = get_option('eshb_settings', []);
        $booking_type   = $settings['booking-type'] ?? 'woocommerce';
        $wc_coupon_id   = get_post_meta($post_id, 'eshb_coupon_wc_id', true);
        
        // Get coupon metabox data
        $coupon = get_post_meta($post_id, 'eshb_coupon_metaboxes', true);
        if (empty($coupon)) return;

        $accomodation_ids = $coupon['accomodation-ids'] ?? [];
        $product_ids       = [];

        foreach ($accomodation_ids as $accomodation_id) {
            $accomodation_id = (int) $accomodation_id;
            $thumbnail_id     = get_post_thumbnail_id($accomodation_id);

            if ($booking_type === 'woocommerce' && class_exists('WC_Product')) {
                $product_ids[] = ESHB_Helper::get_or_create_woocommerce_product($accomodation_id, $thumbnail_id);
            } 
        }

        $product_ids = apply_filters( 'eshb_coupon_product_ids', $product_ids, $accomodation_ids);

        // Prepare coupon data
        $title                 = get_the_title($post_id);
        $product_ids_string    = implode(',', $product_ids);
        $coupon_code           = $coupon['coupon-code'] ?? '';
        $coupon_amount         = $coupon['coupon-amount'] ?? '';
        $discount_type         = $coupon['discount-type'] ?? 'percent';
        $expiry_date           = $coupon['expiry-date'] ?? '';
        $usage_limit           = $coupon['usage-limit'] ?? '';
        $usage_limit_per_user  = $coupon['usage-limit-per-user'] ?? '1';
        $free_shipping         = 'no';

        // Create or update the WooCommerce coupon
        do_action('eshb_before_create_coupon', $post_id, $coupon, $product_ids);

        if ($booking_type === 'woocommerce' && class_exists( 'WooCommerce' )) {
            $coupon_id = $this->create_woocommerce_coupon(
                $title, $coupon_code, $coupon_amount, $discount_type,
                $usage_limit, $usage_limit_per_user, $expiry_date,
                $free_shipping, $product_ids_string, $wc_coupon_id
            );
            if(!empty($coupon_id)){
                update_post_meta($post_id, 'eshb_coupon_wc_id', $coupon_id);
            }

            if(!empty($product_ids_string)){
                update_post_meta($post_id, 'eshb_coupon_wc_product_ids', $product_ids_string);
            }
        } 
    }

    private function get_or_create_woocommerce_product($accomodation_id, $thumbnail_id) {
        $product_id = get_post_meta($accomodation_id, '_woocommerce_product_id', true);

        if (empty($product_id)) {
            $product = new WC_Product();
            $product->set_name(get_the_title($accomodation_id));
            $product->set_price(1);
            $product->set_regular_price(1);
            $product->set_virtual(true);
            $product->set_image_id($thumbnail_id);
            $product->save();

            $product_id = $product->get_id();

            update_post_meta($accomodation_id, '_woocommerce_product_id', $product_id);
            update_post_meta($accomodation_id, '_regular_price', 1);
        }

        return $product_id;
    }

	public function create_woocommerce_coupon($title, $coupon_code, $amount, $discount_type = 'percent', $usage_limit = '', $usage_limit_per_user = '1', $expiry_date = '', $free_shipping = 'no', $product_id = '', $wc_coupon_id = '') {
		
		// Check if WooCommerce is active
		if (!class_exists('WooCommerce')) {
			return 'WooCommerce is not active.';
		}
	
		// Check if the coupon already exists

		if (!empty($wc_coupon_id)) {
			// Update existing coupon
			$coupon_id = $wc_coupon_id;
			wp_update_post(array(
				'ID'         => $coupon_id,
				'post_title' => $coupon_code,
                'post_excerpt'  => $title,
			));
		} else {
			// Create a new coupon
			$coupon = array(
				'post_title'    => $coupon_code,
				'post_excerpt'  => $title,
				'post_status'   => 'publish',
				'post_author'   => 1,
				'post_type'     => 'shop_coupon',
			);
			$coupon_id = wp_insert_post($coupon);
		}
	
		// Add meta data for the coupon
		if(!empty($product_id) && is_array($product_id)){
			$product_id = implode(',', $product_id);
		}


		// Add meta data for the coupon
		update_post_meta($coupon_id, 'discount_type', $discount_type);
		update_post_meta($coupon_id, 'coupon_amount', $amount);
		update_post_meta($coupon_id, 'individual_use', 'yes'); // Allow individual use only
		update_post_meta($coupon_id, 'product_ids', $product_id); // Products this coupon applies to, empty for all
		update_post_meta($coupon_id, 'exclude_product_ids', ''); // Excluded products
		update_post_meta($coupon_id, 'usage_limit', $usage_limit); // Limit usage to X items
		update_post_meta($coupon_id, 'usage_limit_per_user', $usage_limit_per_user); // Limit usage to 1 per user
		update_post_meta($coupon_id, 'expiry_date', $expiry_date); // Expiration date (Y-m-d format)
		update_post_meta($coupon_id, 'free_shipping', $free_shipping); // Enable free shipping if applicable

		return $coupon_id;

	}

	public function delete_coupon($post_id) {
		// Check if the post is being deleted or if it's the wrong post type
		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || get_post_type($post_id) != 'eshb_coupon') {
			return;
		}

		$eshb_settings = get_option('eshb_settings', []);
		$booking_type = isset($eshb_settings['booking-type']) && !empty($eshb_settings['booking-type']) ? $eshb_settings['booking-type'] : 'woocommerce';

        do_action( 'eshb_before_delete_coupon', $post_id, $booking_type );

		if($booking_type == 'woocommerce'){

            // Get the WooCommerce coupon ID from the custom post type
			$coupon_id = get_post_meta( $post_id, 'eshb_coupon_wc_id', true );
			wp_delete_post($coupon_id);

			// delete wc coupon id
			delete_post_meta($post_id, 'eshb_coupon_wc_id');

		}
		
	}

	public function save_coupon($post_id, $post, $update) {
		// Check if the post is being updated
		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || $post->post_type != 'eshb_coupon') {
			return;
		}

        // Check if the post is being deleted
		if(get_post_status($post_id) != 'publish'){
			$this->delete_coupon($post_id);
			return;
		}
		
		$this->create_coupon($post_id);
		
	}

}

// Initialize the class
$eshb_coupon = new ESHB_Coupon();