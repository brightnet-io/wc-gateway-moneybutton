<?php
/**
 * Handler for webhook requests received from Money Button
 *
 * @package WcGatewayMoneyButon\Gateway;
 *
 * @since   0.1.0
 */

namespace WcGatewayMoneyButton\Gateway;

use WcGatewayMoneyButton\Core\UnprocessableEntityException;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use WcGatewayMoneyButton\Payment\EventSequenceException;
use WcGatewayMoneyButton\Payment\InvalidTransactionIdException;
use WcGatewayMoneyButton\Payment\MissingPaymentDataException;
use WcGatewayMoneyButton\Payment\MoneyButtonPayment;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentEvent;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\Payment\RawPaymentEvent;
use WcGatewayMoneyButton\Payment\RawPaymentEventRepository;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WebhookHandler
 *
 * @since 0.1.0
 */
class WebhookHandler {
	/**
	 * Logger
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * WebhookHandler constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->logger = WcGatewayMoneyButtonLogger::get_logger();

	}

	/**
	 * Register route with WP REST API
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_api_route(): void {
		$this->logger->debug( 'register_api_route()' );
		register_rest_route(
			'wc-gateway-moneybutton/v1',
			'/webhook',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'request' ),
			)
		);
	}

	/**
	 *  Handle Money Button webhook request
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return void
	 */
	public function request( \WP_REST_Request $request ): void {
		$this->logger->debug( 'request(): begin' );

		// Must use ssl
		if ( ! is_ssl() ) {
			$error = new \WP_Error( 426, 'Secure HTTPS connection required' );

			wp_send_json_error( $error, $error->get_error_code() );
		}

		$wc_gateways      = new \WC_Payment_Gateways();
		$payment_gateways = $wc_gateways->payment_gateways();
		// Bail if no gateway
		if ( ! isset( $payment_gateways['moneybutton'] ) ) {
			$error = new \WP_Error( 500, 'Gateway is not available to handle requests' );
			wp_send_json_error( $error, $error->get_error_code() );
		}
		/** @var PaymentGatewayImpl $payment_gateway */
		$payment_gateway = $payment_gateways['moneybutton'];

		// Get the post body
		$json_string = $request->get_body();

		// Store the raw request body in the database for potential audit or debugging

		if ( $payment_gateway->is_store_raw_webhook() ) {
			try {
				global $wpdb;
				$raw_repo = new RawPaymentEventRepository( $wpdb );
				$redacted = preg_replace( '/\"secret":.+?,/', '"secret": "REDACTED",', $json_string );
				$raw_data = new RawPaymentEvent( $redacted, gmdate( 'Y-m-d\TH:i:s\Z' ) );
				$raw_data = $raw_repo->save( $raw_data );

			} catch ( WcGatewayMoneyButtonException $e ) {
				$this->logger->error( 'Failed saving raw money button event data.' );
				$this->logger->error( $redacted );
			}
		}

		$decoded = json_decode( $json_string, true );
		// Basic structure check
		if ( ! isset( $decoded['secret'] ) || ! isset( $decoded['payment'] ) ) {
			$error = new \WP_Error( 422, 'Data structure is invalid when decoded' );
			wp_send_json_error( $error, $error->get_error_code() );
		}

		// Bail if no secret.
		$secret = $payment_gateway->get_mb_webhook_secret();
		if ( empty( $secret ) ) {
			$this->logger->error( 'No webhook secret is configured for Money Button Gateway. All webhook events are ignored' );
			$error = new \WP_Error( 500, 'Payment Gateway missing configurations setting to validate webhook secret' );
			wp_send_json_error( $error, $error->get_error_code() );
		}

		// Verify secret in payload against settings
		if ( $secret !== $decoded['secret'] ) {
			$error = new \WP_Error( 403, 'I know something you don\'t know' );

			wp_send_json_error( $error, $error->get_error_code() );
		}
		$json_params   = apply_filters( 'wc_gateway_moneybutton_webhook_params', $decoded );
		$payment_event = MoneyButtonPaymentEvent::from_webhook_object( $json_params );

		// Lookup from order_key, verify returned order_id
		$order_id = (int) wc_get_order_id_by_order_key( $payment_event->get_order_key() );
		if ( $order_id !== $payment_event->get_order_id() ) {
			$this->logger->warning(
				sprintf(
					'A Money Button payment notification was received with an order key/id pair that did not match. The submitted pair was %1$s,%2$s. The order id matching the given key was %3$s',
					$payment_event->get_order_key(),
					$payment_event->get_order_id(),
					$order_id
				)
			);
			$error = new \WP_Error( 403, 'Cheatin huh?' );

			wp_send_json_error( $error, $error->get_error_code() );
		}

		try {
			global $wpdb;
			$payment_repo = new MoneyButtonPaymentRepository( $wpdb );
			$payment      = $payment_repo->find_by_payment_id( $payment_event->get_payment_id() );
			// no payment, create new
			if ( empty( $payment ) ) {
				$payment = new MoneyButtonPayment( $payment_event->get_payment_id(), $order_id );
			}

			try {
				$payment->apply( $payment_event );
			} catch ( \InvalidArgumentException $e ) {
				$this->logger->warning(
					sprintf(
						'Invalid Event received for Payment ID: %1$s, Event Status: %2$s, Order Id: %3$s',
						$payment_event->get_payment_id(),
						$payment_event->get_status(),
						$payment_event->get_order_id()
					)
				);
				$this->logger->warning( 'ROOT CAUSE: ' . $e->getMessage() );
				wp_send_json_error( new UnprocessableEntityException( 'Invalid Data' ) );
			} catch ( EventSequenceException $e ) {
				$default_note = sprintf(
					/* translators: The first place holder is the status of the event/update received from Money Button. The second placeholder is the Money Button PaymentID.  The third placeholder is the current status of the Payment*/
					__(
						'An unexpected payment notification with status %1$s for Payment ID %2$s was received from Money Button when the Payment was already %3$s. This notification was ignored.',
						'wc-gateway-moneybutton'
					),
					$e->get_event_status(),
					$payment->get_payment_id(),
					$payment->get_status()
				);

				// If the  error as an attempt for COMPLETED when already completed, check if the transaction id's match, if not it may be a second payment
				// Note: Multiple Payments with different ID's is handled by the gateway, this is only if it is the same payment id AND different txid
				if ( ( 'COMPLETED' === $e->get_event_status() && 'COMPLETED' === $e->get_payment_status() ) && ( $payment_event->get_transaction_id() !== $payment->get_transaction_id() ) ) {
					// The transaction id's are different, we may have actually been paid more than once.
					$default_note = sprintf(
						/* translators: The first placeholder is the Money Button PaymentID. The ssecond placeholder is the blockchain transaction id of the event, which differed from the txid that was already received */
						__(
							'An additional \'COMPLETED\' payment notification was received from Money Button for Payment ID %1$s with a different transaction id [%2$s] than before. This may be an additional payment and require a refund.',
							'wc-gateway-moneybutton'
						),
						$payment->get_payment_id(),
						$payment_event->get_transaction_id()
					);
					$order = new \WC_Order( $order_id );
					$order->add_order_note( apply_filters( 'wc_gateway_moneybutton_payment_potential_double_pay', $default_note, $order_id, $payment, $different_tx_id ) );
					$order->save();
				} else {
					$order = new \WC_Order( $order_id );
					$order->add_order_note(
						apply_filters(
							'wc_gateway_moneybutton_payment_' . strtolower( $payment_event->get_status() ) . '_when_' . strtolower( $payment->get_status() ),
							$default_note,
							$order_id,
							$payment
						)
					);
					$order->save();
				}
			} catch ( MissingPaymentDataException $e ) {
				$message = sprintf(
					/* translators: The first place holder is the status of the event/update received from Money Button. The second placeholder is the Money Button PaymentID. */
					__(
						'Could not process a payment notification  from Money Button with Status %1$s  for Payment ID %2$s because it did not contain any payment data. ',
						'wc-gateway-moneybutton'
					),
					$e->get_event_status(),
					$payment->get_payment_id()
				);
				$order = new \WC_Order( $order_id );
				$order->add_order_note( $message );
				$order->save();
			} catch ( InvalidTransactionIdException $e ) {
				$message = sprintf(
					/* translators: The first placeholder is the status of the payment event/update received from Money Button. The second placeholder is the Money Button PaymentID. The the third placeholder is invalid Blockchain transaction ID that was included in the event/update */
					__(
						'Could not process a payment notification  from Money Button with Status %1$s  for Payment ID %2$s because the transaction id with value \'%3$s\' is invalid. ',
						'wc-gateway-moneybutton'
					),
					$payment_event->get_status(),
					$payment->get_payment_id(),
					$payment_event->get_transaction_id()
				);
				$order = new \WC_Order( $order_id );
				$order->add_order_note( $message );
				$order->save();
			}

			$payment_repo->save( $payment );

		} catch ( WcGatewayMoneyButtonException $e ) {

			$error = new \WP_Error( 500, $e->getLocalizedMessage() . '. There may be more details in the error log', $payment_event );
			wp_send_json_error( $error, $error->get_error_code() );

		}

		wp_send_json_success( 'update accepted', 200 );
	}
}
