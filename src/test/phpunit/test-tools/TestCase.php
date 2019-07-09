<?php

namespace WcGatewayMoneyButton\Testing\Unit;

use PHPUnit_Framework_TestResult;
use Text_Template;
use WcGatewayMoneyButton\Payment\MoneyButtonPayment;
use WP_Mock;
use WP_Mock\Tools\TestCase as BaseTestCase;

class TestCase extends BaseTestCase {
	public function run( \PHPUnit\Framework\TestResult $result = null ) {
		$this->setPreserveGlobalState( false );
		return parent::run( $result );
	}

	protected $testFiles = array();

	public function setUp() {
		if ( ! empty( $this->testFiles ) ) {
			foreach ( $this->testFiles as $file ) {
				if ( file_exists( PROJECT . $file ) ) {
					require_once PROJECT . $file;
				}
			}
		}

		parent::setUp();

		// Function mocked for logger
		\WP_Mock::userFunction(
			'wc_get_logger',
			array(
				'return'=> new NoOpLogger(), // Change to StdOutLogger if you want log output.
			)
		);

		\WP_Mock::userFunction('get_option',array(
			'args' => 'woocommerce_moneybutton_settings',
			'return' => [
				'debug_logging' => 'yes'
			]
		));
	}

	public function assertActionsCalled() {
		$actions_not_added = $expected_actions = 0;
		try {
			WP_Mock::assertActionsCalled();
		} catch ( \Exception $e ) {
			$actions_not_added = 1;
			$expected_actions  = $e->getMessage();
		}
		$this->assertEmpty( $actions_not_added, $expected_actions );
	}

	public function ns( $function ) {
		if ( ! is_string( $function ) || false !== strpos( $function, '\\' ) ) {
			return $function;
		}

		$thisClassName = trim( get_class( $this ), '\\' );

		if ( ! strpos( $thisClassName, '\\' ) ) {
			return $function;
		}

		// $thisNamespace is constructed by exploding the current class name on
		// namespace separators, running array_slice on that array starting at 0
		// and ending one element from the end (chops the class name off) and
		// imploding that using namespace separators as the glue.
		$thisNamespace = implode( '\\', array_slice( explode( '\\', $thisClassName ), 0, - 1 ) );

		return "$thisNamespace\\$function";
	}

	/**
	 * Define constants after requires/includes
	 *
	 * See http://kpayne.me/2012/07/02/phpunit-process-isolation-and-constant-already-defined/
	 * for more details
	 *
	 * @param \Text_Template $template
	 */
	public function prepareTemplate( \Text_Template $template ) {
		$template->setVar(
			[
				'globals' => '$GLOBALS[\'__PHPUNIT_BOOTSTRAP\'] = \'' . $GLOBALS['__PHPUNIT_BOOTSTRAP'] . '\';',
			]
		);
		parent::prepareTemplate( $template );
	}


	/**
	 * Generate a "loaded"/"existing" MoneyButton payment with the given status.
	 * @param string $status
	 *
	 * @return MoneyButtonPayment
	 */
	protected function generate_mock_payment( string $status ) {
		$payment = new MoneyButtonPayment();
		$payment->fill( array(
			'ID'             => 1,
			'created_at'     => date('Y-m-d H:i:s'),
			'updated_at'     =>  date('Y-m-d H:i:s'),
			'payment_id'     => 'XXXXX',
			'status'         => $status,
			'transaction_id' => null,
			'currency'       => null,
			'satoshis'       => null,
			'amount'         => null,
			'exchange'       => null,
			'order_id'       => 1
		) );

		return $payment;
	}
}
