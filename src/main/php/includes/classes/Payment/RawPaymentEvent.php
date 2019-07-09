<?php
/**
 * Entity storing raw JSON data received via webhook calls form Money Button
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
 * Class RawPaymentEvent
 *
 * @package WcGatewayMoneyButton\Payment
 */
class RawPaymentEvent implements IEntity {


	/** @var \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger */
	private $logger;


	/** @var int */
	private $id;
	/** @var string mysql format UTC time */
	private $received;
	/** @var string */
	private $json_string;


	/**
	 * RawPaymentEvent constructor.
	 *
	 * @param string|null $json_string
	 * @param string|null $received UTC time, MySQL datetime formatted string eg: gmdate( 'Y-m-d\TH:i:s\Z' )
	 */
	public function __construct( string $json_string = null, string $received = null ) {
		$this->logger      = WcGatewayMoneyButtonLogger::get_logger();
		$this->json_string = $json_string;
		$this->received    = $received;
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
		$this->id          = $row['ID'];
		$this->received    = $row['received'];
		$this->json_string = $row['json_string'];

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
	 * @internal
	 * @since 0.1.0
	 *
	 * @param int $id
	 */
	public function set_id( int $id ) {
		$this->id = $id;
	}


	/**
	 * Getter Received
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_received(): string {
		return $this->received;
	}

	/**
	 * Getter JsonString
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_json_string(): string {
		return $this->json_string;
	}



	/**
	 * Getter
	 *
	 * @since 0.1.0
	 *
	 * @return string Type name/
	 */
	public function get_type_name(): string {
		return 'RawPaymentEvent';
	}
}
