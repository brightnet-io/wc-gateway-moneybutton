<?php
/**
 * Plugin Name: Money Button Payment Gateway for WooCommerce
 * Plugin URI: https://brightnet.io/wc-gateway-moneybutton
 * Description:
 * Version:     0.1.0
 * Author:      brightnet.io
 * Author URI:  https://brightnet.io
 * Text Domain: wc-gateway-moneybutton
 * Domain Path: /languages
 *
 * @package WcGatewayMoneyButton
 */

// Useful global constants.
define( 'WC_GATEWAY_MONEYBUTTON_VERSION', '0.1.0' );
define( 'WC_GATEWAY_MONEYBUTTON_URL', plugin_dir_url( __FILE__ ) );
define( 'WC_GATEWAY_MONEYBUTTON_PATH', plugin_dir_path( __FILE__ ) );
define( 'WC_GATEWAY_MONEYBUTTON_INC', WC_GATEWAY_MONEYBUTTON_PATH . 'includes/' );


// Include files.
require_once WC_GATEWAY_MONEYBUTTON_INC . 'functions/core.php';

// Activation/Deactivation.
register_activation_hook( __FILE__, '\WcGatewayMoneyButton\Core\activate' );
register_deactivation_hook( __FILE__, '\WcGatewayMoneyButton\Core\deactivate' );

// Setup / Init the plugin only on plugins_loaded as we have dependencies on other plugins.
add_action( 'plugins_loaded', '\WcGatewayMoneyButton\Core\setup' );



