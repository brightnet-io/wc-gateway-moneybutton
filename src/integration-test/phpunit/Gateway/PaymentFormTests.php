<?php

namespace WcGatewayMoneyButton\Gateway;

use WcGatewayMoneyButton\Core\PaymentHelper;
use WcGatewayMoneyButton\Testing\Integration\GatewayAvailableTestCase;

/**
 * Class PaymentFormTests
 *
 * @package WcGatewayMoneyButton\Gateway
 */
class PaymentFormTests extends GatewayAvailableTestCase {





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


	public function test_it_should_fail_on_no_nonce(){
		$order = \WC_Helper_Order::create_order();

		$this->expectExceptionMessage('Authorization Failed');
		$this->expectException(\WPDieException::class);

		$payment_form = new PaymentForm($order->get_id(),$this->gateway);
		$payment_form->form_submission();
	}


	public function test_it_should_fail_on_incorrect_nonce(){
		$order = \WC_Helper_Order::create_order();
		wp_create_nonce('wc-gateway-moneybutton-payment_' . $order->get_order_key());
		$_POST['_wpnonce'] = 'not_correct';
		$_REQUEST['key'] = $order->get_order_key();

		$this->expectExceptionMessage('Authorization Failed');
		$this->expectException(\WPDieException::class);

		$payment_form = new PaymentForm($order->get_id(),$this->gateway);
		$payment_form->form_submission();
	}


	public function test_it_should_fail_on_incorrect_form_order_key(){
		$expect_order = \WC_Helper_Order::create_order();
		$expect_order->set_status('pending');
		$expect_order = wc_get_order($expect_order->save());

		$this->set_valid_request($expect_order);

		// Change order key post attribute
		$_POST['wc-gateway-moneybutton-order_key'] = 'kaboom!';


		$payment_form = $this->getMockBuilder( PaymentForm::class )
		                     ->setMethods( [ 'success_redirect' ] )
		                     ->setConstructorArgs(array($expect_order->get_id(),$this->gateway))
		                     ->getMock();

		$payment_form->expects($this->never())->method('success_redirect');

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid Money Button payment response');

		$payment_form->form_submission();

		$order = wc_get_order($expect_order->get_id());
		$this->assertEquals('pending',$order->get_status(),'Order status was changed when not expected');
		$this->assertEquals($_POST['wc-gateway-moneybutton-payment_id'],$order->get_meta('_wc_gateway_moneybutton_swipe',true),'Swipe meta set when not expected');

	}

	public function test_it_should_fail_on_incorrect_form_order_id(){
		$expect_order = \WC_Helper_Order::create_order();
		$expect_order->set_status('pending');
		$expect_order = wc_get_order($expect_order->save());

		$this->set_valid_request($expect_order);

		// Change order id post attribute
		$_POST['wc-gateway-moneybutton-order_id'] = 'kaboom!';


		$payment_form = $this->getMockBuilder( PaymentForm::class )
		                     ->setMethods( [ 'success_redirect' ] )
		                     ->setConstructorArgs(array($expect_order->get_id(),$this->gateway))
		                     ->getMock();

		$payment_form->expects($this->never())->method('success_redirect');

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid Money Button payment response');

		$payment_form->form_submission();

		$order = wc_get_order($expect_order->get_id());
		$this->assertEquals('pending',$order->get_status(),'Order status was changed when not expected');
		$this->assertEquals($_POST['wc-gateway-moneybutton-payment_id'],$order->get_meta('_wc_gateway_moneybutton_swipe',true),'Swipe meta set when not expected');

	}

	public function test_it_should_fail_on_incorrect_cart_hash(){
		$expect_order = \WC_Helper_Order::create_order();
		$expect_order->set_status('pending');
		$expect_order = wc_get_order($expect_order->save());

		$this->set_valid_request($expect_order);

		// Change cart hash post attribute
		$_POST['wc-gateway-moneybutton-cart_hash'] = 'kaboom!';

		// Mock out the redirect method on payment form and set call expectation
		// https://pointybrackets.wordpress.com/2016/12/02/testing-methods-that-redirect-with-phpunit/
		$payment_form = $this->getMockBuilder( PaymentForm::class )
		                     ->setMethods( [ 'success_redirect' ] )
		                     ->setConstructorArgs(array($expect_order->get_id(),$this->gateway))
		                     ->getMock();

		$payment_form->expects($this->never())->method('success_redirect');

		$this->expectException(\WPDieException::class);
		$this->expectExceptionMessage('Invalid Money Button payment response');

		$payment_form->form_submission();

		$order = wc_get_order($expect_order->get_id());
		$this->assertEquals('pending',$order->get_status(),'Order status was changed when not expected');
		$this->assertEquals($_POST['wc-gateway-moneybutton-payment_id'],$order->get_meta('_wc_gateway_moneybutton_swipe',true),'Swipe meta set when not expected');

	}


	public function test_it_should_update_status_and_return_order_received(){
		$expect_order = \WC_Helper_Order::create_order();
		$expect_order->set_status('pending');
		$expect_order = wc_get_order($expect_order->save());

		$this->set_valid_request($expect_order);



		// Mock out the redirect method on payment form and set call expectation
		// https://pointybrackets.wordpress.com/2016/12/02/testing-methods-that-redirect-with-phpunit/
		$payment_form = $this->getMockBuilder( PaymentForm::class )
		                   ->setMethods( [ 'success_redirect' ] )
					->setConstructorArgs(array($expect_order->get_id(),$this->gateway))
					->getMock();

		$payment_form->expects($this->once())->method('success_redirect');


		$payment_form->form_submission();
		$order = wc_get_order($expect_order->get_id());
		$this->assertEquals('on-hold',$order->get_status(),'Valid payment form submission did not update order status');
		$this->assertEquals($_POST['wc-gateway-moneybutton-payment_id'],$order->get_meta('_wc_gateway_moneybutton_swipe',true),'Valid payment form submission did not add payment swipe meta');

	}




	private function set_valid_request(\WC_Order $expect_order){
		$nonce = wp_create_nonce('wc-gateway-moneybutton-payment_' . $expect_order->get_order_key());


		$payment_id = 'XXXXX';
		$order_key = $expect_order->get_order_key();
		$cart_hash = $expect_order->get_cart_hash();
		$form_order_id = $expect_order->get_id();


		$_POST['_wpnonce'] = $nonce;
		$_REQUEST['key'] = $expect_order->get_order_key();
		$_POST['wc-gateway-moneybutton-payment_id'] = $payment_id;
		$_POST['wc-gateway-moneybutton-order_key'] = $order_key;
		$_POST['wc-gateway-moneybutton-cart_hash'] = $cart_hash;
		$_POST['wc-gateway-moneybutton-order_id'] = $form_order_id;
	}

}
