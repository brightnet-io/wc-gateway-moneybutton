<?php
/**
 * Template to render moneybutton on payment form
 *
 * @package WcGatewayMoneyButton\Templates
 */

?>
<div class="wc-gateway-moneybutton">
	<?php if ( true === $dev_mode ) { ?>
		<div class="dev-mode">
			<p>Gateway is in Dev Mode. You can use these buttons to simulate different results from the Money Button</p>
			<div class="callback-buttons">
				<button class="button" id="wooGatewayMoneyButtonPaymentCallbackBtn">Payment Callback</button>
				<button class="button" id="wooGatewayMoneyButtonErrorCallbackBtn">Error callback</button>
			</div>
		</div>

	<?php } ?>
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $moneybutton_element;
	?>
	<div id="wc-gateway-moneybutton-error" class="wc-gateway-moneybutton-error">
		<?php esc_html_e( 'Money Button with the following error message: ', 'wc-gateway-moneybutton' ); ?>
		<span id="wc-gateway-moneybutton-error-message"></span>
	</div>
	<form id="wc-gateway-moneybutton-paymentform" method="post" novalidate>
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		wp_nonce_field( 'wc-gateway-moneybutton-payment_' . $order_key );
		?>
	</form>

</div>
