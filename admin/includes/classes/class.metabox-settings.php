<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_Metabox_Settings {

    protected $title_actions = array();
    protected $screen;

    public function __construct() {
        add_action( 'add_meta_boxes', [$this, 'customize_meta_boxes'], 20 );
        add_action( 'init', [$this, 'register_booking_post_statuses'], 5 );
        add_action( 'admin_footer', [$this, 'title_actions_script'] );
        add_action( 'admin_head-edit.php', [$this, 'disable_new_booking']);
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
        
        $plugin_name = 'EHB Manual Booking';
        $plugin_slug = 'ehb-manual-booking';
        $plugin_url = 'https://themewant.com/downloads/'.$plugin_slug;
        $plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php';

        if (! is_plugin_active( $plugin_main_file ) ) {
            remove_meta_box( 'submitdiv', ['eshb_payment'], 'side' ); // Custom Fields meta box
            add_meta_box( 'submitdiv', __( 'Update Payment', 'easy-hotel' ), [$this, 'render_submit_meta_box_payment'], 'eshb_payment', 'side', 'default' );
        }
    }

    public function render_submit_meta_box_booking( $post, $metabox ) {
		$postTypeObject = get_post_type_object( 'eshb_booking' );
		$can_publish    = current_user_can( $postTypeObject->cap->publish_posts );
		$postStatus     = get_post_status( $post->ID );
        $statuses = ESHB_Helper::eshb_get_booking_statuses();
		$post_date = date_i18n( 'F j, Y g:i a', strtotime($post->post_date) );
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
                <p class="eshb-error-message eshb-text-danger"><?php echo esc_html__( 'Full up all required field!', 'easy-hotel' )?></p>
			</div>
		</div>
		<?php
	}
    
    public function disable_new_booking(){
        $screen = get_current_screen();
        if ( $screen && in_array($screen->post_type, ['eshb_booking', 'eshb_payment']) ) {
            $plugin_name = 'EHB Manual Booking';
            $plugin_slug = 'ehb-manual-booking';
            $plugin_url = 'https://themewant.com/downloads/'.$plugin_slug;
            $plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php';

            if (! is_plugin_active( $plugin_main_file ) ) {
                remove_submenu_page( 'edit.php?post_type=eshb_booking', 'post-new.php?post_type=eshb_booking' );
                // Remove the "Add New" button on top of the list table
                if(in_array($screen->post_type, ['eshb_booking'])){
                    $this->modify_title_actions( __( 'New Booking', 'easy-hotel' ), '#', array( 'class' => 'button-disabled', 'after' => $this->eshb_upgrade_message($plugin_name, $plugin_url) ) );
                }else{  
                    $this->modify_title_actions( __( 'New Payment', 'easy-hotel' ), '#', array( 'class' => 'button-disabled', 'after' => $this->eshb_upgrade_message($plugin_name, $plugin_url) ) );
                }
            }
        }
    }

    public function render_submit_meta_box_payment ($post, $metabox){
        $plugin_name = 'EHB Manual Booking';
        $plugin_slug = 'ehb-manual-booking';
        $plugin_url = 'https://themewant.com/downloads/'.$plugin_slug;
        echo esc_html($this->eshb_upgrade_message($plugin_name, $plugin_url, 'div'));
    }

	public function modify_title_actions( $label, $url, $options = array() ) {
        $this->title_actions[] = array(
            'label' => $label,
            'class' => 'eshb-page-title-action button ' . ( $options['class'] ?? '' ),
            'url'   => $url,
            'after' => ! empty( $options['after'] ) ? ' ' . $options['after'] : '',
        );
    }

	public function title_actions_script() {
        if ( empty( $this->title_actions ) ) {
            return;
        }

        $actions = array_map( function( $action ) {
            $html  = '<a href="' . esc_url( $action['url'] ) . '"';
            $html .= ' class="' . esc_attr( $action['class'] ) . '">';
            $html .= esc_html( $action['label'] ) . '</a>';
            $html .= ! empty( $action['after'] ) ? wp_kses_post( $action['after'] ) : '';
            return $html;
        }, $this->title_actions );

        ?>
        <script type="text/javascript">
            jQuery( function( $ ) {
                var actions = <?php echo wp_json_encode( $actions ); ?>;
                var $heading = $( '#wpbody-content > .wrap > .wp-heading-inline' );
                $('.page-title-action').remove();
                if ( $heading.length ) {
                    $heading.after( actions.join('') );
                }
            });
        </script>
        <?php
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

add_action( 'admin_footer-post.php', 'eshb_add_all_custom_statuses_to_dropdown' );
add_action( 'admin_footer-post-new.php', 'eshb_add_all_custom_statuses_to_dropdown' );

function eshb_add_all_custom_statuses_to_dropdown() {
    global $post;
    if ( $post->post_type !== 'eshb_booking'|| $post->post_type !== 'eshb_payment' ) return;

    $statuses = ESHB_Helper::eshb_get_booking_statuses();

    ?>
    <script>
    jQuery(document).ready(function($){
        <?php foreach ( $statuses as $slug => $label ) : ?>
            var selected = '<?php echo esc_js( $post->post_status ); ?>' === '<?php echo esc_js( $slug ); ?>' ? 'selected="selected"' : '';
            $('#post_status').append('<option value="<?php echo esc_js( $slug ); ?>" ' + selected + '><?php echo esc_js( $label ); ?></option>');
        <?php endforeach; ?>
    });
    </script>
    <?php
}


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