<?php
  function eshb_settings_fallback ($plugin_name, $plugin_slug, $plugin_url, $display = ''){
    $plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php';
  
    if (! is_plugin_active( $plugin_main_file ) ) {
        echo '<div class="eshb-admin-notice '. esc_html($display) .'">
        Please activate <a href="' . esc_attr($plugin_url) . '" target="_blank">'. esc_html($plugin_name) .'</a> extension to access this feature.
      </div>
      ';
    }
  }
  
  // ical settings fallback
  function eshb_ical_settings_fallback(){
    $plugin_slug = 'ehb-ical';
    eshb_settings_fallback('EHB iCal', $plugin_slug, 'https://themewant.com/downloads/ehb-ical/', 'eshb-admin-notice-large');
  }
  
  // db settings fallback
  function eshb_db_settings_fallback(){
    $plugin_slug = 'ehb-db';
    eshb_settings_fallback('EHB DB', $plugin_slug, 'https://themewant.com/downloads/ehb-db/', 'eshb-admin-notice-large');
  }
  
  // db settings fallback
  function eshb_reviews_settings_fallback(){
    $plugin_slug = 'ehb-reviews';
    eshb_settings_fallback('EHB Reviews', $plugin_slug, 'https://themewant.com/downloads/ehb-review/', 'eshb-admin-notice-large');
  }
  
  // advanced prcing settings fallback
  function eshb_advanced_pricing_settings_fallback(){
    $plugin_slug = 'ehb-advanced-pricing';
    eshb_settings_fallback('EHB Advanced Pricing', $plugin_slug, 'https://themewant.com/downloads/ehb-advanced-pricing/', 'eshb-admin-notice-large');
  }
  
  // advanced prcing settings fallback
  function eshb_advanced_pricing_settings_fallback_sm(){
    $plugin_slug = 'ehb-advanced-pricing';
    eshb_settings_fallback('EHB Advanced Pricing', $plugin_slug, 'https://themewant.com/downloads/ehb-advanced-pricing/', '');
  }
  
  
  // manual booking settings fallback
  function eshb_manual_booking_settings_fallback(){
    $plugin_slug = 'ehb-manual-booking';
    eshb_settings_fallback('EHB Manual Booking', $plugin_slug, 'https://themewant.com/downloads/ehb-manual-booking/', 'eshb-admin-notice-large');
  }
  
  // manual booking settings fallback
  function eshb_deposit_settings_fallback(){
    $plugin_slug = 'ehb-deposit';
   eshb_settings_fallback('EHB Deposit', $plugin_slug, 'https://themewant.com/downloads/ehb-deposit/', 'eshb-admin-notice-large');
  }
  
  // bricks settings fallback
  function eshb_bricks_settings_fallback(){
    $plugin_slug = 'ehb-bricks';
    eshb_settings_fallback('EHB Bricks', $plugin_slug, 'https://themewant.com/downloads/ehb-bricks/', 'eshb-admin-notice-large');
  }
  
  // email template settings fallback
  function eshb_email_template_settings_fallback(){
    $plugin_slug = 'ehb-email-template';
    eshb_settings_fallback('EHB Email Template', $plugin_slug, 'https://themewant.com/downloads/ehb-email-template/', 'eshb-admin-notice-large');
  }
  
  
  function eshb_booking_details_calendar_callback(){
    $allowed_html = array(
      'div' => array(
          'class' => true,
          'id' => true,
          'style' => true,
      ),
      'form' => array(
          'action' => true,
          'method' => true,
          'class' => true,
      ),
      'select' => array(
          'name' => true,
          'id' => true,
      ),
      'option' => array(
          'value' => true,
          'selected' => true,
      ),
      'input' => array(
          'type' => 'text',
          'name' => true,
          'value' => true,
          'class' => true,
          'style' => true
      ),
      'input' => array(
          'type' => 'hidden',
          'name' => true,
          'value' => true,
      ),
      'input' => array(
          'type' => 'number',
          'name' => true,
          'value' => true,
          'class' => true,
          'style' => true,
      ),
      'input' => array(
        'type' => 'submit',
        'name' => true,
        'class' => true,
        'value' => true,
        'style' => true,
      ),
      'button' => array(
          'type' => true,
          'name' => true,
          'class' => true,
          'style' => true
      ),
      'legend' => array(
          'class' => true,
          'title' => true,
      ),
      'span' => array(),
      'small' => array(
        'class' => true,
      ),
      'h1' => array(
        'class'=> true,
      ),
      'p' => array(
        'class'=> true,
      ),
      'i' => array(
        'class'=> true,
      ),
      'br' => array(),
      'table' => array(
        'class'=> true,
      ),
      'thead' => array(
        'class'=> true,
      ),
      'tbody' => array(),
      'tfoot' => array(),
      'tr' => array(
        'class'=> true,
      ),
      'th' => array(
        'class' => true,
      ),
      'td' => array(
        'colspan' => true,
        'class' => true,
      ),
      'a' => array(
        'href' => true,
        'target' => true,
        'style' => true,
        'class' => true,
        'data-source-type' => true,
        'data-booking-id' => true,
        'data-accomodation-id' => true,

      ),
      'img' => array(),
      'ul' => array(),
      'li' => array(),
      'ol' => array(),
      'hr' => array(),
      'blockquote' => array(),
      'cite' => array(),
      'code' => array(),
      'pre' => array(),
      'samp' => array(),
      'kbd' => array(),
      'var' => array(),
      'sup' => array(),
      'sub' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
      'em' => array(),
      'strong' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
      'em' => array(),
      'strong' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
      'em' => array(),
      'strong' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
      'em' => array(),
      'strong' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
      'em' => array(),
      'strong' => array(),
      'b' => array(),
      'u' => array(),
      'i' => array(),
  );
    $ESHB_Booking_Calendar = new ESHB_Booking_Calendar();
    echo wp_kses($ESHB_Booking_Calendar->render_booking_info_calendar(), $allowed_html);
  }