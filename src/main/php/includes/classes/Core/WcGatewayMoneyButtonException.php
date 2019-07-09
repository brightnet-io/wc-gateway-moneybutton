<?php
/**
 * WooCommerce MoneyButton Gateway Exception Class
 *
 * Provides for ability of localized i18n messages for exceptions
 *
 * @since   1.0.0
 * @package WcGatewayMoneyButton\Core
 */

namespace WcGatewayMoneyButton\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class WcGatewayMoneyButtonException
 *
 * @since 0.1.0
 * @package  WcGatewayMoneyButton\Core
 */
class WcGatewayMoneyButtonException extends \Exception {
	/**
	 * Localized version of the exception message.
	 *
	 * @var string sanitized/localized error message
	 * @since 1.0.0
	 */
	protected $localized_message;


	/**
	 * WC_Gateway_MoneyButton_Exception constructor.
	 *
	 * @param string $error_message     The actual error message.
	 * @param string $localized_message User friendly translated/localized message.
	 */
	public function __construct( $error_message = '', $localized_message = '' ) {
		$this->localized_message = $localized_message;
		parent::__construct( $error_message );
	}

	/**
	 * Returns the localized message.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function getLocalizedMessage() {
		if ( ! empty( $this->localized_message ) ) {
			return $this->localized_message;
		} else {
			return $this->getMessage();
		}

	}
}
