<?php
/**
 * The implementation class for the MoneyButton Payment gateway.
 *
 * @package    WcGatewayMoneyButton\Gateway
 * @since      0.1.0
 */

namespace WcGatewayMoneyButton\Gateway;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use WcGatewayMoneyButton\Payment\MoneyButtonPayment;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\WcGatewayMoneyButtonException;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Implementation fo WC_Payment_Gateway for Money Button
 *
 * @since      0.1.0
 * @extends    WC_Payment_Gateway
 *
 * @package    WcGatewayMoneyButton\Gateway
 */
class PaymentGatewayImpl extends \WC_Payment_Gateway {

	/**
	 * Logger
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var WcGatewayMoneyButtonLogger $logger
	 */
	private $logger;

	/**
	 * Entity repository for MoneyButtonPayment
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var MoneyButtonPaymentRepository
	 */
	private $payment_repository;
	/**
	 * This is the static gateway id for WooCommerce, it must be unique across any installed gateways.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	public static $gateway_id = 'moneybutton';
	// Gateway configuration settings
	/**
	 * MoneyButton Setting: Is the plugin in dev mode.
	 *
	 * When in dev mode the MoneyButton is also set to devmode and the payment screen provides buttons to trigger callbacks for success and error scenarios.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var bool
	 */
	private $mb_dev_mode;
	/**
	 * MoneyButton Setting: This value is used as the data-to attribute when the MoneyButton is rendered
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string Money Button User Id, BSV Address, or paymail address
	 */
	private $mb_to;
	/**
	 * MoneyButton Setting: This value is used as data-clientIdentifier when the MoneyButton is rendered
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $mb_client_identifier;

	/**
	 * MoneyButton Setting: This value is used as data-label when the MoneyButton is rendered
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $mb_label;

	/**
	 * MoneyButton Setting: This value is used as data-buttonId when the MoneyButton is rendered
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $mb_button_id;

	/**
	 * MoneyButton Setting: This is value is used to verify the authenticity of incoming WebHook request from MoneyButton.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $mb_webhook_secret;


	/**
	 * The minimum order amount required for the MoneyButton to be available as a payment option.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var float
	 */
	private $minimum_amount;

	/**
	 * Gateway settings that MUST have a value, or the gateway will not function.
	 *
	 * @var string[]
	 */
	protected $required_settings = array(
		'mb_to',
		'mb_client_identifier',
		'mb_button_id',
		'mb_label',
		'mb_webhook_secret',
	);

	/**
	 * PaymentGatewayImpl constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentRepository $payment_repository Inject instance of payment repo. If not provided will instantiate new
	 */
	public function __construct( MoneyButtonPaymentRepository &$payment_repository = null ) {
		$this->logger = WcGatewayMoneyButtonLogger::get_logger();
		$this->id     = self::$gateway_id; // payment gateway id
		if ( empty( $payment_repository ) ) {
			global $wpdb;
			$this->payment_repository = new MoneyButtonPaymentRepository( $wpdb );
		} else {
			$this->payment_repository = &$payment_repository;
		}
		$this->method_title       = __( 'MoneyButton', 'wc-gateway-moneybutton' );
		$this->method_description = __( 'Allow customers to pay with MoneyButton', 'wc-gateway-moneybutton' ); // will be displayed on the admin payment methods page
		$this->has_fields         = false; // from a checkout/order submit perspective the gateway has no fields
		$this->supports           = array(
			'products',
		);

		$this->order_button_text    = __( 'Proceed to Payment', 'wc-gateway-moneybutton' ); // customize text for 'Place Order' button on checkout
		$this->view_transaction_url = 'https://whatsonchain.com/tx/%s'; // used to build the transaction link displayed in admin

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' ); // Payment Method Title
		$this->icon        = WC_GATEWAY_MONEYBUTTON_URL . '/dist/images/mb-logo-black-checkout.png';
		$this->description = $this->get_option( 'description' );

		// Money button settings
		$this->mb_dev_mode          = 'yes' === $this->get_option( 'mb_dev_mode' );
		$this->mb_to                = $this->get_option( 'mb_to' );
		$this->mb_client_identifier = $this->get_option( 'mb_client_identifier' );
		$this->mb_label             = $this->get_option( 'mb_label' );
		$this->mb_button_id         = $this->get_option( 'mb_button_id' );
		$this->mb_webhook_secret    = $this->get_option( 'mb_webhook_secret' );

		$this->minimum_amount = (float) $this->get_option( 'minimum_amount' );

		$this->store_raw_webhook = 'yes' === $this->get_option( 'store_raw_webhook' );

		add_action( 'wc_gateway_moneybutton_payment_pending', array( $this, 'payment_pending' ), 10, 2 );
		add_action( 'wc_gateway_moneybutton_payment_received', array( $this, 'payment_received' ), 10, 2 );
		add_action( 'wc_gateway_moneybutton_payment_completed', array( $this, 'payment_completed' ), 10, 2 );
		add_action( 'wc_gateway_moneybutton_payment_failed', array( $this, 'payment_failed' ), 10, 2 );

	}


	/**
	 * Override needs_setup to prevent ajax enable of gateway on WC "Payments" screen if missing required settings.
	 *
	 * @since 0.1.0
	 *
	 * @return bool True if Gateway requires addition settings to enable
	 */
	public function needs_setup() {
		return ! $this->has_required_settings();
	}


	/**
	 * Called when customer submits/places order.
	 *
	 * In this payment gateway, this process_payment method prepares the order for payment via Money Button. At this point the user has not actually been presented with the Money Button.
	 * Currently an order must exist *before* we can actually use money button, as the button includes order data that allows us to receive updates on the progress of the payment.
	 *
	 * For the purpose of this gateway, even though the order has not been paid, we will reduce stock levels here on order submission to prevent out of stock errors when the payment is captured in order-pay.
	 * We will also verify that the order total meets the minimum amount (if any) configured in the gateway settings.
	 *
	 * @see   https://docs.woocommerce.com/wc-apidocs/source-class-WC_Payment_Gateway.html#333-3
	 * @since 0.1.0
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$this->logger->debug( sprintf( 'process_payment(): [Order Id: %1$s]', $order_id ) );
		$order = wc_get_order( $order_id );

		// Check the minimum order amount against order total
		if ( $order->get_total() > 0 && false === $this->meets_minimum_order_amount( $order->get_total() ) ) {
			$this->logger->notice( 'An attempt was made to submit an order with Money Button payment method that did not meet the configured minimum order threshold.' );
			wc_add_notice(
				sprintf(
					/* translators: the placeholder is the minimum order amount in the store currency . */
					__( 'Sorry, your order total must be at least %1$s to pay with Money Button.', 'wc-gateway-moneybutton' ),
					wc_price( $this->minimum_amount )
				),
				'error'
			);

			return array(
				'result'   => 'fail',
				'redirect' => '',
			);
		}

		// reduce stock levels
		$this->maybe_reduce_stock_levels( $order );

		// Empty the cart
		WC()->cart->empty_cart();

		$this->logger->debug( sprintf( 'process_payment order_id: %s success', $order_id ) );

		// send them to order-pay
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);

	}


	/**
	 * Override is_available to include check for required opts. This prevents the gateway
	 * from being used if it somehow ends up set to "enabled" and bypassing the settings validation
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		$available = parent::is_available() && $this->has_required_settings();
		if ( false === $available ) {
			WcGatewayMoneyButtonLogger::get_logger()->info( get_class( $this ) . ' has been marked unavailable' );
		}

		return $available;
	}


	/**
	 * Verifies that gateway has all settings required for it to operate.
	 *
	 * @since 0.1.0
	 *
	 * @return bool true if all required settings have values.
	 */
	protected function has_required_settings(): bool {
		$obj_array = get_object_vars( $this );
		foreach ( $this->required_settings as $setting ) {
			$value = $obj_array[ $setting ];
			if ( strlen( $value ) === 0 ) {
				WcGatewayMoneyButtonLogger::get_logger()->info( get_class( $this ) . ' is missing required setting ' . $setting );

				return false;
			}
		}

		return true;
	}

	/**
	 * Check that the order meets the minimum order amount set in the gateway settings
	 *
	 * @since 1.0.0
	 *
	 * @param float $order_total total order amount
	 *
	 * @returns bool  True if order meets minimum amount configured in gateway
	 */
	public function meets_minimum_order_amount( $order_total ): bool {
		$this->logger->debug( sprintf( 'meets_minimum_order_amount(): [Order Total: %1$f] [Min Amt: %2$f]', $order_total, $this->minimum_amount ) );
		if ( $order_total < $this->minimum_amount ) {
			return false;
		} else {
			return true;
		}
	}


	/**
	 * Target callable for when MoneyButtonPayment emits payment_received
	 *
	 * @since 0.1.0
	 *
	 * @param int                $order_id
	 * @param MoneyButtonPayment $payment
	 *
	 * @throws \WC_Data_Exception If invalid order data is found.
	 */
	public function payment_received( int $order_id, MoneyButtonPayment $payment ) {
		$this->logger->debug( sprintf( 'payment_received(): [Order ID: %1$s] [Payment ID: %2$s]', $order_id, $payment->get_payment_id() ) );
		$order = new \WC_Order( $order_id );

		$order->set_transaction_id( $payment->get_transaction_id() );

		$default_note = sprintf(
			/* translators: The first place is the Money Button Payment ID */
			__( 'Money Button with Payment ID %1$s is valid. Order remains on hold pending notification of completed payment from Money Button.', 'wc-gateway-moneybutton' ),
			$payment->get_payment_id()
		);

		$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_received_note', $default_note, $order_id, $payment ), false );
		$this->maybe_reduce_stock_levels( $order );
		$order->save();

	}

	/**
	 * Target callable for when MoneyButtonPayment emits payment_completed
	 *
	 * @since 0.1.0
	 *
	 * @param int                $order_id
	 * @param MoneyButtonPayment $payment
	 *
	 * @throws \WC_Data_Exception If invalid order data is found.
	 */
	public function payment_completed( int $order_id, MoneyButtonPayment $payment ) {
		$this->logger->debug( sprintf( 'payment_completed(): [Order ID: %1$s] [Payment ID: %2$s]', $order_id, $payment->get_payment_id() ) );
		$order = new \WC_Order( $order_id );

		// Set transaction id only if it is not set.
		$order->set_transaction_id( $payment->get_transaction_id() );

		// Order is already cancelled. Add a note regarding payment of cancelled order and bail out
		if ( 'cancelled' === $order->get_status() ) {
			$this->logger->debug(
				sprintf(
					'payment_completed(): {Order ID: %1$s] [Payment Id: %2$s] : Order is cancelled. Potential refund required',
					$order_id,
					$payment->get_payment_id()
				)
			);
			$default_note = sprintf(
				/* translators: the first placeholder is the Payment ID of the Money Button payment. The second placeholder is the order number . */
				__(
					'A COMPLETED  payment event with Payment ID %1$s was received from Money Button for order %2$s when it was in a CANCELLED status.  You may need to manually issue a refund',
					'wc-gateway-moneybutton'
				),
				$payment->get_payment_id(),
				$order->get_order_number(),
				$payment->get_transaction_id()
			);
			$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_completed_when_cancelled_note', $default_note, $order_id, $payment ) );
			$order->save();

			return;
		}

		// Order does not need payment, (already paid), Add order note regarding potential refund and bail out
		if ( $order->is_paid() ) {
			$this->logger->info(
				sprintf(
					'payment_completed(): {Order ID: %1$s] [Payment Id: %2$s] : Received COMPLETED payment notification when order does not need_payment',
					$order_id,
					$payment->get_payment_id()
				)
			);
			$default_note = sprintf(
				/* translators: the first placeholder is the Payment ID of the Money Button payment. The second placeholder is the order number, The third placehold is the order status when this error occured. */
				__(
					'A  completed payment notification with Payment ID: %1$s was received for Order No. %2$s with status %3$s when it did not require payment. Yoy may need to manually issue a refund for incorrect payment',
					'wc-gateway-moneybutton'
				),
				$payment->get_payment_id(),
				$order->get_order_number(),
				$order->get_status()
			);
			$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_completed_when_paid_note', $default_note, $order_id, $payment ) );
			$order->save();

			return;
		} else {
			// Order needs payment

			// Check the amount from the payment matches the order total
			if ( ! ( abs( $payment->get_amount() - $order->get_total() ) < 0.00001 ) ) {
				// Payment does not match (is more or less than) expected payment amount (the order total).  Add order note for manual investigation/decision, do not progress to processing status.
				$default_note = sprintf(
					/* translators: the first place holder is the amount (in fiat currency) of the completed payment. The second placeholder is the total amount of the order */
					__(
						'Money Button payment was completed, however payment amount, %1$.2f no longer matches order total %2$.2f. Manual intervention required to progress order.',
						'wc-gateway-moneybutton'
					),
					$payment->get_amount(),
					$order->get_total()
				);
				$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_completed_incorrect_note', $default_note, $order_id, $payment ) );
				$order->save();
				// Fire Hook
				do_action( 'wc_gateway_moneybutton_payment_incorrect', $order, $payment );
			} else {
				// Payment is complete and for the correct amount matching order total. Add order note and progress order to next status.

				$default_note = sprintf(
					/* translators: the first placeholder is the Payment ID of the Money Button payment. The second placeholder is the order number */
					__( 'Money Button payment with Payment ID %1$s complete for Order No. %2$s .', 'wc-gateway-moneybutton' ),
					$payment->get_payment_id(),
					$order->get_order_number()
				);
				$this->logger->info( $default_note );
				$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_completed_note', $default_note, $order_id, $payment ), true );

				if ( false === $order->payment_complete() ) { // Try to complete payment to progress order
					$this->logger->error(
						sprintf(
							'completed_payment(): {Order ID: %1$s] [Payment Id: %2$s] : payment_complete failed.',
							$order_id,
							$payment_event->get_payment_id()
						)
					);
					$default_note = sprintf(
						/* translators: the first placeholder is the Payment ID of the Money Button payment.  */
						__(
							'Money Button payment with Payment ID %1$s complete however there was an unexpected error progressing to the next order status. It may need to be progressed to the next order status manually.',
							'wc-gateway-moneybutton'
						),
						$payment_event->get_payment_id(),
						$order->get_order_number()
					);
					$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_completed_error_note', $default_note, $order_id, $payment ) );
				}
				$this->maybe_reduce_stock_levels( $order );
				$order->save();

			}
		}
	}

	/**
	 * Target callable for when MoneyButtonPayment emits payment_failed.
	 *
	 * @since 0.1.0
	 *
	 * @param int                $order_id
	 * @param MoneyButtonPayment $payment
	 *
	 * @throws \WC_Data_Exception Throws exception when invalid order data is found.
	 */
	public function payment_failed( int $order_id, MoneyButtonPayment $payment ) {
		$this->logger->debug( sprintf( 'payment_failed(): [Order ID: %1$s] [Payment ID: %2$s]', $order_id, $payment->get_payment_id() ) );
		$order = new \WC_Order( $order_id );
		$order->set_transaction_id( $payment->get_transaction_id() );
		$default_note = sprintf(
			/* translators: The first placeholder is the money button payment id */
			__( 'Money Button payment with Payment ID %1$s Failed.', 'wc-gateway-moneybutton' ),
			$payment->get_payment_id()
		);

		$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_failed_note', $default_note, $order_id, $payment ), true );
		$order->update_status( 'failed' );
		$order->save();
	}

	/**
	 * Target callable for when MoneyButtonPayment emits payment_pending
	 *
	 * @since 0.1.0
	 *
	 * @param int                $order_id
	 * @param MoneyButtonPayment $payment
	 */
	public function payment_pending( int $order_id, MoneyButtonPayment $payment ) {
		$this->logger->debug( sprintf( 'payment_pending(): [Order ID: %1$s] [Payment ID: %2$s]', $order_id, $payment->get_payment_id() ) );
		$order = new \WC_Order( $order_id );
		// Don't think this really needs an order note. Just make sure it is on hold and stock is reduced if it hasn't been reduced allready

		if ( 'pending' === $order->get_status() ) {
			$this->maybe_reduce_stock_levels( $order );
			$order->update_status( 'on-hold' );
			$order->save();
		}

	}


	/**
	 * Reduce stock levels if the have not already been reduces.
	 *
	 * @since 0.1.0
	 *
	 * @param \WC_Order $order order
	 *
	 * @return void
	 */
	private function maybe_reduce_stock_levels( $order ): void {
		$order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );
		if ( ! $order_stock_reduced ) {
			wc_reduce_stock_levels( $order->get_id() );
		}
	}


	/**
	 * Getter MbDevMode
	 *
	 * @return bool
	 */
	public function is_mb_dev_mode(): bool {
		return $this->mb_dev_mode;
	}


	/**
	 * Getter MbTo
	 *
	 * @return string
	 */
	public function get_mb_to(): string {
		return $this->mb_to;
	}

	/**
	 * Getter MbClientIdentifier
	 *
	 * @return string
	 */
	public function get_mb_client_identifier(): string {
		return $this->mb_client_identifier;
	}

	/**
	 * Getter MbLabel
	 *
	 * @return string
	 */
	public function get_mb_label(): string {
		return $this->mb_label;
	}

	/**
	 * Getter MbButtonId
	 *
	 * @return string
	 */
	public function get_mb_button_id(): string {
		return $this->mb_button_id;
	}

	/**
	 * Getter MbWebhookSecret
	 *
	 * @return string
	 */
	public function get_mb_webhook_secret(): string {
		return $this->mb_webhook_secret;
	}

	/**
	 * Getter MinimumAmount
	 *
	 * @return float
	 */
	public function get_minimum_amount(): float {
		return $this->minimum_amount;
	}

	/**
	 * Getter StoreRawWebhook
	 *
	 * @return bool
	 */
	public function is_store_raw_webhook() {
		return $this->store_raw_webhook;
	}


}
