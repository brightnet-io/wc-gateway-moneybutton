<?php

namespace WcGatewayMoneyButton\Testing\Integration;

use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;
use WcGatewayMoneyButton\Payment\RawPaymentEventRepository;

/**
 * Class GatewayAvailableTestCase
 *
 * Base test case for tests that require configured and available gateway.
 * @package WcGatewayMoneyButton\Testing\Integration
 */
class GatewayAvailableTestCase extends PluginTestCase {


	/**
	 * Set Up for plugin test cases
	 *
	 * includes our plugin bootstrap file and makes the plugin active.
	 * Prepares for table drops.
	 */
	public function setUp() {
		$settings = array(
			'mb_to'                => 'to',
			'mb_client_identifier' => 'clientid',
			'mb_button_id'         => 'buttonId',
			'mb_label'             => 'label',
			'mb_webhook_secret'    => 'secret',
			'enabled'              => 'yes',
			'minimum_amount'       =>  '1',
			'debug_logging'     => 'yes'

		);
		update_option( 'woocommerce_moneybutton_settings ', $settings );
		parent::setUp();




	}

	public function tearDown() {

		parent::tearDown();

	}





}





