<?php

namespace WcGatewayMoneyButton\Admin;







use Mockery;
use WcGatewayMoneyButton\Testing\Unit\NoOpLogger;
use WcGatewayMoneyButton\Testing\Unit\TestCase;


class UpdateManagerTests extends TestCase {

	public function setUp() {
		parent::setUp();
		\WP_Mock::userFunction(
			'wc_get_logger',
			array(
				'return'=> new NoOpLogger(),
			)
		);

	}

	/**
	 * It should update when there is no previous version.
	 *
	 * @runInSeparateProcess true
	 */
	public function test_updates_when_no_previous_version() {
		// Setup
		$mock_db_updater =    Mockery::mock('WcGatewayMoneyButton\Admin\DBUpdater')->makePartial();
		$mock_db_updater->shouldReceive('maybe_update')->times(1)->andReturn(true);



		\WP_Mock::userFunction( 'get_option', array(
			'times'  => 1,
			'args'   => array( 'wc_gateway_moneybutton_version' ),
			'return' => false
		) );


		\WP_Mock::userFunction( 'WcGatewayMoneyButton\Core\code_version', array(
			'times' => 1,
			'return' => '0.1.0'
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'times' => 1,
			'args'  => array( 'wc_gateway_moneybutton_version', '0.1.0' )
		) );

		// Fires actions
		\WP_Mock::expectAction( 'wc_gateway_moneybutton_before_update' );
		\WP_Mock::expectAction( 'wc_gateway_moneybutton_after_update' );

		// Act
		$updater = new UpdateManager($mock_db_updater);
		$updated = $updater->maybe_update();

		$this->assertTrue( $updated, 'maybe_update did not return true' );
		$this->assertConditionsMet();


	}

	/**
	 * It should update when the 'code version' is higher than the current version
	 *
	 * @runInSeparateProcess true
	 */
	public function test_updates_when_new_version() {
		// Setup
		$mock_db_updater = Mockery::mock( 'WcGatewayMoneyButton\Admin\DBUpdater' )
		                          ->shouldReceive('maybe_update')->times(1)->andReturn(true)->getMock();

		\WP_Mock::userFunction( 'get_option', array(
			'times'  => 1,
			'args'   => array( 'wc_gateway_moneybutton_version' ),
			'return' => '0.0.1'
		) );


		\WP_Mock::userFunction( 'WcGatewayMoneyButton\Core\code_version', array(
			'times' => 1,
			'return' => '0.2.0'
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'times' => 1,
			'args'  => array( 'wc_gateway_moneybutton_version', '0.2.0' )
		) );


		// Fires actions
		\WP_Mock::expectAction( 'wc_gateway_moneybutton_before_update' );
		\WP_Mock::expectAction( 'wc_gateway_moneybutton_after_update' );

		// Act
		$updater = new UpdateManager($mock_db_updater);
		$updated = $updater->maybe_update();

		$this->assertTrue( $updated, 'maybe_update did not return true' );
		$this->assertConditionsMet();
	}


	/**
	 * It should not update when the 'code version' is equal to the current version
	 *
	 * @runInSeparateProcess true
	 */
	public function test_does_not_update_equal_version() {
		// Setup

		$mock_db_updater = Mockery::mock( 'WcGatewayMoneyButton\Admin\DBUpdater' )
		                          ->shouldReceive('maybe_update')->times(0)->getMock();



		\WP_Mock::userFunction( 'get_option', array(
			'times'  => 1,
			'args'   => array( 'wc_gateway_moneybutton_version' ),
			'return' => '0.0.1'
		) );

		\WP_Mock::userFunction( 'WcGatewayMoneyButton\Core\code_version', array(
			'times' => 1,
			'return' => '0.0.1'
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'times' => 0
		) );


		// NOTE To test that the before_update and after_update actions are not called would require WP_Mock to be in strict mode.

		// Act
		$updater = new UpdateManager($mock_db_updater);
		$updated = $updater->maybe_update();

		$this->assertFalse( $updated, 'maybe_update did not return false' );
		$this->assertConditionsMet();
	}

	/**
	 * It should not update when the 'code version' is older than the current version.
	 *
	 * @runInSeparateProcess true
	 */
	public function test_does_not_update_older_version() {
		// Setup
		$mock_db_updater = Mockery::mock( 'WcGatewayMoneyButton\Admin\DBUpdater' )
		                          ->shouldReceive('maybe_update')->times(0)->getMock();

		\WP_Mock::userFunction( 'get_option', array(
			'times'  => 1,
			'args'   => array( 'wc_gateway_moneybutton_version' ),
			'return' => '0.0.2'
		) );

		\WP_Mock::userFunction( 'WcGatewayMoneyButton\Core\code_version', array(
			'times' => 1,
			'return' => '0.0.1'
		) );

		\WP_Mock::userFunction( 'update_option', array(
			'times' => 0
		) );


		// NOTE To test that the before_update and after_update actions are not called would require WP_Mock to be in strict mode.

		// Act
		$updater = new UpdateManager($mock_db_updater);
		$updated = $updater->maybe_update();

		$this->assertFalse( $updated, 'maybe_update did not return false' );
		$this->assertConditionsMet();
	}


}