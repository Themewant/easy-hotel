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

    /**
     * Post types whose title is filtered through wp_kses_post().
     *
     * eshb_accomodation is on the list because its title is printed with
     * wp_kses_post() by the room grid/slider widgets and blocks, so this filter
     * is what keeps that output safe.
     *
     * @return string[]
     */
    function eshb_title_guarded_post_types() {
        return apply_filters( 'eshb_title_guarded_post_types', array(
            'eshb_accomodation',
            'eshb_coupon',
            'eshb_service',
            'eshb_session',
            'eshb_booking_request',
        ) );
    }

    /**
     * Allow formatting markup in a title, drop everything that can execute.
     *
     * Titles are deliberately NOT reduced to plain text: shop owners style them
     * with <strong>, <em>, <span> and the like, and esc_html()/wp_strip_all_tags()
     * would either print those tags literally or throw the formatting away.
     * wp_kses_post() keeps the post-content tag whitelist and removes <script>,
     * <iframe>, on* event attributes and javascript: URLs.
     *
     * @param string $title Raw title.
     * @return string
     */
    function eshb_clean_post_title( $title ) {
        return trim( wp_kses_post( (string) $title ) );
    }

    /**
     * Filter the guarded post titles on save.
     *
     * Administrators and editors hold the `unfiltered_html` capability, so
     * WordPress stores whatever they type in the title field verbatim —
     * including a <script> tag. These titles are printed as markup in the room
     * widgets, the dashboard, the checkout and notification e-mails, so the
     * dangerous parts are removed before they ever reach the database while the
     * formatting tags survive.
     *
     * wp_insert_post_data covers every write path: the classic editor, the
     * block editor, quick edit, the REST API and programmatic
     * wp_insert_post()/wp_update_post() calls.
     *
     * @param array $data Sanitized post data headed for the database.
     * @return array
     */
    function eshb_sanitize_post_title_on_save( $data ) {

        if ( ! isset( $data['post_type'], $data['post_title'] ) ) {
            return $data;
        }

        if ( ! in_array( $data['post_type'], eshb_title_guarded_post_types(), true ) ) {
            return $data;
        }

        $data['post_title'] = eshb_clean_post_title( $data['post_title'] );

        return $data;
    }
    add_filter( 'wp_insert_post_data', 'eshb_sanitize_post_title_on_save', 99 );

    /**
     * Apply the same filtering on read, so titles that were stored before the
     * save filter existed cannot fire a script either.
     *
     * @param string   $title   Post title.
     * @param int|null $post_id Post the title belongs to.
     * @return string
     */
    function eshb_sanitize_post_title_on_output( $title, $post_id = null ) {

        if ( ! $post_id ) {
            return $title;
        }

        if ( ! in_array( get_post_type( $post_id ), eshb_title_guarded_post_types(), true ) ) {
            return $title;
        }

        return eshb_clean_post_title( $title );
    }
    add_filter( 'the_title', 'eshb_sanitize_post_title_on_output', 10, 2 );

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
