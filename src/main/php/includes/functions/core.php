<?php
/**
 * Core plugin functionality.
 *
 * @package WcGatewayMoneyButton
 */

namespace WcGatewayMoneyButton\Core;

use WcGatewayMoneyButton\Admin\AbstractMetaBox;
use WP_Error as WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activate the plugin
 *
 * @return void
 */
function activate() {

}

/**
 * Deactivate the plugin
 *
 * Uninstall routines should be in uninstall.php
 *
 * @return void
 */
function deactivate() {

}


/**
 * Plugin load target
 */
function setup() {

	// Define constants for target environment
	if ( ! defined( 'WC_GATEWAY_MONEYBUTTON_MINIMUM_WP_VERSION' ) ) {
		define( 'WC_GATEWAY_MONEYBUTTON_MINIMUM_WP_VERSION', '5.2' );
	}
	if ( ! defined( 'WC_GATEWAY_MONEYBUTTON_MIN_PHP_VERSION' ) ) {
		define( 'WC_GATEWAY_MONEYBUTTON_MIN_PHP_VERSION', '7.1.29' );
	}

	if ( ! defined( 'WC_GATEWAY_MONEYBUTTON_MIN_WC_VERSION' ) ) {
		define( 'WC_GATEWAY_MONEYBUTTON_MIN_WC_VERSION', '3.6.1' );
	}

	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	// WooCommerce is a hard dependency. Plugin cannot load without it
	// The below check for woocommerce install is preferred as all other methods, such as checking for WooCommerce class make unit testing more complicated.
	$woocommerce_active = in_array(
		'woocommerce/woocommerce.php',
		apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
		true
	);
	if ( false === $woocommerce_active ) {
		add_action( 'admin_notices', $n( 'missing_wc_notice' ) );

		return;
	}

	// Require Composer autoloader if it exists, else try spl_autoload_register
	if ( file_exists( WC_GATEWAY_MONEYBUTTON_PATH . '/vendor/autoload.php' ) ) {
		require_once WC_GATEWAY_MONEYBUTTON_PATH . 'vendor/autoload.php';
	} else {
		spl_autoload_register( '\WcGatewayMoneyButton\Core\wc_gateway_moneybutton_namespace_autoload' );
	}

	// "Run" the plugin instance
	Plugin::get_instance()->run();

}


/**
 * The list of knows contexts for enqueuing scripts/styles.
 *
 * @return array
 */
function get_enqueue_contexts() {
	return [ 'admin', 'frontend', 'shared' ];
}

/**
 * Generate an URL to a script, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $script  Script file name (no .js extension)
 * @param string $context Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string|WP_Error URL
 */
function script_url( $script, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WcGatewayMoneyButton script loader.' );
	}

	return WC_GATEWAY_MONEYBUTTON_URL . "dist/js/${script}.js";

}

/**
 * Generate an URL to a stylesheet, taking into account whether SCRIPT_DEBUG is enabled.
 *
 * @param string $stylesheet Stylesheet file name (no .css extension)
 * @param string $context    Context for the script ('admin', 'frontend', or 'shared')
 *
 * @return string URL
 */
function style_url( $stylesheet, $context ) {

	if ( ! in_array( $context, get_enqueue_contexts(), true ) ) {
		return new WP_Error( 'invalid_enqueue_context', 'Invalid $context specified in WcGatewayMoneyButton stylesheet loader.' );
	}

	return WC_GATEWAY_MONEYBUTTON_URL . "dist/css/${stylesheet}.css";

}

/**
 * Convenience method to return the current code version of the plugin.
 *
 * @return string code version of plugin as defined by constant WC_GATEWAY_MONEYBUTTON_VERSION
 */
function code_version() {
	return WC_GATEWAY_MONEYBUTTON_VERSION;
}

/**
 * Callback for spl_autoload_register to autoload classes in the WcGatewayMoneyButton names space from includes/classes dir.
 *
 * @param string $class_name class
 */
function wc_gateway_moneybutton_namespace_autoload( $class_name ) {
	$namespace_prefix     = 'WcGatewayMoneyButton\\';
	$namespace_prefix_len = strlen( $namespace_prefix );
	$namespace_base_dir   = __DIR__ . '/../classes/';
	// Does class use prefix
	if ( strncmp( $namespace_prefix, $class_name, $namespace_prefix_len ) === 0 ) {
		$class_name_in_namespace = substr( $class_name, $namespace_prefix_len );
		$file                    = $namespace_base_dir . str_replace( '\\', '/', $class_name_in_namespace ) . '.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

/**
 * WooCommerce fallback notice.
 *
 * @since 1.0.0
 */
function missing_wc_notice() {

	echo '<div class="error"><p><strong>' . sprintf(
		/* translators: the placeholder is a clickable URL that links to the woocommerce.com home page . */
			esc_html__(
				'MoneyButton Gateway for WooCommerce  requires WooCommerce to be installed and active. You can download %s here.',
				'wc-gateway-moneybutton'
			),
		'<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>'
	) . '</strong></p></div>';
}

/**
 * Add a custom meta-box that extends AbstractMetaBox
 *
 * @since 0.1.0
 *
 * @param AbstractMetaBox $meta_box
 */
function add_meta_box( AbstractMetaBox $meta_box ) {
	\add_meta_box(
		$meta_box->get_id(),
		$meta_box->get_title(),
		$meta_box->get_callback(),
		$meta_box->get_screens(),
		$meta_box->get_context(),
		$meta_box->get_priority()
	);
}
