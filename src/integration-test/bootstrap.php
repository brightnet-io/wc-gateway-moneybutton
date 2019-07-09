<?php
/**
 * PHPUnit bootstrap file for integration testing.
 *
 * Sets Up WordPress and WooCommerce Test Tooling/Framework
 *
 */

if ( ! file_exists( __DIR__ . '/../../vendor/autoload.php' ) ) {
	throw new PHPUnit_Framework_Exception(
		'ERROR' . PHP_EOL . PHP_EOL .
		'You must use Composer to install the test suite\'s dependencies!' . PHP_EOL
	);
}
$loader = include __DIR__ . '/../../vendor/autoload.php';
$loader->addPsr4( 'WcGatewayMoneyButton\\Testing\\Integration\\', __DIR__ . '/phpunit/test-tools' );
$loader->addPsr4( 'WcGatewayMoneyButton\\', __DIR__ . '/phpunit' );

if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', __DIR__ . '/../src/main/php/includes/' );
}

if ( ! defined( 'WP_TESTS_DIR' ) ) {
	define( 'WP_TESTS_DIR', __DIR__ . '/../../vendor/wordpress/wp-develop/tests/phpunit/' );
}

if ( ! defined( 'WC_TESTS_DIR' ) ) {
	define( 'WC_TESTS_DIR', __DIR__ . '/../../vendor/woocommerce/woo-develop/tests/' );
}
putenv( 'WP_TESTS_DIR=' . WP_TESTS_DIR );

if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', dirname( __FILE__ ) . '/wp-tests-config.php' );
}

require_once WP_TESTS_DIR . 'includes/functions.php';





/**
 * Call back to add this as active plugin.
 *
 * @param array $active_plugins other active plugins
 *
 * @return array
 */
function set_active_plugins( $active_plugins ) {
	return array_unique(
		array_merge(
			[
				'woocommerce/woocommerce.php',
				//'wc-gateway-moneybutton.php',
			],
			$active_plugins ?: []
		)
	);
}

tests_add_filter( 'option_active_plugins', 'set_active_plugins' );

/**
 * Call back to load this plugin
 */
function _manually_load_plugin() {
	//$path =  __DIR__ . '/../main/php/wc-gateway-moneybutton.php';
	//require_once $path;
	///WcGatewayMoneyButton\Core\setup();
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );


require_once WC_TESTS_DIR . 'bootstrap.php';
