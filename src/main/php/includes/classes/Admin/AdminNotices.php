<?php
/**
 *  Responsible for adding/showing and hiding the plugins various admin notices.
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Admin
 */

namespace WcGatewayMoneyButton\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AdminNotices
 *
 * @package WcGatewayMoneyButton\Admin
 * @since   0.1.0
 */
class AdminNotices {
	/**
	 * The collected admin notices.
	 *
	 * @since 0.1.0
	 * @access private
	 *
	 * @var array
	 */
	private $notices = array();

	/**
	 * AdminNotices constructor.
	 *
	 * Add methods to WordPress hooks for showing and hiding notices.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'admin_init', array( $this, 'hide_notice' ), 11 );
	}

	/**
	 * Add a notice to be displayed on WordPress admin screens.
	 *
	 * @since 0.1.0
	 *
	 * @param string $slug        A short, unique identifier for this specific notice
	 * @param string $class       CSS class or classes to be added to the admin notice when displayed
	 * @param string $message     Localized message
	 * @param bool   $dismissible true for a dismissible message
	 */
	public function add_notice( string $slug, string $class, string $message, bool $dismissible = false ): void {
		$this->notices[ $slug ] = array(
			'class'       => $class,
			'message'     => $message,
			'dismissible' => $dismissible,
		);
	}

	/**
	 * To be called by the 'admin_notices' hook to display the collected admin notices.
	 *
	 * @since 0.1.0
	 */
	public function show_notices(): void {

		foreach ( (array) $this->notices as $notice_key => $notice ) {
			$show_notice = get_option( 'wc_gateway_moneybutton_show_notice_' . $notice_key );
			if ( empty( $show_notice ) ) {
				echo '<div class="' . esc_attr( $notice['class'] ) . '" style="position:relative;">';

				if ( $notice['dismissible'] ) { ?><a href="
					<?php
					echo esc_url(
						wp_nonce_url(
							add_query_arg( 'wc-gateway-moneybutton-hide-notice', $notice_key ),
							'wc_gateway_moneybutton_hide_notices_nonce',
							'_wc_gateway_moneybutton_notice_nonce'
						)
					);
					?>
					" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0 9px 9px ;text-decoration:none;"></a>
					<?php
				}

				echo '<p>';
				echo wp_kses( $notice['message'], array( 'a' => array( 'href' => array() ) ) );
				echo '</p></div>';

			}
		}
	}

	/**
	 * Instance has any collected notices.
	 *
	 * @since 0.1.0
	 *
	 * @return bool true if has collected notices
	 */
	public function has_notices(): bool {
		return ! empty( $this->notices );
	}

	/**
	 * Hides a admin notice.
	 *
	 * Check the request for hide notices targeted at this plugin and update options to set show notice to no.
	 *
	 * @since 0.1.0
	 */
	public function hide_notice(): void {

		if ( isset( $_GET['wc-gateway-moneybutton-hide-notice'] ) && isset( $_GET['_wc_gateway_moneybutton_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_wc_gateway_moneybutton_notice_nonce'], 'wc_gateway_moneybutton_hide_notices_nonce' ) ) {
				wp_die( esc_html( 'Action failed. Please refresh the page and retry.', 'wc-gateway-moneybutton' ) );
			}

			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_die();

			}

			$notice = wc_clean( $_GET['wc-gateway-moneybutton-hide-notice'] );

			update_option( 'wc_gateway_moneybutton_show_notice_' . $notice, 'no' );

		}
	}
}
