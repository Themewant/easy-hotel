<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$eshb_settings = get_option( 'eshb_settings' );
add_action( 'wp_enqueue_scripts', 'eshb_dynamic_css' );
function eshb_dynamic_css() {

    $eshb_settings = get_option( 'eshb_settings' );
    $theme_colors = is_array($eshb_settings['theme-colors']) && count($eshb_settings['theme-colors']) > 0 ? $eshb_settings['theme-colors'] : '';
    $primary_color = !empty($theme_colors['primary-color']) ? $theme_colors['primary-color'] : '';
    $secondary_color = !empty($theme_colors['secondary-color']) ? $theme_colors['secondary-color'] : '';
    $territory_color = !empty($theme_colors['territory-color']) ? $theme_colors['territory-color'] : '';
    $success_color = !empty($theme_colors['success-color']) ? $theme_colors['success-color'] : '';
    $danger_color = !empty($theme_colors['danger-color']) ? $theme_colors['danger-color'] : '';
    $dark_color = !empty($theme_colors['dark-color']) ? $theme_colors['dark-color'] : '';
    $text_color = !empty($theme_colors['text-color']) ? $theme_colors['text-color'] : '';
    $white_color = !empty($theme_colors['white-color']) ? $theme_colors['white-color'] : '';
    $border_color = !empty($theme_colors['border-color']) ? $theme_colors['border-color'] : '';

    $calendar_colors = !empty($eshb_settings['calendar-colors']) && is_array($eshb_settings['calendar-colors']) && count($eshb_settings['calendar-colors']) > 0 ? $eshb_settings['calendar-colors'] : '';
    $booked_bg_color = !empty($calendar_colors['booked-bg-color']) ? $calendar_colors['booked-bg-color'] : '#bebcbb';
    $active_bg_color = !empty($calendar_colors['active-bg-color']) ? $calendar_colors['active-bg-color'] : '#ab8965';
    $inrange_bg_color = !empty($calendar_colors['inrange-bg-color']) ? $calendar_colors['inrange-bg-color'] : '#181818';

    $booked_color = !empty($calendar_colors['booked-color']) ? $calendar_colors['booked-color'] : '#181818';
    $active_color = !empty($calendar_colors['active-color']) ? $calendar_colors['active-color'] : '#fff';
    $inrange_color = !empty($calendar_colors['inrange-color']) ? $calendar_colors['inrange-color'] : '#fff';

    $page_padding = !empty($eshb_settings['page-spacing']) ? $eshb_settings['page-spacing'] : '';
    $page_container_size = !empty($eshb_settings['page-container-size']) ? $eshb_settings['page-container-size'] : '';

    $custom_css = "";

    if (!empty($theme_colors)) {
        $custom_css .= "
        :root {
            --eshb-primary-color: " . esc_attr($primary_color) . ";
            --eshb-secondary-color: " . esc_attr($secondary_color) . ";
            --eshb-territory-color: " . esc_attr($territory_color) . ";
            --eshb-success-color: " . esc_attr($success_color) . ";
            --eshb-danger-color: " . esc_attr($danger_color) . ";
            --eshb-dark-color: " . esc_attr($dark_color) . ";
            --eshb-text-color: " . esc_attr($text_color) . ";
            --eshb-white-color: " . esc_attr($white_color) . ";
            --eshb-border-color: " . esc_attr($border_color) . ";
        }
        ";
    }

    if (!empty($calendar_colors)) {
        $custom_css .= "
        :root {
            --eshb-booked-bg-color: " . esc_attr($booked_bg_color) . ";
            --eshb-active-bg-color: " . esc_attr($active_bg_color) . ";
            --eshb-inrange-bg-color: " . esc_attr($inrange_bg_color) . ";
            --eshb-booked-color: " . esc_attr($booked_color) . ";
            --eshb-active-color: " . esc_attr($active_color) . ";
            --eshb-inrange-color: " . esc_attr($inrange_color) . ";
        }
        ";
    }


    if(!empty($page_padding)) {
        $custom_css .= "
        .eshb-archive-wrapper.eshb-container, .eshb-details-page .eshb-container {";
        
        if(!empty($page_padding['top'])) {
            $custom_css .= "padding-top: " . esc_attr($page_padding['top']) . "px;";
        }
        if(!empty($page_padding['bottom'])) {
            $custom_css .= "padding-bottom: " . esc_attr($page_padding['bottom']) . "px;";
        }

        $custom_css .= "
        }
        ";
    }

    if(!empty($page_container_size)) {
        $custom_css .= "
        .eshb-container {";
        
        if(!empty($page_container_size['width'])) {
            $custom_css .= "max-width: " . esc_attr($page_container_size['width']) . "px;";
        }

        $custom_css .= "
        }
        ";
    }

    wp_add_inline_style('eshb-style', $custom_css);
}



add_action( 'elementor/editor/after_enqueue_scripts', function() {
    ?>
    <style>
        .elementor-panel .easy-hotel-widget-icon {
            background-image: url('<?php echo esc_url( ESHB_DIR_URL . 'public/assets/img/easy-hotel-icon.png' ); ?>');
            background-size: cover;
            background-position: center;
            width: 20px;
            height: 20px;
            display: inline-block;
        }

        .elementor-panel .easy-hotel-widget-icon:before {
            content: "";
            display: none;
        }
    </style>
    <?php
});