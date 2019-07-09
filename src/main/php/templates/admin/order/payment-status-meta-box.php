<?php
/**
 * Template to render content of payment status met-box on order admin screen
 *
 * @package WcGatewayMoneyButton\Templates\Admin
 */

?>
<div class="wc-gateway-moneybutton-meta-box">
	<div class="wc-gateway-moneybutton-meta-box-row">
		<div class="wc-gateway-moneybutton-meta-box-attribute"><?php esc_html_e( 'Payment ID', 'wc-gateway-moneybutton' ); ?></div>
		<div class="wc-gateway-moneybutton-meta-box-label"><?php echo esc_html( $payment->get_payment_id() ); ?></div>
	</div>
	<div class="wc-gateway-moneybutton-meta-box-row">
		<div class="wc-gateway-moneybutton-meta-box-attribute">
			<?php
			esc_html_e( 'Status', 'wc-gateway-moneybutton' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_status_help_tip( $payment->get_status() );
			?>
		</div>
		<div class="wc-gateway-moneybutton-meta-box-label"><?php echo esc_html( $payment->get_status() ); ?></div>
	</div>

	<div class="wc-gateway-moneybutton-meta-box-row">
		<div class="wc-gateway-moneybutton-meta-box-attribute"><?php esc_html_e( 'Amount - Fiat', 'wc-gateway-moneybutton' ); ?></div>
		<div class="wc-gateway-moneybutton-meta-box-label"><?php echo esc_html( $payment->get_currency() . ' ' . $payment->get_amount() ); ?></div>
	</div>
	<div class="wc-gateway-moneybutton-meta-box-row">
		<div class="wc-gateway-moneybutton-meta-box-attribute">
			<?php
			esc_html_e( 'Amount - Satoshis', 'wc-gateway-moneybutton' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wc_help_tip( __( 'The total number of "satoshis" sent to you after currency conversion', 'wc-gateway-moneybutton' ), false );
			?>
		</div>
		<div class="wc-gateway-moneybutton-meta-box-label"><?php echo esc_html( $payment->get_satoshis() ); ?></div>
	</div>
	<div class="wc-gateway-moneybutton-meta-box-row">
		<div class="wc-gateway-moneybutton-meta-box-attribute">
			<?php
			esc_html_e( 'Last Updated', 'wc-gateway-moneybutton' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wc_help_tip( __( 'UTC time of last update from MoneyButton for this payment', 'wc-gateway-moneybutton' ), false );
			?>
		</div>
		<div class="wc-gateway-moneybutton-meta-box-label"><?php echo esc_html( $payment->get_updated_at() ); ?></div>
	</div>
</div>
