<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: map
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'ESHB_Field_map' ) ) {
  class ESHB_Field_map extends ESHB_Fields {

    public $version = '1.9.4';

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args              = wp_parse_args( $this->field, array(
        'placeholder'    => esc_html__( 'Search...', 'easy-hotel' ),
        'latitude_text'  => esc_html__( 'Latitude', 'easy-hotel' ),
        'longitude_text' => esc_html__( 'Longitude', 'easy-hotel' ),
        'address_field'  => '',
        'height'         => '',
      ) );

      $value             = wp_parse_args( $this->value, array(
        'address'        => '',
        'latitude'       => '20',
        'longitude'      => '0',
        'zoom'           => '2',
      ) );

      $default_settings   = array(
        'center'          => array( $value['latitude'], $value['longitude'] ),
        'zoom'            => $value['zoom'],
        'scrollWheelZoom' => false,
      );

      $settings = ( ! empty( $this->field['settings'] ) ) ? $this->field['settings'] : array();
      $settings = wp_parse_args( $settings, $default_settings );

      $style_attr  = ( ! empty( $args['height'] ) ) ? ' style="min-height:'. esc_attr( $args['height'] ) .';"' : '';
      $placeholder = ( ! empty( $args['placeholder'] ) ) ? array( 'placeholder' => $args['placeholder'] ) : '';

      echo wp_kses_post($this->field_before());

      if ( empty( $args['address_field'] ) ) {
        echo '<div class="csf--map-search">';
        echo '<input type="text" name="'. esc_attr( $this->field_name( '[address]' ) ) .'" value="'. esc_attr( $value['address'] ) .'"'. esc_attr($this->field_attributes( $placeholder )) .' />';
        echo '</div>';
      } else {
        echo '<div class="csf--address-field" data-address-field="'. esc_attr( $args['address_field'] ) .'"></div>';
      }

      echo '<div class="csf--map-osm-wrap"><div class="csf--map-osm" data-map="'. esc_attr( wp_json_encode( $settings ) ) .'"'. esc_attr($style_attr) .'></div></div>';

      echo '<div class="csf--map-inputs">';

        echo '<div class="csf--map-input">';
        echo '<label>'. esc_attr( $args['latitude_text'] ) .'</label>';
        echo '<input type="text" name="'. esc_attr( $this->field_name( '[latitude]' ) ) .'" value="'. esc_attr( $value['latitude'] ) .'" class="csf--latitude" />';
        echo '</div>';

        echo '<div class="csf--map-input">';
        echo '<label>'. esc_attr( $args['longitude_text'] ) .'</label>';
        echo '<input type="text" name="'. esc_attr( $this->field_name( '[longitude]' ) ) .'" value="'. esc_attr( $value['longitude'] ) .'" class="csf--longitude" />';
        echo '</div>';

      echo '</div>';

      echo '<input type="hidden" name="'. esc_attr( $this->field_name( '[zoom]' ) ) .'" value="'. esc_attr( $value['zoom'] ) .'" class="csf--zoom" />';

      echo wp_kses_post($this->field_after());

    }

    public function enqueue() {

      if ( ! wp_script_is( 'csf-leaflet' ) ) {
        wp_enqueue_script( 'csf-leaflet', ESHB_PL_PATH . 'admin/includes/framework/assets/js/leaflet.js', array( 'easy-hotel' ), $this->version, true );
      }

      if ( ! wp_style_is( 'csf-leaflet' ) ) {
        wp_enqueue_style( 'csf-leaflet', ESHB_PL_PATH . 'admin/includes/framework/assets/js/leaflet.css', array(), $this->version );
      }

      if ( ! wp_script_is( 'jquery-ui-autocomplete' ) ) {
        wp_enqueue_script( 'jquery-ui-autocomplete' );
      }

    }

  }
}
