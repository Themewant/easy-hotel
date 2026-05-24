<?php
/**
 * Abstract base class for native-checkout payment gateways.
 *
 * Concrete gateways extend this and implement the create_payment() /
 * capture_payment() lifecycle. Adding a new gateway is a matter of
 * dropping a subclass into ./gateways/ and registering it with the
 * gateway manager — no edits to the checkout flow are required.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class ESHB_Native_Abstract_Gateway {

    /** Unique gateway identifier (e.g. 'paypal'). */
    protected $id = '';

    /** Human-readable title shown in the checkout. */
    protected $title = '';

    /** Short description shown under the title. */
    protected $description = '';

    public function get_id() {
        return $this->id;
    }

    public function get_title() {
        return $this->title;
    }

    public function get_description() {
        return $this->description;
    }

    /**
     * Whether this gateway is fully configured and enabled.
     */
    abstract public function is_enabled();

    /**
     * Frontend payload (script handles, API keys, gateway-specific
     * settings) needed by the JS layer to render the gateway button.
     *
     * @return array
     */
    public function get_frontend_data() {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
        ];
    }

    /**
     * Create a server-side payment intent / order for the given reservation.
     * Returns gateway-specific data the JS layer needs to launch the flow
     * (e.g. PayPal order id).
     *
     * @return array { success: bool, data: array, message?: string }
     */
    abstract public function create_payment( array $reservation, array $customer, array $pricing );

    /**
     * Verify and capture a previously-created payment. Called server-side
     * once the JS layer signals user approval. Must return a normalized
     * result so the checkout flow can persist payment metadata.
     *
     * @return array { success: bool, transaction_id?: string, amount?: float, currency?: string, mode?: string, raw?: array, message?: string }
     */
    abstract public function capture_payment( array $params );
}
