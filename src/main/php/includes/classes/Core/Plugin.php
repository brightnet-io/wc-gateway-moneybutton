<?php
/**
 * The Main class of the Plugin
 *
 * @package    WcGatewayMoneyButton\Core
 * @since      0.1.0
 */

namespace WcGatewayMoneyButton\Core;

use WcGatewayMoneyButton\Admin\AdminNotices;
use WcGatewayMoneyButton\Admin\DBUpdater;
use WcGatewayMoneyButton\Admin\DependencyChecker;
use WcGatewayMoneyButton\Admin\Order\MockWebhookRequestsMetaBox;
use WcGatewayMoneyButton\Admin\Order\PaymentStatusMetaBox;
use WcGatewayMoneyButton\Admin\UpdateManager;
use WcGatewayMoneyButton\Gateway\PaymentForm;
use WcGatewayMoneyButton\Gateway\PaymentGatewayImpl;
use WcGatewayMoneyButton\Gateway\PaymentGatewaySettings;
use WcGatewayMoneyButton\Gateway\WebhookHandler;
use WcGatewayMoneyButton\Payment\MoneyButtonPaymentRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class. Responsible for preparing the plug primarily by hooking in plugin functions to the appropriate WordPress actions and filters
 *
 * @since    0.1.0
 * @package  WcGatewayMoneyButton\Core
 */
class Plugin {
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.1.0
	 * @access   protected
	 * @var      PluginLoader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Logger
	 *
	 * @var WcGatewayMoneyButtonLogger
	 */
	private $logger;

	/**
	 * Prepare the plugin by defining the appropriate hooks and filters.
	 *
	 * @since 0.1.0
	 */
	private function init() {

		$this->plugin_name = 'wc-gateway-moneybutton';
		$this->version     = code_version();
		$this->loader      = new PluginLoader();
		$this->logger      = WcGatewayMoneyButtonLogger::get_logger();

		$this->logger->debug( 'init()' );
		$this->i18n();
		$this->register_gateway();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		do_action( 'wc_gateway_moneybutton_loaded' );

	}

	/**
	 * Registers the plugin text domain.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function i18n() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'wc-gateway-moneybutton' );
		load_plugin_textdomain( 'wc-gateway-moneybutton', false, plugin_basename( WC_GATEWAY_MONEYBUTTON_PATH ) . '/languages/' );
	}

	/**
	 * Registers the payment gateway implementation class
	 *
	 * @since   0.1.0
	 * @access  private
	 */
	private function register_gateway() {
		add_filter(
			'woocommerce_payment_gateways',
			function ( $gateways ) {
				WcGatewayMoneyButtonLogger::get_logger()->debug( 'Register payment gateway class' );
				$gateways[] = 'WcGatewayMoneyButton\Gateway\PaymentGatewayImpl';

				return $gateways;
			}
		);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$this->logger->debug( 'define_admin_hooks()' );
		$this->loader->add_action( 'admin_init', $this, 'check_dependencies' );
		$this->loader->add_action( 'admin_init', $this, 'maybe_update' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_scripts' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_styles' );
		$this->loader->add_action( 'add_meta_boxes', $this, 'add_meta_boxes' );

		// Payment Gateway Hooks
		$gateway_settings = new PaymentGatewaySettings();
		$this->loader->add_filter( 'plugin_action_links_' . $this->plugin_name . '/wc-gateway-moneybutton.php', $gateway_settings, 'plugin_action_links' );
		$this->loader->add_action( 'woocommerce_update_options_payment_gateways_' . PaymentGatewayImpl::$gateway_id, $gateway_settings, 'process_admin_options' );
		$this->loader->add_action( 'woocommerce_settings_api_form_fields_' . PaymentGatewayImpl::$gateway_id, $gateway_settings, 'form_fields' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 */
	private function define_public_hooks() {

		// Scripts and styles
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'styles' );
		$this->loader->add_filter( 'script_loader_tag', $this, 'script_loader_tag', 10, 2 );

		// Payment Form on order-pay screen
		$this->loader->add_filter( 'woocommerce_receipt_' . PaymentGatewayImpl::$gateway_id, $this, 'include_moneybutton_payform' );
		$this->loader->add_filter( 'woocommerce_thankyou_order_received_text', $this, 'order_received_text', 10, 2 );
		$this->loader->add_filter( 'template_redirect', $this, 'maybe_intercept_form_submission' );
		$this->loader->add_filter( 'nocache_headers', $this, 'maybe_modify_order_pay_cache_headers' );

		// Webhook Handler
		$webhook_handler = new WebhookHandler();
		$this->loader->add_action( 'rest_api_init', $webhook_handler, 'register_api_route' );

		// Order status hooks+filters
		$this->loader->add_action( 'woocommerce_order_status_pending_to_cancelled', $this, 'maybe_return_order_stock', 10, 2 );
		$this->loader->add_filter( 'woocommerce_valid_order_statuses_for_payment', $this, 'valid_order_statuses_for_payment', 10, 2 );
	}

	/**
	 * Callable to render the payment form
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @param int $order_id
	 */
	public function include_moneybutton_payform( int $order_id ) {
		WcGatewayMoneyButtonLogger::get_logger()->debug( 'include_moneybutton_payform' );
		$payment_form = new PaymentForm( $order_id, PaymentHelper::get_gateway() );

		return $payment_form->render();
	}

	/**
	 * Intercept post requests to order-pay endpoint and process money button form data.
	 *
	 * @since  0.1.0
	 * @access public
	 *
	 * @throws WcGatewayMoneyButtonException If Money Button gateway is not available/registered.
	 */
	public function maybe_intercept_form_submission() {
		$this->logger->debug( 'maybe_intercept_form_submission():' );
		$endpoint = WC()->query->get_current_endpoint();
		$this->logger->debug( 'maybe_intercept_form_submission(): current endpoint -> ' . $endpoint );

		if ( 'order-pay' === $endpoint && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$this->logger->debug( 'maybe_intercept_form_submission()): is post to order pay screen' );
			if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wc-gateway-moneybutton-payment_' . $_REQUEST['key'] ) ) {
				$this->logger->info( 'Money Button Payment response form was submitted with an invalid nonce' );
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				wp_die( new \WP_Error( 403, 'Authorization Failed' ) );
			}
			$order_id     = wc_get_order_id_by_order_key( sanitize_key( $_REQUEST['key'] ) );
			$payment_form = new PaymentForm( $order_id, PaymentHelper::get_gateway() );
			$payment_form->form_submission();

		}
	}


	/**
	 * Return items to inventory.
	 *
	 * When the money button payment gateway is used, stock is reduced when the order is put into 'pending'. By default
	 * WooCommerce no longer returns order stock on pending->cancelled transition. This function is a target callable for
	 * that order transition to re-instate stock for order initially ssubmited with Money Button as the selected payment method.
	 *
	 * @since 0.1.0
	 *
	 * @param int       $order_id
	 * @param \WC_Order $order
	 */
	public function maybe_return_order_stock( $order_id, $order ) {
		$this->logger->debug( 'maybe_return_order_stock()' );
		if ( PaymentGatewayImpl::$gateway_id === $order->get_payment_method() ) {
			wc_increase_stock_levels( $order );
			$this->logger->debug( 'maybe_return_order_stock(): Order had money button payment method. Stock levels increased ' );
		}
	}

	/**
	 * Callable for customizing gateway specific order received text on thanykou screen
	 *
	 * @since 0.1.0
	 *
	 * @param string    $message The default/original order received message as provided by the template when the filter is applied.
	 * @param \WC_Order $order
	 */
	public function order_received_text( $message, $order ) {
		$this->logger->debug( 'order_received_text()' );
		if ( PaymentGatewayImpl::$gateway_id === $order->get_payment_method() ) {
			$message = $message . __( 'It has been placed on hold pending confirmation of payment from Money Button.', 'wc-gateway-moneybutton' );
		}

		return $message;

	}


	/**
	 * Modify cache headers on order-pay endpoint to include no-store.
	 *
	 * Without no-store, history.back() or browser back buttons  will still render cached pages served with nocache_headers. We do not want them to swipe the button again.
	 *
	 * @since 0.1.0
	 *
	 * @param array $headers
	 *
	 * @return mixed
	 */
	public function maybe_modify_order_pay_cache_headers( $headers ) {
		$this->logger->debug( 'maybe_modify_order_pay_cache_headers()' );
		$endpoint = WC()->query->get_current_endpoint();
		$this->logger->debug( 'maybe_modify_order_pay_cache_headers(): current endpoint -> ' . $endpoint );
		if ( 'order-pay' === $endpoint && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->logger->debug( 'maybe_modify_order_pay_cache_headers(): is GET on order pay screen, modifying headers' );
			$cache_control_header     = $headers['Cache-Control'];
			$headers['Cache-Control'] = $cache_control_header . ', no-store';
		}

		return $headers;
	}


	/**
	 * Filter hook target to determine valid order statuses for payment when this gateway is used
	 *
	 * @since 0.1.0
	 *
	 * @param array     $valid_statuses
	 * @param \WC_Order $order
	 */
	public function valid_order_statuses_for_payment( $valid_statuses, $order ) {
		$this->logger->debug( 'valid_order_statuses_for_payment(): [valid statuses arg: ' . implode( ',', $valid_statuses ) . ']' );

		if ( PaymentGatewayImpl::$gateway_id === $order->get_payment_method() ) {
			global $wpdb;
			$repo    = new MoneyButtonPaymentRepository( $wpdb );
			$payment = $repo->find_by_order_id( $order->get_id() );
			$swipe   = $order->get_meta( '_wc_gateway_moneybutton_swipe', true );
			// If the order is on-hold already, and there is no Payment data, and no button swipe meta.
			if ( 'on-hold' === $order->get_status() && ( empty( $payment ) && ! $swipe ) ) {
				$this->logger->debug(
					sprintf(
						'valid_order_statuses_for_payment(): [Order Id: %1$s] [Order Status: %2$s] [Payment Method: %3$s] is valid state for payment',
						$order->get_id(),
						$order->get_status(),
						$order->get_payment_method()
					)
				);
				$valid_statuses = array_merge( [ 'on-hold' ], $valid_statuses );
			}
		}

		return $valid_statuses;
	}


	/**
	 * Callable to check plugin environment/configuration dependencies and display admin notices
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function check_dependencies(): void {
		if ( ! is_admin() ) {
			return;
		}
		$checker = new DependencyChecker( WC_GATEWAY_MONEYBUTTON_MIN_WC_VERSION, WC_GATEWAY_MONEYBUTTON_MIN_PHP_VERSION );
		if ( ! $checker->passes_check() ) {

			$errors        = $checker->get_errors();
			$admin_notices = new AdminNotices();
			$log_warning   = 'Failed Dependency Check: %s';
			// There were errors. Add admin notices
			foreach ( $errors as $error ) {
				$admin_notices->add_notice(
					$error->getDependencyName(),
					$error->getSeverity() === 'error' ? 'notice notice-error' : 'notice notice-warning',
					$error->getLocalizedMessage(),
					! $error->isPersistent()
				);
				WcGatewayMoneyButtonLogger::get_logger()->debug( sprintf( $log_warning, $error->getMessage() ) );
			}
		}
	}

	/**
	 *  Callable to check for and perform any self managed plugin updates.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function maybe_update(): void {
		if ( ! is_admin() ) {
			return;
		}
		global $wpdb;
		$db_updater = new DBUpdater( code_version(), $wpdb );
		$updater    = new UpdateManager( $db_updater );
		$updater->maybe_update();

	}

	/**
	 * Callable to add the appropriate custom meta box to admin screens
	 *
	 * @since 0.1.0
	 *
	 * @param string $post_type WP post type
	 *
	 * @return void
	 */
	public function add_meta_boxes( $post_type ): void {

		if ( 'shop_order' === $post_type ) {
			global $post;
			$order    = wc_get_order( $post->ID );
			$meta_box = new PaymentStatusMetaBox();

			// Only display the payment meta-box if Money Button was the payment method
			if ( 'moneybutton' === $order->get_payment_method() ) {
				\WcGatewayMoneyButton\Core\add_meta_box( $meta_box );

				$gateway = PaymentHelper::get_gateway();
				if ( $gateway->is_mb_dev_mode() ) {
					$mock_meta_box = new MockWebhookRequestsMetaBox();

					\WcGatewayMoneyButton\Core\add_meta_box( $mock_meta_box );
				}
			}
		}

	}

	/**
	 * Callable to enqueue appropriate front end (storefront) script for the request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function scripts(): void {

		// TODO shared/frontend js scripts probably only need to go on order-pay as well.

		wp_enqueue_script(
			'wc_gateway_moneybutton_shared',
			script_url( 'shared', 'shared' ),
			[ 'jquery' ],
			WC_GATEWAY_MONEYBUTTON_VERSION,
			true
		);

		wp_enqueue_script(
			'wc_gateway_moneybutton_frontend',
			script_url( 'frontend', 'frontend' ),
			[ 'wc_gateway_moneybutton_shared' ],
			WC_GATEWAY_MONEYBUTTON_VERSION,
			true
		);

		// Add money button scripts on order-pay screen when gateway is enabled.

		if ( get_query_var( 'order-pay' ) ) {
			$gateway = false;
			try {
				$gateway = PaymentHelper::get_gateway();
			} catch ( WcGatewayMoneyButtonException $e ) {
				$this->logger->debug( $e->getMessage() );
			}
			if ( false !== $gateway ) {
				// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				wp_enqueue_script( 'moneybutton_js', 'https://www.moneybutton.com/moneybutton.js', [], null, true );
				if ( true === $gateway->is_mb_dev_mode() ) {
					// Add dev mode scripts when gateway dev mode is enabled.
					wp_enqueue_script(
						'wc_gateway_moneybutton_dev_mode',
						script_url( 'devmode', 'frontend' ),
						[ 'jquery', 'wc_gateway_moneybutton_shared', 'wc_gateway_moneybutton_frontend' ],
						WC_GATEWAY_MONEYBUTTON_VERSION,
						true
					);
				}
			}
		}
	}

	/**
	 * Callable to enqueue appropriate front end (storefront) styles for the request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function styles(): void {

		// TODO Front end styles probably onle need to be loaded on order-pay screen

		wp_enqueue_style(
			'wc_gateway_moneybutton_shared',
			style_url( 'shared-style', 'shared' ),
			[],
			WC_GATEWAY_MONEYBUTTON_VERSION
		);

		if ( is_admin() ) {
				wp_enqueue_style(
					'wc_gateway_moneybutton_admin',
					style_url( 'admin-style', 'admin' ),
					[],
					WC_GATEWAY_MONEYBUTTON_VERSION
				);
		} else {
			wp_enqueue_style(
				'wc_gateway_moneybutton_frontend',
				style_url( 'style', 'frontend' ),
				[],
				WC_GATEWAY_MONEYBUTTON_VERSION
			);
		}

	}

	/**
	 * Callable to enqueue appropriate front end (admin) scripts for the request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function admin_scripts(): void {

		$screen = get_current_screen();
		if ( 'shop_order' === $screen->post_type && 'post' === $screen->base ) {
			$gateway = PaymentHelper::get_gateway();

			wp_enqueue_script(
				'wc_gateway_moneybutton_shared',
				script_url( 'shared', 'shared' ),
				[ 'jquery' ],
				WC_GATEWAY_MONEYBUTTON_VERSION,
				true
			);

			wp_enqueue_script(
				'wc_gateway_moneybutton_admin',
				script_url( 'admin', 'admin' ),
				[],
				WC_GATEWAY_MONEYBUTTON_VERSION,
				true
			);

			// Add devmode scripts if dev mode
			if ( true === $gateway->is_mb_dev_mode() ) {
				// Add dev mode scripts when gateway dev mode is enabled.
				wp_enqueue_script(
					'wc_gateway_moneybutton_dev_mode',
					script_url( 'admin-devmode', 'admin' ),
					[ 'jquery', 'wc_gateway_moneybutton_shared', 'wp-api-request' ],
					WC_GATEWAY_MONEYBUTTON_VERSION,
					true
				);
			}
		}

	}

	/**
	 * Callable to enqueue appropriate front end (admin) styles for the request.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function admin_styles(): void {

		$screen = get_current_screen();
		if ( 'shop_order' === $screen->post_type && 'post' === $screen->base ) {
			wp_enqueue_style(
				'wc_gateway_moneybutton_shared',
				style_url( 'shared-style', 'shared' ),
				[],
				WC_GATEWAY_MONEYBUTTON_VERSION
			);

			wp_enqueue_style(
				'wc_gateway_moneybutton_admin',
				style_url( 'admin-style', 'admin' ),
				[],
				WC_GATEWAY_MONEYBUTTON_VERSION
			);
		}

	}


	/**
	 * Add async/defer attributes to enqueued scripts that have the specified script_execution flag.
	 *
	 * @link https://core.trac.wordpress.org/ticket/12009
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 *
	 * @return string
	 */
	public function script_loader_tag( $tag, $handle ) {
		$script_execution = wp_scripts()->get_data( $handle, 'script_execution' );

		if ( ! $script_execution ) {
			return $tag;
		}

		if ( 'async' !== $script_execution && 'defer' !== $script_execution ) {
			return $tag;
		}

		// Abort adding async/defer for scripts that have this script as a dependency. _doing_it_wrong()?
		foreach ( wp_scripts()->registered as $script ) {
			if ( in_array( $handle, $script->deps, true ) ) {
				return $tag;
			}
		}

		// Add the attribute if it hasn't already been added.
		if ( ! preg_match( ":\s$script_execution(=|>|\s):", $tag ) ) {
			$tag = preg_replace( ':(?=></script>):', " $script_execution", $tag, 1 );
		}

		return $tag;
	}

	/**
	 * Run the loader to register all of the hooks with WordPress.
	 *
	 * @since    0.1.0
	 *
	 * @return void
	 */
	public function run(): void {
		$this->logger->debug( 'run()' );
		$this->loader->run();
	}


	/**
	 * Return singleton instance of class.
	 *
	 * @since 0.1.0
	 *
	 * @return  Plugin
	 */
	public static function get_instance() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Hide clone
	 */
	private function __clone() {
	}

	/**
	 * Hide wakeup
	 */
	private function __wakeup() {
	}

	/**
	 * Private  constructor for singleton
	 */
	private function __construct() {
		$this->init();
	}
}
