<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
    // custom validation filter
    function eshb_validate_for_required ( $value ) {
        // return error message if empty
        if ( empty( $value ) ) {
            return esc_html__( 'This field is required.', 'easy-hotel' );
        }
    };

    /**
     * Required validation for the customer State field.
     *
     * State is only demanded when the chosen country actually has states to offer.
     * Countries such as American Samoa carry an empty states list, so their State
     * dropdown holds nothing but its placeholder and the plain required check would
     * reject a save nobody could ever fix.
     *
     * @param string $value Posted state.
     * @return string|void Error message when the field is genuinely missing.
     */
    function eshb_validate_state_for_required ( $value ) {

        if ( ! empty( $value ) ) {
            return;
        }

        $country = '';
        $uniques = array(
            'eshb_booking_customer_details_metaboxes',
            'eshb_payment_customer_details_metaboxes',
        );

        // The metabox verified its own nonce before handing the value over here.
        foreach ( $uniques as $unique ) {
            if ( isset( $_POST[ $unique ]['country'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                $country = sanitize_text_field( wp_unslash( $_POST[ $unique ]['country'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
                break;
            }
        }

        if ( '' !== $country && ! ESHB_Helper::eshb_country_has_states( $country ) ) {
            return;
        }

        return esc_html__( 'This field is required.', 'easy-hotel' );

    };

    include 'accomodation/accomodation.php';
    include 'session/session.php';
    include 'service/service.php';
    include 'booking/booking.php';
    include 'coupon/coupon.php';
    include 'booking-request/booking-request.php';
    include 'payment/payment.php';

   
    add_action( 'plugins_loaded', function(){
        
        
        // add nonce param to edit url
        add_filter( 'get_edit_post_link', function( $link, $post_id, $context ) {

            // Check for your custom post type (optional)
            if ( in_array(get_post_type( $post_id ), ['eshb_booking', 'eshb_payment', 'eshb_coupon', 'eshb_booking_request', 'eshb_service', 'eshb_session']) ) {
                // Add your custom parameter
                $nonce_action = ESHB_Helper::generate_secure_nonce_action('eshb_global_nonce_action');
                $link = add_query_arg( 'nonce', wp_create_nonce($nonce_action), $link );
            }

            return $link;
        }, 10, 3 );
    } );
