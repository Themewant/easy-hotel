<?php
/**
 * Easy Hotel - PMS module bootstrap.
 *
 * This module is fully self contained. It NEVER writes to the meta keys owned by the
 * booking engine (`eshb_accomodation_metaboxes`, `eshb_booking_metaboxes`), it only reads
 * from them, and it never takes part in the availability calculation. Removing the
 * `include` of this file from easy-hotel.php restores the plugin to its previous behaviour
 * with zero side effects.
 *
 * Architecture (mirrors the room-type / sub-unit split used by mature hotel PMS software):
 *
 *   Layer 1 - required : accommodation (room type) + `total_rooms`  ->  owned by the booking engine
 *   Layer 2 - optional : unit labels + per booking unit assignment  ->  owned by this module
 *
 * Layer 2 is decorative for the booking engine: a booking with no unit assigned is a
 * perfectly valid booking, exactly like it was before this module existed.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( defined( 'ESHB_PMS_VERSION' ) ) {
    return; // Already loaded.
}

define( 'ESHB_PMS_VERSION', '1.0.0' );
define( 'ESHB_PMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ESHB_PMS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Post meta key holding the accommodation unit list.
 *
 * The unit editor lives in a "Rooms" tab inside the engine's own accommodation metabox, so the
 * framework would otherwise store the rows in the engine's serialized array. It does not:
 * `ESHB_PMS_Accommodation_Metabox::extract_units()` moves them into this key on every save, so
 * engine meta stays untouched and the rows survive the module being removed.
 * Shape: array{units: array<int, array{label:string, floor:string}>}
 */
define( 'ESHB_PMS_UNITS_META', 'eshb_pms_units_metaboxes' );

/**
 * Metabox unique key for the per booking unit assignment.
 * Shape: array{units: int[], auto_assign: string} — one slot per booked room inside `units`,
 * where `0` means "not assigned" (the nullable state).
 */
define( 'ESHB_PMS_ASSIGN_META', 'eshb_pms_assignment_metaboxes' );

/**
 * Key holding the unit rows / assignment slots inside both meta arrays. It is also the
 * assignment metabox's field ID; the accommodation side uses the prefixed
 * `ESHB_PMS_Accommodation_Metabox::FIELD_ID` on the form, because that field is rendered
 * inside a metabox owned by the engine.
 */
define( 'ESHB_PMS_UNITS_FIELD', 'units' );

/** Option key holding the module settings. */
define( 'ESHB_PMS_OPTION', 'eshb_pms_options' );

require_once ESHB_PMS_PATH . 'includes/class.pms-units.php';
require_once ESHB_PMS_PATH . 'includes/class.pms-assignment.php';
require_once ESHB_PMS_PATH . 'includes/class.pms-accommodation-metabox.php';
require_once ESHB_PMS_PATH . 'includes/class.pms-booking-metabox.php';
require_once ESHB_PMS_PATH . 'includes/class.pms-rack.php';
require_once ESHB_PMS_PATH . 'includes/class.pms-ajax.php';

/**
 * Module bootstrapper.
 */
final class ESHB_PMS {

    /**
     * Singleton instance.
     *
     * @var ESHB_PMS|null
     */
    private static $instance = null;

    /**
     * Returns the shared instance.
     *
     * @return ESHB_PMS
     */
    public static function instance() {

        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Registers every hook owned by the module.
     */
    private function __construct() {

        /*
         * The metaboxes are declared through the plugin's own settings framework, which
         * collects them on `plugins_loaded` and instantiates them on `init`. Priority 20
         * keeps our declarations after the booking engine's own ones, so on `save_post` the
         * engine has already written `eshb_booking_metaboxes` when we validate against it.
         */
        add_action( 'plugins_loaded', [ ESHB_PMS_Accommodation_Metabox::class, 'register' ], 20 );
        add_action( 'plugins_loaded', [ ESHB_PMS_Booking_Metabox::class, 'register' ], 20 );

        // Room rack (tape chart) admin page.
        ESHB_PMS_Rack::register();

        // Ajax endpoints.
        ESHB_PMS_Ajax::register();

        // Optional auto assignment when a booking is created outside the admin screens.
        add_action( 'added_post_meta', [ $this, 'maybe_auto_assign' ], 20, 4 );
        add_action( 'updated_post_meta', [ $this, 'maybe_auto_assign' ], 20, 4 );

        // Housekeeping of our own meta.
        add_action( 'before_delete_post', [ $this, 'cleanup_booking_meta' ] );
    }

    /**
     * Reads a module setting.
     *
     * @param string $key     Setting key.
     * @param mixed  $default Fallback value.
     *
     * @return mixed
     */
    public static function get_option( $key, $default = '' ) {

        $options = get_option( ESHB_PMS_OPTION, [] );

        if ( ! is_array( $options ) || ! array_key_exists( $key, $options ) ) {
            return $default;
        }

        return $options[ $key ];
    }

    /**
     * Persists a set of module settings.
     *
     * @param array $values Key/value pairs to merge into the stored options.
     */
    public static function update_options( array $values ) {

        $options = get_option( ESHB_PMS_OPTION, [] );
        $options = is_array( $options ) ? $options : [];

        update_option( ESHB_PMS_OPTION, array_merge( $options, $values ) );
    }

    /**
     * Capability required to operate the PMS screens.
     *
     * @return string
     */
    public static function capability() {
        return apply_filters( 'eshb_pms_capability', 'edit_posts' );
    }

    /**
     * Assigns free units to a booking right after the booking engine stored its own meta.
     *
     * Hooked on the meta write instead of `save_post` because the booking engine inserts the
     * post first and the serialized meta afterwards, so on `save_post` the dates are not
     * available yet.
     *
     * @param int    $meta_id    Unused.
     * @param int    $post_id    Booking post ID.
     * @param string $meta_key   Meta key being written.
     * @param mixed  $meta_value Unused.
     */
    public function maybe_auto_assign( $meta_id, $post_id, $meta_key, $meta_value ) {

        if ( 'eshb_booking_metaboxes' !== $meta_key ) {
            return;
        }

        if ( ! ESHB_PMS::get_option( 'auto_assign', 0 ) ) {
            return;
        }

        if ( 'eshb_booking' !== get_post_type( $post_id ) ) {
            return;
        }

        // The row we may have cached earlier in this request is the pre-write one.
        ESHB_PMS_Assignment::flush_cache();

        // Never overwrite a choice made by a human: only empty slots get filled.
        ESHB_PMS_Assignment::auto_assign( (int) $post_id );
    }

    /**
     * Drops the module meta when a booking is permanently deleted.
     *
     * @param int $post_id Post ID being deleted.
     */
    public function cleanup_booking_meta( $post_id ) {

        if ( 'eshb_booking' === get_post_type( $post_id ) ) {
            delete_post_meta( $post_id, ESHB_PMS_ASSIGN_META );
        }
    }
}

ESHB_PMS::instance();
