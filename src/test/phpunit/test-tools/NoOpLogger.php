<?php

namespace WcGatewayMoneyButton\Testing\Unit;



class NoOpLogger {
	public function __construct() {
		require_once __DIR__ . '/../../../../vendor/woocommerce/woo-develop/includes/class-wc-log-levels.php';
	}

	public function log( $level, $message, $context = array() ) {
		return;
	}
}