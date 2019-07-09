<?php

namespace WcGatewayMoneyButton\Core;


use WcGatewayMoneyButton\Testing\Integration\PluginTestCase;

/**
 * A somewhat contrived "integration" test example. These tests could probably be achieved with an adequate level of confidence by using WP_Mock, and are here only as an example.
 * Integration tests should be geared more towards testing integration between plugins, such as WooCommerce.
 */
class PluginBootstrapTests extends PluginTestCase {

	// TODO Refactor tests for scripts and styles to actually use different public and admin screens to test that the files are enqueued correctly rather than calling the enqueue methods which just tests they are registered correctly
	public function setUp() {
		parent::setUp();
		//do_action('plugins_loaded');

	}

	/**
	 * Test teardown
	 */
	public function tearDown() {

		// Need to dequeue scripts on teardown as parent teardown does manage $wp_scripts global;
		wp_dequeue_script( 'wc_gateway_moneybutton_frontend' );
		wp_dequeue_script( 'wc_gateway_moneybutton_shared' );
		wp_dequeue_script( 'wc_gateway_moneybutton_admin' );
		wp_dequeue_style( 'wc_gateway_moneybutton_frontend' );
		wp_dequeue_style( 'wc_gateway_moneybutton_shared' );
		wp_dequeue_style( 'wc_gateway_moneybutton_admin' );

		parent::tearDown();

	}


	/**
	 * Test that the client side scripts are enqueued and the admin script is not
	 */
	public function test_enqueued_scripts() {

		do_action( 'wp_enqueue_scripts' );
		$this->assertTrue( wp_script_is( 'wc_gateway_moneybutton_frontend' ) );
		$this->assertTrue( wp_script_is( 'wc_gateway_moneybutton_shared' ) );
		$this->assertFalse( wp_script_is( 'wc_gateway_moneybutton_admin' ) );

		// TODO test for moneybutton js on order-pay screen


	}

	/**
	 * Test that the admin side scripts are enqueued and the client side is not
	 */
	public function test_admin_scripts() {
		set_current_screen('shop_order');
		do_action( 'admin_enqueue_scripts' );
		$this->assertTrue( wp_script_is( 'wc_gateway_moneybutton_shared' ) );
		$this->assertTrue( wp_script_is( 'wc_gateway_moneybutton_admin' ) );
		$this->assertFalse( wp_script_is( 'wc_gateway_moneybutton_frontend' ) );
	}

	/**
	 * Test that the client side styles are enqueued and the admin script is not
	 */
	public function test_enqueued_styles() {

		do_action( 'wp_enqueue_scripts' ); // Note: The plugin core hook styles to to enqueue_scripts;
		$this->assertTrue( wp_style_is( 'wc_gateway_moneybutton_frontend' ) );
		$this->assertTrue( wp_style_is( 'wc_gateway_moneybutton_shared' ) );
		$this->assertFalse( wp_style_is( 'wc_gateway_moneybutton_admin' ) );
	}

	/**
	 * Test that the admin side styles are enqueued and the client side is not
	 */
	public function test_admin_styles() {
		set_current_screen('shop_order');
		do_action( 'admin_enqueue_scripts' );
		$this->assertTrue( wp_style_is( 'wc_gateway_moneybutton_shared' ) );
		$this->assertTrue( wp_style_is( 'wc_gateway_moneybutton_admin' ) );
		$this->assertFalse( wp_style_is( 'wc_gateway_moneybutton_frontend' ) );
	}

	/**
	 * Test that the updater/installer ran
	 */
	public function test_sets_version_option() {
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user    = wp_set_current_user( $user_id );

		// This is the key here.
		set_current_screen( 'plugins' );
		Plugin::get_instance()->maybe_update();
		$this->assertEquals( WC_GATEWAY_MONEYBUTTON_VERSION, get_option( 'wc_gateway_moneybutton_version' ) );
	}


}
