<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WC_Gateway_UPI extends WC_Payment_Gateway {

    public function __construct() {
        $this->id                 = 'upi_gateway';
        $this->method_title       = __( 'UPI Payment', 'woocommerce' );
        $this->method_description = __( 'Pay using UPI ID or QR Code.', 'woocommerce' );
        $this->title              = 'UPI Payment';
        $this->has_fields         = true;

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->upi_id   = $this->get_option('upi_id');
        $this->upi_name = $this->get_option('upi_name');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable UPI Payment',
                'default' => 'yes'
            ],
            'upi_id' => [
                'title'       => 'UPI ID',
                'type'        => 'text',
                'description' => 'Your UPI VPA (e.g., name@upi)',
                'default'     => ''
            ],
            'upi_name' => [
                'title'       => 'Account Holder Name',
                'type'        => 'text',
                'description' => 'Name associated with UPI ID',
                'default'     => ''
            ]
        ];
    }

    public function payment_fields() {
        echo '<p>Scan the QR code or use the button below to pay via UPI.</p>';
        $upi_url = 'upi://pay?pa=' . urlencode($this->upi_id) . '&pn=' . urlencode($this->upi_name) . '&cu=INR&am=' . WC()->cart->total;
        echo '<img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($upi_url) . '" />';
        echo '<p><a href="' . esc_url($upi_url) . '" class="button alt">Pay via UPI App</a></p>';
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // ⚠️ Ye automatic verification nahi karega, manual confirm karna hoga
        $order->update_status('on-hold', 'Awaiting UPI payment confirmation from merchant.');

        // Redirect to thank you page
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }
}
