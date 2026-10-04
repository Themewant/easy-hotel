<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Metabox_Settings {

    protected $screen;

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'customize_meta_boxes'], 20 );
        add_action( 'init', [$this, 'register_booking_post_statuses'], 5 );
        add_action( 'admin_notices', [$this, 'render_list_screen_note'] );
    }
    
    function register_booking_post_statuses(){

        $booking_statuses = ESHB_Helper::eshb_get_booking_statuses();

        foreach ( $booking_statuses as $slug => $label ) {
            register_post_status( $slug, array(
                'label'                     => esc_html( $label ),
                'public'                    => true,
                'exclude_from_search'       => false,
                'show_in_admin_all_list'    => true,
                'show_in_admin_status_list' => true,
                /* translators: 1: booking count, 2: booking count */
                'label_count'               => _n_noop(
                    'Booking <span class="count">(%s)</span>',
                    'Booking <span class="count">(%s)</span>',
                    'easy-hotel'
                ),
            ) );
        }
    }

    // remove unnecessary thirdparty metaboxes
	function customize_meta_boxes() {
        remove_meta_box( 'slider_revolution_metabox', ['eshb_booking', 'eshb_accomodation', 'eshb_service', 'eshb_session', 'eshb_coupon', 'eshb_booking_request', 'eshb_payment', 'eshb_email_template'], 'side' ); // Custom Fields meta box
        remove_meta_box( 'astra_settings_meta_box', ['eshb_booking', 'eshb_accomodation', 'eshb_service', 'eshb_session', 'eshb_coupon', 'eshb_booking_request', 'eshb_payment', 'eshb_email_template'], 'side' ); // Custom Fields meta box   

        remove_meta_box( 'submitdiv', ['eshb_booking'], 'side' ); // Custom Fields meta box
        add_meta_box( 'submitdiv', __( 'Update Booking', 'easy-hotel' ), [$this, 'render_submit_meta_box_booking'], 'eshb_booking', 'side', 'default' );

        remove_meta_box( 'submitdiv', ['eshb_payment'], 'side' );
        add_meta_box( 'submitdiv', __( 'Update Payment', 'easy-hotel' ), [$this, 'render_submit_meta_box_payment'], 'eshb_payment', 'side', 'default' );
    }

    public function render_submit_meta_box_booking( $post, $metabox ) {
		$postTypeObject = get_post_type_object( 'eshb_booking' );
		$can_publish    = current_user_can( $postTypeObject->cap->publish_posts );
		$postStatus     = get_post_status( $post->ID );
        $statuses = ESHB_Helper::eshb_get_booking_statuses();
		// post_date is stored in the site timezone already (post_date_gmt is the
		// UTC copy), so reading it with strtotime() shifted it by the offset a
		// second time.
		$post_date = ESHB_Helper::eshb_format_datetime( $post->post_date, 'F j, Y g:i a' );
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
				</div>
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="eshb_post_status"><?php echo esc_html__( 'Status:', 'easy-hotel' )?></label>
						<select name="post_status" id="eshb_post_status">
							<?php foreach ( $statuses as $statusName => $statusDetails ) { ?>
								<option value="<?php echo esc_attr( $statusName ); ?>" <?php selected( $statusName, $postStatus ); ?>>
									<?php echo esc_html( $statusDetails ) ; ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'easy-hotel' ); ?></span>
						<strong><?php echo esc_html($post_date) ; ?></strong>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = __( 'Delete Permanently', 'easy-hotel' );
						} else {
							$delete_text = __( 'Move to Trash', 'easy-hotel' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update Booking', 'easy-hotel' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="
					<?php
					in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create Booking', 'easy-hotel' ) : esc_attr_e( 'Update Booking', 'easy-hotel' );
					?>
					" />
				</div>
				<div class="clear"></div>
			</div>
            <p class="eshb-error-message eshb-text-danger"><?php echo esc_html__( 'Full up all required field!', 'easy-hotel' )?></p>
		</div>
		<?php
	}
    
    // Save box for payments recorded by the checkout. Lets the admin change
    // the status of an existing payment.
    public function render_submit_meta_box_payment( $post, $metabox ) {
		$postStatus = get_post_status( $post->ID );
        $statuses   = ESHB_Helper::eshb_get_payment_statuses();
		$post_date  = ESHB_Helper::eshb_format_datetime( $post->post_date, 'F j, Y g:i a' );

		$default_status = in_array( $postStatus, array( 'new', 'auto-draft' ), true ) ? 'completed' : $postStatus;
		?>
		<div class="submitbox" id="submitpost">
			<div id="minor-publishing">
				<div id="minor-publishing-actions">
				</div>
				<div id="misc-publishing-actions">
					<div class="misc-pub-section">
						<label for="eshb_post_status"><?php echo esc_html__( 'Status:', 'easy-hotel' )?></label>
						<select name="post_status" id="eshb_post_status">
							<?php foreach ( $statuses as $statusName => $statusDetails ) { ?>
								<option value="<?php echo esc_attr( $statusName ); ?>" <?php selected( $statusName, $default_status ); ?>>
									<?php echo esc_html( $statusDetails ) ; ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="misc-pub-section">
						<span><?php esc_html_e( 'Created on:', 'easy-hotel' ); ?></span>
						<strong><?php echo esc_html($post_date) ; ?></strong>
					</div>
				</div>
			</div>
			<div id="major-publishing-actions">
				<div id="delete-action">
					<?php
					if ( current_user_can( 'delete_post', $post->ID ) ) {
						if ( ! EMPTY_TRASH_DAYS ) {
							$delete_text = __( 'Delete Permanently', 'easy-hotel' );
						} else {
							$delete_text = __( 'Move to Trash', 'easy-hotel' );
						}
						?>
						<a class="submitdelete deletion" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
					<?php } ?>
				</div>
				<div id="publishing-action">
					<span class="spinner"></span>
					<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Update Payment', 'easy-hotel' ); ?>" />
					<input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="<?php esc_attr_e( 'Update Payment', 'easy-hotel' ); ?>" />
				</div>
				<div class="clear"></div>
			</div>
			<p class="eshb-error-message eshb-text-danger"><?php echo esc_html__( 'Full up all required field!', 'easy-hotel' )?></p>
		</div>
		<?php
	}

    // Hook point on the Bookings / Payments list screens for extensions.
    public function render_list_screen_note() {
        $screen = get_current_screen();
        if ( ! $screen || 'edit' !== $screen->base || ! in_array( $screen->post_type, array( 'eshb_booking', 'eshb_payment' ), true ) ) {
            return;
        }

        do_action( 'eshb_admin_list_screen_note', $screen->post_type );
    }

    public static function eshb_upgrade_message( $plugin_name, $plugin_url, $wrapper = 'span', $wrapperClass = 'eshb-admin-notice eshb-admin-notice-small' ) {
        $message = sprintf(
            /* translators: 1: plugin URL, 2: plugin name */
            __( 'Please activate <a href="%1$s" target="_blank">%2$s</a> extension to access this feature.', 'easy-hotel' ),
            esc_url( $plugin_url ),
            esc_html( $plugin_name )
        );


        if ( ! empty( $wrapper ) ) {
            if ( $wrapper === 'div' ) {
                $message = '<div class="' . esc_attr( $wrapperClass ) . '">' . $message . '</div>';
            }elseif($wrapper === 'p'){
                $message = '<p class="' . esc_attr( $wrapperClass ) . '">' . $message . '</p>';
            } else {
                $message = '<span class="' . esc_attr( $wrapperClass ) . '">' . $message . '</span>';
            }
        }

        return $message;
    }
}
new ESHB_Metabox_Settings();


// add custom order status
add_action('init', 'eshb_register_custom_order_status');
add_filter('wc_order_statuses', 'eshb_add_custom_order_status_to_woocommerce');
function eshb_register_custom_order_status() {
    register_post_status( 'wc-deposit-payment', array(
        'label'                     => _x( 'Deposit Payment', 'Order status', 'easy-hotel' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        /* translators: 1: depoist payment count, 2: depoist payment count */
        'label_count'               => _n_noop(
            'Deposit Payment <span class="count">(%s)</span>',
            'Deposit Payment <span class="count">(%s)</span>',
            'easy-hotel'
        ),
    ) );
}

function eshb_add_custom_order_status_to_woocommerce($order_statuses) {
    // Place it after processing
    $new_order_statuses = array();

    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-deposit-payment'] = __('Deposit Payment', 'easy-hotel');
        }
    }

    return $new_order_statuses;
}

// allow payment for this status
add_filter( 'wc_order_is_pending_status', 'eshb_enable_payment_for_custom_status' );
function eshb_enable_payment_for_custom_status( $statuses ) {
    $statuses[] = 'deposit-payment';
    return $statuses;
}
add_filter( 'woocommerce_resend_order_emails_available', 'eshb_enable_resend_email_for_custom_status', 10, 1 );
function eshb_enable_resend_email_for_custom_status( $emails ) {
    $emails[] = 'customer_invoice'; // Invoice email with payment link
    return $emails;
}