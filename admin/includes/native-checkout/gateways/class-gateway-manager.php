<?php
/**
 * Gateway manager / loader.
 *
 * Holds a registry of payment gateways and lets the checkout class
 * fetch enabled ones for the UI / processing. Other plugins can hook
 * into 'eshb_native_checkout_gateways' to register more gateways
 * without touching this plugin.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ESHB_Native_Gateway_Manager {

    private static $instance = null;

    /** @var ESHB_Native_Abstract_Gateway[] */
    private $gateways = [];

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Built-in gateways. Cash on Delivery is registered first so it
        // is the natural fallback default when no online gateway is set up.
        $this->register_gateway( new ESHB_Native_COD_Gateway() );
        $this->register_gateway( new ESHB_Native_PayPal_Gateway() );

        /**
         * Filter the array of registered gateway instances. Third-party
         * gateways should be appended here as ESHB_Native_Abstract_Gateway
         * instances.
         *
         * @param ESHB_Native_Abstract_Gateway[] $gateways
         */
        $this->gateways = apply_filters( 'eshb_native_checkout_gateways', $this->gateways );
    }

    public function register_gateway( ESHB_Native_Abstract_Gateway $gateway ) {
        $this->gateways[ $gateway->get_id() ] = $gateway;
    }

    /**
     * @return ESHB_Native_Abstract_Gateway[]
     */
    public function get_gateways( $only_enabled = true ) {
        if ( ! $only_enabled ) {
            return $this->gateways;
        }
        return array_filter( $this->gateways, function ( $gw ) {
            return $gw->is_enabled();
        } );
    }

    public function get_gateway( $id ) {
        return $this->gateways[ $id ] ?? null;
    }
}
