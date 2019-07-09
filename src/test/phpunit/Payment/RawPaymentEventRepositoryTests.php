<?php

namespace WcGatewayMoneyButton\Payment;

use Mockery;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;
use WcGatewayMoneyButton\Testing\Unit\TestCase;


class RawPaymentEventRepositoryTests extends TestCase {




	public function test_should_save(){
		// Setup
		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
;


		$wpdb->shouldReceive( 'insert' )
		     ->once()
		     ->andReturn( 1 );



		$repo = new RawPaymentEventRepository($wpdb);

		$json_string = '{"foo": "bar"}';
		$received = gmdate( 'Y-m-d\TH:i:s\Z' );

		$raw_payment = $repo->save(new RawPaymentEvent($json_string,$received));

		// Verify
		$this->assertConditionsMet();
		$this->assertEquals( 1, $raw_payment->get_id() );


	}

	public function test_should_error_if_has_id(){
		// Setup
		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		;


		$wpdb->shouldReceive( 'insert' )
		     ->times(0);


		$this->expectException( WcGatewayMoneyButtonException::class );

		$repo = new RawPaymentEventRepository($wpdb);

		$json_string = '{"foo": "bar"}';
		$received = gmdate( 'Y-m-d\TH:i:s\Z' );

		$raw_payment = new RawPaymentEvent($json_string,$received);
		$raw_payment->set_id(1);

		$repo->save($raw_payment);


		// Verify
		$this->assertConditionsMet();
		$this->assertEquals( 1, $raw_payment->get_id() );
	}


	public function test_should_find_by_id(){
		// Setup

		$result_row = array(
			'ID'             => 1,
			'json_string' => 'json_string',
			'received' => 'received'
		);

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )
		     ->withArgs(
			     array(
				     'SELECT * from wp_wcgmb_raw WHERE ID  = %d',
				     1
			     )
		     )
		     ->once();

		$wpdb->shouldReceive( 'get_row' )
		     ->once()
		     ->andReturn( $result_row );

		//Act

		$repo = new RawPaymentEventRepository($wpdb);

		$payment = $repo->find_by_id( 1 );


		// Verify
		$this->assertConditionsMet();

		$this->assertEquals( $result_row['ID'], $payment->get_id() );
		$this->assertEquals( $result_row['json_string'], $payment->get_json_string() );
		$this->assertEquals( $result_row['received'], $payment->get_received() );

	}


}
