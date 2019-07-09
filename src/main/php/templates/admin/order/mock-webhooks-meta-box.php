<?php
/**
 * Template to render content of mock webhooks metabox
 *
 * @package WcGatewayMoneyButton\Templates\Admin
 */

?>
<div class="wc-gateway-moneybutton-meta-box dev-mode">
	<div class="overlay"><img src="<?php echo esc_url( get_admin_url() . 'images/wpspin_light.gif' ); ?>"/> </div>
	<div class="wc-gateway-moneybutton-meta-box-row">
		<?php
		// The output below is already escaped
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<button  class="button-primary mock-button" data-json=<?php echo $received_json; ?>>Received</button>
		<button class="button-primary mock-button" data-json=<?php echo $completed_json; ?>>Completed</button>
		<button class="button-primary mock-button" data-json=<?php echo $failed_json; ?>>Failed</button>
		<?php // phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<div class="wc-gateway-moneybutton-meta-box-row">
		<p class="error"></p>
	</div>
</div>
