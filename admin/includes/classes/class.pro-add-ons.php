<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class ESHB_PRO_ADDONS {

    private $license_status = false;

    public function __construct() {
        
    }

    public function eshb_get_addons(){
        $addons = array(
                array(
                    'name' => 'EHB Email Template',
                    'slug' => 'ehb-email-template/ehb-email-template.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-email-template/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-email-template/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/email-template.png',
                    'desc' => 'Easy Hotel deposit Plugin, email template builder for for Easy Hotel plugin. Whether you can create custom template for emails of booking.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Deposit',
                    'slug' => 'ehb-currency/ehb-deposit.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-deposit/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-deposit/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/deposit.png',
                    'desc' => 'Easy Hotel deposit Plugin, deposit payment solution for Easy Hotel plugin. Whether customer can pay partial payment',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Manual Booking',
                    'slug' => 'ehb-manual-booking/ehb-manual-booking.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-manual-booking/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-manual-booking/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/manual-booking.png',
                    'desc' => 'Easy Hotel deposit Plugin, manual booking solution for WordPress. Whether you manage manually booking from admin area',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB iCal',
                    'slug' => 'ehb-ical/ehb-ical.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-ical/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-ical/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/ical.png',
                    'desc' => 'Easy Hotel iCal Plugin, A complete ical booking solution for easy hotel plugin.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Advanced Pricing',
                    'slug' => 'ehb-db/ehb-advanced-pricing.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-advanced-pricing/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-advanced-pricing/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/advanced-pricing.png',
                    'desc' => 'Easy Hotel Advanced Pricing plugin, extension for adding variable and longest pricing to accommodations.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Reviews',
                    'slug' => 'ehb-db/ehb-db.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-review/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-review/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/review.png',
                    'desc' => 'Easy Hotel Review plugin, extension for users to give review on their favorite accommodation.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB DB',
                    'slug' => 'ehb-db/ehb-db.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-db/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-db/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/db.png',
                    'desc' => 'Bookings Database solution for easy hotel. Whether you can export bookings and booking requests.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Week',
                    'slug' => 'ehb-week/ehb-week.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-week/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-week/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/week.png',
                    'desc' => 'Week reservation solution for WordPress. Whether you manage single day booking',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Single Day',
                    'slug' => 'ehb-db/ehb-db.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-single-day/',
                    'demo_url'  => 'https://themewant.com/downloads/eehb-single-day',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/db.png',
                    'desc' => 'Single day booking solution. Manage single day booking with custom pricing',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Min Max',
                    'slug' => 'ehb-min-max/ehb-min-max.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-min-max/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-min-max/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/min-max.png',
                    'desc' => 'Minimum and Maximum reservation conditional solution for WordPress. Whether you manage single day booking',
                    'is_pro' => true
                ),
                array(
                    'name' => 'EHB Currency',
                    'slug' => 'ehb-currency/ehb-currency.php',
                    'external_url'  => 'https://themewant.com/downloads/ehb-currency/',
                    'demo_url'  => 'https://themewant.com/downloads/ehb-currency/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/addons/currency.png',
                    'desc' => 'Easily enable multi-currency pricing on your hotel booking website. This add-on allowing guests to view and pay in their preferred currency.',
                    'is_pro' => true
                )
               
            );
        return $addons;
    }

    public function eshb_get_themes(){
        $addons = array(
                array(
                    'name' => 'Almaris - Hotel Booking WordPress Theme',
                    'slug' => 'almaris',
                    'external_url'  => 'https://themeforest.net/item/almaris-hotel-booking-wordpress-theme/55353710',
                    'demo_url'  => 'https://themewant.com/products/wordpress/landing/almaris/',
                    'thumbnail' => 'https://market-resized.envatousercontent.com/themeforest.net/files/542534134/preview.__large_preview.png?auto=format&q=94&cf_fit=crop&gravity=top&h=8000&w=590&s=9fd5e70fc097e3326b8910ead954ce48f2d0de1bd8f9e00fd6c2336fb677aaa9',
                    'desc' => 'A complete hotel booking solution for wordpress website.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'Moonlit - Hotel Booking WordPress Theme',
                    'slug' => 'moonlit',
                    'external_url'  => 'https://themeforest.net/item/moonlit-hotel-booking-wordpress-theme/57289887',
                    'demo_url'  => 'https://reactheme.com/products/wordpress/landing/moonlit/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/themes/moonlit.jpg',
                    'desc' => 'A complete hotel booking solution for wordpress website.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'Luxera - Hotel Booking WordPress Theme',
                    'slug' => 'luxera',
                    'external_url'  => 'https://themewant.com/downloads/luxera-hotel-booking-wordpress-theme/',
                    'demo_url'  => 'https://themewant.com/luxera/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/themes/luxera.jpg',
                    'desc' => 'A complete hotel booking solution for wordpress website.',
                    'is_pro' => true
                ),
                array(
                    'name' => 'CitySpot - Hotel Booking WordPress Theme',
                    'slug' => 'cityspot',
                    'external_url'  => 'https://themewant.com/downloads/cityspot-hotel-booking-wordpress-theme/',
                    'demo_url'  => 'https://themewant.com/downloads/cityspot-hotel-booking-wordpress-theme/',
                    'thumbnail' => ESHB_PL_URL.'admin/assets/img/thumbnails/themes/cityspot.jpg',
                    'desc' => 'A complete hotel booking solution for wordpress website.',
                    'is_pro' => true
                )
            );
        return $addons;
    }

    public function is_plugin_active_by_name( $plugin_name ) {
        $active_plugins = get_option( 'active_plugins' );
        
        foreach ( $active_plugins as $plugin ) {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
    
            if ( isset( $plugin_data['Name'] ) && stripos( $plugin_data['Name'], $plugin_name ) !== false ) {
                return true;
            }
        }
        
        return false;
    }
    
    public function eshb_get_addons_html(){
        $addons = $this->eshb_get_addons();
        ?>
            <div class="eshb-pro-addons-card-group">
                <?php 
                    foreach ($addons as $key => $addon) {

                        $thumbnail_url = isset($addon['thumbnail']) && !empty($addon['thumbnail']) ? $addon['thumbnail'] : ''; 
                        $plugin_slug = $addon['slug']; // Plugin main file path
                        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug;
                        $plugin_data = [];
                        if( file_exists($plugin_path) ){
                            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_slug);
                        }
                        
                        ?>
                            <div class="eshb-pro-addons-card">
                                <span class="eshb-pro-addons-base">
                                    <?php 
                                        if($addon['is_pro'] == true ){
                                            echo '<span class="eshb-pro-base">' . esc_html__('Pro', 'easy-hotel') . '</span>';
                                        }else{
                                            echo '<span class="eshb-free-base">' . esc_html__('Free', 'easy-hotel') . '</span>';
                                        }
                                    ?>
                                </span>
                                <div class="eshb-pro-addons-body">
                                    <div class="eshb-pro-addons-thumbnail">
                                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="plugin-icon">
                                        
                                    </div>
                                    <div class="eshb-pro-addons-content">
                                        <h3 class="eshb-pro-addons-name"><?php echo esc_html( $addon['name'] )?></h3>
                                        <p class="eshb-pro-addons-desc"><?php echo esc_html( $addon['desc'] )?></p>
                                        <div class="eshb-pro-addons-action">
                                    <input type="hidden" class="eshb-plugin-url" value="<?php echo esc_attr( $addon['external_url'] )?>">
                                    <input type="hidden" class="eshb-plugin-slug" value="<?php echo esc_attr( $addon['slug'] )?>">
                                    <?php 
                                       
                                        
                                    if (!file_exists($plugin_path)) {
                                        ?>
                                            <a href="<?php echo esc_url($addon['external_url']); ?>" class="eshb-action-btn-primary eshb-addons-action-button" target="_blank"><span class="button-text"><?php echo  esc_html__( 'Get It Now', 'easy-hotel' ); ?></span></a>
                                        <?php
                                    }
                                    ?>
                                    
                                    <a href="<?php echo esc_url($addon['demo_url']); ?>" class="eshb-action-btn-secondary eshb-addons-action-button" target="_blank"><span class="button-text"><?php echo esc_html__( 'Live Demo', 'easy-hotel' )?></span></a>
                                    <?php 
                                        if (file_exists($plugin_path)) {
                                           ?>
                                            <span class="eshb-plugin-install-status eshb-pro-addons-version"><?php echo esc_html__( 'Installed Version ', 'easy-hotel' ) . esc_html( $plugin_data['Version'] )?></span>
                                           <?php
                                        }
                                    ?>
                                </div>
                                    </div>
                                </div>
                                
                            </div>
                        <?php
                    }
                ?>
            </div>
            <?php 
                if( count($addons) > 20){
                    ?>
                        <a href="https://themewant.com/downloads" class="eshb-addons-action-button view-all-btn" target="_blank"><span class="button-text"><?php echo esc_html__( 'View All Addons', 'easy-hotel' )?></span></a>
                    <?php
                }
            ?>
            
        <?php
    }

    public function eshb_get_themes_html(){
        $themes = $this->eshb_get_themes();
        ?>
            <div class="eshb-pro-addons-card-group">
                <?php 

                    foreach ($themes as $key => $theme) {

                        $thumbnail_url = isset($theme['thumbnail']) && !empty($theme['thumbnail']) ? $theme['thumbnail'] : ''; 
                        $theme_slug = $theme['slug']; // Plugin main file path
                        $selected_theme = wp_get_theme($theme_slug);
                        $theme_data = [];
                        if( $selected_theme->exists() ){
                            $theme_data['version'] = $selected_theme->get('Version');
                        }
                        
                        
                        ?>
                            <div class="eshb-pro-addons-card">
                                <span class="eshb-pro-addons-base">
                                    <?php 
                                        if($theme['is_pro'] == true ){
                                            echo '<span class="eshb-pro-base">' . esc_html__('Pro', 'easy-hotel') . '</span>';
                                        }else{
                                            echo '<span class="eshb-free-base">' . esc_html__('Free', 'easy-hotel') . '</span>';
                                        }
                                    ?>
                                </span>
                                <div class="eshb-pro-addons-body">
                                    <div class="eshb-pro-addons-thumbnail">
                                        <img src="<?php echo esc_url($thumbnail_url); ?>" alt="plugin-icon">
                                        
                                    </div>
                                    <div class="eshb-pro-addons-content">
                                        <h3 class="eshb-pro-addons-name"><?php echo esc_html( $theme['name'] )?></h3>
                                        <p class="eshb-pro-addons-desc"><?php echo esc_html( $theme['desc'] )?></p>
                                        <div class="eshb-pro-addons-action">
                                    <input type="hidden" class="eshb-plugin-url" value="<?php echo esc_attr( $theme['external_url'] )?>">
                                    <input type="hidden" class="eshb-plugin-slug" value="<?php echo esc_attr( $theme['slug'] )?>">
                                    <?php 
                                       
                                    if (!isset($theme_data['version'])) {
                                    ?>
                                        <a href="<?php echo esc_url($theme['external_url']); ?>" class="eshb-action-btn-primary eshb-addons-action-button" target="_blank"><span class="button-text"><?php echo  esc_html__( 'Get It Now', 'easy-hotel' ); ?></span></a>
                                    <?php
                                    }
                                        
                                    ?>
                                    
                                    <a href="<?php echo esc_url($theme['demo_url']); ?>" class="eshb-action-btn-secondary eshb-addons-action-button" target="_blank"><span class="button-text"><?php echo esc_html__( 'Live Demo', 'easy-hotel' )?></span></a>
                                    <?php 
                                        if (isset($theme_data['version'])) {
                                           ?>
                                            <span class="eshb-plugin-install-status eshb-pro-addons-version"><?php echo esc_html__( 'Installed Version ', 'easy-hotel' ) . esc_html( $theme_data['version'] )?></span>
                                           <?php
                                        }
                                    ?>
                                </div>
                                    </div>
                                </div>
                                
                            </div>
                        <?php
                    }
                ?>
                
            </div>
            <?php 
                if( count($themes) > 20){
                    ?>
                        <a href="https://themeforest.net/user/reacthemes/portfolio" class="eshb-addons-action-button view-all-btn" target="_blank"><span class="button-text"><?php echo esc_html__( 'View All Themes', 'easy-hotel' )?></span></a>
                    <?php
                }
            ?>
            
        <?php
    }
}