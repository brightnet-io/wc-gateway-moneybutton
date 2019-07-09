<?php
/**
 *  Responsible for adding/showing and hiding the plugins various admin notices.
 *
 * @package WcGatewayMoneyButton\Admin\Order
 */

namespace WcGatewayMoneyButton\Admin\Order;

use WcGatewayMoneyButton\Admin\AbstractMetaBox;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use WcGatewayMoneyButton\Payment\MoneyButtonPayment;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PaymentStatusMetaBox
 *
 * @package WcGatewayMoneyButton\Admin\Order
 */
class PaymentStatusMetaBox extends AbstractMetaBox {

	/**
	 * Template to be rendered
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string $template
	 */
	private $template = WC_GATEWAY_MONEYBUTTON_PATH . '/templates/admin/order/payment-status-meta-box.php';

	/**
	 * OrderPaymentMetaBox constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		parent::__construct( 'wc-gateway-moneybutton-order-payment-metabox', __( 'Money Button Payment', 'wc-gateway-moneybutton' ), 'side', 'default', array( 'shop_order' ) );

	}

	/**
	 * Helper function to return wc_help_tip based on a money button payment status.
	 *
	 * @since 0.1.0
	 *
	 * @param string $status status of money button payment
	 *
	 * @return string The help tip for a particular status
	 */
	public function get_status_help_tip( string $status ): string {
		switch ( $status ) {
			case 'PENDING':
				return wc_help_tip( __( 'tx has been built but not yet signed', 'wc-gateway-moneybutton' ) );

			case 'RECEIVED':
				return wc_help_tip( __( 'tx has been signed, broadcast, and is valid', 'wc-gateway-moneybutton' ) );

			case 'COMPLETED':
				return wc_help_tip( __( 'tx has been confirmed in a block', 'wc-gateway-moneybutton' ) );
			case 'FAILED':
				return wc_help_tip( __( 'tx has been rejected by the network', 'wc-gateway-moneybutton' ) );
			default:
				return wc_help_tip( 'This is a previously unknown status that the gateway is not away of ' );

		}
	}

	/**
	 * Render the contents of the meta-box.
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Post $post Current post, in this case a shop_order
	 */
	public function render( \WP_Post $post ) {
		WcGatewayMoneyButtonLogger::get_logger()->debug( get_class( $this ) . ' render' );

		if ( ! is_readable( $this->template ) ) {
			WcGatewayMoneyButtonLogger::get_logger()->error( 'Unable to read template file ' . $template );

			return;
		}

		$button_swipe_meta = get_metadata( 'post', $post->ID, '_wc_gateway_moneybutton_swipe', true );
		global $wpdb;
		$repo    = new MoneyButtonPaymentRepository( $wpdb );
		$payment = $repo->find_by_order_id( $post->ID );

		if ( ! empty( $payment ) ) {
			return $this->render_template( $payment );
		} elseif ( ! empty( $button_swipe_meta ) ) {
			return $this->render_swipe_only_fallback( $button_swipe_meta );
		} else {
			return $this->render_error();
		}
	}


	/**
	 * Render content when all that has been received for Payment is a successful button swipe.
	 *
	 * There may be a small period of time between the user paying with moneybutton and receiveing the first webhook call. This method
	 * renders an appropriate method when we know the customer successfully 'swiped' but have yet to received any webhook calls.
	 *
	 * @since 0.1.0
	 *
	 * @param string $button_swipe_meta content of meta data that is added by PaymentForm submission on successful Money Button payment. ie: Payment ID
	 */
	protected function render_swipe_only_fallback( $button_swipe_meta ) {
		$message = sprintf(
			/* translators: The first placeholder is the Payment ID that was returned from a successful 'swipe' of the Money Buttonn */
			__( 'Successful button swipe recorded with Payment ID %1$s. Waiting for Money Button payment notifications.', 'wc-gateway-moneybutton' ),
			$button_swipe_meta
		);

		echo '<p>';
		echo esc_html( $message );
		echo '</p>';
	}

	/**
	 * Render main template
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPayment $payment
	 */
	protected function render_template( $payment ) {
		include $this->template;
	}

	/**
	 * Render an error
	 *
	 * @since 0.1.0
	 */
	protected function render_error() {
		echo '<p style="color: red;">Interim MoneyButton payment response not available</p>';
	}
}
