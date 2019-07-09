<?php
/**
 * Helper class payment gateway
 *
 * @since 0.1.0
 * @package    WcGatewayMoneyButton\Core
 */

namespace WcGatewayMoneyButton\Core;

use WcGatewayMoneyButton\Gateway\PaymentGatewayImpl;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class PaymentHelper
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Core
 */
class PaymentHelper {



	/**
	 * Convenience function to get the declared gateway from WooCommerce
	 *
	 * @since  0.1.0
	 *
	 * @return PaymentGatewayImpl
	 * @throws WcGatewayMoneyButtonException If the money button gateway is not registered with WooCommerce
	 */
	public static function get_gateway(): PaymentGatewayImpl {
		if ( ! isset( $wc_gateway_moneybutton ) || empty( $wc_gateway_moneybutton ) ) {
			global $wc_gateway_moneybutton;
			$wc_gateways      = new \WC_Payment_Gateways();
			$payment_gateways = $wc_gateways->payment_gateways();
			if ( ! isset( $payment_gateways['moneybutton'] ) ) {
				throw new WcGatewayMoneyButtonException( 'Money Button Gateway not registered.' );

			}
			$wc_gateway_moneybutton = $payment_gateways['moneybutton'];
		}

		return $wc_gateway_moneybutton;
	}


}
