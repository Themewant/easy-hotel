<?php
class ESHB_NOTICE{ 

    // Get Instance
    private static $_instance = null;
    public static function instance(){
        if( is_null( self::$_instance ) ){
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct(){
        add_action('admin_notices', array($this, 'eshb_woocommerce_missing_notice'));
    }

    public function eshb_woocommerce_missing_notice(){
        if ( ! class_exists( 'WooCommerce' ) ) {
            $notice_id = 'woocommerce_missing';
        
            $content = 'Easy Hotel Plugin requires WooCommerce to function. Please install and activate WooCommerce to use this plugin\'s features. Visit Plugins > Add New and search for \'WooCommerce\' to install.';
            $action_buttons = array(
                array(
                    'title' => 'Install WooCommerce',
                    'url'   => admin_url().'plugin-install.php?s=woocommerce&tab=search&type=term',
                ),
            );
            
            ?>
            <div data-notice_id="<?php echo esc_attr( $notice_id )?>" id="eshb-notice-<?php echo esc_attr( $notice_id )?>" class="eshb-notice notice is-dismissible" expired_time="<?php echo esc_attr( $notice_id )?>" dismissible="global">

            
                <div class="notice-right-container ">
                    <div class="notice-contents">
                        
                        <?php 
                            if(!empty($content)){
                                echo wp_kses_post( $content );
                            }
                        ?>              
                    </div>

                    <?php 
                        if(!empty($action_buttons) && count($action_buttons) > 0){
                            
                            echo '<div class="eshb-notice-action-buttons">'; 
                                foreach ($action_buttons as $key => $button) {
                                    $action_url = isset($button['url']) && !empty($button['url']) ? $button['url'] : '';
                                    $action_title = isset($button['title']) && !empty($button['title']) ? $button['title'] : '';
                                    if(!empty($action_url)){
                                        echo '<a href="'. esc_url($action_url) .'" class="eshb-notice-button button-small">'. esc_html($action_title) .'</a>';
                                    }
                                    
                                }
                            echo '</div>';
                            
                        }
                    ?>
                    
                </div>
                <div style="clear:both"></div>

            </div>
        <?php
        }
        
    }

}

// Instantiate the class to ensure the menu is registered
if ( class_exists( 'ESHB_NOTICE' ) ) {
    $ESHB_NOTICE = new ESHB_NOTICE();
}



