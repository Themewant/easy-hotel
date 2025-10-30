<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$eshb_settings = get_option( 'eshb_settings' );
$search_form_archive_visibility = isset($eshb_settings['search-form-archive']) ? $eshb_settings['search-form-archive'] : true ;

?>

<div class="easy-hotel">
    <div class="eshb-archive-wrapper eshb-container">

        <?php
                        
            if($search_form_archive_visibility == true){
                echo '<div class="archive-search-warpper">';
                require_once 'easy-hotel-search.php';
                echo '</div>';
            }

            $eshb_settings = get_option( 'eshb_settings' );
            $posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
            $posts_per_row = isset($eshb_settings['accomodation_posts_per_row']) && !empty($eshb_settings['accomodation_posts_per_row']) ? $eshb_settings['accomodation_posts_per_row'] : 3;
            $posts_order_by = isset($eshb_settings['accomodation_posts_order_by']) && !empty($eshb_settings['accomodation_posts_order_by']) ? $eshb_settings['accomodation_posts_order_by'] : 'id';
            $posts_order = isset($eshb_settings['accomodation_posts_order']) && !empty($eshb_settings['accomodation_posts_order']) ? $eshb_settings['accomodation_posts_order'] : 'DESC';
        
            $paged = get_query_var('paged') ? get_query_var('paged') : get_query_var('page');
            $paged = $paged ? $paged : 1;

            if(is_tax( 'eshb_category' )){
                $category_id = get_queried_object_id(  );
            }

            $available_accomodations_args = array(
                'post_type' => 'eshb_accomodation',
                'paged'     => $paged,
                'post_status' => 'publish',
                'posts_per_page' => $posts_per_page,
                'orderby' => $posts_order_by,
                'order' => $posts_order
            );

            // Add taxonomy filter if category is set
            if ( ! empty( $category_id ) ) {
                $available_accomodations_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
                    array(
                        'taxonomy' => 'eshb_category',
                        'field'    => 'term_id',
                        'terms'    => $category_id,
                    ),
                );
            }

            $query = new WP_Query($available_accomodations_args);

            $view = new ESHB_View();

            $template = $view->eshb_get_accomodation_grid($query, '', '', $posts_per_row, '');


        ?>

    </div>
</div>
