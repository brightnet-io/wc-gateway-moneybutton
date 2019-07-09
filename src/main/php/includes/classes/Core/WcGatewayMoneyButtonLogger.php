<?php
/**
 * Delegate to WC_Logger with the intention of providing additional control over the level of logging events that can be configured via plugin settings.
 *
 * @package WcGatewayMoneyButton\Core;
 * @since   0.1.0
 */

namespace WcGatewayMoneyButton\Core;

use WC_Log_Levels;
use WcGatewayMoneyButton\WcGatewayMoneyButtonException;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WcGatewayMoneyButtonLogger
 *
 * A delegate for WC_Logger that adds additional context and filtering options
 *
 * @since   0.1.0
 * @package WcGatewayMoneyButton\Core
 */
class WcGatewayMoneyButtonLogger {

	/**
	 * Instance of WC_Logger
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var \WC_Logger
	 */
	private $logger;

	/**
	 * Debugging logging enabled / disabled
	 *
	 * @var bool
	 */
	private $debug_logging = false;


	/**
	 * Return singleton instance of class.
	 *
	 * @since 0.1.0
	 *
	 * @return bool|WcGatewayMoneyButtonLogger
	 */
	public static function get_logger() {
		// TODO Use a factory approach that can prefix a category or class name to log messages
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}


	/**
	 * WcGatewayMoneyButtonLogger constructor.
	 *
	 * @since 0.1.0
	 *
	 * @throws WcGatewayMoneyButtonException When WooCommerce Logger is unavailable;
	 */
	public function __construct() {

		// Setup a default source/file name for WC_LOGGER
		if ( ! defined( 'WC_MONEYBUTTON_GATEWAY_LOG_FILENAME' ) ) {
			define( 'WC_MONEYBUTTON_GATEWAY_LOG_FILENAME', 'wc-gateway-moneybutton' );
		}

		$this->logger = wc_get_logger();
		if ( empty( $this->logger ) ) {
			throw new WcGatewayMoneyButtonException(
				'Could not initialize logger, WC_Logger unavailable',
				__( 'Could not initialize logger. WC_Logger unavailable. This typically means WooCommerce is not installed and active' )
			);
		}

		if ( function_exists( 'get_option' ) ) {
			$plugin_options = get_option( 'woocommerce_moneybutton_settings' );
			if ( ! empty( $plugin_options ) ) {
				if ( isset( $plugin_options['debug_logging'] ) && 'yes' === $plugin_options['debug_logging'] ) {
					$this->debug_logging = true;
				}
			}
		}

	}

	/**
	 * Add a log entry.
	 *
	 * @since 0.1.0
	 *
	 * @param string $level   One of the following:
	 *                        'emergency': System is unusable.
	 *                        'alert': Action must be taken immediately.
	 *                        'critical': Critical conditions.
	 *                        'error': Error conditions.
	 *                        'warning': Warning conditions.
	 *                        'notice': Normal but significant condition.
	 *                        'info': Informational messages.
	 *                        'debug': Debug-level messages.
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	protected function log( $level, $message, $context = array() ): void {
		// TODO Check level against configured logging level for plugin settings.
		if ( 'debug' === $level && ! $this->debug_logging ) {
			return;
		}
		$context = $this->decorate_context( $context );
		$this->logger->log( $level, $message, $context );
	}

	/**
	 * Adds an emergency level message.
	 *
	 * System is unusable.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function emergency( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::EMERGENCY, $message, $context );

	}

	/**
	 * Adds an alert level message.
	 *
	 * Action must be taken immediately.
	 * Example: Entire website down, database unavailable, etc.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function alert( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::ALERT, $message, $context );
	}

	/**
	 * Adds a critical level message.
	 *
	 * Critical conditions.
	 * Example: Application component unavailable, unexpected exception.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function critical( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::CRITICAL, $message, $context );
	}

	/**
	 * Adds an error level message.
	 *
	 * Runtime errors that do not require immediate action but should typically be logged
	 * and monitored.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function error( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Adds a warning level message.
	 *
	 * Exceptional occurrences that are not errors.
	 *
	 * Example: Use of deprecated APIs, poor use of an API, undesirable things that are not
	 * necessarily wrong.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function warning( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::WARNING, $message, $context );
	}

	/**
	 * Adds a notice level message.
	 *
	 * Normal but significant events.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function notice( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::NOTICE, $message, $context );
	}

	/**
	 * Adds a info level message.
	 *
	 * Interesting events.
	 * Example: User logs in, SQL logs.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function info( $message, $context = array() ): void {
		$this->log( WC_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Adds a debug level message.
	 *
	 * Detailed debug information.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional. Additional information for log handlers.
	 */
	public function debug( $message, $context = array() ) {
		$this->log( WC_Log_Levels::DEBUG, $message, $context );
	}

	/**
	 * Add some additional data, such as source, to the context.
	 *
	 * @since 0.1.0
	 *
	 * @param array $context optional parameter passed to one of the log functions
	 *
	 * @return array
	 */
	private function decorate_context( $context ): array {
		return array_merge(
			array(
				'source' => WC_MONEYBUTTON_GATEWAY_LOG_FILENAME,
			),
			$context
		);
	}


}
