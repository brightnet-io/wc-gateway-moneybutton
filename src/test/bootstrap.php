<?php

if (! defined('WC_GATEWAY_MONEYBUTTON_VERSION')){
	define( 'WC_GATEWAY_MONEYBUTTON_VERSION', '0.1.0' );
	define( 'WC_GATEWAY_MONEYBUTTON_URL', '' );
	define( 'WC_GATEWAY_MONEYBUTTON_PATH',__DIR__ . '../main/php/');
	define( 'WC_GATEWAY_MONEYBUTTON_INC', WC_GATEWAY_MONEYBUTTON_PATH . 'includes/' );
}


if ( ! defined( 'PROJECT' ) ) {
	define( 'PROJECT', __DIR__ . '/../../src/main/php/includes/' );
}



// Place any additional bootstrapping requirements here for PHP Unit.
if ( ! defined( 'WP_LANG_DIR' ) ) {
	define( 'WP_LANG_DIR', 'lang_dir' );
}
if ( ! defined( 'WC_GATEWAY_MONEYBUTTON_PATH' ) ) {
	define( 'WC_GATEWAY_MONEYBUTTON_PATH', 'path' );
}

if ( ! file_exists( __DIR__ . '/../../vendor/autoload.php' ) ) {
	throw new PHPUnit_Framework_Exception(
		'ERROR' . PHP_EOL . PHP_EOL .
		'You must use Composer to install the test suite\'s dependencies!' . PHP_EOL
	);
}






$loader = include __DIR__ . '/../../vendor/autoload.php';



$loader->addPsr4( 'WcGatewayMoneyButton\\Testing\\Unit\\', __DIR__ . '/phpunit/test-tools' );
$loader->addPsr4( 'WcGatewayMoneyButton\\', __DIR__ . '/phpunit' );
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
WP_Mock::tearDown();
