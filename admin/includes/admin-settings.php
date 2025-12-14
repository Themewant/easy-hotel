<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
add_action('admin_menu', 'eshb_custom_submenu_page');

function eshb_custom_submenu_page() {

    include 'functions.php';

    // Define all submenus in an array
    $submenus = [

        // Always visible menus
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Services',
            'menu'     => 'Services',
            'cap'      => 'manage_options',
            'slug'     => 'edit.php?post_type=eshb_service',
            'callback' => ''
        ],
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Seasons & Pricing',
            'menu'     => 'Seasons & Pricing',
            'cap'      => 'manage_options',
            'slug'     => 'edit.php?post_type=eshb_session',
            'callback' => ''
        ],
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Coupons',
            'menu'     => 'Coupons',
            'cap'      => 'manage_options',
            'slug'     => 'edit.php?post_type=eshb_coupon',
            'callback' => ''
        ],
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Bookings',
            'menu'     => 'Bookings',
            'cap'      => 'manage_options',
            'slug'     => 'edit.php?post_type=eshb_booking',
            'callback' => ''
        ],
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Payments',
            'menu'     => 'Payments',
            'cap'      => 'manage_options',
            'slug'     => 'edit.php?post_type=eshb_payment',
            'callback' => ''
        ],
        [
            'parent'   => 'edit.php?post_type=eshb_accomodation',
            'page'     => 'Booking Calendar',
            'menu'     => 'Booking Calendar',
            'cap'      => 'manage_options',
            'slug'     => 'eshb_bookings_calendar',
            'callback' => 'eshb_booking_details_calendar_callback'
        ],

        // Hidden page
        [
            'parent'   => 'admin.php',
            'page'     => 'View Booking Data',
            'menu'     => 'View Booking Data',
            'cap'      => 'edit_posts',
            'slug'     => 'view_booking_data',
            'callback' => 'eshb_get_booking_data_tables'
        ]
    ];

    // Loop through and register each submenu
    foreach ($submenus as $submenu) {
        if ( $submenu ) {
            add_submenu_page(
                $submenu['parent'],
                $submenu['page'],
                $submenu['menu'],
                $submenu['cap'],
                $submenu['slug'],
                $submenu['callback']
            );
        }
    }
}

add_action( 'plugins_loaded', function(){
  // Create a section
  $general_option_fields =  array(
            
    array(
      'id'      => 'booking-auto-approval',
      'type'    => 'switcher',
      'title'   => 'Auto Approve Booking',
      'default' => true
    ),
    array(
      'id'    => 'availability-calendar',
      'type'  => 'switcher',
      'title' => 'Availability Calendar',
      'desc'  => 'On this switch if you want to show Availability Calendar on the accomodation page.',
      'default' => true,
    ),
    array(
      'id'    => 'day-wise-pricing',
      'type'  => 'switcher',
      'title' => 'Show Day Wise Pricing',
      'desc'  => 'On this switch if you want to show day wise pricing on the accomodation page.',
      'default' => true,
    ),
    array(
      'id'    => 'check-in-out-time',
      'type'  => 'switcher',
      'title' => 'Show Check In & Out Time',
      'desc'  => 'On this switch if you want to show check-in and check-out on the accomodation page.',
      'default' => true,
    ),
    array(
      'id'      => 'extra-services-switcher',
      'type'    => 'switcher',
      'title'   => 'Extra Services',
      'default' => true
    ),
    array(
      'id'      => 'booking-btn-switcher',
      'type'    => 'switcher',
      'title'   => 'Booking Button Visibility',
      'default' => true
    ),
    array(
      'id'    => 'accomodation-gallery',
      'type'  => 'switcher',
      'title' => 'Accomodation Gallery',
      'desc'  => 'On this switch if you want to show accomodation gallery on the accomodation page.',
      'default' => true,
    ),
    array(
      'id'    => 'booking-form',
      'type'  => 'switcher',
      'title' => 'Booking Form',
      'desc'  => 'On this switch if you want to show booking form on the accomodation page.',
      'default' => true,
    ),
  );
  do_action( 'eshb_settings_fields_init');

  //error_log('easy hotel loaded');

  $general_option_fields = apply_filters( 'eshb_general_option_fields', $general_option_fields );

// Control core classes for avoid errors
  if( class_exists( 'ESHB' ) ) {

        // Set a unique slug-like ID
        $prefix = 'eshb_settings';

        // Create options
        ESHB::createOptions( $prefix, array(
      
          // framework title
          'framework_title'         => 'Easy Hotel Settings',
          'framework_class'         => 'easy-hotel-settings easy-hotel-plugin-settings',
      
          // menu settings
          'menu_title'              => 'Settings',
          'menu_slug'               => 'easy-hotel-settings',
          //'menu_type'               => 'submenu',
          'menu_capability'         => 'manage_options',
          'menu_icon'               => 'dashicons-schedule',
          'menu_position'           => 15,
          'menu_hidden'             => true,
          //'menu_parent'             => 'edit.php?post_type=eshb_accomodation',
      
          // menu extras
          'show_bar_menu'           => false,
          'show_sub_menu'           => true,
          'show_in_network'         => true,
          'show_in_customizer'      => false,
      
          'show_search'             => false,
          'show_reset_all'          => true,
          'show_reset_section'      => false,
          'show_footer'             => false,
          'show_all_options'        => false,
          'show_form_warning'       => true,
          'sticky_header'           => true,
          'save_defaults'           => true,
          'ajax_save'               => true,
      
          // admin bar menu settings
          'admin_bar_menu_icon'     => '',
          'admin_bar_menu_priority' => 80,
      
          // footer
          'footer_text'             => '',
          'footer_after'            => '',
          'footer_credit'           => '',
      
          // database model
          'database'                => '', // options, transient, theme_mod, network
          'transient_time'          => 0,
      
          // contextual help
          'contextual_help'         => array(),
          'contextual_help_sidebar' => '',
      
          // typography options
          'enqueue_webfont'         => true,
          'async_webfont'           => false,
      
          // others
          'output_css'              => true,
      
          // theme and wrapper classname
          'nav'                     => 'inline',
          'theme'                   => 'light',
          'class'                   => '',
      
          // external default values
          'defaults'                => array(),
      
        ) );

        
        
        ESHB::createSection( $prefix, array(
          'title'  => 'General',
          'fields' => $general_option_fields
          ));

        ESHB::createSection( $prefix, array(
          'title'  => 'Pages',
          'fields' => array(
                  // Select with pages
                  array(
                    'id'          => 'archive-page',
                    'type'        => 'select',
                    'title'       => 'Rooms Archive Page',
                    'placeholder' => 'Default',
                    'options'     => 'pages',
                    'default' => '1',
                    'query_args'  => array(
                        'posts_per_page' => -1 // for get all pages (also it's same for posts).
                      ),
                  ),
                  array(
                    'id'          => 'single-page-template-style',
                    'type'        => 'select',
                    'title'       => 'Room Details Page Style',
                    'options'     => array(
                      'style-one' => 'Style One',
                      'style-two' => 'Style Two'
                    ),
                    'default' => 'style-one',
                  ),
                  array(
                    'id'          => 'search-result-page',
                    'type'        => 'select',
                    'title'       => 'Search Result Page',
                    'placeholder' => 'Default',
                    'options'     => 'pages',
                    'query_args'  => array(
                        'posts_per_page' => -1 // for get all pages (also it's same for posts).
                      ),
                  ),
            )
          )
        );

        ESHB::createSection( $prefix, array(
          'title'  => 'Accomodations',
          'fields' => array(
                  array(
                    'id'    => 'accomodation_base_name',
                    'type'  => 'text',
                    'title' => 'Base Name',
                    //'desc'  => 'Add URL if you select External Booking'
                    'default' => 'eshb_accomodation'
                  ),
                  array(
                    'id'    => 'accomodation_posts_per_page',
                    'type'  => 'number',
                    'title' => 'Posts Per Page',
                    //'desc'  => 'Add URL if you select External Booking'
                    'default' => 6
                  ),
                  array(
                    'id'    => 'accomodation_posts_per_row',
                    'type'  => 'number',
                    'title' => 'Posts Per Row (Columns)',
                    //'desc'  => 'Add URL if you select External Booking'
                    'default' => 3
                  ),
                  array(
                    'id'    => 'accomodation_posts_order_by',
                    'type'  => 'select',
                    'title' => 'Posts Order By',
                    //'desc'  => 'Add URL if you select External Booking'
                    'options' => array(
                      'none' => 'none',
                      'id' => 'ID',
                      'date' => 'Date',
                      'title' => 'Title',
                      'name' => 'name',
                      'menu_order' => 'Menu Order',
                      'random' => 'Random'
                    ),
                    'default' => 'none'
                  ),
                  array(
                    'id'    => 'accomodation_posts_order',
                    'type'  => 'select',
                    'title' => 'Posts Order',
                    //'desc'  => 'Add URL if you select External Booking'
                    'options' => array(
                      'DESC' => 'DESC',
                      'ASC' => 'ASC'
                    ),
                    'default' => 'DESC'
                  ),
                  array(
                    'id'      => 'related-accomodation-switcher-in-single',
                    'type'    => 'switcher',
                    'title'   => 'Show Related Accomodations in Details Page',
                    'default' => true
                  ),
            )
          )
        );

        $admin_email = get_option('admin_email');

        ESHB::createSection( $prefix, array(
          'title'  => 'Booking',
          'fields' => array(
                  // Select with pages
                  array(
                    'id'          => 'booking-type',
                    'type'        => 'select',
                    'title'       => 'Booking',
                    'placeholder' => 'Select an option',
                    'options'     => array(
                      'woocommerce'    => 'Woocoommerce Booking',
                      'surecart'    => 'SurecCart Booking',
                      'external'    => 'External Booking',
                      'booking_request'    => 'Booking Request',
                      'disable'    => 'Disable Booking',
                    ),
                    'default'     => 'woocommerce'
                  ),
                  array(
                    'id'          => 'booking-form-type',
                    'type'        => 'select',
                    'title'       => 'Booking Form Type',
                    'placeholder' => 'Select an option',
                    'options'     => array(
                      'default'    => 'Default',
                      'cf7'    => 'Contact Form 7',
                      'fluentform'    => 'Fluent Form',
                    ),
                    'default'     => 'default',
                    'dependency' => array( 'booking-type', '==', 'booking_request' ) 
                  ),
                  array(
                    'id'    => 'booking-form-shortcode',
                    'type'  => 'text',
                    'title' => 'Booking Form Shortcode',
                    'desc'  => 'You can use Contact Form 7 or MetForm shortcode to display booking form. Read <a href="https://documentation.themewant.com/easy-hotel/booking-form-shortcode/" target="_blank">Documentation</a> to know how to configure this.',
                    'default' => '',
                    'dependency' => array( ['booking-type', '==', 'booking_request'], ['booking-form-type', '!=', 'default'] ) 
                  ),
                  array(
                    'id'    => 'external-booking-link',
                    'type'  => 'text',
                    'title' => 'External Booking Link',
                    'desc'  => 'Add URL if you select External Booking',
                    'dependency' => array( 'booking-type', '==', 'external' ) 
                  ),
                  array(
                    'id'    => 'recipent_email',
                    'type'  => 'text',
                    'title' => 'Recipent Email Address',
                    'desc'  => 'Add recipent email address for getting Booking Request.',
                    'default' => $admin_email,
                    'dependency' => array( ['booking-type', '==', 'booking_request'], ['booking-form-type', '==', 'default'] ) 
                  ),
                  array(
                    'id'    => 'currency_symbol',
                    'type'  => 'text',
                    'title' => 'Currency Symbol',
                    'desc'  => 'Add currency symbol here if you want to use custom currency.'
                  ),
                  array(
                    'id'          => 'currency_position',
                    'type'        => 'select',
                    'title'       => 'Currency Position',
                    'placeholder' => 'Select an option',
                    'options'     => array(
                      'left'    => 'Left',
                      'right'    => 'Right',
                    ),
                    'default'     => 'left'
                  ),
                  array(
                    'id'      => 'search-form-archive',
                    'type'    => 'switcher',
                    'title'   => 'Search Form at Archive Page',
                    'default' => true
                  ),
                  array(
                    'id'          => 'search-form-fields',
                    'type'        => 'select',
                    'title'       => 'Search Form Fields',
                    'multiple'    => true,
                    'placeholder' => 'Select an option',
                    'options'     => array(
                      'adults'    => 'Adults',
                      'childrens'    => 'Children',
                      'rooms'    => 'Rooms',
                    ),
                    'default'     => ['adults', 'childrens']
                  ),
                  array(
                    'id'    => 'adult-capacity',
                    'type'  => 'number',
                    'title' => 'Maximum Adult Capacity for Search',
                  ),
                  array(
                    'id'    => 'children-capacity',
                    'type'  => 'number',
                    'title' => 'Maximum Children Capacity for Search',
                  ),
                  array(
                    'id'          => 'booking-form-fields',
                    'type'        => 'select',
                    'title'       => 'Booking Form Fields',
                    'multiple'    => true,
                    'placeholder' => 'Select an option',
                    'options'     => array(
                      'adults'    => 'Adults',
                      'childrens'    => 'Children',
                      'rooms'    => 'Rooms',
                      'extra_beds'    => 'Extra Beds',
                    ),
                    'default'     => ['adults', 'childrens', 'rooms', 'extra_beds']
                  ),
                  array(
                    'id'    => 'check-in-time',
                    'type'  => 'datetime',
                    'title' => 'Check in Time',
                    //'desc'  => 'Add Booking Time',
                    'settings' => array(
                      'time_24hr' => false,
                      'noCalendar' => true,
                      'enableTime' => true,
                    ),
                  ),
                  array(
                    'id'    => 'check-out-time',
                    'type'  => 'datetime',
                    'title' => 'Check out Time',
                    //'desc'  => 'Add Booking Time',
                    'settings' => array(
                      'time_24hr' => false,
                      'noCalendar' => true,
                      'enableTime' => true,
                    ),
                  ),
                  array(
                    'id'        => 'holidays',
                    'type'      => 'repeater',
                    'title'     => 'Holidays',
                    'fields'    => array(
                  
                      array(
                        'id'    => 'holiday-date',
                        'type'  => 'date',
                        'title' => 'Date',
                        'desc'  => 'Add holiday by date from here.',
                        'from_to'  => true,
                        'settings' => array(
                          'dateFormat'      => 'yy-mm-dd',
                          'changeMonth'     => true,
                          'changeYear'      => true,
                          'showButtonPanel' => true,
                          'weekHeader'      => 'Week',
                          'monthNamesShort' => array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
                          'dayNamesMin'     => array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
                        ),
                      ),
                      array(
                        'id'    => 'holiday-title',
                        'type'  => 'text',
                        'title' => 'Title',
                        'desc'  => 'Add holiday title from here.',
                      ),
                      array(
                        'id'          => 'accomodation-ids',
                        'type'        => 'select',
                        'title'       => 'Accomodations',
                        'placeholder' => 'Select Accomodations',
                        'desc'  => 'Select Accomodations for this coupon.',
                        'options'     => 'posts',
                        'multiple'     => true,
                        'chosen'      => false,
                        'query_args'  => array(
                                            'post_type' => 'eshb_accomodation',
                                            'posts_per_page' => -1,
                                        ),
                      ),
                  
                    ),
                  ),

                  
                  
            )
          )
        );

        

        ESHB::createSection( $prefix, array(
          'title'  => 'Design',
          'fields' => array(
                    array(
                      'id'          => 'booking-form-style',
                      'type'        => 'select',
                      'title'       => 'Booking Form Style',
                      
                      'options'     => array(
                        'default' => 'Default',
                        'style-one' => 'Style One',
                        'style-two' => 'Style Two'
                      ),
                      'default'     => 'style-two'
                    ),
                    array(
                      'id'        => 'calendar-colors',
                      'type'      => 'color_group',
                      'title'     => 'Calendar Colors',
                      'output_mode' => 'background-color', 
                      'options'   => array(
                        'booked-bg-color' => 'Booked Background',
                        'booked-color' => 'Booked Color',
                        'active-bg-color' => 'Active Background',
                        'active-color' => 'Active Color',
                        'inrange-bg-color' => 'Inrange Background',
                        'inrange-color' => 'Inrange Color',
                      )
                    ),
                    array(
                      'id'        => 'theme-colors',
                      'type'      => 'color_group',
                      'title'     => 'Theme Colors',
                      'output_mode' => 'background-color', 
                      'options'   => array(
                        'primary-color' => 'Primary Color',
                        'secondary-color' => 'Secondary Color',
                        'territory-color' => 'Territory Color',
                        'success-color' => 'Success Color',
                        'danger-color' => 'Danger Color',
                        'dark-color' => 'Dark Color',
                        'text-color' => 'Text Color',
                        'white-color' => 'White Color',
                        'border-color' => 'Border Color',
                      ),
                      'default'   => array(
                        'primary-color' => '#ab8965',
                        'secondary-color' => '#fff5ed',
                        'territory-color' => '#70533a',
                        'success-color' => '#1ec734',
                        'danger-color' => '#e41749',
                        'dark-color' => '#181818',
                        'text-color' => '#212529',
                        'white-color' => '#ffffff',
                        'border-color' => '#ab8965',
                      )
                    ),
                    array(
                      'id'    => 'page-spacing',
                      'type'  => 'spacing',
                      'title' => 'Page Spacing',
                      'left'  => false,
                      'right' => false,
                      'units' => array( 'px' ),
                      'output_mode' => 'padding', // or margin, relative
                    ),
                    
                )
          )
        );

        // Create a section
        ESHB::createSection( $prefix, array(
        'title'  => 'Usage',
        'fields' => array(
              array(
                'type'     => 'callback',
                'function' => 'eshb_easy_hotel_usage_callback',
              ),
            )
          )
        );

        

      ESHB::createSection( $prefix, array(
        'title'  => 'Translation',
        'fields' => array(
                array(
                  'type'    => 'subheading',
                  'content' => 'Booking & Search Form Translation',
                ),
                array(
                  'id'    => 'string_reserve',
                  'type'  => 'text',
                  'title' => 'Reserve',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Reserve'
                ),
                array(
                  'id'    => 'string_check_availability',
                  'type'  => 'text',
                  'title' => 'Check Availability',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Check Availability'
                ),
                array(
                  'id'    => 'string_from',
                  'type'  => 'text',
                  'title' => 'From',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'From'
                ),
                array(
                  'id'    => 'view_details',
                  'type'  => 'text',
                  'title' => 'View Details',
                  //'desc'  => 'Add URL if you select External Booking'
                ),
                array(
                  'id'    => 'string_night',
                  'type'  => 'text',
                  'title' => 'Night',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'night'
                ),
                array(
                  'id'    => 'string_hour',
                  'type'  => 'text',
                  'title' => 'Hour',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'hour'
                ),
                array(
                  'id'    => 'string_check_in',
                  'type'  => 'text',
                  'title' => 'Check In',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Check In'
                ),
                array(
                  'id'    => 'string_check_out',
                  'type'  => 'text',
                  'title' => 'Check Out',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Check Out'
                ),
                array(
                  'id'    => 'string_guest',
                  'type'  => 'text',
                  'title' => 'Guest',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Guest'
                ),
                array(
                  'id'    => 'string_adult',
                  'type'  => 'text',
                  'title' => 'Adult',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Adult'
                ),
                array(
                  'id'    => 'string_children',
                  'type'  => 'text',
                  'title' => 'Children',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Children'
                ),
                array(
                  'id'    => 'string_room',
                  'type'  => 'text',
                  'title' => 'Room',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Room'
                ),
                array(
                  'id'    => 'string_rooms',
                  'type'  => 'text',
                  'title' => 'Rooms',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Rooms'
                ),
                array(
                  'id'    => 'available_rooms',
                  'type'  => 'text',
                  'title' => 'Available Rooms',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Available Rooms:'
                ),
                array(
                  'id'    => 'string_extra_bed',
                  'type'  => 'text',
                  'title' => 'Extra Bed',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Extra Bed'
                ),
                array(
                  'id'    => 'string_extra_services',
                  'type'  => 'text',
                  'title' => 'Extra Services',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Extra Services'
                ),
                array(
                  'id'    => 'string_total_cost',
                  'type'  => 'text',
                  'title' => 'Total Cost',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Total Cost'
                ),
                array(
                  'id'    => 'string_disocunted_price',
                  'type'  => 'text',
                  'title' => 'Discounted Price',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Discounted Price'
                ),
                array(
                  'id'    => 'string_book_your_stay',
                  'type'  => 'text',
                  'title' => 'Book Your Stay',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Book Your Stay'
                ),
                array(
                  'id'    => 'string_book_now',
                  'type'  => 'text',
                  'title' => 'Book Now',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Book Now'
                ),
                array(
                  'id'    => 'string_booking_success_msg',
                  'type'  => 'text',
                  'title' => 'Booking Success Message',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Reservation Successfully added to your cart.'
                ),
                array(
                  'id'    => 'string_booking_failed_msg',
                  'type'  => 'text',
                  'title' => 'Booking Failed Message',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Ops! This Reservation has been failed.'
                ),
                array(
                  'id'    => 'string_booking_request_success_msg',
                  'type'  => 'text',
                  'title' => 'Booking Request Success Message',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Booking request has been sent successfully.'
                ),
                array(
                  'id'    => 'string_booking_request_failed_msg',
                  'type'  => 'text',
                  'title' => 'Booking Request Failed Message',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Ops! Booking request can\'t be sent.'
                ),
                array(
                  'id'    => 'string_availability_calendar',
                  'type'  => 'text',
                  'title' => 'Availability Calendar',
                  //'desc'  => 'Add URL if you select External Booking'
                  'default' => 'Availability Calendar',
                ),
                array(
                  'id'    => 'string_apply',
                  'type'  => 'text',
                  'title' => 'Apply',
                  //'desc'  => 'Add URL if you select External Booking'
                ),
                array(
                  'id'    => 'string_cancel',
                  'type'  => 'text',
                  'title' => 'Cancel',
                  //'desc'  => 'Add URL if you select External Booking'
                ),

                array(
                  'id'    => 'string_related_sub_title',
                  'type'  => 'text',
                  'title' => 'Related Sub Title Change Here',
                  //'desc'  => 'Related Sub Title Change Here'
                ),

                array(
                  'id'    => 'string_related__title',
                  'type'  => 'text',
                  'title' => 'Related Title Change Here',
                  //'desc'  => 'Related Title Change Here'
                ),
          )
        )
      );

      // Create a section
      ESHB::createSection( $prefix, array(
        'title'  => 'Help & Support',
        'fields' => array(
              array(
                'type'     => 'callback',
                'function' => 'eshb_easy_hotel_help_support_callback',
              ),
            )
          )
        );
    

    
      
  }
  function eshb_easy_hotel_usage_callback() {
    ?>

      <div class="eshb_easy_hotel_usage_callback">
        <h3> <?php echo esc_html__('Shortcode:', 'easy-hotel')?></h3>
        <div>
          <p><strong>Archive Grid: </strong><?php echo esc_html__('[eshb_accomodation_grid]', 'easy-hotel')?></p>
          <p><strong>Search Form: </strong><?php echo esc_html__('[eshb_search_form]', 'easy-hotel')?></p>
          <p><strong>Booking Form For Room Details Page: </strong><?php echo esc_html__('[eshb_booking_form]', 'easy-hotel')?></p>
          <p><strong>Booking Form For Global: </strong><?php echo esc_html__('[eshb_booking_form accomodation_id="123"]', 'easy-hotel')?></p>
          <p><strong>Availability Calendar For Room Details Page: </strong><?php echo esc_html__('[eshb_availability_calendar]', 'easy-hotel')?></p>
          <p><strong>Availability Calendar For Global: </strong><?php echo esc_html__('[eshb_availability_calendar accomodation_id="123"]', 'easy-hotel')?></p>
          <p><strong>Related Accomodations: </strong><?php echo esc_html__('[eshb_related_accomodations]', 'easy-hotel')?> <?php echo esc_html__('(only allowed for room details page)', 'easy-hotel')?></p>
          <p><strong>Day Wise Pricing Table For Room Details Page: </strong><?php echo esc_html__('[eshb_daywise_pricing_table]', 'easy-hotel')?></p>
          <p><strong>Day Wise Pricing Table For Global: </strong><?php echo esc_html__('[eshb_daywise_pricing_table accomodation_id="123"]', 'easy-hotel')?></p>
          <p><strong>Check In/Out Times: </strong><?php echo esc_html__('[eshb_check_in_out_times]', 'easy-hotel')?> <?php echo esc_html__('(only allowed for room details page)', 'easy-hotel')?></p>
          <p><strong>Reviews: </strong><?php echo esc_html__('[eshb_average_rarings]', 'easy-hotel')?> <?php echo esc_html__('(only allowed for room details page)', 'easy-hotel')?></p>
        </div>
      </div>

      <p>
        <?php echo esc_html__('Easy Hotel', 'easy-hotel')?> 
        <a href="<?php echo esc_url('https://documentation.themewant.com/easy-hotel-booking/')?>" target="_blank"><?php echo esc_html__('documentation ', 'easy-hotel')?></a><?php echo esc_html__('to learn more in details.', 'easy-hotel')?>
      </p>

      <?php

  }

  function eshb_easy_hotel_help_support_callback() {
      ?>

      <div class="eshb_easy_hotel_help_support_callback">
        <div class="eshb-help-support">
      
          <h2><?php echo esc_html__( 'Welcome to Easy Hotel Booking – Help & Support', 'easy-hotel' )?></h2>
          <p><?php echo esc_html__('Thank you for using', 'easy-hotel')?> <strong><?php echo esc_html__('Easy Hotel Booking', 'easy-hotel') ?></strong>. <?php echo esc_html__('If you need assistance or want to learn how to get the most from the plugin, please explore the resources below:', 'easy-hotel')?></p>

          <div class="help-col-container">
            <div class="help-col">
              <h3><span class="eshb-dashicon-bg docs"><span class="dashicons dashicons-book-alt"></span></span><?php echo esc_html__('Documentation', 'easy-hotel')?></h3>
              <p>
              <?php echo esc_html__('Find step-by-step guides and configuration help in our documentation:', 'easy-hotel')?><br>
                  <a class="btn-docs" href="<?php echo esc_url('https://documentation.themewant.com/easy-hotel-booking/')?>" target="_blank" rel="noopener"><?php echo esc_html__('Easy Hotel Booking Documentation', 'easy-hotel')?></a>
              </p>
            </div>
            <div class="help-col">
              <h3><span class="eshb-dashicon-bg support"><span class="dashicons dashicons-sos"></span></span><?php echo esc_html__('Get Support', 'easy-hotel')?></h3>
              <p><?php echo esc_html__('If you have questions or face any issues, please reach out to us:', 'easy-hotel')?></p>
              <ul>
                  <li><a class="btn-support" href="<?php echo esc_url('https://themewant.com/support/')?>" target="_blank" rel="noopener"><?php echo esc_html__('Submit a Support Ticket', 'easy-hotel')?></a></li>
                  
              </ul>
            </div> 
            <div class="help-col">
              <h3><span class="eshb-dashicon-bg community"><span class="dashicons dashicons-groups"></span></span><?php echo esc_html__('Community', 'easy-hotel')?></h3>
              <p><?php echo esc_html__('Share feedback, ask questions, or get help from other Easy Hotel Booking users. 👉 Connect with our group and get an extra 20% discount!', 'easy-hotel') ?></p>
              <a class="btn-community" href="<?php echo esc_url('https://www.facebook.com/groups/easyhotelbookingcommunity')?>" target="_blank" rel="noopener"><?php echo esc_html__('Join Our Community', 'easy-hotel')?></a>
            </div>
            <div class="help-col">
              <h3><span class="eshb-dashicon-bg follow"><span class="dashicons dashicons-share"></span></span><?php echo esc_html__('Follow Us', 'easy-hotel')?></h3>
              <p><?php echo esc_html__('Stay updated and connect with us on social media:', 'easy-hotel')?></p>
              <p class="eshb-social-icons" style="font-size: 24px;">
                  <a href="<?php echo esc_url('https://www.facebook.com/themewant') ?>" target="_blank" rel="noopener">
                      <i class="fab fa-facebook"></i>
                  </a>
                  <a href="<?php echo esc_url('https://www.youtube.com/watch?v=m5wq7NE2rzw&list=PL-hcevD9GINf8Ae3I6UddfU1TJfRZzst4') ?>" target="_blank" rel="noopener">
                      <i class="fab fa-youtube"></i>
                  </a>
                  <a href="<?php echo esc_url('https://www.linkedin.com/company/themewant/') ?>" target="_blank" rel="noopener">
                      <i class="fab fa-linkedin"></i>
                  </a>
              </p>
            </div>
          </div>
          <div class="help-col-video">
            <h3><span class="eshb-dashicon-bg video"><span class="dashicons dashicons-format-video"></span></span><?php echo esc_html__('Video Tutorials', 'easy-hotel')?></h3>
            <p><?php echo esc_html__('Watch video guides on how to set up and customize Easy Hotel Booking:', 'easy-hotel')?></p>
            <div class="eshb-youtube-playlist">
              <div class="eshb-video-player">
                <iframe id="eshb-playlist-player" src="https://www.youtube.com/embed/videoseries?si=r_rVc9IYXTzIkDbG&amp;list=PL-hcevD9GINf8Ae3I6UddfU1TJfRZzst4" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
              </div>
              <div class="eshb-playlist-sidebar">
                <h3><?php echo esc_html__('Playlist', 'easy-hotel') ?></h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('NUb_lGXbhyU')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/intro.jpg' ) ?>" alt="Video 1 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Intro Video', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('m5wq7NE2rzw')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/setup-guide.jpg' ) ?>" alt="Video 2 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Setup Guide', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('HYKkw8SK-7U')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/seasonal-pricing' ) ?>" alt="Video 3 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Seasonal Pricing', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('YbUmgyhSGP8')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/setup-coupon-code.jpg' ) ?>" alt="Video 3 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Setup Coupon Code', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('Kd72Tekpd8Q')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/setup-extra-services.jpg' ) ?>" alt="Video 3 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Setup Extra Services', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('E8_RSyVLbjg')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/setup-ical.jpg' ) ?>" alt="Video 3 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Setup iCal', 'easy-hotel') ?></strong>
                  </button>
                  <button class="eshb-playlist-item" type="button" onclick="loadEshbTutotialVideo('rowRYH4Y5lk')">
                      <img src="<?php echo esc_url( ESHB_PL_URL.'admin/assets/img/thumbnails/yt/setup-min-max.jpg' ) ?>" alt="Video 3 Thumbnail" style="width: 100%; display: block;">
                      <strong><?php echo esc_html__('Setup Max/Min Night', 'easy-hotel') ?></strong>
                  </button>
                </div>
              </div>
            </div>
            <script>
              function loadEshbTutotialVideo(videoId) {
                  document.querySelector('iframe').src = `https://www.youtube.com/embed/${videoId}?rel=0`;
              }
            </script>
          </div>
        </div>
      </div>
      <?php

  }

  function eshb_pro_addons_themes_callback(){
    $pro_addons = new ESHB_PRO_ADDONS();
    ?>
      <h1><?php echo esc_html__( 'Get Addons & Themes', 'easy-hotel' ); ?></h1>
      <div class="eshb-themes-addons-row">
        <div class="eshb-themes-addons-col">
          <h2><?php echo esc_html__( 'Addons', 'easy-hotel' ); ?></h2>
          <?php $pro_addons->eshb_get_addons_html(); ?>
        </div>
        <div class="eshb-themes-addons-col">
          <h2><?php echo esc_html__( 'Themes', 'easy-hotel' ); ?></h2>
          <?php $pro_addons->eshb_get_themes_html(); ?>
        </div>
      </div>
    <?php
  }

  // Add custom field (nonce) to taxonomy edit page
  function eshb_add_custom_nonce_field() {
    // Add nonce field
    wp_nonce_field('eshb_save_meta_box_nonce', 'eshb_save_meta');
  }

  add_action('category_edit_form_fields', 'eshb_add_custom_nonce_field', 10, 2);
  add_action('category_add_form_fields', 'eshb_add_custom_nonce_field', 10, 2);

  // resposition admin menus
  function eshb_reposition_admin_submenus() {
    global $submenu;

    $parent_slug = 'edit.php?post_type=eshb_accomodation'; // Example: 'edit.php' for Posts menu
    $submenu_items = $submenu[$parent_slug] ?? [];

    if (!empty($submenu_items)) {

        // Remove existing submenus
        remove_submenu_page($parent_slug, 'easy-hotel-settings');

        // Re-add submenus in the desired order
        add_submenu_page($parent_slug, 'Settings', 'Settings', 'manage_options', 'easy-hotel-settings');
        add_submenu_page($parent_slug, 'Themes & Addons', 'Themes & Addons', 'manage_options', 'edit.php?post_type=eshb_addons', 'eshb_pro_addons_themes_callback');
        
    }
  }
  
  add_action('admin_menu', 'eshb_reposition_admin_submenus', 999); // Priority 999 ensures it runs after all menus are added



  // Register the string for translation
  function eshb_get_translated_string($option_key, $option_name = 'eshb_settings') {
    if (empty($option_key) || empty($option_name)) {
        return '';
    }

    $settings = get_option($option_name);

    $default_text = isset($settings[$option_key]) && !empty($settings[$option_key]) 
        ? $settings[$option_key] 
        : $option_key;

    // Polylang exists
    if (function_exists('pll__')) {
        return pll__($default_text);
    }
    // WPML exists
    else if (function_exists('icl_t')) {
        return icl_t('Easy Hotel Booking', $option_key, $default_text);
    }
    // No translation plugin
    else {
        return $default_text;
    }
  }


  /**
   * Register strings for translation with Polylang or WPML
   */
  function eshb_register_string_translations($fields = [], $option_name = 'eshb_settings') {
    if (empty($fields) || !is_array($fields)) {
        return;
    }

    // Get the settings array
    $settings = get_option($option_name);

    foreach ($fields as $option_key) {
        // Get default text
        $default_text = isset($settings[$option_key]) && !empty($settings[$option_key])
            ? $settings[$option_key]
            : str_replace('_', ' ', ucfirst(str_replace('string_', '', $option_key)));

        // Register with Polylang
        if (function_exists('pll_register_string')) {
            pll_register_string($option_key, $default_text, 'Easy Hotel Booking');
        }
        // Register with WPML
        elseif (function_exists('icl_register_string')) {
            icl_register_string('Easy Hotel Booking', $option_key, $default_text);
        }
    }
  }
}, 11);

// Auto hook into init
add_action('init', function() {
  $fields = [
      'accomodation_base_name',
      'string_reserve',
      'string_check_availability',
      'string_from',
      'view_details',
      'string_night',
      'string_hour',
      'string_check_in',
      'string_check_out',
      'string_guest',
      'string_adult',
      'string_children',
      'string_room',
      'string_rooms',
      'available_rooms',
      'string_extra_bed',
      'string_extra_services',
      'string_total_cost',
      'string_disocunted_price',
      'string_book_your_stay',
      'string_book_now',
      'string_booking_success_msg',
      'string_booking_failed_msg',
      'string_booking_request_success_msg',
      'string_booking_request_failed_msg',
      'string_availability_calendar',
      'string_apply',
      'string_cancel',
      'string_related_sub_title',
      'string_related__title',
  ];

  eshb_register_string_translations($fields, 'eshb_settings');
});