<?php

namespace WcGatewayMoneyButton\Gateway;


use WcGatewayMoneyButton\PluginTestCase;

/**
 * Class PaymentGatewayAvailabilityTests
 *
 * Tests cover scenarios that enable / disable gateway based on whether it is configured correctly.
 *
 * @package WcGatewayMoneyButton\Gateway
 */
class PaymentGatewayAvailabilityTests extends \WcGatewayMoneyButton\Testing\Integration\PluginTestCase {
	/** @var \WC_Payment_Gateway */
	private $gateway;

	public function setUp() {
		parent::setUp();


	}

	public function tearDown() {
		unset( $this->gateway );
		parent::tearDown();
	}


	/**
	 * Test that the Gateway is registered with WooCommerce
	 */
	public function test_gateway_is_registered() {
		$wc_gateways       = new \WC_Payment_Gateways();
		$payment_gateways  = $wc_gateways->get_payment_gateway_ids();
		$this->assertContains( PaymentGatewayImpl::$gateway_id, $payment_gateways );
	}


	/**
	 * Test that the Gateway is "available" if required settings are configured and enabled is truthy
	 */
	public function test_gateway_avaialble_when_required_settings_configured() {
		// Setup

		$settings = array(
			'mb_to'                => 'to',
			'mb_client_identifier' => 'clientid',
			'mb_button_id'         => 'buttonId',
			'mb_label'             => 'label',
			'mb_webhook_secret'    => 'secret',
			'enabled'              => 'yes',

		);
		update_option( 'woocommerce_moneybutton_settings ', $settings );
		$wc_gateways       = new \WC_Payment_Gateways();

		$available_gateway = $wc_gateways->get_available_payment_gateways();
		$this->assertArrayHasKey( PaymentGatewayImpl::$gateway_id, $available_gateway );

	}

	/**
	 * Test gateway us not available if missing required setttings, even if enabled is truthy
	 */
	public function test_gateway_not_avaialble_when_required_settings_missing() {
		// Setup
		$settings = array(
			'enabled'              => 'yes',
		);
		update_option( 'woocommerce_moneybutton_settings ', $settings );
		$wc_gateways       = new \WC_Payment_Gateways();
		$payment_gateway_ids  = $wc_gateways->get_payment_gateway_ids();
		$available_gateways = $wc_gateways->get_available_payment_gateways();
		$this->assertContains( PaymentGatewayImpl::$gateway_id, $payment_gateway_ids );
		$this->assertArrayNotHasKey( PaymentGatewayImpl::$gateway_id, $available_gateways );

	}

	/**
	 * Test that needs_setup returns true if required settings missing.
	 *
	 * This prevents the toggle switch on gateways screen from working
	 */
	public function test_gateway_cannot_be_enabled_via_ajax_toggle_if_required_settings_missing(){
		$settings = array(
			'enabled'              => 'yes',
		);
		update_option( 'woocommerce_moneybutton_settings ', $settings );
		$wc_gateways       = new \WC_Payment_Gateways();
		$payment_gateways = $wc_gateways->payment_gateways();
		$gateway = $payment_gateways[PaymentGatewayImpl::$gateway_id];
		$this->assertTrue($gateway->needs_setup(), 'needs_setup should return true if gateway not configured');

	}
}
