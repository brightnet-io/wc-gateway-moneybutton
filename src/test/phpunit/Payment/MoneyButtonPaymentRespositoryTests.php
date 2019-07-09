<?php

namespace WcGatewayMoneyButton\Payment;

use Mockery;
use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonException;
use WcGatewayMoneyButton\Testing\Unit\TestCase;


class MoneyButtonPaymentRepositoryTests extends TestCase {



	public function test_should_insert_on_new() {
		// Setup
		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'START TRANSACTION' ) )
		     ->once()
		     ->andReturnTrue();


		$wpdb->shouldReceive( 'insert' )
		     ->once()
		     ->andReturn( 1 );

		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'COMMIT' ) )
		     ->once()
		     ->andReturnTrue();


		// Act
		$payment_id = 'xxx';
		$order_id   = 1;

		$repo = new MoneyButtonPaymentRepository( $wpdb );


		$payment = new MoneyButtonPayment(  $payment_id, $order_id );
		$payment->set_status( 'RECEIVED' );
		$payment->set_updated_at( date('Y-m-d\TH:i:s\Z') );
		$payment->set_created_at( date('Y-m-d\TH:i:s\Z') );
		$payment->set_transaction_id( 'transactionid' );
		$payment->set_amount( 3 );
		$payment->set_currency( 'USD' );
		$payment->set_satoshis( 82732 );
		$payment = $repo->save( $payment );


		// Verify
		$this->assertConditionsMet();
		$this->assertEquals( 1, $payment->get_id() );
	}

	public function test_should_update_on_existing() {
		// Setup
		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'START TRANSACTION' ) )
		     ->once()
		     ->andReturnTrue();
		$wpdb->shouldReceive( 'update' )
		     ->once()
		     ->andReturn( 1 );
		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'COMMIT' ) )
		     ->once()
		     ->andReturnTrue();


		$payment = Mockery::mock( 'WcGatewayMoneyButton\Payment\MoneyButtonPayment[is_new]', array(  'xxx', '1' ) )
		                  ->shouldReceive( 'is_new' )->andReturnFalse()->getMock();


		$repo    = new MoneyButtonPaymentRepository( $wpdb );
		$payment = $repo->save( $payment );


		// Verify
		$this->assertConditionsMet();

	}

	public function test_should_find_by_order_id() {
		// Setup

		$result_row = array(
			'ID'             => 1,
			'created_at'     => date('Y-m-d\TH:i:s\Z'),
			'updated_at'     => date('Y-m-d\TH:i:s\Z'),
			'payment_id'     => 'payment_id',
			'status'         => 'status',
			'transaction_id' => 'transaction_id',
			'currency'       => 'currency',
			'satoshis'       => (int) 1111,
			'amount'         => (float) 1.11,
			'exchange'       => (float) 0.23,
			'order_id'       => 1
		);

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )
		     ->withArgs(
			     array(
				     'SELECT * from wp_wcgmb_payments WHERE order_id  = %d ORDER BY created_at DESC',
				     1
			     )
		     )
		     ->once();
		$wpdb->shouldReceive( 'get_row' )
		     ->once()
		     ->andReturn( $result_row );

		//Act

		$repo = new MoneyButtonPaymentRepository( $wpdb );

		$payment = $repo->find_by_order_id( 1 );


		// Verify
		$this->assertConditionsMet();

		$this->assertEquals( $result_row['ID'], $payment->get_id() );
		$this->assertEquals( $result_row['created_at'], $payment->get_created_at() );
		$this->assertEquals( $result_row['updated_at'], $payment->get_updated_at() );
		$this->assertEquals( $result_row['payment_id'], $payment->get_payment_id() );
		$this->assertEquals( $result_row['status'], $payment->get_status() );
		$this->assertEquals( $result_row['transaction_id'], $payment->get_transaction_id() );
		$this->assertEquals( $result_row['currency'], $payment->get_currency() );
		$this->assertEquals( $result_row['satoshis'], $payment->get_satoshis() );
		$this->assertEquals( $result_row['amount'], $payment->get_amount() );
		$this->assertEquals( $result_row['exchange'], $payment->get_effective_exchange() );
		$this->assertEquals( $result_row['order_id'], $payment->get_order_id() );

	}




	public function test_should_find_by_id() {
		// Setup

		$result_row = array(
			'ID'             => 1,
			'created_at'     => date('Y-m-d\TH:i:s\Z'),
			'updated_at'     => date('Y-m-d\TH:i:s\Z'),
			'payment_id'     => 'payment_id',
			'status'         => 'status',
			'transaction_id' => 'transaction_id',
			'currency'       => 'currency',
			'satoshis'       => (int) 1111,
			'amount'         => (float) 1.11,
			'exchange'       => (float) 0.23,
			'order_id'       => 1
		);

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )
		     ->withArgs(
			     array(
				     'SELECT * from wp_wcgmb_payments WHERE ID  = %d',
				     1
			     )
		     )
		     ->once();

		$wpdb->shouldReceive( 'get_row' )
		     ->once()
		     ->andReturn( $result_row );

		//Act

		$repo = new MoneyButtonPaymentRepository( $wpdb );

		$payment = $repo->find_by_id( 1 );


		// Verify
		$this->assertConditionsMet();

		$this->assertEquals( $result_row['ID'], $payment->get_id() );
		$this->assertEquals( $result_row['created_at'], $payment->get_created_at() );
		$this->assertEquals( $result_row['updated_at'], $payment->get_updated_at() );
		$this->assertEquals( $result_row['payment_id'], $payment->get_payment_id() );
		$this->assertEquals( $result_row['status'], $payment->get_status() );
		$this->assertEquals( $result_row['transaction_id'], $payment->get_transaction_id() );
		$this->assertEquals( $result_row['currency'], $payment->get_currency() );
		$this->assertEquals( $result_row['satoshis'], $payment->get_satoshis() );
		$this->assertEquals( $result_row['amount'], $payment->get_amount() );
		$this->assertEquals( $result_row['exchange'], $payment->get_effective_exchange() );
		$this->assertEquals( $result_row['order_id'], $payment->get_order_id() );
	}

	public function test_should_find_by_payment() {
		// Setup

		$result_row = array(
			'ID'             => 1,
			'created_at'     => date('Y-m-d\TH:i:s\Z'),
			'updated_at'     => date('Y-m-d\TH:i:s\Z'),
			'payment_id'     => 'payment_id',
			'status'         => 'status',
			'transaction_id' => 'transaction_id',
			'currency'       => 'currency',
			'satoshis'       => (int) 1111,
			'amount'         => (float) 1.11,
			'exchange'       => (float) 0.23,
			'order_id'       => 1
		);

		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'prepare' )
		     ->withArgs(
			     array(
				     'SELECT * from wp_wcgmb_payments WHERE payment_id  = %s',
				     $result_row['payment_id']
			     )
		     )
		     ->once();

		$wpdb->shouldReceive( 'get_row' )
		     ->once()
		     ->andReturn( $result_row );

		//Act

		$repo = new MoneyButtonPaymentRepository( $wpdb );

		$payment = $repo->find_by_payment_id( $result_row['payment_id'] );


		// Verify
		$this->assertConditionsMet();

		$this->assertEquals( $result_row['ID'], $payment->get_id() );
		$this->assertEquals( $result_row['created_at'], $payment->get_created_at() );
		$this->assertEquals( $result_row['updated_at'], $payment->get_updated_at() );
		$this->assertEquals( $result_row['payment_id'], $payment->get_payment_id() );
		$this->assertEquals( $result_row['status'], $payment->get_status() );
		$this->assertEquals( $result_row['transaction_id'], $payment->get_transaction_id() );
		$this->assertEquals( $result_row['currency'], $payment->get_currency() );
		$this->assertEquals( $result_row['satoshis'], $payment->get_satoshis() );
		$this->assertEquals( $result_row['amount'], $payment->get_amount() );
		$this->assertEquals( $result_row['exchange'], $payment->get_effective_exchange() );
		$this->assertEquals( $result_row['order_id'], $payment->get_order_id() );
	}

	public function test_should_rollback_on_wpdb_error() {
		// Setup
		$wpdb         = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'START TRANSACTION' ) )
		     ->once()
		     ->andReturnTrue();


		$wpdb->shouldReceive( 'insert' )
		     ->once()
		     ->andReturn( false );

		$wpdb->shouldReceive( 'query' )
		     ->withArgs( array( 'ROLLBACK' ) )
		     ->once()
		     ->andReturnTrue();

		$this->expectException( WcGatewayMoneyButtonException::class );

		// have to move the last_x props

		$wpdb->last_query = 'last_query';
		$wpdb->last_error = 'last_error';


		// Act
		$payment_id = 'xxx';
		$order_id   = 1;

		$repo = new MoneyButtonPaymentRepository( $wpdb );


		$payment = new MoneyButtonPayment(  $payment_id, $order_id );

		$payment = $repo->save( $payment );


		// Verify
		$this->assertConditionsMet();

	}

}
