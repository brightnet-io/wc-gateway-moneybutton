<?php

namespace WcGatewayMoneyButton\Core;

use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\Payment\RawPaymentEvent;
use WcGatewayMoneyButton\Payment\RawPaymentEventRepository;
use WcGatewayMoneyButton\PluginTestCase;

/**
 * Some tests for plugin update/installation. Checking that the db schema updates are run
 */
class UpdatePluginTests extends \WcGatewayMoneyButton\Testing\Integration\PluginTestCase {


	public function test_it_creates_the_raw_table() {
		set_current_screen('plugins');
		Plugin::get_instance()->maybe_update();
		global $wpdb;
		$table_name = $wpdb->prefix . RawPaymentEventRepository::$table_name;

		$wpdb->insert(
			$table_name,
			array(
				'received'  => current_time('mysql'),
				'json_string' => '{\"prop\" : \"value\"}',
			),
			array(
				'%s',
				'%s',
			)
		);
		$insert_id = $wpdb->insert_id;
		$this->assertNotEmpty( $insert_id );

	}


	public function test_it_creates_the_payments_table(){
		set_current_screen('plugins');
		Plugin::get_instance()->maybe_update();
		global $wpdb;
		$table_name = $wpdb->prefix . MoneyButtonPaymentRepository::$table_name;

		$wpdb->insert(
			$table_name,
			array(
				'payment_id'  => 'payment_id',
				'created_at' => 'created_at',
				'order_id' => 1
			),
			array(
				'%s',
				'%s',
				'%d'
			)
		);
		$insert_id = $wpdb->insert_id;
		$this->assertNotEmpty( $insert_id );
	}

}
