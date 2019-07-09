<?php
/**
 * Exception indicating that payment event is missing required data from paymentOutputs
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
 * Class MissingPaymentDataException
 *
 * @package WcGatewayMoneyButton\Payment
 */
class MissingPaymentDataException extends WcGatewayMoneyButtonException {

	/**
	 * MissingPaymentDataException constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $error_message
	 * @param string $localized_message
	 */
	public function __construct( string $error_message = 'Payment Event missing required payment data from outputs', string $localized_message = '' ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct( $error_message, $localized_message );
	}
}
