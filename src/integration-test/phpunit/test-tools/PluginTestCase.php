<?php

namespace WcGatewayMoneyButton\Testing\Integration;

use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\Payment\RawPaymentEventRepository;

/**
 * Class PluginTestCase
 * @package WcGatewayMoneyButton
 */
class PluginTestCase extends \WP_UnitTestCase {


	/**
	 * Set Up for plugin test cases
	 *
	 * includes our plugin bootstrap file and makes the plugin active.
	 * Prepares for table drops.
	 */
	public function setUp() {

		//remove_filter( 'query', array( $this, '_create_temporary_tables' ) );
		require_once __DIR__ . '/../../../main/php/wc-gateway-moneybutton.php';
		tests_add_filter( 'option_active_plugins', array( $this, 'set_active_plugins' ) );
		do_action('plugins_loaded');

		parent::setUp();




	}

	public function tearDown() {
		//remove_filter( 'query', array( $this, '_drop_temporary_tables' ) );
		global $wpdb;
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . RawPaymentEventRepository::$table_name .';' );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MoneyButtonPaymentRepository::$table_name .';' );
		unset( $GLOBALS['current_screen'] );
		parent::tearDown();

	}

	function set_active_plugins( $activePlugins ) {
		return array_unique(
			array_merge( [
				//'woocommerce/woocommerce.php',
				'wc-gateway-moneybutton.php'
			], $activePlugins ?: [] )
		);
	}



}





