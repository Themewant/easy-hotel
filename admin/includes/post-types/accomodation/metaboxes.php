<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
add_action( 'plugins_loaded', function(){
    if( class_exists( 'ESHB' ) ) {
        $variable_pricing_warning_message = '<a href="' . admin_url( 'edit.php?post_type=eshb_session' ) . '" target="_blank">Add Variable Pricing</a>';
        $add_new_service_message = '<a href="' . admin_url( 'edit.php?post_type=eshb_service' ) . '" target="_blank">Add New Service</a>';

        // Set a unique slug-like ID
        $prefix = 'eshb_accomodation_metaboxes';


        // Create a section
        ESHB::createSection( $prefix, array(
            'title'  => 'Capacity',
            'fields' => array(
            // A text field
            array(
                'id'    => 'total_rooms',
                'type'  => 'number',
                'title' => 'Total Rooms',
                'default' => 1
            ),
            array(
                'id'    => 'available_rooms',
                'type'  => 'number',
                'title' => 'Available Rooms',
                'class' => 'hidden',
            ),
            array(
                'id'    => 'total_extra_beds',
                'type'  => 'number',
                'title' => 'Extra Beds',
                'default' => 1,
            ),
            array(
                'id'    => 'adult_capacity',
                'type'  => 'number',
                'title' => 'Adult',
                'subtitle' => 'Total Adult Capacity for 1 Room',
                'desc'  => 'If you leave this empty, the adult capacity will extract from total capacity',
            ),
            array(
                'id'    => 'children_capacity',
                'type'  => 'number',
                'title' => 'Children',
                'subtitle' => 'Total Children Capacity for 1 Room',
                'desc'  => 'If you leave this empty, the children capacity will extract from total capacity',
            ),
            array(
                'id'    => 'total_capacity',
                'type'  => 'number',
                'title' => 'Total Capacity',
                'subtitle' => 'Total Capacity for 1 Room',
                'desc'  => 'If you leave this empty, the total capacity will extract from adult & children capacity. For examle: you leave this empty and adult capcity 3 and children capacity 2. So total capacity is 3 + 2 = 5',
            )
            )
        ) );

        // Create a metabox
        ESHB::createMetabox( $prefix, array(
            'title'              => 'Options & Features',
            'post_type'          => 'eshb_accomodation',
            'data_type'          => 'serialize',
            'context'            => 'advanced',
            'priority'           => 'default',
            'exclude_post_types' => array(),
            'page_templates'     => '',
            'post_formats'       => '',
            'show_restore'       => false,
            'enqueue_webfont'    => true,
            'async_webfont'      => false,
            'output_css'         => true,
            'nav'                => 'inline',
            'theme'              => 'light',
            'class'              => '',
        ) );

        // Create a section
        $pricing_fields = array(
            // A text field
            array(
                'id'    => 'pricing_type',
                'type'  => 'select',
                'title' => 'Pricing Type',
                'options' => array(
                    'room_wise' => 'Room Wise',
                    'person_wise' => 'Person Wise'
                ),
                'default' => 'room_wise'
            ),

            array(
                'id'    => 'regular_price',
                'type'  => 'number',
                'title' => 'Regular Price',
                'desc' => $variable_pricing_warning_message,
            ),
            array(
                'id'    => 'sale_price',
                'type'  => 'number',
                'title' => 'Sale Price',
            ),
            array(
                'id'        => 'day_wise_price',
                'type'      => 'repeater',
                'title'     => 'Day Wise Price',
                'max'       => 1,
                'min'       => 1,
                'class'     => 'hide-add-new has-inline-repeater-fields',
                'fields'    => array(
                array(
                    'id'    => 'monday',
                    'type'  => 'number',
                    'title' => 'Monday',
                ),
                array(
                    'id'    => 'tuesday',
                    'type'  => 'number',
                    'title' => 'Tuesday',
                ),
                array(
                    'id'    => 'wednesday',
                    'type'  => 'number',
                    'title' => 'Wednesday',
                ),
                array(
                    'id'    => 'thursday',
                    'type'  => 'number',
                    'title' => 'Thursday',
                ),
                array(
                    'id'    => 'friday',
                    'type'  => 'number',
                    'title' => 'Friday',
                ),
                array(
                    'id'    => 'saturday',
                    'type'  => 'number',
                    'title' => 'Saturday',
                ),
                array(
                    'id'    => 'sunday',
                    'type'  => 'number',
                    'title' => 'Sunday',
                ),
                ),
                'default'   => array(
                        array(
                        'monday'     => '',
                        'tuesday'     => '',
                        'wednesday'    => '',
                        'thursday' => '',
                        'friday' =>   '',
                        'saturday' => '',
                        'sunday' => '',
                        ),
                    ),
            ),
            
            array(
                'id'    => 'extra_bed_price',
                'type'  => 'number',
                'title' => 'Extra Bed Price (Per Bed)',
            ),
            array(
                'id'      => 'is_best_seller',
                'type'    => 'checkbox',
                'title'   => 'Best Selller',
                'label'   => 'Yes',
                'default' => false // or false
            ),

        );

        ESHB::createSection( $prefix, array(
            'title'  => 'Pricing',
            'fields' => $pricing_fields
        ) );

        do_action( 'eshb_accomodation_metaboxes_after_pricing_section'  );

       
        // Create a section
        ESHB::createSection( $prefix, array(
            'title'  => 'Basic Info',
            'fields' => array(
            array(
                'id'        => 'accomodation_info_group',
                'type'      => 'group',
                'title'     => 'Group',
                'fields'    => array(
                    array(
                        'id'    => 'info_title',
                        'type'  => 'text',
                        'title' => 'Title',
                    ),
                    array(
                        'id'    => 'info_icon',
                        'type'  => 'icon',
                        'title' => 'Icon',
                    ),
                    array(
                        'id'      => 'info_icon_img',
                        'type'    => 'media',
                        'title'   => 'Icon Image',
                        'library' => 'image',
                    ),
                ),
                'default' => array(
                    array(
                        'info_icon' => 'fas fa-users',
                        'info_title' => '2 Guests'
                    ),
                    array(
                        'info_icon' => 'fas fa-expand-arrows-alt',
                        'info_title' => '35 Feets Size'
                    ),
                    array(
                        'info_icon' => 'fas fa-door-open',
                        'info_title' => 'Connecting Rooms'
                    ),
                    array(
                        'info_icon' => 'fas fa-bed',
                        'info_title' => '1 King Bed'
                    )
                )
            ),

            )
        ) );

        // Create a section
        ESHB::createSection( $prefix, array(
            'title'  => 'Services',
            'fields' => array(
            // A text field
            array(
                'id'          => 'accomodation_services',
                'type'        => 'select',
                'multiple'    => true,
                'title'       => 'Services',
                'placeholder' => 'Select an option',
                'options'     => 'posts',
                'query_args'  => array(
                                    'post_type' => 'eshb_service',
                                    'posts_per_page' => -1,
                                ),
                'desc' => $add_new_service_message,
                
            ),

            )
        ) );


        // Create Side Metaboxes
        $prefix = 'eshb_accomodation_metaboxes_side';
        ESHB::createMetabox( $prefix, array(
            'title'     => 'Accomodation Options',
            'post_type' => 'eshb_accomodation',
            'context'   => 'side', // The context within the screen where the boxes should display. `normal`, `side`, `advanced`
        ) );

        


        // Create a section
        ESHB::createSection( $prefix, array(
            'title'  => 'Gallery & Video',
            'fields' => array(
                array(
                    'id'    => 'accomodation_hero_type',
                    'type'  => 'select',
                    'title' => 'Hero Type',
                    'options'     => array(
                        'gallery'  => 'Gallery',
                        'video'  => 'Video',
                    ),
                    'default'     => 'gallery',
                    
                ),
                array(
                    'id'          => 'accomodation_gallery',
                    'type'        => 'gallery',
                    'title'       => 'Gallery',
                    'add_title'   => 'Add Images',
                    'edit_title'  => 'Edit Images',
                    'clear_title' => 'Remove Images',
                    'dependency' => array('accomodation_hero_type', '==', 'gallery', )
                ),
                array(
                    'id'          => 'slides_per_view',
                    'type'        => 'number',
                    'title'       => 'Slides per view',
                    'desc'        => 'Number of slides per view (slides visible at the same time on slider\'s container',
                    'dependency' => array('accomodation_hero_type', '==', 'gallery', )
                ),
                array(
                    'id'    => 'accomodation_video_source',
                    'type'  => 'select',
                    'title' => 'Video Type',
                    'options'     => array(
                        'external'  => 'External',
                        'self_hosted'  => 'Self Hosted',
                    ),
                    'default'     => 'self_hosted',
                    'dependency' => array('accomodation_hero_type', '==', 'video'),
                    
                ),
                array(
                    'id'          => 'accomodation_video',
                    'type'        => 'text',
                    'title'       => 'Video Url',
                    'dependency' => array('accomodation_hero_type', '==', 'video'),
                ),
                array(
                    'id'          => 'accomodation_video_height',
                    'type'        => 'text',
                    'title'       => 'Video Height',
                    'desc'        => 'Add height for video iframe',
                    'placeholder' => '720',
                    'dependency' => array('accomodation_hero_type', '==', 'video', )
                ),
            )
        ) );
    }
}, 10);

function eshb_add_custom_columns_to_accomodation($columns) {
    $columns['total_capacity'] = esc_html__( 'Total Capacity' , 'easy-hotel' );
    $columns['adult_capacity'] = esc_html__( 'Adult Capacity' , 'easy-hotel' );
    $columns['children_capacity'] = esc_html__( 'Children Capacity' , 'easy-hotel' );
    $columns['menu_order'] = esc_html__( 'Menu Order' , 'easy-hotel' );
    return $columns;
}
add_filter('manage_eshb_accomodation_posts_columns', 'eshb_add_custom_columns_to_accomodation');

function eshb_accomodation_custom_column_content($column, $post_id) {

    $eshb_accomodation_metaboxes = get_post_meta($post_id, 'eshb_accomodation_metaboxes', true);

    switch ($column) {
        case 'total_capacity':
            echo esc_html($eshb_accomodation_metaboxes['total_capacity']);
            break;
        case 'adult_capacity':
            echo esc_html($eshb_accomodation_metaboxes['adult_capacity']);
            break;
        case 'children_capacity':
            echo esc_html($eshb_accomodation_metaboxes['children_capacity']);
            break;
        case 'menu_order':
            echo esc_html(get_post_field( 'menu_order' ));
            break;
    }
}
add_action('manage_eshb_accomodation_posts_custom_column', 'eshb_accomodation_custom_column_content', 10, 2);


function eshb_accomodation_reorder_columns($columns) {
    // Save the date column
    $date_column = $columns['date'];
    
    unset($columns['date']); // Remove the date column temporarily

    // Add your custom columns
    $columns['total_capacity'] = esc_html__('Total Capacity', 'easy-hotel');
    $columns['adult_capacity'] = esc_html__('Adult Capacity', 'easy-hotel');
    $columns['children_capacity'] = esc_html__('Children Capacity', 'easy-hotel');
    $columns['menu_order'] = esc_html__('Menu Order', 'easy-hotel');

    // Add the date column back as the last column
    $columns['date'] = $date_column;

    unset($columns['author']);
    unset($columns['taxonomy-eshb_amenitie']);

    return $columns;
}
add_filter('manage_eshb_accomodation_posts_columns', 'eshb_accomodation_reorder_columns');