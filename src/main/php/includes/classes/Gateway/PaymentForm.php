<?php
/**
 * Class responsible for preparing and rendering the payment form Money Button fragment
 *
 * @package WcGatewayMoneyButton\Gateway
 * @since   0.1.0
 */

namespace WcGatewayMoneyButton\Gateway;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backing class for rendering money button fragment
 *
 * @since   0.1.0
 *
 * @package WcGatewayMoneyButton\Gateway
 */
class PaymentForm {
	/**
	 * Logger
	 *
	 * @since 0.1.0
	 *
	 * @var WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var \WC_Order
	 */
	private $order;


	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var PaymentGatewayImpl
	 */
	private $moneybutton_gateway;

	/**
	 * PaymentForm constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param int                $order_id
	 * @param PaymentGatewayImpl $moneybutton_gateway
	 */
	public function __construct( int $order_id, PaymentGatewayImpl $moneybutton_gateway ) {
		$this->logger              = WcGatewayMoneyButtonLogger::get_logger();
		$this->order               = wc_get_order( $order_id );
		$this->moneybutton_gateway = $moneybutton_gateway;

	}


	/**
	 * Renders the form
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render(): void {

		$moneybutton_element = $this->get_moneybutton_html_output();
		$dev_mode            = $this->moneybutton_gateway->is_mb_dev_mode();
		$order_key           = $this->order->get_order_key();

		include WC_GATEWAY_MONEYBUTTON_PATH . 'templates/wc-gateway-moneybutton-paymentform-template.php';
	}

	/**
	 * Handle submission of payment form.
	 *
	 * @since 0.1.0
	 */
	public function form_submission() {

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wc-gateway-moneybutton-payment_' . $_REQUEST['key'] ) ) {
			$this->logger->info( 'Money Button Payment response form was submitted with an invalid nonce' );
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( new \WP_Error( 403, 'Authorization Failed' ) );
		}

		$payment_id    = sanitize_text_field( $_POST['wc-gateway-moneybutton-payment_id'] );
		$order_key     = sanitize_key( $_POST['wc-gateway-moneybutton-order_key'] );
		$cart_hash     = sanitize_text_field( $_POST['wc-gateway-moneybutton-cart_hash'] );
		$form_order_id = (int) sanitize_key( $_POST['wc-gateway-moneybutton-order_id'] );

		$this->logger->debug(
			sprintf(
				'form_submission(): Submitted data [Payment ID: %1$s] [Order Key: %2$s] [Cart Hash: %3$s] [Order ID: %4$s]',
				$payment_id,
				$order_key,
				$cart_hash,
				$form_order_id
			)
		);
		if (
			$this->order->get_id() !== $form_order_id
			|| strtolower( $this->order->get_order_key() ) !== $order_key  // Note sanitize_key converts eveything to lowercase.
			|| $this->order->get_cart_hash() !== $cart_hash
			|| empty( $payment_id ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_die( new \WP_Error( 400, esc_html__( 'Invalid Money Button payment response', 'wc-gateway-moneybutton' ) ) );
		} else {

			$this->order->add_meta_data( '_wc_gateway_moneybutton_swipe', $payment_id );

			$message = sprintf(
				/* translators: the placeholder is the Money Button Payment ID */
				__( 'Money Button swipe successful. Waiting for payment confirmation [Payment ID: %1$s]', 'wc-gateway-moneybutton' ),
				$payment_id
			);
			$this->order->set_status( 'on-hold', $message );
			$this->order->save();
			$this->logger->debug( 'form_submission(): order updated. redirecting to order received' );
			$this->success_redirect();
		}
	}


	/**
	 * Performs redirect to order received on success.
	 *
	 * @since  0.1.0
	 * @access protected
	 */
	protected function success_redirect() {
		$return_url = $this->order->get_checkout_order_received_url();
		wp_safe_redirect( $return_url );
		exit;
	}

	/**
	 * Get html element for money button for this order
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	private function get_moneybutton_html_output(): string {

		$button_data              = (object) [
			'order_key' => $this->order->get_order_key(),
			'order_id'  => $this->order->get_id(),
			'cart_hash' => $this->order->get_cart_hash(),
		];
		$json_encoded_button_data = htmlspecialchars( wp_json_encode( $button_data ), ENT_QUOTES, 'UTF-8' );

		$moneybutton_html = '<div class="money-button" '
							. ' data-to="' . $this->moneybutton_gateway->get_mb_to() . '"
                     data-type="buy"
                     data-amount="' . $this->order->get_total() . '"
                     data-currency="' . $this->order->get_currency( 'view' ) . '"
                     data-label="' . $this->moneybutton_gateway->get_mb_label() . '"
                     data-client-identifier="' . $this->moneybutton_gateway->get_mb_client_identifier() . '"
                     data-button-id="' . $this->moneybutton_gateway->get_mb_button_id() . '"
                     data-button-data="' . $json_encoded_button_data . '"
                     data-on-payment="wooMoneyButtonOnPayment"
                     data-on-error="wooMoneyButtonOnError" ';

		if ( true === $this->moneybutton_gateway->is_mb_dev_mode() ) {
			$moneybutton_html .= 'data-dev-mode="true"';
		}
		$moneybutton_html .= '></div>';

		$moneybutton_html .= '<div class="money-button-error"></div>';

		return $moneybutton_html;
	}
}
