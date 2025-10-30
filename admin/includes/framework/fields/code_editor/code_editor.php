<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: code_editor
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'ESHB_Field_code_editor' ) ) {
  class ESHB_Field_code_editor extends ESHB_Fields {

    public $version = '6.65.7';

    public $cdn_url = ESHB_PL_PATH . 'admin/includes/framework/assets/js/';

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $default_settings = array(
        'tabSize'       => 2,
        'lineNumbers'   => true,
        'theme'         => 'default',
        'mode'          => 'htmlmixed',
        'cdnURL'        => $this->cdn_url . $this->version,
      );

      $settings = ( ! empty( $this->field['settings'] ) ) ? $this->field['settings'] : array();
      $settings = wp_parse_args( $settings, $default_settings );

      echo wp_kses_post($this->field_before());
      echo '<textarea name="'. esc_attr( $this->field_name() ) .'"'. esc_attr($this->field_attributes()) .' data-editor="'. esc_attr( wp_json_encode( $settings ) ) .'">'. esc_html($this->value) .'</textarea>';
      echo wp_kses_post($this->field_after());

    }

    public function enqueue() {
       // Verify nonce for security
      if (isset($_POST['eshb_save_meta'])) {
        wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['eshb_save_meta'])), 'eshb_save_meta_box_nonce');
      }

      $page = ( ! empty( $_GET[ 'page' ] ) ) ? sanitize_text_field( wp_unslash( $_GET[ 'page' ] ) ) : '';

      // Do not loads CodeMirror in revslider page.
      if ( in_array( $page, array( 'revslider' ) ) ) { return; }

      if ( ! wp_script_is( 'csf-codemirror' ) ) {
        wp_enqueue_script( 'csf-codemirror' );
        wp_enqueue_script( 'csf-codemirror-loadmode', esc_url( $this->cdn_url . '/loadmode.min.js' ), array( 'csf-codemirror' ), $this->version, true );
      }

      if ( ! wp_style_is( 'csf-codemirror' ) ) {
        wp_enqueue_style( 'csf-codemirror' );
      }

    }

  }
}
