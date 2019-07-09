<?php

namespace WcGatewayMoneyButton\Gateway;


use WcGatewayMoneyButton\PluginTestCase;
use WcGatewayMoneyButton\Testing\Integration\GatewayAvailableTestCase;

/**
 * Class PaymentGatewayProcessPaymentTests
 *
 * Testing of place order (process_payment)
 *
 * @package WcGatewayMoneyButton\Gateway
 */
class PaymentGatewayProcessPaymentTests extends GatewayAvailableTestCase {

	/** @var \WC_Product */
	private $product;

	/**
	 * @var \WC_Payment_Gateway
	 */
	private $gateway;

	public function setUp() {
		parent::setUp();

			$wc_gateways       = new \WC_Payment_Gateways();
		$available_gateway = $wc_gateways->get_available_payment_gateways();
		$this->gateway = $available_gateway[PaymentGatewayImpl::$gateway_id];

	}

	public function tearDown() {
			parent::tearDown();
			unset($this->gateway);
	}


	/**
	 * Should return a fail result and add a notice if order total does not meet configured amount.
	 *
	 * @throws \WC_Data_Exception
	 */
	public function test_it_should_fail_when_not_meet_min_order(){


		//Setup
		$order = \WC_Helper_Order::create_order(1);
		$order->set_total('0.50');
		$order_id = $order->save();


		//Act
		$result = $this->gateway->process_payment($order_id);

		// Verify
		$this->assertEquals('fail',$result['result']);

		$notices = wc_get_notices();
		$error_notices = $notices['error'];
		$this->assertNotEmpty($error_notices,'Error notice not added');



	}

	/**
	 * Should reduce stock levels, empty cart, and return success.
	 *
	 * @throws \Exception
	 */
	public function test_it_should_reduce_stocklevels_and_empty_cart_on_success(){


		//Setup
		$product = \WC_Helper_Product::create_simple_product(true);
		$product->set_manage_stock(true);
		$product->set_stock_quantity(10);
		$product = wc_get_product($product->save());

		// Put some stuff in the cart to check it gets cleared
		/** @var \WC_Cart $cart */
		$cart  = WC()->cart;
		$cart_item_key = $cart->add_to_cart($product->get_id(),4);

		// Create an order
		$order = \WC_Helper_Order::create_order(1,$product);
		$order->set_payment_method( $this->gateway );
		$order = wc_get_order($order->save());

		//Act
		$result = $this->gateway->process_payment($order->get_id());

		// Verify
		$this->assertEquals('success',$result['result']);
		$expect_url = sprintf('http://example.org?order-pay=%1$s&key=%2$s',$order->get_id(),$order->get_order_key());
		$this->assertEquals($expect_url,$result['redirect'],'Returned incorrect pay for order url');

		// Check stock was reduced
		$check_product = wc_get_product($product->get_id());
		$this->assertEquals(6,$check_product->get_stock_quantity()); // create_order defaults to qty of 4.

		// Check cart was emptied
		$this->assertEmpty($cart->find_product_in_cart($cart_item_key),'Cart was not emptied');

	}

}
