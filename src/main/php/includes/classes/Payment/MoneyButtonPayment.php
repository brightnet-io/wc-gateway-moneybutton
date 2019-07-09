<?php
/**
 * MoneyButtonPayment
 *
 * Domain object / entity for a single money button payment.
 *
 * @since   0.1.0
 *
 * @package WcGatewayMoneyButton\Payment
 */

namespace WcGatewayMoneyButton\Payment;

use WcGatewayMoneyButton\Core\IEntity;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MoneyButtonPayment
 *
 * @since   0.1.0
 *
 * @package WcGatewayMoneyButton\Payment
 */
class MoneyButtonPayment implements IEntity {


	/**
	 * @since  0.1.0
	 * @access private
	 * @var \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * @since  0.1.0
	 * @access private
	 * @var int PK
	 */
	private $id;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string Money Button payment id
	 */
	private $payment_id;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string Date/Time in ISO8601 format
	 */
	private $created_at;

	/**
	 * @since  01.0
	 * @access private
	 *
	 * @var string Date/Time in ISO8601 Format
	 */
	private $updated_at;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string Blockchain TX id for payment
	 */
	private $transaction_id;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string Money Button Payment Status
	 */
	private $status;

	/**
	 * The currency that was requested for payment
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string ISO4217 Currency Code
	 */
	private $currency;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var float Payment amount in requested currency
	 */
	private $amount;


	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var int payment amount in satoshis
	 */
	private $satoshis; // This is the actual number of satoshis paid.


	/**
	 * The effective exchange rate between Bitcoin/BSV and requested currency
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var float $effective_exchange
	 */
	private $effective_exchange;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var int
	 */
	private $order_id;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var \wpdb
	 */
	private $wpdb;


	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var bool flag for new/unsaved entity
	 */
	private $is_new = true;

	/**
	 * MoneyButtonPayment constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $payment_id Money Button Payment ID
	 * @param  int    $order_id
	 */
	public function __construct( string $payment_id = null, int $order_id = null ) {
		$this->logger     = WcGatewayMoneyButtonLogger::get_logger();
		$this->is_new     = true;
		$this->payment_id = $payment_id;
		$this->order_id   = $order_id;

	}


	/**
	 * Fill entity with table row.
	 *
	 * @since 0.1.0
	 * @internal
	 *
	 * @param array $row wpdb row result
	 */
	public function fill( array $row ): void {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
		$this->logger->debug( 'fill() with ' . PHP_EOL . print_r( $row, true ) );
		$this->id                 = $row['ID'];
		$this->updated_at         = ! empty( $row['updated_at'] ) ? date_format( new \DateTime( $row['updated_at'] ), 'Y-m-d\TH:i:s\Z' ) : null;
		$this->created_at         = ! empty( $row['created_at'] ) ? date_format( new \DateTime( $row['created_at'] ), 'Y-m-d\TH:i:s\Z' ) : null;
		$this->payment_id         = $row['payment_id'];
		$this->status             = $row['status'];
		$this->transaction_id     = $row['transaction_id'];
		$this->currency           = $row['currency'];
		$this->satoshis           = (int) $row['satoshis'];
		$this->amount             = (float) $row['amount'];
		$this->effective_exchange = (float) $row['exchange'];
		$this->order_id           = $row['order_id'];
		$this->is_new             = false;

	}


	/**
	 * Getter Id
	 *
	 * @since 0.1.0
	 *
	 * @return int|null
	 */
	public function get_id(): ?int {
		return $this->id;
	}

	/**
	 * Setter id
	 *
	 * @since 0.1.0
	 * @internal
	 *
	 * @param int $id
	 */
	public function set_id( int $id ) {
		$this->id = $id;
	}


	/**
	 * Getter PaymentId
	 *
	 * @since 0.1.0
	 *
	 * @return string|null
	 */
	public function get_payment_id(): ?string {
		return $this->payment_id;
	}


	/**
	 * Getter CreatedAt
	 *
	 * @since 0.1.0
	 *
	 * @return string|null
	 */
	public function get_created_at(): ?string {
		return $this->created_at;
	}

	/**
	 * Setter created_at
	 *
	 * @param string $created_at
	 */
	public function set_created_at( string $created_at ) {
		$this->created_at = $created_at;
	}

	/**
	 * Getter UpdatedAt
	 *
	 * @since 0.1.0
	 *
	 * @return string|null
	 */
	public function get_updated_at(): ?string {
		return $this->updated_at;
	}

	/**
	 * Setter updated_at
	 *
	 * @since 0.1.0
	 *
	 * @param string $updated_at
	 */
	public function set_updated_at( string $updated_at ) {
		$this->updated_at = $updated_at;
	}

	/**
	 * Getter TransactionId
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_transaction_id(): ?string {
		return $this->transaction_id;
	}

	/**
	 * Setter transaction_id
	 *
	 * @since 0.1.0
	 *
	 * @param string $transaction_id
	 */
	public function set_transaction_id( ?string $transaction_id ) {
		// no changing the txid
		if ( empty( $this->transaction_id ) ) {
			$this->transaction_id = $transaction_id;
			do_action( 'wc_gateway_moneybutton_payment_txid', $this->order_id, $this->transaction_id );
		}

	}

	/**
	 * Getter Status
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_status(): ?string {
		return $this->status;
	}

	/**
	 * Setter status
	 *
	 * @since 0.1.0
	 *
	 * @param string|null $status
	 */
	public function set_status( string $status ) {
		$this->status = $status;
	}

	/**
	 * Getter Currency
	 *
	 * @since 0.1.0
	 *
	 * @return string|null
	 */
	public function get_currency(): ?string {
		return $this->currency;
	}

	/**
	 * Setter currency
	 *
	 * @since 0.1.0
	 *
	 * @param string $currency
	 */
	public function set_currency( string $currency ) {
		$this->currency = $currency;
	}

	/**
	 * Getter Amount
	 *
	 * @since 0.1.0
	 *
	 * @return float|null
	 */
	public function get_amount(): ?float {
		return $this->amount;
	}

	/**
	 * Setter amount
	 *
	 * @param float|null $amount
	 */
	public function set_amount( ?float $amount ) {
		$this->amount = $amount;
		$this->calculate_exchange_rate();
	}

	/**
	 * Getter Satoshis
	 *
	 * @since 0.1.0
	 *
	 * @return int|null
	 */
	public function get_satoshis(): ?int {
		return $this->satoshis;
	}

	/**
	 * Setter satoshis
	 *
	 * @since 0.1.0
	 *
	 * @param int|null $satoshis
	 */
	public function set_satoshis( ?int $satoshis ) {
		$this->satoshis = $satoshis;
		$this->calculate_exchange_rate();
	}

	/**
	 * Getter OrderId
	 *
	 * @since 0.1.0
	 *
	 * @return int|null
	 */
	public function get_order_id(): ?int {
		return $this->order_id;
	}


	/**
	 * Getter isNew
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function is_new(): bool {
		return $this->is_new;
	}

	/**
	 * Setter is_new
	 *
	 * @since 0.1.0
	 * @internal
	 *
	 * @param bool $is_new
	 */
	public function set_is_new( bool $is_new ) {
		$this->is_new = $is_new;
	}


	/**
	 * Getter EffectiveExchange
	 *
	 * @return float | null
	 */
	public function get_effective_exchange(): ?float {
		return $this->effective_exchange;
	}


	/**
	 * Calculate and set effective exchange rate
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function calculate_exchange_rate(): void {
		if ( ! empty( $this->get_satoshis() ) && ! empty( $this->get_amount() ) ) {
			$power_of_satoshis        = 10 ** 8;
			$coin                     = $this->get_satoshis() / $power_of_satoshis;
			$this->effective_exchange = $this->get_amount() / $coin;
		} else {
			$this->effective_exchange = null;
		}
	}

	/**
	 * Getter
	 *
	 * @since 0.1.0
	 *
	 * @return string Type name/
	 */
	public function get_type_name(): string {
		return 'MoneyButtonPayment';
	}


    // phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber
	/**
	 * Given a MoneyButtonPayment event, attempt to update the Payment.
	 *
	 * Successfully applying an event will trigger the appropriate action such as 'wc_gateway_moneybutton_payment_failed' that can  be used by the Payment Gateway to update the order.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws EventSequenceException If event is received in incorrect sequence.
	 * @throws InvalidTransactionIdException If transaction id in event is invalid.
	 * @throws MissingPaymentDataException If required data is missing from event.
	 * @throws \InvalidArgumentException If Event is for a previously unknown status.
	 */
	public function apply( MoneyButtonPaymentEvent $event ) {
		$this->logger->debug( sprintf( 'apply(): [Event Status: %1$s]', $event->get_status() ) );
		if ( $event->get_order_id() !== $this->get_order_id() || $event->get_payment_id() !== $this->get_payment_id() ) {
			throw new \InvalidArgumentException( 'Event cannot be applied to Payment. Id mismatch' );
		}

		switch ( $event->get_status() ) {
			case 'PENDING':
				$this->apply_pending( $event );
				break;
			case 'RECEIVED':
				$this->apply_received( $event );
				break;
			case 'FAILED':
				$this->apply_failed( $event );
				break;
			case 'COMPLETED':
				$this->apply_completed( $event );
				break;
			default:
				throw new \InvalidArgumentException( 'Can not apply unknown event status ' . strtoupper( $event->get_status() ) );
		}

	}
	// phpcs:enable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber

	/**
	 * Apply a MoneyButtonPaymentEvent that has a status of Pending.
	 *
	 * Will call wc_gateway_moneybutton_payment_pending action if successfully applied.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws EventSequenceException  If event is received in incorrect sequence.
	 * @throws \InvalidArgumentException If event does not have 'PENDING' status
	 */
	protected function apply_pending( MoneyButtonPaymentEvent $event ) {
		// I'm a teapot
		if ( 'PENDING' !== $event->get_status() ) {
			throw new \InvalidArgumentException( sprintf( 'Do not apply %1$s events as PENDING', $event->get_status() ) );
		}

		$this->logger->debug( sprintf( 'apply_pending(): [Payment ID: %1$s] [Event Status: %2$s]', $event->get_payment_id(), $event->get_status() ) );
		// Pending event can only be applied to payment that has no previous event state
		if ( ! empty( $this->get_status() ) ) {
			$message = sprintf( 'PENDING event cannot be applied to MoneyButtonPayment with event status %1$s', $this->get_status() );
			$this->logger->error( $message );
			throw new EventSequenceException(
				$message,
				$this->out_of_sequence_localized_text( $event ),
				$this->get_status(),
				$event->get_status()
			);
		}

		if ( empty( $this->payment_id ) ) {
			$this->payment_id = $event->get_payment_id();
		}

		if ( empty( $this->order_id ) ) {
			$this->order_id = $event->get_order_id();
		}

		$this->set_status( $event->get_status() );
		$this->set_created_at( $event->get_created_at() );
		$this->set_updated_at( $event->get_updated_at() );
		// No transaction id in pending

		// Hardcoded to first output as MoneyButton currently does not support multiple outputs
		$output = ! empty( $event->get_outputs() ) && ! empty( $event->get_outputs()[0] ) ? $event->get_outputs()[0] : null;

		if ( ! empty( $output ) ) {
			$this->set_amount( $output->get_amount() );
			$this->set_currency( $output->get_currency() );
			$this->set_satoshis( $output->get_satoshis() );
		}

		$this->logger->debug( 'apply_pending(): event applied. fire action ' );
		do_action( 'wc_gateway_moneybutton_payment_pending', $this->order_id, $this, $event );
	}

	// phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber
	/**
	 *  Apply a MoneyButtonPaymentEvent that has a status of RECEIVED
	 *
	 * Will call wc_gateway_moneybutton_payment_received if successfully applied.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws \InvalidArgumentException If status of event is not RECEIVED
	 * @throws EventSequenceException If event is received in incorrect sequence.
	 * @throws InvalidTransactionIdException If transaction id in event is invalid.
	 * @throws MissingPaymentDataException If required data is missing from event.
	 */
	protected function apply_received( MoneyButtonPaymentEvent $event ) {
		// I'm a teapot
		if ( 'RECEIVED' !== $event->get_status() ) {
			throw new \InvalidArgumentException( sprintf( 'Do not apply %1$s events as RECEIVED', $event->get_status() ) );
		}

		$this->logger->debug( sprintf( 'apply_received(): [Payment ID: %1$s] [Event Status: %2$s]', $event->get_payment_id(), $event->get_status() ) );
		// Received can only be applied to new event, or pending event
		if ( ! empty( $this->get_status() ) && 'PENDING' !== $this->get_status() ) {
			$message = sprintf( 'RECEIVED event cannot be applied to MoneyButtonPayment with event status %1$s', $this->get_status() );
			$this->logger->error( $message );
			throw new EventSequenceException(
				$message,
				$this->out_of_sequence_localized_text( $event ),
				$this->get_status(),
				$event->get_status()
			);
		}

		// RECEIVED must have valid tx id
		$this->validate_event_tx_id( $event );

		// Must have output
		$output = $this->get_event_ouput( $event, true );

		if ( empty( $this->payment_id ) ) {
			$this->payment_id = $event->get_payment_id();
		}

		if ( empty( $this->order_id ) ) {
			$this->order_id = $event->get_order_id();
		}
		if ( empty( $this->created_at ) ) {
			$this->created_at = $event->get_created_at();
		}

		$this->set_updated_at( $event->get_updated_at() );
		$this->set_status( 'RECEIVED' );

		$this->set_amount( $output->get_amount() );
		$this->set_currency( $output->get_currency() );
		$this->set_satoshis( $output->get_satoshis() );

		$this->set_transaction_id( $event->get_transaction_id() );

		$this->logger->debug( 'apply_received(): event applied. fire action ' );
		do_action( 'wc_gateway_moneybutton_payment_received', $this->order_id, $this, $event );

	}
	// phpcs:enable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber


	// phpcs:disable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber
	/**
	 * Apply a MoneyButtonPaymentEvent that has a status of COMPLETED
	 *
	 * Will call wc_gateway_moneybutton_payment_completed if successfully applied.
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws \InvalidArgumentException If status of event is not COMPLETED
	 * @throws EventSequenceException If event is received in incorrect sequence.
	 * @throws InvalidTransactionIdException If transaction id in event is invalid.
	 * @throws MissingPaymentDataException If required data is missing from event.
	 */
	protected function apply_completed( MoneyButtonPaymentEvent $event ) {
		// I'm a teapot
		if ( 'COMPLETED' !== $event->get_status() ) {
			throw new \InvalidArgumentException( sprintf( 'Do not apply %1$s events as COMPLETED', $event->get_status() ) );
		}

		// COMPLETED Events can be applied to any status (or no status) except FAILED or COMPLETED
		$this->logger->debug( sprintf( 'apply_completed(): [Payment ID: %1$s] [Event Status: %2$s]', $event->get_payment_id(), $event->get_status() ) );
		if ( 'FAILED' === $this->get_status() || 'COMPLETED' === $this->get_status() ) {
			$message = sprintf( 'COMPLETED  event cannot be applied to MoneyButtonPayment with event status %1$s', $this->get_status() );
			$this->logger->error( $message );
			throw new EventSequenceException(
				$message,
				$this->out_of_sequence_localized_text( $event ),
				$this->get_status(),
				$event->get_status()
			);
		}

		// Must have valid tx id
		$this->validate_event_tx_id( $event );

		// Must have output
		$output = $this->get_event_ouput( $event, true );

		if ( empty( $this->payment_id ) ) {
			$this->payment_id = $event->get_payment_id();
		}

		if ( empty( $this->order_id ) ) {
			$this->order_id = $event->get_order_id();
		}
		if ( empty( $this->created_at ) ) {
			$this->created_at = $event->get_created_at();
		}

		$this->set_updated_at( $event->get_updated_at() );
		$this->set_status( 'COMPLETED' );

		$this->set_amount( $output->get_amount() );
		$this->set_currency( $output->get_currency() );
		$this->set_satoshis( $output->get_satoshis() );

		$this->set_transaction_id( $event->get_transaction_id() );

		$this->logger->debug( 'apply_completed(): event applied. fire action ' );
		do_action( 'wc_gateway_moneybutton_payment_completed', $this->order_id, $this, $event );

	}

	// phpcs:enable Squiz.Commenting.FunctionCommentThrowTag.WrongNumber

	/**
	 * Apply a MoneyButtonPaymentEvent that has a status of FAILED.
	 *
	 * Will call wc_gateway_moneybutton_payment_failed if successfully applied.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws \InvalidArgumentException If status of event is not FAILED
	 * @throws EventSequenceException If event is received in incorrect sequence.
	 */
	protected function apply_failed( MoneyButtonPaymentEvent $event ) {
		// I'm a teapot
		if ( 'FAILED' !== $event->get_status() ) {
			throw new \InvalidArgumentException( sprintf( 'Do not apply %1$s events as FAILED', $event->get_status() ) );
		}

		$this->logger->debug( sprintf( 'apply_failed(): [Payment ID: %1$s] [Event Status: %2$s]', $event->get_payment_id(), $event->get_status() ) );

		// FAILED can be applied to any status (including new) except COMPLETED

		if ( 'COMPLETED' === $this->get_status() ) {
			$message = sprintf( 'FAILED event cannot be applied to MoneyButtonPayment with event status %1$s', $this->get_status() );
			$this->logger->error( $message );
			throw new EventSequenceException(
				$message,
				$this->out_of_sequence_localized_text( $event ),
				$this->get_status(),
				$event->get_status()
			);
		}

		if ( empty( $this->payment_id ) ) {
			$this->payment_id = $event->get_payment_id();
		}

		if ( empty( $this->order_id ) ) {
			$this->order_id = $event->get_order_id();
		}
		if ( empty( $this->created_at ) ) {
			$this->created_at = $event->get_created_at();
		}

		$this->set_updated_at( $event->get_updated_at() );
		$this->set_status( 'FAILED' );

		// Hardcoded to first output as MoneyButton currently does not support multiple outputs
		$output = ! empty( $event->get_outputs() ) && ! empty( $event->get_outputs()[0] ) ? $event->get_outputs()[0] : null;
		if ( ! empty( $output ) ) {
			$this->set_amount( $output->get_amount() );
			$this->set_currency( $output->get_currency() );
			$this->set_satoshis( $output->get_satoshis() );
		} else {
			$this->logger->debug( 'FAILED Event Payment ID  ' . $this->get_payment_id() . ' has no ouput' );
		}

		$this->set_transaction_id( $event->get_transaction_id() );

		$this->logger->debug( 'apply_failed(): event applied. fire action ' );
		do_action( 'wc_gateway_moneybutton_payment_failed', $this->order_id, $this, $event );
	}

	/**
	 * Given an event, produce localized out of sequence error message
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @return string Localized out of sequence error text
	 */
	protected function out_of_sequence_localized_text( MoneyButtonPaymentEvent $event ) {
		return sprintf(
			/* translators: The first place holder the the status of the payment event. The second placeholder is the Money Button Payment ID. The third placholder is order id. The fourth placeholder is the current payment status of the order */
			__( 'Unable to process event with status %1$s for Payment ID %2$s on Order %3$s with payment status %4$s.', 'wc-gateway-moneybutton' ),
			$event->get_status(),
			$this->get_payment_id(),
			$this->get_order_id(),
			$this->get_status()
		);
	}

	/**
	 * @param MoneyButtonPaymentEvent $event
	 *
	 * @throws InvalidTransactionIdException If event does not have valid transaction id
	 */
	protected function validate_event_tx_id( MoneyButtonPaymentEvent $event ) {

		if ( empty( $event->get_transaction_id() ) ) {
			$message = sprintf(
				'%1$s is not a valid transaction id for Payment %2$s. \'%3$s\' events must contain a valid transaction id',
				$event->get_transaction_id(),
				$this->get_payment_id(),
				strtoupper( $event->get_status() )
			);
			$this->logger->error( $message );
			throw new InvalidTransactionIdException( $message );

		}
		if ( ! empty( $this->get_transaction_id() ) && $this->get_transaction_id() !== $event->get_transaction_id() ) {
			$message = sprintf(
				'%1$s is not a valid transaction id for Payment %2$s. Payment already has Transaction ID %3$s. Transaction ID\'s for a payment cannot change between updates.',
				$event->get_transaction_id(),
				$this->get_payment_id(),
				$this->get_transaction_id()
			);
			$this->logger->error( $message );
			throw new InvalidTransactionIdException( $message );
		}
	}


	/**
	 * Convinience method to get the payment output from a payment  event.
	 *
	 * Optionally throw an error if output does not exist.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPaymentEvent $event
	 * @param bool                    $required
	 *
	 * @return null|MoneyButtonPaymentOutput
	 * @throws MissingPaymentDataException When arg required is true and output is missing data.
	 */
	protected function get_event_ouput( MoneyButtonPaymentEvent $event, $required = false ) {
		// Hardcoded to first output as MoneyButton currently does not support multiple outputs
		$output = ! empty( $event->get_outputs() ) && ! empty( $event->get_outputs()[0] ) ? $event->get_outputs()[0] : null;

		// A received payment must have a payment output
		if ( $required && ( empty( $output ) || empty( $output->get_satoshis() ) || empty( $output->get_currency() ) || empty( $output->get_amount() ) ) ) {
			throw new MissingPaymentDataException();
		}

		return $output;

	}
}
