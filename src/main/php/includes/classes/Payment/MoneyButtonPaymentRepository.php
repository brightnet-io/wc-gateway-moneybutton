<?php
/**
 * Repository for MoneyButtonPaymentEvent entities
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
 * Class MoneyButtonPaymentRepository
 *
 * @package WcGatewayMoneyButton\Payment
 * @since   0.1.0
 */
class MoneyButtonPaymentRepository {

	/**
	 * @since   0.1.0
	 * @aaccess public
	 * @var string
	 */
	public static $table_name = 'wcgmb_payments';


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
	 * MoneyButtonPaymentEventRepository constructor.
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
	 * @return MoneyButtonPayment|null
	 */
	public function find_by_id( int $id ): ?MoneyButtonPayment {
		$this->logger->debug( 'find_by_id(): with id ' . $id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare( "SELECT * from {$this->table} WHERE ID  = %d", $id );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $this->wpdb->get_row( $query, ARRAY_A, 0 );
		if ( isset( $row ) ) {
			$payment = new MoneyButtonPayment( $this->wpdb );
			$payment->fill( $row );

			return $payment;
		} elseif ( '' !== $this->wpdb->last_error ) {
			$this->probably_wpdb_error( 'find_by_id', $query );

		} else {
			return null;
		}
	}

	/**
	 * Save entity.
	 *
	 * Insert or updated.
	 *
	 * @since 0.1.0
	 *
	 * @param MoneyButtonPayment $payment
	 *
	 * @return MoneyButtonPayment
	 */
	public function save( MoneyButtonPayment $payment ): MoneyButtonPayment {
		$query = false;
		if ( false === $payment->is_new() ) { // use the getter so we can override it for testing.
			$this->wpdb->query( 'START TRANSACTION' );
			// update
			$query = $this->wpdb->update(
				$this->table,
				array(
					'updated_at'     => $payment->get_updated_at(),
					'status'         => $payment->get_status(),
					'transaction_id' => $payment->get_transaction_id(),
					'currency'       => $payment->get_currency(),
					'satoshis'       => $payment->get_satoshis(),
					'amount'         => $payment->get_amount(),
					'exchange'       => $payment->get_effective_exchange(),
				),
				array(
					'ID' => $payment->get_id(),
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%f',
					'%f',
				)
			);

			if ( false !== $query ) {
				$this->logger->debug(
					sprintf(
						'save(): [Order Id: %1$s] [Payment Id: %2$s] [Payment Entity Id: %3$s] updated.',
						$payment->get_order_id(),
						$payment->get_payment_id(),
						$query
					)
				);
				$this->wpdb->query( 'COMMIT' );
				$payment->set_is_new( false );

			} else {
				$this->probably_wpdb_error( 'save', $query );
			}
		} else {
			$this->wpdb->query( 'START TRANSACTION' );
			$query = $this->wpdb->insert(
				$this->table,
				array(
					'created_at'     => $payment->get_created_at(),
					'updated_at'     => $payment->get_updated_at(),
					'payment_id'     => $payment->get_payment_id(),
					'status'         => $payment->get_status(),
					'transaction_id' => $payment->get_transaction_id(),
					'currency'       => $payment->get_currency(),
					'satoshis'       => $payment->get_satoshis(),
					'amount'         => $payment->get_amount(),
					'exchange'       => $payment->get_effective_exchange(),
					'order_id'       => $payment->get_order_id(),
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%f',
					'%f',
					'%d',
				)
			);
			if ( false !== $query ) {
				$this->logger->debug(
					sprintf(
						'save(): [Order Id: %1$s] [Payment Id: %2$s] [Payment Entity Id: %3$s] inserted.',
						$payment->get_order_id(),
						$payment->get_payment_id(),
						$query
					)
				);

				$payment->set_id( $query );
				$this->wpdb->query( 'COMMIT' );
				$payment->set_is_new( false );
			} else {
				$this->probably_wpdb_error( 'save', $query );
			}
		}

		return $payment;
	}


	/**
	 * Find one by order id
	 *
	 * @param int $order_id
	 *
	 * @return MoneyButtonPayment|null
	 */
	public function find_by_order_id( int $order_id ): ?MoneyButtonPayment {
		$this->logger->debug( 'find_by_order_id(): with id ' . $order_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare( "SELECT * from {$this->table} WHERE order_id  = %d ORDER BY created_at DESC", $order_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $this->wpdb->get_row( $query, ARRAY_A, 0 );
		if ( isset( $row ) ) {
			$payment = new MoneyButtonPayment();
			$payment->fill( $row );

			return $payment;
		} elseif ( '' !== $this->wpdb->last_error ) {
			$this->probably_wpdb_error( 'find_by_order_id', $query );

		} else {
			$this->logger->debug( 'find_by_order_id(): no record. return null' );

			return null;
		}
	}

	/**
	 * Find one by payment_id
	 *
	 * @since 0.1.0
	 *
	 * @param string $payment_id
	 *
	 * @return MoneyButtonPayment|null
	 */
	public function find_by_payment_id( string $payment_id ): ?MoneyButtonPayment {
		$this->logger->debug( 'find_by_payment_id(): with id ' . $payment_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$query = $this->wpdb->prepare( "SELECT * from {$this->table} WHERE payment_id  = %s", $payment_id );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $this->wpdb->get_row( $query, ARRAY_A, 0 );
		if ( isset( $row ) ) {
			$payment = new MoneyButtonPayment();
			$payment->fill( $row );

			return $payment;
		} elseif ( '' !== $this->wpdb->last_error ) {
			$this->probably_wpdb_error( 'find_by_payment_id', $query );

		} else {
			$this->logger->debug( 'find_by_payment_id(): no record. return null' );

			return null;
		}
	}

	/**
	 * Log WPDB errors and throw exception
	 *
	 * @since 0.1.0
	 *
	 * @param string    $source_method Method in which error occured.
	 * @param int|false $result        Result of WPDB query.
	 *
	 * @throws WcGatewayMoneyButtonException Always.
	 */
	private function probably_wpdb_error( $source_method, $result ): void {
		if ( false === $result ) { // Guard in case incorrectly called.
			$this->wpdb->query( 'ROLLBACK' );
			$this->logger->error(
				sprintf(
					'%1$s(): WPDB Error result: %2$s',
					$source_method,
					$result
				)
			);

			$this->logger->error(
				sprintf(
					'%1$s(): WPDB Error  last query: ' . PHP_EOL . '%2$s',
					$source_method,
					$this->wpdb->last_query
				)
			);
			$this->logger->error(
				sprintf(
					'%1$s(): WPDB ERROR  last error: ' . PHP_EOL . '%2$s',
					$source_method,
					$this->wpdb->last_error
				)
			);
			throw new WcGatewayMoneyButtonException(
				sprintf(
					'Error occurred during DataStore operation %1$s. ',
					$source_method
				)
			);
		}
	}
}
