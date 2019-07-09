<?php
/**
 * The Admin/Settings for the payment gateway implementation
 *
 * @package    WcGatewayMoneyButton\Gateway
 *
 * @since      0.1.0
 */

namespace WcGatewayMoneyButton\Gateway;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The gateway settings
 *
 * Settings for payment gateway implementation
 *
 * @package    WcGatewayMoneyButton\Gateway
 *
 * @since      0.1.0
 */
class PaymentGatewaySettings extends PaymentGatewayImpl {

	/**
	 * PaymentGatewaySettings constructor.
	 *
	 * Sets gateway id. Inits form fields and settings.
	 */
	public function __construct() {
		$this->id = PaymentGatewayImpl::$gateway_id; // payment gateway id
		$this->init_form_fields();
		$this->init_settings();
	}

	/**
	 * override process admin options to include display errors;
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public function process_admin_options() {
		WcGatewayMoneyButtonLogger::get_logger()->debug( get_class( $this ) . ' process_admin_options()' );
		$saved = parent::process_admin_options();
		$this->display_errors();

		return $saved;
	}


	/**
	 * Callable for 'woocommerce_settings_api_form_fields_<gateway_id>.
	 *
	 * We use this method specifically so we can keep all the 'settings' functionality seperate from the Gateway Implementation class itself.
	 *
	 * @since 0.1.0
	 *
	 * @param  array $fields fields from gateway to be filtered. In this case always empty as all fields are added in this filter target.
	 *
	 * @return array See https://docs.woocommerce.com/document/settings-api/#section-1
	 */
	public static function form_fields( $fields ) {
		WcGatewayMoneyButtonLogger::get_logger()->debug( 'PaymentGatewaySettings  form_fields()' );
		$form_fields = array(
			'enabled'              => array(
				'title'       => __( 'Enable/Disable', 'wc-gateway-moneybutton' ),
				'label'       => __( 'Enable Money Button Payments', 'wc-gateway-moneybutton' ),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),

			'title'                => array(
				'title'       => __( 'Title', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __( 'This is what is shown during checkout as the payment option.', 'wc-gateway-moneybutton' ),
				'default'     => 'Money Button',
			),
			'description'          => array(
				'title'       => __( 'Description', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __( 'This controls the descriptive text which the user sees during checkout', 'wc-gateway-moneybutton' ),
				'default'     => 'The simplest way to pay with BitcoinSV. Proceed to payment to complete your order.',
			),
			'minimum_amount'       => array(
				'title'       => __( 'Minimum Order Amount', 'wc-gateway-moneybutton' ),
				'type'        => 'price',
				'description' => __( 'The minimum total order amount required to use payment method. Leave empty to disable', 'wc-gateway-moneybutton' ),
				'default'     => '',
			),
			'moneybutton_settings' => array(
				'title'       => __( 'Money Button Settings', 'wc-gateway-moneybutton' ),
				'type'        => 'title',
				'description' => '',
			),
			'mb_to'                => array(
				'title'       => __( 'Send To', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __( 'Money Button User ID, or valid BSV Address. Hint: Your Money Button User ID is a number eg: 1234.', 'wc-gateway-moneybutton' ),
				'default'     => '',
			),
			'mb_label'             => array(
				'title'       => __( 'Button Label', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __( 'Label that appears inside your Money Button during payment/checkout', 'wc-gateway-moneybutton' ),
				'default'     => 'Pay Now',
			),
			'mb_button_id'         => array(
				'title'       => __( 'Button Id', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __(
					'The Button ID is an identifier submitted with the payment data. Or, put simply, \'where did this payment come from\'.It will be displayed prominently on incoming payments in your Money Button details to help you identify the source of payment. ',
					'wc-gateway-moneybutton'
				),
				'default'     => 'wc-gateway-moneybutton',
			),
			'mb_client_identifier' => array(
				'title'       => __( 'App Client Identifier', 'wc-gateway-moneybutton' ),
				'type'        => 'text',
				'description' => __( 'You application specific client identifier. You can find this on your Money Button profile under Apps heading.', 'wc-gateway-moneybutton' ),
				'default'     => '',
			),
			'mb_webhook_secret'    => array(
				'title'       => __( 'App WebHook Secret', 'wc-gateway-moneybutton' ),
				'type'        => 'password',
				'description' => __(
					'Data that is sent with webhook requests from MoneyButton to verify that the request came from Money Button directly. ',
					'wc-gateway-moneybutton'
				),
				'default'     => '',
			),
			'advanced_settings'    => array(
				'title'       => __( 'Advanced Settings', 'wc-gateway-moneybutton' ),
				'type'        => 'title',
				'description' => '',
			),
			'mb_dev_mode'          => array(
				'title'       => 'Dev Mode',
				'label'       => 'Enable Dev Mode',
				'type'        => 'checkbox',
				'description' => __(
					'In dev mode the Money Button will be simulated when you swipe, and it will always be successful. In addition, when logged in as an admin, additional buttons will be rendered on the order pay screen to produce different simulated results.',
					'wc-gateway-moneybutton'
				),
				'default'     => 'no',
			),

			'debug_logging'        => array(
				'title'       => 'Debug Logging',
				'label'       => 'Enable',
				'type'        => 'checkbox',
				'description' => __( 'Enable additional debug level logging. DO NOT enable this unless specifically needed.', 'wc-gateway-moneybutton' ),
				'default'     => 'no',
			),

			'store_raw_webhook'    => array(
				'title'       => 'Store Raw Webhook Event Data',
				'label'       => 'Enable',
				'type'        => 'checkbox',
				'description' => __( 'Store the raw JSON received from Money Button webhook calls in the database.', 'wc-gateway-moneybutton' ),
				'default'     => 'no',
			),

		);

		return array_merge( $form_fields, $fields );
	}

	/**
	 * Validate Text Field.
	 *
	 * Make sure the data is escaped correctly, etc.
	 *
	 * @since 0.1.0
	 *
	 * @see   https://docs.woocommerce.com/wc-apidocs/class-WC_Settings_API.html#_validate_text_field
	 *
	 * @param  string $key   Field key.
	 * @param  string $value Posted Value.
	 *
	 * @return string
	 */
	public function validate_text_field( $key, $value ) {

		$value = parent::validate_text_field( $key, $value );
		// loop through required settings, if any are message display
		if ( in_array( $key, $this->required_settings, true ) ) {
			if ( strlen( $value ) === 0 ) {
				$form_field = $this->get_form_fields()[ $key ];
				$message    = sprintf(
					/* translators: The first place holder is the name of the configuration field that is required for the payment gateway to function */
					__( '%1$s is required. Payment gateway has been disabled', 'wc-gateway-moneybutton' ),
					$form_field['title']
				);
				$this::log_validation_error( $key, $value, $message );
				$this->add_error( $message );
				$this->update_option( 'enabled', 'no' ); // disable plugin

				return false;
			}
		}

		return $value;
	}


	/**
	 * Validate Password Fields
	 *
	 * Make sure the data is escaped correctly, etc.
	 *
	 * @since 0.1.0
	 *
	 * @see   https://docs.woocommerce.com/wc-apidocs/class-WC_Settings_API.html#_validate_password_field
	 *
	 * @param  string $key   Field key.
	 * @param  string $value Posted Value.
	 *
	 * @return string
	 */
	public function validate_password_field( $key, $value ) {

		$value = parent::validate_text_field( $key, $value );
		// loop through required settings, if any are message display
		if ( in_array( $key, $this->required_settings, true ) ) {
			if ( strlen( $value ) === 0 ) {
				$form_field = $this->get_form_fields()[ $key ];
				$message    = sprintf(
					/* translators: The first place holder is the name of the configuration field that is required for the payment gateway to function */
					__( '%1$s is required. Payment gateway has been disabled', 'wc-gateway-moneybutton' ),
					$form_field['title']
				);
				$this::log_validation_error( $key, $value, $message );
				$this->add_error( $message );
				$this->update_option( 'enabled', 'no' ); // disable plugin

				return false;
			}
		}

		return $value;
	}

	/**
	 * Convenience function to log a validation error to the plugin logger.
	 *
	 * @since 0.1.0
	 *
	 * @param string $input   Setting field name
	 * @param string $value   Setting field value
	 * @param string $message Log message
	 *
	 * @return void
	 */
	private function log_validation_error( $input, $value, $message ): void {
		WcGatewayMoneyButtonLogger::get_logger()->debug( get_class( $this ) . 'Settings Validation Error for ' . $input . ' [value:' . $value . ']. Message:' . $message );
	}

	/**
	 * Add additional action links on the plugin page. Fired by admin hooks in main class
	 *
	 * @see   https://developer.wordpress.org/reference/hooks/plugin_action_links_plugin_file/
	 * @since 0.1.0
	 *
	 * @param string[] $links An array of plugin action links. By default this can include 'activate', 'deactivate', and 'delete'.
	 *
	 * @return string[]
	 */
	public function plugin_action_links( $links ) {
		WcGatewayMoneyButtonLogger::get_logger()->debug( get_class( $this ) . ' plugin_action_links()' );
		$plugin_links = array(
			'<a href="admin.php?page=wc-settings&tab=checkout&section=' . PaymentGatewayImpl::$gateway_id . '">' .
			esc_html__( 'Gateway Settings', 'wc-gateway-moneybutton' )
			. '</a>',
		);

		return array_merge( $plugin_links, $links );

	}
}
