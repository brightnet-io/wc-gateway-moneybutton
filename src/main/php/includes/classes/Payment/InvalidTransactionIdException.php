<?php
/**
 * Exception indicating a transaction id is invalid
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
 * Class InvalidTransactionIdException
 *
 * A transaction ID is considered invalid if it is different to any existing ID set for a particular MoneyButtonPayment.
 *
 * @package WcGatewayMoneyButton\Payment
 *
 * @since 0.1.0
 */
class InvalidTransactionIdException extends WcGatewayMoneyButtonException {

}
