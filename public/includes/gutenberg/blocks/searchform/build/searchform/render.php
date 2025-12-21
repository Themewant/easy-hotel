<?php
/**
 * PHP file to use when rendering the `easy-hotel/searchform` block on the front-end.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#render
 */


$attributes = isset( $attributes ) ? $attributes : [];
$bg_colors = isset( $attributes['customBackgroundColor'] ) ? $attributes['customBackgroundColor'] : '';

$style = '';
if ( ! empty( $bg_colors ) ) {
    $style = 'style="background-color: ' . esc_attr( $bg_colors ) . ';"';
}

$output = do_shortcode( '[eshb_search_form]' );

if ( ! empty( $style ) ) {
    // Inject custom background style into the container with class eshb-search
    $output = preg_replace( '/class="([^"]*eshb-search[^"]*)"/', 'class="$1" ' . $style, $output, 1 );
}

echo $output;

