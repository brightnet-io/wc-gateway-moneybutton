<?php
/**
 *  Provides buttons that allow administrators to send mock payment webhook calls in devmode.
 *
 * @package WcGatewayMoneyButton\Admin\Order
 */

namespace WcGatewayMoneyButton\Admin\Order;

use WcGatewayMoneyButton\Admin\AbstractMetaBox;
use WcGatewayMoneyButton\Core\PaymentHelper;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use WcGatewayMoneyButton\Payment\MoneyButtonPayment;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MockWebhookRequestsMetaBox
 *
 * @package WcGatewayMoneyButton\Admin\Order
 */
class MockWebhookRequestsMetaBox extends AbstractMetaBox {


	/**
	 * @var \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * MockWebhookRequestsMetaBox constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		parent::__construct( 'wc-gateway-moneybutton-mock-webhooks-metabox', __( 'Money Button Dev Mode', 'wc-gateway-moneybutton' ), 'side', 'default', array( 'shop_order' ) );
		$this->logger   = WcGatewayMoneyButtonLogger::get_logger();
		$this->template = WC_GATEWAY_MONEYBUTTON_PATH . '/templates/admin/order/mock-webhooks-meta-box.php';
	}


	/**
	 * Render the contents of the meta-box.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Post $post Current post, in this case a shop_order
	 */
	public function render( \WP_Post $post ) {
		$this->logger->debug( 'render()' );

		if ( ! is_readable( $this->template ) ) {
			WcGatewayMoneyButtonLogger::get_logger()->error( 'Unable to read template file ' . $template );

			return;
		}

		$order = new \WC_Order( $post->ID );

		return $this->render_template( $order );
	}


	/**
	 * Render main template
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order
	 */
	protected function render_template( \WC_Order $order ) {
		$gateway           = PaymentHelper::get_gateway();
		$button_swipe_meta = $order->get_meta( '_wc_gateway_moneybutton_swipe', true );
		global $wpdb;
		$repo = new MoneyButtonPaymentRepository( $wpdb );
		/** @var MoneyButtonPayment $payment */
		$payment                  = $repo->find_by_order_id( $order->get_id() );
		$button_data              = (object) [
			'order_key' => $order->get_order_key(),
			'order_id'  => $order->get_id(),
			'cart_hash' => $order->get_cart_hash(),
		];
		$json_encoded_button_data = wp_json_encode( $button_data );

		$timestamp = new \DateTime();
		$now       = $timestamp->format( 'Y-m-d\TH:i:s.000' ) . 'Z';

		$txid = uniqid();

		// Setup fake received data
		$received_data = (object) [
			'secret'  => $gateway->get_mb_webhook_secret(),
			'payment' => (object) [
				'id'             => ! empty( $payment ) ? $payment->get_payment_id() : $button_swipe_meta,
				'createdAt'      => ! empty( $payment ) ? $payment->get_created_at() : $now,
				'updatedAt'      => $now,
				'txid'           => ! empty( $payment ) ? $payment->get_transaction_id() : $txid,
				'normalizedTxid' => 'not_used',
				'amount'         => $order->get_total(),
				'currency'       => $order->get_currency(),
				'satoshis'       => '55555',
				'status'         => 'RECEIVED',
				'buttonId'       => 'wc-gateway-moneybutton',
				'buttonData'     => $json_encoded_button_data,
				'paymentOutputs' => array(
					(object) [
						'id'        => 123456,
						'paymentId' => ! empty( $payment ) ? $payment->get_payment_id() : $button_swipe_meta,
						'createdAt' => ! empty( $payment ) ? $payment->get_created_at() : $now,
						'updatedAt' => $now,
						'amount'    => $order->get_total(),
						'currency'  => $order->get_currency(),
						'satoshis'  => '55555',
						'to'        => '0000',
						'type'      => 'USER',
					],
				),
			],
		];

		$received_json = htmlspecialchars( wp_json_encode( $received_data ), ENT_NOQUOTES, 'UTF-8' );

		// Setup fake completed data
		$completed_data                  = $received_data;
		$completed_data->payment->status = 'COMPLETED';
		$completed_json                  = htmlspecialchars( wp_json_encode( $completed_data ), ENT_NOQUOTES, 'UTF-8' );

		// Setup fake failed data
		$failed_data                  = $received_data;
		$failed_data->payment->status = 'FAILED';
		$failed_json                  = htmlspecialchars( wp_json_encode( $failed_data ), ENT_NOQUOTES, 'UTF-8' );

		include $this->template;
	}

}
