<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
/*
 * Front-end assets.
 *
 * Styles load on every front-end page, as before, so the plugin's markup is
 * always styled wherever a theme or builder prints it.
 *
 * Scripts are registered on every request (cheap), but only enqueued where
 * the plugin actually outputs something:
 *  - up front, on pages we can recognise (accommodation pages, plugin pages,
 *    content with our shortcodes/blocks/page-builder widgets, cart/checkout);
 *  - otherwise the moment one of our shortcodes, blocks or widgets renders
 *    (scripts are all in the footer, so enqueuing during render still works).
 *
 * Sites that print plugin markup some other way (e.g. a custom theme header)
 * can load everything everywhere again with:
 *     add_filter( 'eshb_load_frontend_assets', '__return_true' );
 */
// This file is included from ESHB_MAIN::includes() on `init` priority 11, so
// an `init` priority 5 callback added here would never run (WordPress does
// not go back to a priority it has passed). Register right away instead;
// keep the hook for the case where the file is loaded before `init`.
if ( did_action( 'init' ) ) {
    eshb_register_frontend_assets();
} else {
    add_action( 'init', 'eshb_register_frontend_assets', 5 );
}
add_action( 'wp_enqueue_scripts', 'eshb_wp_enqueue_scripts', 999 );
add_filter( 'do_shortcode_tag', 'eshb_enqueue_assets_for_shortcode', 10, 2 );
add_filter( 'render_block', 'eshb_enqueue_assets_for_block', 10, 2 );
add_action( 'elementor/frontend/widget/before_render', 'eshb_enqueue_assets_for_elementor_widget' );

function eshb_register_frontend_assets() {

    $css_version = filemtime( ESHB_PL_PATH . 'public/assets/css/public.css' );

    wp_register_style( 'eshb-daterangepicker-style', ESHB_PL_URL . 'public/assets/css/date-range-picker.css', array(), $css_version );
    wp_register_style( 'eshb-style', ESHB_PL_URL . 'public/assets/css/public.css', array(), $css_version );
    // Show a formatted, translated date on top of the machine (Y-m-d) date input.
    // The machine input stays in normal flow (so the calendar opens exactly as before);
    // only its text is hidden. The display overlay is click-through (pointer-events:none).
    wp_add_inline_style( 'eshb-style', '.eshb-date-field{position:relative;display:block;width:100%}.eshb-date-field .eshb-date-machine{color:transparent;-webkit-text-fill-color:transparent;caret-color:transparent}.eshb-date-field .eshb-date-machine::selection{background:transparent}.eshb-date-field .eshb-date-display{position:absolute;top:0;left:0;width:100%;height:100%;margin:0;pointer-events:none;background:transparent;border-color:transparent;box-shadow:none}.daterangepicker td.active.start-date{pointer-events:none;cursor:not-allowed}' );
    wp_register_style( 'eshb-fontawesome-style', ESHB_PL_URL . 'public/assets/css/fontawesome.all.min.css', array(), '7.2.0', 'all' );
    wp_register_style( 'swiper', ESHB_PL_URL . 'public/assets/css/swiper-bundle.min.css', array(), '12.1.4', 'all' );
    wp_register_script( 'eshb-date-range-picker-js', ESHB_PL_URL . 'public/assets/js/date-range-picker.js', array('jquery'),'3.1',true );
    // In the footer: nothing calls Swiper before the page has loaded.
    wp_register_script( 'eshb-swiper', ESHB_PL_URL . 'public/assets/js/swiper-bundle.min.js', array(), '12.1.4', true );
    wp_register_script( 'eshb-swiper-init', ESHB_PL_URL . 'public/assets/js/swiper-init.js', array( 'eshb-swiper' ), ESHB_VERSION, true );
    wp_register_script( 'eshb-public-script', ESHB_PL_URL . 'public/assets/js/public.js', array(), ESHB_VERSION, true );
    wp_register_script( 'eshb-booking-script', ESHB_PL_URL . 'public/assets/js/booking.js', array(), ESHB_VERSION, true );
}

function eshb_wp_enqueue_scripts() {
    eshb_enqueue_frontend_styles();

    if ( eshb_page_needs_frontend_assets() ) {
        eshb_enqueue_frontend_assets();
    }
}

/**
 * Whether the current page is known to show plugin output.
 *
 * @return bool
 */
function eshb_page_needs_frontend_assets() {

    $force = apply_filters( 'eshb_load_frontend_assets', null );
    if ( null !== $force ) {
        return (bool) $force;
    }

    // Page-builder editors render widgets live.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    if ( isset( $_GET['elementor-preview'] ) || ( function_exists( 'bricks_is_builder' ) && bricks_is_builder() ) ) {
        return true;
    }

    if ( is_singular( 'eshb_accomodation' ) || is_post_type_archive( 'eshb_accomodation' ) || is_tax( get_object_taxonomies( 'eshb_accomodation' ) ) ) {
        return true;
    }

    // WooCommerce cart / checkout: booking items and the reservation hold notice.
    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        return true;
    }

    if ( ! is_singular() ) {
        return eshb_elementor_locations_use_plugin();
    }

    $post_id = get_queried_object_id();

    // Archive / search-result / account pages chosen in the settings (or a translation of them).
    $settings = get_option( 'eshb_settings', array() );
    $pages    = array_filter( array_map( 'absint', array(
        $settings['archive-page'] ?? 0,
        $settings['search-result-page'] ?? 0,
        $settings['account-page'] ?? 0,
    ) ) );
    if ( $pages && in_array( (int) ESHB_Helper::get_main_post_id_for_translated( $post_id ), $pages, true ) ) {
        return true;
    }

    if ( eshb_content_uses_plugin( $post_id ) ) {
        return true;
    }

    return eshb_elementor_locations_use_plugin();
}

/**
 * Whether a post's content, Elementor data or Bricks data contains one of
 * our shortcodes, blocks or widgets (all are prefixed "eshb" / "easy-hotel/").
 *
 * @param int $post_id
 * @return bool
 */
function eshb_content_uses_plugin( $post_id ) {
    $content = (string) get_post_field( 'post_content', $post_id );
    if ( false !== strpos( $content, '[eshb_' ) || false !== strpos( $content, '<!-- wp:easy-hotel/' ) ) {
        return true;
    }

    $elementor = get_post_meta( $post_id, '_elementor_data', true );
    if ( is_string( $elementor ) && false !== strpos( $elementor, '"widgetType":"eshb' ) ) {
        return true;
    }

    foreach ( array( '_bricks_page_content_2', '_bricks_page_header_2', '_bricks_page_footer_2' ) as $bricks_key ) {
        $bricks = get_post_meta( $post_id, $bricks_key, true );
        if ( ! empty( $bricks ) && false !== strpos( maybe_serialize( $bricks ), 'eshb' ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Elementor Pro theme-builder header/footer holding one of our widgets
 * (e.g. a search form in the site header).
 *
 * @return bool
 */
function eshb_elementor_locations_use_plugin() {
    if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
        return false;
    }
    try {
        $conditions = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager();
        foreach ( array( 'header', 'footer' ) as $location ) {
            foreach ( (array) $conditions->get_documents_for_location( $location ) as $document_id => $document ) {
                if ( eshb_content_uses_plugin( (int) $document_id ) ) {
                    return true;
                }
            }
        }
    } catch ( \Throwable $e ) {
        return false;
    }
    return false;
}

function eshb_enqueue_assets_for_shortcode( $output, $tag ) {
    if ( 0 === strpos( (string) $tag, 'eshb_' ) ) {
        eshb_enqueue_frontend_assets();
    }
    return $output;
}

function eshb_enqueue_assets_for_block( $block_content, $block ) {
    if ( ! empty( $block['blockName'] ) && 0 === strpos( $block['blockName'], 'easy-hotel/' ) ) {
        eshb_enqueue_frontend_assets();
    }
    return $block_content;
}

function eshb_enqueue_assets_for_elementor_widget( $widget ) {
    if ( is_object( $widget ) && method_exists( $widget, 'get_name' ) && 0 === strpos( (string) $widget->get_name(), 'eshb' ) ) {
        eshb_enqueue_frontend_assets();
    }
}

/**
 * Front-end styles, on every front-end page.
 */
function eshb_enqueue_frontend_styles() {
    if ( is_admin() ) {
        return;
    }
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'eshb-daterangepicker-style' );
    wp_enqueue_style( 'eshb-style' );
    wp_enqueue_style( 'eshb-fontawesome-style' );
    wp_enqueue_style( 'swiper' );
}

/**
 * Enqueue the front-end assets and their script data. Safe to call more
 * than once and from inside a render callback.
 */
function eshb_enqueue_frontend_assets() {
    static $done = false;
    if ( $done || is_admin() ) {
        return;
    }
    $done = true;

    if ( ! wp_script_is( 'eshb-booking-script', 'registered' ) ) {
        eshb_register_frontend_assets();
    }

    eshb_enqueue_frontend_styles();
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'moment' );
    wp_enqueue_script( 'eshb-date-range-picker-js' );
    wp_enqueue_script( 'eshb-swiper' );
    wp_enqueue_script( 'eshb-swiper-init' );
    wp_enqueue_script( 'eshb-public-script' );
    wp_enqueue_script( 'eshb-booking-script' );

     // Get WordPress current locale
     $locale = get_locale();
     $eshb_settings = get_option('eshb_settings');
     $apply_text_default = isset($eshb_settings['string_apply']) && !empty($eshb_settings['string_apply']) ? $eshb_settings['string_apply'] : '';
     $cancel_text_default = isset($eshb_settings['string_cancel']) && !empty($eshb_settings['string_cancel']) ? $eshb_settings['string_cancel'] : '';

    // The page's own accommodation (not whichever post a widget loop is on
    // when this runs during rendering).
    ESHB_Helper::eshb_set_accomodation_localize( is_singular() ? get_queried_object_id() : null );

    // Cart blocking notice config for JS injection
    $eshb_notice_msg = ! empty( $eshb_settings['cart-blocking-notice-msg'] )
        ? esc_html( $eshb_settings['cart-blocking-notice-msg'] )
        : esc_html__( 'Your reservation is held for', 'easy-hotel' );
    wp_localize_script( 'eshb-booking-script', 'eshb_cart_notice', [
        'enabled' => ! empty( $eshb_settings['cart-blocking-switcher'] ) ? '1' : '0',
        'msg'     => $eshb_notice_msg,
    ] );

    wp_localize_script('eshb-public-script', 'eshb_rest', [
        'root'  => esc_url(rest_url()),
        'nonce' => wp_create_nonce('wp_rest')
    ]);

     // Convert the WordPress "date_format" (Settings > General) into a moment.js format
     // so the calendar can display dates exactly as WordPress does, per the site locale.
     // The conversion has to walk the format token by token rather than character by
     // character: locales escape literal words with a backslash — Spanish ships
     // "j \d\e F \d\e Y" — and a plain strtr() rewrites the "de" inside those escapes
     // into date tokens, printing "4 DDe agosto DDe 2026".
     $eshb_moment_format = ESHB_Helper::eshb_date_format_to_moment( get_option( 'date_format', 'F j, Y' ) );

     // Translated month + short weekday names (from WordPress translations, so they are
     // guaranteed localized even when moment.js has no bundled locale on the page).
     // Each sample instant is built at local noon in the site timezone: wp_date() renders
     // in the site timezone, so a UTC-midnight sample would roll back to the previous
     // day/month on any timezone behind UTC and shift the whole array by one.
     $eshb_tz = wp_timezone();

     $eshb_months = array();
     for ( $m = 1; $m <= 12; $m++ ) {
         $eshb_month_sample = new DateTime( sprintf( '2025-%02d-15 12:00:00', $m ), $eshb_tz );
         $eshb_months[] = wp_date( 'F', $eshb_month_sample->getTimestamp() );
     }

     $eshb_weekdays_short = array(); // Sunday-first to match moment.js weekdaysShort()
     $eshb_weekday_sample = new DateTime( '2025-01-05 12:00:00', $eshb_tz ); // a Sunday
     for ( $d = 0; $d < 7; $d++ ) {
         $eshb_weekdays_short[] = wp_date( 'D', $eshb_weekday_sample->getTimestamp() );
         $eshb_weekday_sample->modify( '+1 day' );
     }

     // moment.monthsShort() drives the calendar header; without it the header falls back
     // to moment's built-in English names regardless of the site locale.
     $eshb_months_short = array();
     for ( $m = 1; $m <= 12; $m++ ) {
         $eshb_month_sample = new DateTime( sprintf( '2025-%02d-15 12:00:00', $m ), $eshb_tz );
         $eshb_months_short[] = wp_date( 'M', $eshb_month_sample->getTimestamp() );
     }

     // Prepare translations dynamically based on current locale
     $translations = [
         'displayFormat'     => $eshb_moment_format,
         'locale'            => str_replace( '_', '-', strtolower( $locale ) ),
         'months'            => $eshb_months,
         'monthsShort'       => $eshb_months_short,
         'weekdaysShort'     => $eshb_weekdays_short,
         'applyLabel'        => !empty($apply_text_default) ? eshb_get_translated_string($apply_text_default) : __('Apply', 'easy-hotel'),
         'cancelLabel'       => !empty($cancel_text_default) ? eshb_get_translated_string($cancel_text_default) : __('Cancel', 'easy-hotel'),
         'BookedTooltip'       => __('Already Booked!', 'easy-hotel'),
         'holidayTooltip'       => __('This is Holiday!', 'easy-hotel'),
         'fromLabel'         => __('From', 'easy-hotel'),
         'toLabel'           => __('To', 'easy-hotel'),
         'customRangeLabel'  => __('Custom Range', 'easy-hotel'),
         'weekLabel'         => __('W', 'easy-hotel'),
         'firstDay'          => get_option('start_of_week'), // Get WordPress's start of the week setting
         'currentLocale'     => $locale, // Pass current locale for debugging or further use
     ];

     // Pass translations to JavaScript
     wp_localize_script('eshb-date-range-picker-js', 'eshb_daterangepicker_i18n', $translations);
}



/**
 * Hand a slider's Swiper options to public/assets/js/swiper-init.js through a hidden
 * data attribute, so widgets do not need an inline <script> of their own.
 *
 * @param string $selector CSS selector of the .swiper element to start.
 * @param array  $options  Swiper options.
 */
function eshb_print_swiper_config( $selector, $options ) {
    if ( ! wp_script_is( 'eshb-swiper-init', 'registered' ) ) {
        eshb_register_frontend_assets();
    }
    wp_enqueue_script( 'eshb-swiper-init' );

    printf(
        '<div class="eshb-swiper-config" hidden data-eshb-swiper="%s"></div>',
        esc_attr( wp_json_encode( array( 'selector' => $selector, 'options' => $options ) ) )
    );
}
