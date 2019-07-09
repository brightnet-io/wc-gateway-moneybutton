<?php

namespace WcGatewayMoneyButton\Testing\Unit;



class StdOutLogger {
	public function __construct() {
		require_once __DIR__ . '/../../../../vendor/woocommerce/woo-develop/includes/class-wc-log-levels.php';
	}

	public function log( $level, $message, $context = array() ) {
		fwrite(STDOUT,sprintf('%1$s %2$s' . PHP_EOL,strtoupper($level),$message));
	}
}
