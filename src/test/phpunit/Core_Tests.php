<?php
namespace WcGatewayMoneyButton\Core;

/**
 * This is a very basic test case to get things started. You should probably rename this and make
 * it work for your project. You can use all the tools provided by WP Mock and Mockery to create
 * your tests. Coverage is calculated against your includes/ folder, so try to keep all of your
 * functional code self contained in there.
 *
 * References:
 *   - http://phpunit.de/manual/current/en/index.html
 *   - https://github.com/padraic/mockery
 *   - https://github.com/10up/wp_mock
 */

use WcGatewayMoneyButton as Base;

class Core_Tests extends Base\Testing\Unit\TestCase {


		public function setUp() {
			parent::setUp(); // TODO: Change the autogenerated stub
			require_once PROJECT  . 'functions/core.php';
		}


	/**
	 * Test activation routine.
	 */
	public function test_activate() {

		// Act
		activate();

		// Verify
		$this->assertTrue( true ); // Replace with actual assertion
	}

	/**
	 * Test deactivation routine.
	 */
	public function test_deactivate() {
		// Setup

		// Act
		deactivate();

		// Verify
		$this->assertTrue( true ); // Replace with actual assertion
	}
}
