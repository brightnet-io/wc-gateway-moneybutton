<?php
/**
 *  Dependency checker for plugin
 *
 *  Responsible for perfmoring environment/platform checks for plugin compatability.
 *
 * @package WcGatewayMoneyButton\Admin
 */

namespace WcGatewayMoneyButton\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DependencyChecker
 *
 * @since   0.1.0
 * @package WcGatewayMoneyButton\Admin
 */
class DependencyChecker {


	/**
	 * Errors and warnings collected from running the check
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Minimum WooCommerce version supported.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string PHP-Standardized version number
	 */
	private $min_wc_version;

	/**
	 * Minimum PHP version supported.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string PHP-Standardized version number
	 */
	private $min_php_version;

	/**
	 * DependencyChecker  constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $min_wc_version  PHP-Standardized version number
	 * @param string $min_php_version PHP-Standardized version number
	 *
	 * @throws \InvalidArgumentException When param is not a valid PHP-Standardized version number.
	 */
	public function __construct( string $min_wc_version, string $min_php_version ) {

		if ( ! $this->is_version_number( $min_wc_version ) ) {
			throw new \InvalidArgumentException( '$min_wc_version must be a valid PHP Standardized version number' );
		}
		if ( ! $this->is_version_number( $min_php_version ) ) {
			throw new \InvalidArgumentException( '$min_php_version must be a valid PHP Standardized version number' );
		}
		$this->min_wc_version  = $min_wc_version;
		$this->min_php_version = $min_php_version;

	}

	/**
	 * Runs the check
	 *
	 * @since 0.1.0
	 *
	 * @return bool true if no errors or warnings
	 */
	public function passes_check(): bool {
		$this->errors = array_merge( $this->errors, $this->check_versions(), $this->check_config() );

		return empty( $this->errors );
	}

	/**
	 * Retrieve any errors and warning from passes_check
	 *
	 * @since 0.1.0
	 *
	 * @return FailedDependencyException[]
	 */
	public function get_errors(): array {
		return $this->errors;
	}


	/**
	 * Perform the checks for minimum supported versions.
	 *
	 * @since 0.1.0
	 *
	 * @return  FailedDependencyException[]
	 */
	public function check_versions(): array {
		$version_errors = array();

		try {
			$this->check_woocommerce_version();
		} catch ( FailedDependencyException $e ) {
			$version_errors[] = $e;
		}
		try {
			$this->check_php_version();
		} catch ( FailedDependencyException $e ) {
			$version_errors[] = $e;
		}

		return $version_errors;
	}

	/**
	 * Perform the checks for any required WordPress/WooCommerce configurable options.
	 *
	 * @since 0.1.0
	 *
	 * @return  FailedDependencyException[]
	 */
	public function check_config(): array {
		$version_errors = array();

		try {
			$this->check_https_checkout();
		} catch ( FailedDependencyException $e ) {
			$version_errors[] = $e;
		}

		return $version_errors;
	}

	/**
	 * Verify installed WooCommerce version meets supported minimum.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 *
	 * @throws FailedDependencyException If does not meet specified minimum
	 */
	public function check_woocommerce_version(): bool {
		if ( version_compare( WC_VERSION, $this->min_wc_version, '<' ) ) {
			/* translators: the first placeholder is the minimum version of WooCommerce supported by this plugin. The second placeholder is the version of WooCommerce installed */
			$message = __(
				'WooCommerce  Gateway MoneyButton - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.',
				'wc-gateway-moneybutton'
			);

			throw new FailedDependencyException(
				'wcver_' . $this->min_wc_version,
				'error',
				false,
				'Environment does not meet minimum required WooCommerce version',
				sprintf( $message, $this->min_wc_version, WC_VERSION )
			);
		}

		return true;
	}

	/**
	 * Verify installed PHP version meets supported minimum.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 *
	 * @throws FailedDependencyException If does not meet specified minimum
	 */
	public function check_php_version(): bool {
		if ( version_compare( PHP_VERSION, $this->min_php_version, '<' ) ) {
			/* translators: the first placeholder is the minimum version of PHP supported by this plugin. The second placeholder is the version of PHP installed */
			$message = __(
				'WooCommerce MoneyButton Gateway - The minimum PHP version required for this plugin is %1$s. You are running %2$s.',
				'wc-gateway-moneybutton'
			);

			throw new FailedDependencyException(
				'phpver_' . $this->min_php_version,
				'error',
				false,
				'Environment does not meet minimum required PHP version',
				sprintf( $message, $this->min_php_version, PHP_VERSION )
			);
		}

		return true;
	}

	/**
	 * Verify that forced HTTPS checkout is enabled.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 *
	 * @throws FailedDependencyException If HTTPS checkout is not enabled
	 */
	public function check_https_checkout(): bool {
		if ( ! wc_checkout_is_https() ) {
			$message = __(
				'WooCommerce MoneyButton Gateway is enabled, but a SSL certificate is not detected. MoneyButton will not work without SSL configured.',
				'wc-gateway-moneybutton'
			);

			throw new FailedDependencyException(
				'ssl',
				'warning',
				false,
				'MoneyButton requires SSL',
				$message
			);
		}

		return true;
	}

	/**
	 * Determines if a string is a valid PHP Standardized version number by checking if it is greater than or
	 * equal to 0.0.1-dev (the lowest possible version number)
	 *
	 * @param string $test_string String to check.
	 *
	 * @return bool true if valid PHP-Standardized version number, otherwise false
	 */
	private function is_version_number( string $test_string ): bool {

		return ! ! version_compare( $test_string, '0.0.1.dev', 'ge' );

	}
}
