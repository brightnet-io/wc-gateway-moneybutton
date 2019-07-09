<?php


namespace WcGatewayMoneyButton\Admin;

use Mockery;
use WcGatewayMoneyButton\Testing\Unit\TestCase;


class DBUpdaterTests extends TestCase {

		public function setUp() {
			parent::setUp();


			\WP_Mock::userFunction(
				'wp_json_encode',
				array(
					'return' => json_encode(0)
				)
			);


		}

	/**
	 * It should error if $target_version argument is lower than current version.
	 */
	public function test_exception_on_lower_version_number() {
		// Setup
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';

		$wpdb->shouldNotHaveBeenCalled();

		\WP_Mock::userFunction(
			'get_option',
			array(
				'args'   => array( DBUpdater::$option_name_scheme_version ),
				'return' => '1.0.0'
			)
		);

		$this->expectException( \InvalidArgumentException::class );

		// Act
		$lower_version = '0.1.0';
		$db_updater    = new DBUpdater( $lower_version, $wpdb );

		// Verify
		$this->assertConditionsMet();

	}

	public function test_should_run_scripts_on_no_version() {
		// Setup
		$target_version = '0.1.0';

		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive('get_charset_collate')->andReturn('utf8');
		$wpdb->shouldReceive('prepare');
		$wpdb->shouldReceive( 'query' )
		     ->twice()
		     ->andReturn( true );

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'args'   => array( DBUpdater::$option_name_scheme_version ),
				'return' => null
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 1,
				'args'  => array( DBUpdater::$option_name_scheme_version, '0.0.1' ),
			)
		);
		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 1,
				'args'  => array( DBUpdater::$option_name_scheme_version, $target_version ),
			)
		);

		//Act
		$db_updater = new DBUpdater($target_version,$wpdb);
		$result = $db_updater->maybe_update();

		// Verify
		$this->assertConditionsMet();
		$this->assertTrue($result);
	}

	public function test_should_not_run_scripts_on_same_version(){
		// Setup
		$target_version = '1.0.0';
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive('get_charset_collate')->andReturn('utf8');
		$wpdb->shouldReceive('prepare');
		// No query execution expected
		$wpdb->shouldNotHaveReceived('query');

		// On get option return $target_version
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'args'   => array( DBUpdater::$option_name_scheme_version ),
				'return' => $target_version // <-- same version
			)
		);

		// No version updated
		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 0,
			)
		);


		//Act
		$db_updater = new DBUpdater($target_version,$wpdb);

		// Verify
		$this->assertConditionsMet();

	}

	public function test_should_not_run_scripts_older_than_current_version(){
		// Setup
		$target_version = '2.0.0';
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive('get_charset_collate')->andReturn('utf8');
		$wpdb->shouldReceive('prepare');
		// No query execution expected, becuase we don't actually have newer scripts. But can test it doesn't run older ones
		$wpdb->shouldNotHaveReceived('query');

		// On get option return $target_version
		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'args'   => array( DBUpdater::$option_name_scheme_version ),
				'return' => '1.0.1' // Shouldn't run 1.0.0 scripts
			)
		);

		// No version updated
		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 0,
			)
		);


		//Act
		$db_updater = new DBUpdater($target_version,$wpdb);

		// Verify
		$this->assertConditionsMet();
	}

	/**
	 * It should run any update scripts between the current version and the target version. not just those for the target version
	 */
	public function test_should_run_intermediate_scripts_between_current_and_target(){
		// Setup
		$target_version = '1.0.1';
		$wpdb = Mockery::mock( '\WPDB' );
		$wpdb->prefix = 'wp_';
		$wpdb->shouldReceive('get_charset_collate')->andReturn('utf8');
		$wpdb->shouldReceive('prepare');
		$wpdb->shouldReceive( 'query' )
		     ->twice()
		     ->andReturn( true );

		\WP_Mock::userFunction(
			'get_option',
			array(
				'times'  => 1,
				'args'   => array( DBUpdater::$option_name_scheme_version ),
				'return' => '0.0.0'
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 1,
				'args'  => array( DBUpdater::$option_name_scheme_version, '0.0.1' ), // <- this is an intermidiate version
			)
		);

		\WP_Mock::userFunction(
			'update_option',
			array(
				'times' => 1,
				'args'  => array( DBUpdater::$option_name_scheme_version, $target_version ),
			)
		);

		//Act
		$db_updater = new DBUpdater($target_version,$wpdb);
		$result = $db_updater->maybe_update();

		// Verify
		$this->assertConditionsMet();
		$this->assertTrue($result);

	}

}
