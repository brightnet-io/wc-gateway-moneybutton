<?php
/**
 * DB Schema Updates and possible migrations
 *
 * Responsible for performing an necessary schema updates and migrations of custom tables between versions.
 * Does not use dbDelta, in preference for approach that uses hand written (eek) version -> version migration scripts for greater flexibility.
 *
 * @package WcGatewayMoneyButton\Admin
 */

namespace WcGatewayMoneyButton\Admin;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\Payment\RawPaymentEventRepository;



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DBUpdater
 *
 * @package WcGatewayMoneyButton\Admin
 */
class DBUpdater {

	/**
	 * Option name key for holding current schema version
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @var string
	 */
	public static $option_name_scheme_version = 'wc_gateway_moneybutton_schema_version';

	/**
	 * An array of containing statements for execution by `wpdb`.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var array The keys of the array are PHP-Standardized version numbers. The value of each is an array of script statements to be executed by $wpdb->query
	 */
	private $schema_version_updates;

	/**
	 * The current schema version. To be updated as script versions are executed
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $current_version;

	/**
	 * The version of the schema when db update started. Not updated as db scripts run
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $start_version;

	/**
	 * The target schema version to update to.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $target_version;

	/**
	 * WPDB
	 *
	 * @since  0.1.0
	 * @access private
	 * @see    /wp-includes/wp-db.php
	 * @var object
	 */
	private $wpdb;


	/**
	 * The full, prefixed name of the table for the RawPaymentEvent
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $raw_table_name;

	/**
	 * The full, prefixed name of the table for the MoneyButtonPayment entity
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $payments_table_name;

	/**
	 * DBUpdater constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string $target_version Target version to update schema to
	 * @param \wpdb  $wpdb           wpdb
	 *
	 * @throws \InvalidArgumentException If target version is lower than current version
	 */
	public function __construct( string $target_version, \wpdb $wpdb ) {
		// populate current_version
		$this->current_version = get_option( self::$option_name_scheme_version );
		// Default the current version for new installs.
		if ( empty( $this->current_version ) ) {
			$this->current_version = '0.0.0';
			$this->start_version   = '0.0.0';
		}

		// Don't support downgrades
		if ( version_compare( $target_version, $this->current_version, '<' ) === true ) {
			throw new \InvalidArgumentException( 'Target version for DB Update cannot be less than current version' );
		}

		// Setup instance variables
		$this->target_version = $target_version;
		$this->wpdb           = $wpdb;

		$this->raw_table_name         = $this->wpdb->prefix . RawPaymentEventRepository::$table_name;
		$this->payments_table_name    = $this->wpdb->prefix . MoneyButtonPaymentRepository::$table_name;
		$this->schema_version_updates = array();

		// Updates scripts go here keyed to version number

		$this->schema_version_updates['0.0.1'] = [

			/* @lang text */
			'
			CREATE TABLE ' . $this->raw_table_name . ' (
            `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
             `received` DATETIME NOT NULL,
            `json_string` VARCHAR(6146) NULL,
            PRIMARY KEY (`ID`)
            ) ' . $this->wpdb->get_charset_collate() . ';',

			'
CREATE TABLE ' . $this->payments_table_name . ' (
  `ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` VARCHAR(64) NOT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `transaction_id` CHAR(64) NULL,
  `currency` VARCHAR(3) NULL,
  `satoshis` BIGINT(20) UNSIGNED NULL,
  `amount` DECIMAL(8,2) NULL,
  `exchange` DECIMAL(8,4) NULL,
  `order_id` BIGINT(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE INDEX `payment_id_UNIQUE` (`payment_id` ASC),
  INDEX `order_id` (`order_id` ASC),
  UNIQUE INDEX `ID_UNIQUE` (`ID` ASC)
  )' . $this->wpdb->get_charset_collate() . ';',

		];
	}


	/**
	 * Execute scripts greater than current schema version up to, and including target version.
	 *
	 * @since 0.1.0
	 *
	 * @return bool  true if there where schema updates run.
	 * @throws WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException If there was an error during the update process
	 */
	public function maybe_update(): bool {

		if ( version_compare( $this->target_version, $this->start_version, '>' ) ) {

			$filtered_updates = array_filter(
				$this->schema_version_updates,
				function ( $key ) {
					return (bool) version_compare( $this->start_version, $key, '<=' ) && version_compare( $key, $this->target_version, '<=' );
				},
				ARRAY_FILTER_USE_KEY
			);

			if ( ! empty( $filtered_updates ) ) {

				if ( uksort(
					$filtered_updates,
					array(
						'self',
						'compare_version_asc',
					)
				)
				) {
					WcGatewayMoneyButtonLogger::get_logger()->debug( 'sorted updates ' . wp_json_encode( $filtered_updates ) );
					// TODO Refactor this monstrosity into more discreet checks and specific functions.
					foreach ( $filtered_updates as $script_version => $updates ) {
						foreach ( $updates as $statement ) {
							// phpcs:ignore WordPress.DB.PreparedSQL
							$result = (bool) $this->wpdb->query( $statement );
							WcGatewayMoneyButtonLogger::get_logger()->debug(
								sprintf(
									'query result [%b] for query statement: %s',
									$result,
									$statement
								)
							);

							if ( false === $result ) {
								if ( '' !== $this->wpdb->last_error ) {
									WcGatewayMoneyButtonLogger::get_logger()->warning( 'There was an error executing a database update script. The error was ' . $this->wpdb->last_error );

								}
								$localized = __( 'An database update query for WooCommerce Gateway MoneyButton failed. The plugin may be unusable. ', 'wc-gateway-moneybutton' );
								throw new WcGatewayMoneyButtonException( sprintf( 'Database Update Failed for update statement: %s', $statement, $localized ) );
							}
						}
						// Update the option for each collection of scripts, so if later scripts fail we don't run them again.
						update_option( self::$option_name_scheme_version, $script_version );
						$this->current_version = $script_version;
					}

					// when the loop is finished, $current_version and schema version may not actually equal target version, as the target version may not have had db scripts.
					if ( empty( $this->schema_version_updates[ $this->target_version ] ) ) {
						update_option( self::$option_name_scheme_version, $this->target_version );
						$this->current_version = $this->target_version;
					}
				} else {
					$localized = __( 'Correct ordering of database update scripts failed. Updates were not performed', 'wc-gateway-moneybutton' );
					throw new WcGatewayMoneyButtonException( 'Error sorting database update scripts', $localized );
				}
			} else {
				// There were no schema updates for target_version, but we should set the new version anyway
				update_option( self::$option_name_scheme_version, $this->target_version );
				$this->current_version = $this->target_version;
			}
		}

		return version_compare( $this->current_version, $this->start_version, '>' );

	}


	/**
	 * array sort callable function for an ascending ordering of PHP Standardized versions
	 *
	 * @since 0.1.0
	 *
	 * @param string $a first
	 * @param string $b second
	 *
	 * @return int
	 */
	private static function compare_version_asc( string $a, string $b ): int {
		return version_compare( $a, $b, '>' );
	}
}
