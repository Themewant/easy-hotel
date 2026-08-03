<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$eshb_settings = get_option( 'eshb_settings' );
$eshb_search_form_archive_visibility = isset($eshb_settings['search-form-archive']) ? $eshb_settings['search-form-archive'] : true ;

?>

<div class="easy-hotel">
    <div class="eshb-archive-wrapper eshb-container">

        <?php
                        
            if($eshb_search_form_archive_visibility == true){
                echo '<div class="archive-search-warpper">';
                require_once 'easy-hotel-search.php';
                echo '</div>';
            }

            $eshb_settings = get_option( 'eshb_settings' );
            $eshb_posts_per_page = isset($eshb_settings['accomodation_posts_per_page']) && !empty($eshb_settings['accomodation_posts_per_page']) ? $eshb_settings['accomodation_posts_per_page'] : 6;
            $eshb_posts_per_row = isset($eshb_settings['accomodation_posts_per_row']) && !empty($eshb_settings['accomodation_posts_per_row']) ? $eshb_settings['accomodation_posts_per_row'] : 3;
            $eshb_posts_order_by = isset($eshb_settings['accomodation_posts_order_by']) && !empty($eshb_settings['accomodation_posts_order_by']) ? $eshb_settings['accomodation_posts_order_by'] : 'id';
            $eshb_posts_order = isset($eshb_settings['accomodation_posts_order']) && !empty($eshb_settings['accomodation_posts_order']) ? $eshb_settings['accomodation_posts_order'] : 'DESC';
        
            $paged = max( 1, get_query_var('paged') );

            $eshb_available_accomodations_args = array(
                'post_type'      => 'eshb_accomodation',
                'post_status'    => 'publish',
                'posts_per_page' => $eshb_posts_per_page,
                'paged'          => $paged,
                'orderby'        => $eshb_posts_order_by,
                'order'          => $eshb_posts_order,
            );

            // ✅ IMPORTANT: add tax_query for taxonomy archive
            if ( is_tax( 'eshb_category' ) ) {

                $term = get_queried_object();

                $eshb_available_accomodations_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
                    array(
                        'taxonomy' => 'eshb_category',
                        'field'    => 'term_id',
                        'terms'    => $term->term_id,
                    ),
                );
            }

            // Add taxonomy filter if category is set
            if ( ! empty( $category_id ) ) {
                $eshb_available_accomodations_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- necessary taxonomy filter, limited query
                    array(
                        'taxonomy' => 'eshb_category',
                        'field'    => 'term_id',
                        'terms'    => $category_id,
                    ),
                );
            }

            $eshb_query = new WP_Query($eshb_available_accomodations_args);

            $eshb_view = new ESHB_View();

            $eshb_style = isset($eshb_settings['archive-page-template-style']) && !empty($eshb_settings['archive-page-template-style']) ? $eshb_settings['archive-page-template-style'] : 'style-one';
            $eshb_template = $eshb_view->eshb_get_accomodation_grid($eshb_query, '', '', $eshb_posts_per_row, 'eshb_thumbnail', $eshb_style);


        ?>

    </div>
</div>
