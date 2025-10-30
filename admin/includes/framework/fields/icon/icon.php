<?php if ( ! defined( 'ABSPATH' ) ) { die; } // Cannot access directly.
/**
 *
 * Field: icon
 *
 * @since 1.0.0
 * @version 1.0.0
 *
 */
if ( ! class_exists( 'ESHB_Field_icon' ) ) {
  class ESHB_Field_icon extends ESHB_Fields {

    public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
      parent::__construct( $field, $value, $unique, $where, $parent );
    }

    public function render() {

      $args = wp_parse_args( $this->field, array(
        'button_title' => esc_html__( 'Add Icon', 'easy-hotel' ),
        'remove_title' => esc_html__( 'Remove Icon', 'easy-hotel' ),
      ) );

      echo wp_kses_post($this->field_before());

      $nonce  = wp_create_nonce( 'eshb_icon_nonce' );
      $hidden = ( empty( $this->value ) ) ? ' hidden' : '';

      echo '<div class="csf-icon-select">';
      echo '<span class="csf-icon-preview'. esc_attr( $hidden ) .'"><i class="'. esc_attr( $this->value ) .'"></i></span>';
      echo '<a href="#" class="button button-primary csf-icon-add" data-nonce="'. esc_attr( $nonce ) .'">'. esc_html($args['button_title']) .'</a>';
      echo '<a href="#" class="button csf-warning-primary csf-icon-remove'. esc_attr( $hidden ) .'">'. esc_html($args['remove_title']) .'</a>';
      echo '<input type="hidden" name="'. esc_attr( $this->field_name() ) .'" value="'. esc_attr( $this->value ) .'" class="csf-icon-value"'. esc_attr($this->field_attributes()) .' />';
      echo '</div>';

      echo wp_kses_post($this->field_after());

    }

    public function enqueue() {
      add_action( 'admin_footer', array( 'ESHB_Field_icon', 'add_footer_modal_icon' ) );
      add_action( 'customize_controls_print_footer_scripts', array( 'ESHB_Field_icon', 'add_footer_modal_icon' ) );
    }

    public static function add_footer_modal_icon() {
    ?>
      <div id="csf-modal-icon" class="csf-modal csf-modal-icon hidden">
        <div class="csf-modal-table">
          <div class="csf-modal-table-cell">
            <div class="csf-modal-overlay"></div>
            <div class="csf-modal-inner">
              <div class="csf-modal-title">
                <?php esc_html_e( 'Add Icon', 'easy-hotel' ); ?>
                <div class="csf-modal-close csf-icon-close"></div>
              </div>
              <div class="csf-modal-header">
                <input type="text" placeholder="<?php esc_html_e( 'Search...', 'easy-hotel' ); ?>" class="csf-icon-search" />
              </div>
              <div class="csf-modal-content">
                <div class="csf-modal-loading"><div class="csf-loading"></div></div>
                <div class="csf-modal-load"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    }

  }
}
