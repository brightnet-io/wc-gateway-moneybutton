<?php


namespace WcGatewayMoneyButton\Admin;


use WcGatewayMoneyButton\Testing\Unit\TestCase;

class DependencyCheckerTests extends TestCase {


	public function testConstructorErrorOnInvalidVersionNumber() {
		$this->expectException( \InvalidArgumentException::class );
		new DependencyChecker( 'a.a.0-123', '0.0.0-dev' );
	}

	/**
	 * @runInSeparateProcess true
	 */
	public function testLowerWooCommerceVersionThrowsException() {
		define( 'WC_VERSION', '2.7' );

		$this->expectException( FailedDependencyException::class );
		$dependencyChecker = new DependencyChecker( '3.6.0', '5.3.2' );
		$dependencyChecker->check_woocommerce_version();
	}

	/**
	 * @runInSeparateProcess true
	 */
	public function testHigherWooCommerceVersion() {
		define( 'WC_VERSION', '3.6.2' );

		$dependencyChecker = new DependencyChecker( '3.6.0', '5.3.2' );
		$this->assertTrue( $dependencyChecker->check_woocommerce_version() );
	}

	/**
	 * @runInSeparateProcess true
	 */
	public function testEqualWooCommerceVersion() {
		define( 'WC_VERSION', '3.6.2' );

		$dependencyChecker = new DependencyChecker( '3.6.0', '5.3.2' );
		$this->assertTrue( $dependencyChecker->check_woocommerce_version() );
	}


	public function testLowerPhpVersionThrowsException() {
		$this->expectException( FailedDependencyException::class );
		// Use a ridiculous PHP version number, can't redefine PHP_VERSION
		$dependencyChecker = new DependencyChecker( '3.6.0', '99.9.9' );
		$dependencyChecker->check_php_version();
	}


	public function testEqualPHPVersion() {
		$dependencyChecker = new DependencyChecker( '3.6.0', PHP_VERSION );
		$this->assertTrue( $dependencyChecker->check_php_version() );
	}

	public function testNoHttpsCheckoutThrowsException() {

		\WP_Mock::userFunction(
			'wc_checkout_is_https',
			array(
				'return' => false
			)
		);
		$this->expectException( FailedDependencyException::class );
		$dependencyChecker = new DependencyChecker( '3.6.0', PHP_VERSION );
		$dependencyChecker->check_https_checkout();
	}

	public function testHttpsCheckoutDoesNotThrowException() {
		\WP_Mock::userFunction(
			'wc_checkout_is_https',
			array(
				'return' => true
			)
		);

		$dependencyChecker = new DependencyChecker( '3.6.0', PHP_VERSION );
		$this->assertTrue( $dependencyChecker->check_https_checkout() );
	}

	/**
	 * @runInSeparateProcess true
	 */
	public function testPassesCheckReturnsFalseAndHasErrors() {
		\WP_Mock::userFunction(
			'wc_checkout_is_https',
			array(
				'return' => false
			)
		);
		\WP_Mock::userFunction(
			'wc_checkout_is_https',
			array(
				'return' => false
			)
		);

		define( 'WC_VERSION', '3.6.0' );
		$dependencyChecker = new DependencyChecker( '3.6.2', '9.99.9' );
		$this->assertFalse( $dependencyChecker->passes_check() );
		$errors = $dependencyChecker->get_errors();
		$this->assertEquals( 3, count( $errors ) );
		$this->assertContainsOnlyInstancesOf( FailedDependencyException::class, $errors );

	}

}
