<?php
/**
 * Repository for RawPaymentEvent entities
 *
 * @package WcMoneyButtonGatway\Payment
 * @since   0.1.0
 */

namespace WcGatewayMoneyButton\Payment;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RawPaymentEventRepository
 *
 * @package WcGatewayMoneyButton\Payment
 * @since   0.1.0
 */
class RawPaymentEventRepository {
	/**
	 * @since   0.1.0
	 * @aaccess public
	 * @var string
	 */
	public static $table_name = 'wcgmb_raw';

	/**
	 * @since  0.1.0
	 * @access private
	 * @var \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * WPDB
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var \wpdb $wpdb
	 */
	private $wpdb;

	/**
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string prefixed table name set on constructor
	 */
	private $table;

	/**
	 * RawPaymentEventRepository constructor.
	 *
	 * @param \wpdb $wpdb
	 */
	public function __construct( \wpdb $wpdb ) {
		$this->logger = WcGatewayMoneyButtonLogger::get_logger();
		$this->wpdb   = $wpdb;
		$this->table  = $wpdb->prefix . self::$table_name;
	}

	/**
	 * Retrieve single entity by id
	 *
	 * @param int $id
	 *
	 * @return RawPaymentEvent|null
	 */
	public function find_by_id( int $id ): ?RawPaymentEvent {
		$this->logger->debug( 'find_by_id(): with id ' . $id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare( "SELECT * from {$this->table} WHERE ID  = %d", $id );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $this->wpdb->get_row( $query, ARRAY_A, 0 );
		if ( isset( $row ) ) {
			$raw_event = new RawPaymentEvent();
			$raw_event->fill( $row );

			return $raw_event;
		} elseif ( '' !== $this->wpdb->last_error ) {
			$this->probably_wpdb_error( 'find_by_id', $query );

		} else {
			return null;
		}
	}

	/**
	 * Saves the newly created entity
	 *
	 * @since 0.1.0
	 *
	 * @param RawPaymentEvent $raw_event
	 *
	 * @return RawPaymentEvent
	 *
	 * @throws \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException If you try to save more than once (this entity is immutable)
	 */
	public function save( RawPaymentEvent $raw_event ): RawPaymentEvent {
		if ( ! empty( $raw_event->get_id() ) ) {
			throw new WcGatewayMoneyButtonException( 'RawPaymentEvent data cannot be modified' );
		}

		$result = $this->wpdb->insert(
			$this->table,
			array(
				'received'    => $raw_event->get_received(),
				'json_string' => $raw_event->get_json_string(),
			),
			array(
				'%s',
				'%s',
			)
		);

		if ( false !== $result ) {
			$raw_event->set_id( $result );
			$this->logger->debug(
				sprintf(
					'save(): [ID: %1$s] [Received: %2$s] saved ',
					$result,
					$raw_event->get_received()
				)
			);

			return $raw_event;
		} else {
			$this->logger->error( 'save(): Failed saving raw event data. SQL Error ' . $this->wpdb->last_error );
			throw new WcGatewayMoneyButtonException( 'Error occurred during save operation.' );
		}

	}

}
