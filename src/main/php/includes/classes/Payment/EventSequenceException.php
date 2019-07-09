<?php
/**
 * Exception indicating that payment event is out of sequence
 *
 * @package WcGatewayMoneyButton\Payment
 * @since   0.1.0
 */

namespace WcGatewayMoneyButton\Payment;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EventSequenceException
 *
 * @package WcGatewayMoneyButton\Payment
 */
class EventSequenceException extends WcGatewayMoneyButtonException {

	/** @var string */
	private $payment_status;
	/** @var string  */
	private $event_status;

	/**
	 * EventSequenceException constructor.
	 *
	 * @param string $error_message     The actual error message.
	 * @param string $localized_message User friendly translated/localized message.
	 * @param string $payment_status    Current status of the MoneyButtonPayment that threw the exception.
	 * @param string $event_status      Status of the MoneyButtonPaymentEvent that was being applied when exception thrown.
	 */
	public function __construct( string $error_message = '', string $localized_message = '', $payment_status, $event_status ) {
		parent::__construct( $error_message, $localized_message );
		$this->payment_status = $payment_status;
		$this->event_status   = $event_status;
	}

	/**
	 * Getter PaymentStatus
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_payment_status(): ?string {
		return $this->payment_status;
	}

	/**
	 * Getter EventStatus
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function get_event_status(): ?string {
		return $this->event_status;
	}


}
