<?php
/**
 *  Update manager for plugin.
 *
 * Responsible for performing any necessary operations such as db schema changes, data migraton or option/settings changes when plugin is installed or updated
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Admin
 */

namespace WcGatewayMoneyButton\Admin;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;
use function WcGatewayMoneyButton\Core\code_version;


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class UpdateManager
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Admin
 */
class UpdateManager {

	/**
	 * Logger
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var \WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * instance of WcGatewayMoneyButton\Admin\AdminNotices
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var AdminNotices $admin_notices
	 */
	private $admin_notices;


	/**
	 * Injected DB updater instance
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var DBUpdater
	 */
	private $db_updater;

	/**
	 * UpdateManager constructor.
	 *
	 * @param DBUpdater $db_updater Inject DBUpdater instance
	 */
	public function __construct( DBUpdater $db_updater ) {
		$this->logger     = WcGatewayMoneyButtonLogger::get_logger();
		$this->db_updater = $db_updater;
	}

	/**
	 * Will run plugin update (or installation) scripts if necessary.
	 *
	 * @return bool true if update was performed, otherwise false.
	 */
	public function maybe_update(): bool {
		$this->logger->debug( 'maybe_update' );
		$current_version = get_option( 'wc_gateway_moneybutton_version' );
		$target_version  = code_version();
		if ( $this->requires_update( $current_version, $target_version ) ) {

			do_action( 'wc_gateway_moneybutton_before_update' );
			$update_result = $this->run_updates( $current_version, $target_version );
			do_action( 'wc_gateway_moneybutton_after_update' );

			return $update_result;
		} else {
			return false;
		}
	}

	/**
	 * Provided two PHP-Standardized version numbers, determines whether an update is required
	 *
	 * @param string $current_version The 'current' version of the plugin before an update is run.
	 * @param string $target_version  The 'target' version for updates.
	 *
	 * @return bool true if update required, otherwise false
	 */
	protected function requires_update( string $current_version, string $target_version ): bool {

		// If current version is not set (new install) or version constant in code is higher, then update is required
		return empty( $current_version ) || ! ! version_compare( $target_version, $current_version, '>' );

	}

	/**
	 * Run required updates from $current_version up to (including) $target_version.
	 *
	 * @param string $current_version The 'current' version of the plugin before an update is run.
	 * @param string $target_version  The 'target' version for updates.
	 *
	 * @return bool If updates occured
	 */
	protected function run_updates( string $current_version, string $target_version ): bool {
		update_option( 'wc_gateway_moneybutton_version', $target_version );

		// Code to perform the update go here
		$result = $this->db_updater->maybe_update();
		if ( true === $result ) {
			$this->admin_notices = new AdminNotices();
			$notice              = sprintf(
				/* translators: the first placeholder is the previously installed version of the plugin. The second placeholder is the new installed/updated version of the plugin . */
				__( 'MoneyButton Gateway for WooCommerce: Database schema updated from version %1$s to version %2$s', 'wc-gateway-moneybutton' ),
				$current_version,
				$target_version
			);
			$this->admin_notices->add_notice( 'wc_gateway_moneybutton_db_updates', 'notice-upgrade', $notice, true );
		}
		// Here we are just returning the result of db updater, as it is currently the only update task
		return $result;
	}


}
