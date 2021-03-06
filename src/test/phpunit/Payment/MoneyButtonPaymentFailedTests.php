<?php

namespace WcGatewayMoneyButton\Payment;

use WcGatewayMoneyButton\Testing\Unit\StdOutLogger;
use WcGatewayMoneyButton\Testing\Unit\TestCase;

class MoneyButtonPaymentFailedTests extends TestCase {





	public function test_should_apply_to_new() {

		$payment            = new MoneyButtonPayment( 'XXXXX', 1 );
		$button_data_encode = '{"order_key": "order_key", "order_id": ' . $payment->get_order_id() . ', "cart_hash": "cart_hash"}';
		$new_status         = 'FAILED';
		$txid               = 'txid';

		$event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id'             => $payment->get_payment_id(),
					'createdAt'      => '',
					'updatedAt'      => '',
					'status'         => $new_status,
					'buttonData'     => $button_data_encode,
					'txid'           => $txid,
					'paymentOutputs' => array(
						[
							'id'        => 'output_id',
							'createdAt' => '',
							'updatedAt' => '',
							'type'      => 'user',
							'userId'    => 'user_id',
							'address'   => null,
							'currency'  => 'USD',
							'amount'    => '0.10',
							'satoshis'  => '5555'
						]
					)
				)
			)
		);


		\WP_Mock::expectAction( 'wc_gateway_moneybutton_payment_failed',
			$payment->get_order_id(),
		    $payment,
			$event
		);

		// Act
		$payment->apply( $event );

		$this->assertEquals( 'FAILED', $payment->get_status() );
		$this->assertEquals( $txid, $payment->get_transaction_id() );
		$this->assertEquals( 0.10, $payment->get_amount() );
		$this->assertEquals( 'USD', $payment->get_currency() );
		$this->assertEquals( (int) 5555, $payment->get_satoshis() );
		$this->assertConditionsMet();
	}




	public function test_should_error_out_of_sequence_completed() {
		// SETUP
		$payment = $this->generate_mock_payment( 'COMPLETED' );
		$button_data_encode = '{"order_key": "order_key", "order_id": ' . $payment->get_order_id() . ', "cart_hash": "cart_hash"}';
		$new_status = 'FAILED';
		$txid       = 'txid';

		$received_event = MoneyButtonPaymentEvent::from_webhook_object(
			array(
				'payment' => array(
					'id'             => $payment->get_payment_id(),
					'createdAt'      => '',
					'updatedAt'      => '',
					'status'         => $new_status,
					'buttonData'     => $button_data_encode,
					'txid'           => $txid,
					'paymentOutputs' => array(
						[
							'id'        => 'output_id',
							'createdAt' => '',
							'updatedAt' => '',
							'type'      => 'user',
							'userId'    => 'user_id',
							'address'   => null,
							'currency'  => 'USD',
							'amount'    => '0.10',
							'satoshis'  => '5555'
						]
					)
				)
			)
		);

		$this->expectException( EventSequenceException::class );

		// Act
		$payment->apply( $received_event );
		$this->assertConditionsMet();

	}




}
