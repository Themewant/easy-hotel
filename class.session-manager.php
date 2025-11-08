<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly.
class ESHB_Session_Manager {

    protected $cookie_name = 'eshb_session';
    protected $cookie_lifetime = 3600;

    public function __construct( $cookie_name = '', $cookie_lifetime = 3600 ) {
        if ( ! empty( $cookie_name ) ) {
            $this->cookie_name = sanitize_key( $cookie_name );
        }
        $this->cookie_lifetime = intval( $cookie_lifetime );
        add_action( 'init', [ $this, 'init' ], 1 );
    }
    
    /**
     * Initialize cookie if not exists
     */
    public function init() {
        if ( ! isset( $_COOKIE[ $this->cookie_name ] ) ) {
            $this->set_cookie_data( [] );
        }
    }

    /**
     * Set entire session data as array
     */
    protected function set_cookie_data( $data ) {
        if ( ! headers_sent() ) {
            setcookie( $this->cookie_name, json_encode( $data ), time() + $this->cookie_lifetime, '/' );
        }
        $_COOKIE[ $this->cookie_name ] = json_encode( $data ); // for immediate access
    }

    /**
     * Get full session data
     */
    public function all() {
        return isset( $_COOKIE[ $this->cookie_name ] )
            ? json_decode( sanitize_textarea_field( wp_unslash( $_COOKIE[ $this->cookie_name ] ) ), true )
            : [];
    }


    /**
     * Get a value by key
     */
    public function get( $key, $default = null ) {
        $data = $this->all();
        return $data[$key] ?? $default;
    }

    /**
     * Set a value by key
     */
    public function set( $key, $value ) {
        $data = $this->all();
        $data[$key] = $value;
        $this->set_cookie_data( $data );
    }

    /**
     * Remove a key from session
     */
    public function remove( $key ) {
        $data = $this->all();
        unset( $data[$key] );
        $this->set_cookie_data( $data );
    }

    /**
     * Clear all session data
     */
    public function clear() {
        $this->set_cookie_data( [] );
    }
}
