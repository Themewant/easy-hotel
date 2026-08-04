<?php
/**
 * Room rack (tape chart) screen.
 *
 * One row per physical unit, one column per night, plus a dedicated "Unassigned" lane so
 * that bookings without a room are visible instead of hidden. That lane is the visual proof
 * that the assignment layer is optional: the hotel can run with everything sitting in it.
 *
 * @package Easy_Hotel\PMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Renders the room rack admin page.
 */
class ESHB_PMS_Rack {

    /**
     * Admin page slug.
     */
    const PAGE = 'eshb-pms-rack';

    /**
     * Selectable window lengths, in nights.
     */
    const WINDOWS = [ 7, 14, 30, 60 ];

    /**
     * Hooks the screen in.
     */
    public static function register() {
        add_action( 'admin_menu', [ __CLASS__, 'add_menu' ], 20 );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'maybe_enqueue_assets' ] );
        add_action( 'admin_post_eshb_pms_save_settings', [ __CLASS__, 'save_settings' ] );
    }

    /**
     * Adds the submenu entry under Easy Hotel.
     */
    public static function add_menu() {

        add_submenu_page(
            'edit.php?post_type=' . ESHB_PMS_Units::POST_TYPE,
            esc_html__( 'Room Rack', 'easy-hotel' ),
            esc_html__( 'Room Rack', 'easy-hotel' ),
            ESHB_PMS::capability(),
            self::PAGE,
            [ __CLASS__, 'render' ]
        );

        // Hidden alias so `admin.php?page=eshb-pms-rack` links keep working.
        add_submenu_page(
            'admin.php',
            esc_html__( 'Room Rack', 'easy-hotel' ),
            esc_html__( 'Room Rack', 'easy-hotel' ),
            ESHB_PMS::capability(),
            self::PAGE,
            [ __CLASS__, 'render' ]
        );
    }

    /**
     * Enqueues the module assets on the screens that need them.
     *
     * @param string $hook Current admin page hook.
     */
    public static function maybe_enqueue_assets( $hook ) {

        $screen    = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $post_type = $screen && isset( $screen->post_type ) ? $screen->post_type : '';

        $is_pms_screen = (
            false !== strpos( (string) $hook, self::PAGE )
            || in_array( $post_type, [ ESHB_PMS_Units::POST_TYPE, ESHB_PMS_Assignment::POST_TYPE ], true )
        );

        if ( ! $is_pms_screen ) {
            return;
        }

        self::enqueue_assets();
    }

    /**
     * Enqueues the module stylesheet and script.
     */
    public static function enqueue_assets() {

        /*
         * Depends on the settings framework's stylesheet (handle `easy-hotel`) so our rules
         * are always printed after it — the metabox callbacks build on its `csf-*` classes.
         */
        wp_enqueue_style(
            'eshb-pms-admin',
            ESHB_PMS_URL . 'assets/css/pms-admin.css',
            wp_style_is( 'easy-hotel', 'registered' ) ? [ 'easy-hotel' ] : [],
            ESHB_PMS_VERSION
        );

        wp_enqueue_script(
            'eshb-pms-admin',
            ESHB_PMS_URL . 'assets/js/pms-admin.js',
            [ 'jquery' ],
            ESHB_PMS_VERSION,
            true
        );

        wp_localize_script(
            'eshb-pms-admin',
            'eshbPms',
            [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( ESHB_PMS_Ajax::NONCE ),
                'i18n'    => [
                    'assignTo'   => esc_html__( 'Assign to', 'easy-hotel' ),
                    'unassign'   => esc_html__( '— Not assigned —', 'easy-hotel' ),
                    'noFreeUnit' => esc_html__( 'No free unit on those dates.', 'easy-hotel' ),
                    'saving'     => esc_html__( 'Saving…', 'easy-hotel' ),
                    'error'      => esc_html__( 'Something went wrong.', 'easy-hotel' ),
                    'confirmAll' => esc_html__( 'Auto-assign every unassigned booking in this window?', 'easy-hotel' ),
                ],
            ]
        );
    }

    /**
     * Saves the module settings posted from the rack screen.
     */
    public static function save_settings() {

        if ( ! current_user_can( ESHB_PMS::capability() ) ) {
            wp_die( esc_html__( 'You are not allowed to do that.', 'easy-hotel' ) );
        }

        check_admin_referer( 'eshb_pms_settings' );

        ESHB_PMS::update_options(
            [
                'auto_assign' => empty( $_POST['auto_assign'] ) ? 0 : 1,
            ]
        );

        $redirect = isset( $_POST['_wp_http_referer'] )
            ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) )
            : admin_url( 'admin.php?page=' . self::PAGE );

        wp_safe_redirect( add_query_arg( 'eshb_pms_saved', 1, $redirect ) );
        exit;
    }

    /**
     * Renders the rack screen.
     */
    public static function render() {

        if ( ! current_user_can( ESHB_PMS::capability() ) ) {
            wp_die( esc_html__( 'You are not allowed to view this page.', 'easy-hotel' ) );
        }

        $accommodations = ESHB_PMS_Units::get_accommodations();

        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read only filters.
        $selected = isset( $_GET['accommodation'] ) ? (int) $_GET['accommodation'] : 0;
        $from     = isset( $_GET['from'] ) ? sanitize_text_field( wp_unslash( $_GET['from'] ) ) : '';
        $nights   = isset( $_GET['nights'] ) ? (int) $_GET['nights'] : 14;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        if ( ! $selected || ! isset( $accommodations[ $selected ] ) ) {
            $selected = (int) key( $accommodations );
        }

        if ( ! in_array( $nights, self::WINDOWS, true ) ) {
            $nights = 14;
        }

        $window = self::build_window( $from, $nights );
        $saved  = ! empty( $_GET['eshb_pms_saved'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <div class="wrap eshb-pms-wrap">

            <h1 class="wp-heading-inline"><?php esc_html_e( 'Room Rack', 'easy-hotel' ); ?></h1>

            <?php if ( $saved ) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e( 'PMS settings saved.', 'easy-hotel' ); ?></p>
                </div>
            <?php endif; ?>

            <p class="eshb-pms-hint">
                <?php esc_html_e( 'Assigning a room here is purely operational. Availability, pricing and the front-end booking flow are untouched by this screen — a booking left in the "Unassigned" lane is still a valid, confirmed booking.', 'easy-hotel' ); ?>
            </p>

            <?php if ( empty( $accommodations ) ) : ?>
                <div class="notice notice-warning">
                    <p><?php esc_html_e( 'Create an accommodation first.', 'easy-hotel' ); ?></p>
                </div>
            <?php else : ?>
                <?php self::render_filters( $accommodations, $selected, $window, $nights ); ?>
                <?php self::render_chart( $selected, $window ); ?>
                <?php self::render_settings_form(); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renders the filter bar.
     *
     * @param array    $accommodations Accommodation list.
     * @param int      $selected       Selected accommodation ID.
     * @param string[] $window         Date window.
     * @param int      $nights         Window length.
     */
    protected static function render_filters( array $accommodations, $selected, array $window, $nights ) {

        $first = reset( $window );
        ?>
        <form method="get" class="eshb-pms-filters">
            <input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE ); ?>" />
            <?php if ( isset( $_GET['post_type'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
                <input type="hidden" name="post_type" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_GET['post_type'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>" />
            <?php endif; ?>

            <label for="eshb-pms-accommodation"><?php esc_html_e( 'Accommodation', 'easy-hotel' ); ?></label>
            <select name="accommodation" id="eshb-pms-accommodation">
                <?php foreach ( $accommodations as $id => $title ) : ?>
                    <option value="<?php echo (int) $id; ?>" <?php selected( $selected, $id ); ?>>
                        <?php echo esc_html( $title ); ?>
                        (<?php echo (int) ESHB_PMS_Units::get_total_units( $id ); ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="eshb-pms-from"><?php esc_html_e( 'From', 'easy-hotel' ); ?></label>
            <input type="date" name="from" id="eshb-pms-from" value="<?php echo esc_attr( $first ); ?>" />

            <label for="eshb-pms-nights"><?php esc_html_e( 'Nights', 'easy-hotel' ); ?></label>
            <select name="nights" id="eshb-pms-nights">
                <?php foreach ( self::WINDOWS as $option ) : ?>
                    <option value="<?php echo (int) $option; ?>" <?php selected( $nights, $option ); ?>><?php echo (int) $option; ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button button-primary"><?php esc_html_e( 'Show', 'easy-hotel' ); ?></button>

            <button type="button" class="button eshb-pms-auto-all"
                data-accommodation="<?php echo (int) $selected; ?>"
                data-from="<?php echo esc_attr( $first ); ?>"
                data-to="<?php echo esc_attr( end( $window ) ); ?>">
                <?php esc_html_e( 'Auto-assign window', 'easy-hotel' ); ?>
            </button>
        </form>
        <?php
    }

    /**
     * Renders the tape chart itself.
     *
     * @param int      $accommodation_id Accommodation post ID.
     * @param string[] $window           Date window.
     */
    protected static function render_chart( $accommodation_id, array $window ) {

        $total  = ESHB_PMS_Units::get_total_units( $accommodation_id );
        $units  = ESHB_PMS_Units::get_units( $accommodation_id );
        $from   = reset( $window );
        $to     = end( $window );
        $today  = current_time( 'Y-m-d' );
        $capped = $total > ESHB_PMS_Units::MAX_RENDERED_UNITS;

        $bookings = ESHB_PMS_Assignment::get_bookings_for( $accommodation_id, $from, $to );
        $lanes    = self::build_lanes( $bookings, $window );

        if ( $capped ) {
            printf(
                '<div class="notice notice-warning"><p>%s</p></div>',
                sprintf(
                    /* translators: %d: maximum rendered units. */
                    esc_html__( 'This accommodation has more units than the rack can draw. Only the first %d are shown.', 'easy-hotel' ),
                    (int) ESHB_PMS_Units::MAX_RENDERED_UNITS
                )
            );
        }
        ?>
        <div class="eshb-pms-chart-scroll">
            <table class="eshb-pms-chart">
                <thead>
                    <tr>
                        <th class="eshb-pms-unit-head"><?php esc_html_e( 'Unit', 'easy-hotel' ); ?></th>
                        <?php foreach ( $window as $date ) : ?>
                            <?php
                            $timestamp = strtotime( $date );
                            $classes   = [ 'eshb-pms-day' ];

                            if ( $date === $today ) {
                                $classes[] = 'is-today';
                            }

                            if ( in_array( (int) gmdate( 'N', $timestamp ), [ 6, 7 ], true ) ) {
                                $classes[] = 'is-weekend';
                            }
                            ?>
                            <th class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
                                <span class="eshb-pms-day-dow"><?php echo esc_html( gmdate( 'D', $timestamp ) ); ?></span>
                                <span class="eshb-pms-day-num"><?php echo esc_html( gmdate( 'j', $timestamp ) ); ?></span>
                                <span class="eshb-pms-day-mon"><?php echo esc_html( gmdate( 'M', $timestamp ) ); ?></span>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                $render_total = $capped ? ESHB_PMS_Units::MAX_RENDERED_UNITS : $total;

                for ( $index = 1; $index <= $render_total; $index++ ) :
                    $unit = $units[ $index ];
                    ?>
                    <tr class="eshb-pms-unit-row">
                        <th class="eshb-pms-unit-head" scope="row">
                            <span class="eshb-pms-unit-label"><?php echo esc_html( $unit['label'] ); ?></span>
                            <?php if ( '' !== $unit['floor'] ) : ?>
                                <span class="eshb-pms-unit-floor"><?php echo esc_html( $unit['floor'] ); ?></span>
                            <?php endif; ?>
                        </th>
                        <?php self::render_lane( isset( $lanes['units'][ $index ] ) ? $lanes['units'][ $index ] : [], $window, $today ); ?>
                    </tr>
                <?php endfor; ?>

                <?php foreach ( $lanes['unassigned'] as $lane_index => $lane ) : ?>
                    <tr class="eshb-pms-unit-row eshb-pms-unassigned-row">
                        <th class="eshb-pms-unit-head" scope="row">
                            <?php if ( 0 === $lane_index ) : ?>
                                <span class="eshb-pms-unit-label eshb-pms-unit-label--warn"><?php esc_html_e( 'Unassigned', 'easy-hotel' ); ?></span>
                            <?php endif; ?>
                        </th>
                        <?php self::render_lane( $lane, $window, $today ); ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Renders one row of cells.
     *
     * @param array    $segments Segments keyed by their starting column offset.
     * @param string[] $window   Date window.
     * @param string   $today    Today's date.
     */
    protected static function render_lane( array $segments, array $window, $today ) {

        $skip = 0;

        foreach ( $window as $offset => $date ) {

            if ( $skip > 0 ) {
                $skip--;
                continue;
            }

            if ( ! isset( $segments[ $offset ] ) ) {
                $classes = [ 'eshb-pms-cell', 'is-free' ];

                if ( $date === $today ) {
                    $classes[] = 'is-today';
                }

                printf( '<td class="%s"></td>', esc_attr( implode( ' ', $classes ) ) );
                continue;
            }

            $segment = $segments[ $offset ];
            $skip    = $segment['span'] - 1;

            self::render_segment( $segment );
        }
    }

    /**
     * Renders a single booking bar.
     *
     * @param array $segment Segment data.
     */
    protected static function render_segment( array $segment ) {

        $booking = $segment['booking'];
        $classes = [ 'eshb-pms-cell', 'is-booked', 'status-' . sanitize_html_class( $booking['status'] ) ];

        if ( $segment['unassigned'] ) {
            $classes[] = 'is-unassigned';
        }

        if ( $segment['starts'] ) {
            $classes[] = 'starts';
        }

        if ( $segment['ends'] ) {
            $classes[] = 'ends';
        }

        $guest = '' !== $booking['guest'] ? $booking['guest'] : sprintf( '#%d', $booking['id'] );

        $tooltip = sprintf(
            /* translators: 1: booking ID, 2: guest, 3: check-in, 4: check-out, 5: status. */
            esc_html__( 'Booking #%1$d — %2$s — %3$s → %4$s — %5$s', 'easy-hotel' ),
            $booking['id'],
            $guest,
            $booking['start_date'],
            $booking['end_date'],
            $booking['status']
        );
        ?>
        <td class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" colspan="<?php echo (int) $segment['span']; ?>">
            <div class="eshb-pms-bar"
                data-booking="<?php echo (int) $booking['id']; ?>"
                data-slot="<?php echo (int) $segment['slot']; ?>"
                data-unit="<?php echo (int) $segment['unit']; ?>"
                title="<?php echo esc_attr( $tooltip ); ?>">
                <span class="eshb-pms-bar-guest"><?php echo esc_html( $guest ); ?></span>
                <a class="eshb-pms-bar-edit" href="<?php echo esc_url( get_edit_post_link( $booking['id'] ) ); ?>">#<?php echo (int) $booking['id']; ?></a>
            </div>
        </td>
        <?php
    }

    /**
     * Turns bookings into drawable segments, split between unit rows and unassigned lanes.
     *
     * @param array    $bookings Bookings overlapping the window.
     * @param string[] $window   Date window.
     *
     * @return array{units: array<int, array>, unassigned: array<int, array>}
     */
    protected static function build_lanes( array $bookings, array $window ) {

        $offsets    = array_flip( $window );
        $units      = [];
        $loose      = [];

        foreach ( $bookings as $booking_id => $booking ) {

            $slots = ESHB_PMS_Assignment::get_assignment( $booking_id );

            foreach ( $slots as $slot => $unit ) {

                $segment = self::build_segment( $booking, $slot, $unit, $offsets, $window );

                if ( ! $segment ) {
                    continue;
                }

                if ( ESHB_PMS_Assignment::UNASSIGNED === $unit ) {
                    $loose[] = $segment;
                    continue;
                }

                $units[ $unit ][ $segment['offset'] ] = $segment;
            }
        }

        return [
            'units'      => $units,
            'unassigned' => self::pack_lanes( $loose ),
        ];
    }

    /**
     * Builds one segment, clipped to the visible window.
     *
     * @param array    $booking Booking record.
     * @param int      $slot    0 based slot index.
     * @param int      $unit    Assigned unit, or 0.
     * @param array    $offsets `Y-m-d` => column offset.
     * @param string[] $window  Date window.
     *
     * @return array|null
     */
    protected static function build_segment( array $booking, $slot, $unit, array $offsets, array $window ) {

        $visible = array_values( array_intersect( $booking['dates'], $window ) );

        if ( empty( $visible ) ) {
            return null;
        }

        $first = reset( $visible );
        $last  = end( $visible );

        return [
            'offset'     => (int) $offsets[ $first ],
            'span'       => count( $visible ),
            'slot'       => (int) $slot,
            'unit'       => (int) $unit,
            'booking'    => $booking,
            'unassigned' => ( ESHB_PMS_Assignment::UNASSIGNED === (int) $unit ),
            'starts'     => ( $first === reset( $booking['dates'] ) ),
            'ends'       => ( $last === end( $booking['dates'] ) ),
        ];
    }

    /**
     * Packs overlapping segments into as few non-overlapping lanes as possible.
     *
     * @param array $segments Flat segment list.
     *
     * @return array<int, array<int, array>> Lanes, each keyed by starting offset.
     */
    protected static function pack_lanes( array $segments ) {

        if ( empty( $segments ) ) {
            return [ [] ]; // Always draw the unassigned lane, even when empty.
        }

        usort(
            $segments,
            function ( $a, $b ) {
                return $a['offset'] <=> $b['offset'];
            }
        );

        $lanes = [];

        foreach ( $segments as $segment ) {

            $placed = false;

            foreach ( $lanes as $lane_index => $lane ) {

                if ( self::lane_has_room( $lane, $segment ) ) {
                    $lanes[ $lane_index ][ $segment['offset'] ] = $segment;
                    $placed                                     = true;
                    break;
                }
            }

            if ( ! $placed ) {
                $lanes[] = [ $segment['offset'] => $segment ];
            }
        }

        return $lanes;
    }

    /**
     * Whether a segment fits into a lane without overlapping.
     *
     * @param array $lane    Lane segments keyed by offset.
     * @param array $segment Candidate segment.
     *
     * @return bool
     */
    protected static function lane_has_room( array $lane, array $segment ) {

        $start = $segment['offset'];
        $end   = $segment['offset'] + $segment['span'] - 1;

        foreach ( $lane as $existing ) {

            $existing_start = $existing['offset'];
            $existing_end   = $existing['offset'] + $existing['span'] - 1;

            if ( $start <= $existing_end && $end >= $existing_start ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Builds the list of dates shown by the chart.
     *
     * @param string $from   `Y-m-d` start, empty for today.
     * @param int    $nights Number of columns.
     *
     * @return string[]
     */
    protected static function build_window( $from, $nights ) {

        $start = false;

        if ( '' !== $from ) {
            $start = DateTime::createFromFormat( 'Y-m-d', $from );
        }

        if ( ! $start instanceof DateTime ) {
            $start = new DateTime( current_time( 'Y-m-d' ) );
        }

        $start->setTime( 0, 0, 0 );

        $window = [];

        for ( $day = 0; $day < $nights; $day++ ) {
            $window[] = $start->format( 'Y-m-d' );
            $start->modify( '+1 day' );
        }

        return $window;
    }

    /**
     * Renders the small settings form shown under the chart.
     */
    protected static function render_settings_form() {
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="eshb-pms-settings">
            <?php wp_nonce_field( 'eshb_pms_settings' ); ?>
            <input type="hidden" name="action" value="eshb_pms_save_settings" />

            <h2><?php esc_html_e( 'PMS settings', 'easy-hotel' ); ?></h2>

            <label>
                <input type="checkbox" name="auto_assign" value="1" <?php checked( (int) ESHB_PMS::get_option( 'auto_assign', 0 ), 1 ); ?> />
                <?php esc_html_e( 'Automatically assign a free unit when a booking is created or updated', 'easy-hotel' ); ?>
            </label>

            <p class="eshb-pms-hint">
                <?php esc_html_e( 'Off by default. When off, every booking lands in the Unassigned lane and staff pick the room manually.', 'easy-hotel' ); ?>
            </p>

            <p><button type="submit" class="button button-primary"><?php esc_html_e( 'Save settings', 'easy-hotel' ); ?></button></p>
        </form>
        <?php
    }
}
