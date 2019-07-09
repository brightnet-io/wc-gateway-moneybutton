<?php
/**
 * Exception for when cannot or will not process a payment update
 *
 * @package WcGatewayMoneyButton\Core
 * @since 0.1.0
 */

namespace WcGatewayMoneyButton\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UnprocessableEntityException
 *
 * @package WcGatewayMoneyButton\Core
 * @deprecated
 */
class UnprocessableEntityException extends WcGatewayMoneyButtonException {

}
