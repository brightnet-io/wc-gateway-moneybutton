<?php

namespace WcGatewayMoneyButton\Payment;

use PHPUnit\Framework\TestCase;
use WcGatewayMoneyButton\Testing\Unit\NoOpLogger;
use WcGatewayMoneyButton\Testing\Unit\StdOutLogger;

class MoneyButtonPaymentPendingTests extends \WcGatewayMoneyButton\Testing\Unit\TestCase {



	public function test_should_create_from_pending_event() {

		$order_id = 1;
		//$button_data_encode = addslashes(json_encode( $button_data ));
		 $button_data_encode = '{"order_key": "order_key", "order_id": '. $order_id .', "cart_hash": "cart_hash"}';
		$payment_event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id' => 'payment_id',
					'createdAt' => '',
					'updatedAt' => '',
					'status' => 'PENDING',
					'buttonData' => $button_data_encode,
					'paymentOutputs' => array(
						 [
							'id' => 'output_id',
							'createdAt' => '',
							'updatedAt' => '',
							'type' => 'user',
							'userId' => 'user_id',
							'address' => null,
							'currency'=> 'USD',
							'amount' => '0.10',
							'satoshis'=> '5555'
						]
					)
				)
			)
		);

		\WP_Mock::expectAction('wc_gateway_moneybutton_payment_pending',
			$order_id,
			\WP_Mock\Functions::type('MoneyButtonPayment'),
			$payment_event
		);



		$payment = new MoneyButtonPayment($payment_event->get_payment_id(),$payment_event->get_order_id());
		$payment->apply($payment_event);



		$this->assertEquals('payment_id',$payment->get_payment_id());
		$this->assertEquals($order_id,$payment->get_order_id());
		$this->assertEquals('PENDING', $payment->get_status());

		$this->assertConditionsMet();



	}

	public function test_should_error_out_of_sequence_RECEIVED() {
		// SETUP
		$payment = $this->generate_mock_payment( 'RECEIVED' );
		$button_data_encode = '{"order_key": "order_key", "order_id": ' . $payment->get_order_id() . ', "cart_hash": "cart_hash"}';
		$new_status = 'PENDING';
		$txid       = 'txid';

		$received_event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id'             => $payment->get_payment_id(),
					'createdAt'      => '',
					'updatedAt'      => '',
					'status'         => $new_status,
					'buttonData'     => $button_data_encode

			)
			)
		);


		$this->expectException( EventSequenceException::class );

		// Act
		$payment->apply( $received_event );
		$this->assertConditionsMet();

	}

	public function test_should_error_out_of_sequence_COMPLETED() {
		// SETUP
		$payment = $this->generate_mock_payment( 'COMPLETED' );
		$button_data_encode = '{"order_key": "order_key", "order_id": ' . $payment->get_order_id() . ', "cart_hash": "cart_hash"}';
		$new_status = 'PENDING';
		$txid       = 'txid';

		$received_event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id'             => $payment->get_payment_id(),
					'createdAt'      => '',
					'updatedAt'      => '',
					'status'         => $new_status,
					'buttonData'     => $button_data_encode

				)
			)
		);


		$this->expectException( EventSequenceException::class );

		// Act
		$payment->apply( $received_event );
		$this->assertConditionsMet();

	}

	public function test_should_error_out_of_sequence_FAILED() {
		// SETUP
		$payment = $this->generate_mock_payment( 'FAILED' );
		$button_data_encode = '{"order_key": "order_key", "order_id": ' . $payment->get_order_id() . ', "cart_hash": "cart_hash"}';
		$new_status = 'PENDING';
		$txid       = 'txid';

		$received_event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id'             => $payment->get_payment_id(),
					'createdAt'      => '',
					'updatedAt'      => '',
					'status'         => $new_status,
					'buttonData'     => $button_data_encode

				)
			)
		);


		$this->expectException( EventSequenceException::class );

		// Act
		$payment->apply( $received_event );
		$this->assertConditionsMet();

	}

}
