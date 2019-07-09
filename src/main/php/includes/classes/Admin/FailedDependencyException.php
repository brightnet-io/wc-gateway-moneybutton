<?php
/**
 * An exception that may be thrown if one of the environment or configuration dependencies for the plugin is not met.
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Admin
 */

namespace WcGatewayMoneyButton\Admin;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FailedDependencyException
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Admin
 */
class FailedDependencyException extends WcGatewayMoneyButtonException {
	/**
	 * A short unique name or code to identify the dependency. ie: wc_version.
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $dependency_name;

	/**
	 * Severity of the violation, can be one of 'warning' or 'error'
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $severity;

	/**
	 * Whether or not the error is something that should be considered permanent until expressly resolved
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var bool naggy/dismissable
	 */
	private $persistent;

	/**
	 * Valid values for $severity.
	 *
	 * @since 0.1.0
	 * @access protected
	 *
	 * @var array
	 */
	protected static $severities = array( 'warning', 'error' );

	/**
	 * FailedDependencyException constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $dependency_name   A short unique name or code to identify the dependency. ie: wc_version.
	 * @param string $severity          Severity of the violation, can be one of 'warning' or 'error'.
	 * @param bool   $persistent        Whether or not the error is something that should be considered permanent untill expressly resolved.
	 * @param string $error_message     Non-localized error message.
	 * @param string $localized_message Localized error message.
	 *
	 * @throws \InvalidArgumentException Missing dependency_name or invalid $severity.
	 */
	public function __construct( string $dependency_name, string $severity = 'info', bool $persistent = false, string $error_message = '', string $localized_message = '' ) {
		if ( empty( $dependency_name ) ) {
			throw new \InvalidArgumentException( 'dependencyName argument is required' );
		}

		if ( ! in_array( $severity, self::$severities, true ) ) {
			throw new \InvalidArgumentException( 'value of severity is invalid. Allowable value are ' . implode( ', ', self::$severities ) );
		}
		$this->dependency_name = $dependency_name;
		$this->severity        = $severity;
		$this->persistent      = $persistent;

		parent::__construct( $error_message, $localized_message );
	}

	/**
	 * Getter
	 *
	 * @return string
	 */
	public function getDependencyName(): string {
		return $this->dependency_name;
	}

	/**
	 * Getter
	 *
	 * @return string
	 */
	public function getSeverity(): string {
		return $this->severity;
	}

	/**
	 * Getter
	 *
	 * @return bool
	 */
	public function isPersistent(): bool {
		return $this->persistent;
	}
}
