=== MoneyButton Payment Gateway for WooCommerce ===
Contributors:      awol55
Tags:			   payment gateway, woocommerce, money button, bitcoin, bitcoinsv, bsv, crypto
Requires at least: 4.9
Tested up to:      5.2
Requires PHP:	   7.1
Stable tag:        trunk
License: 		   GPLv3
License URI: 	https://www.gnu.org/licenses/gpl-3.0.html

Accept MoneyButton payments in your WooCommerce Store.

== Description ==
 A WooCommerce Payment Gateway for accepting Money Button (https://www.moneybutton.com) payments.

Money Button is the simplest way to make crypto currency payments on the BitcoinSV blockchain. No complicated addresses, no scanning of QR codes, all a Money Button
user has to to is swipe the button to make the payment.

Money Button has support for over 100+ currencies, so even if your store is setup for Botswanan Pula, no problem.
All the currency conversions between a stores requested currency and the preferred currency of the user (as set in their Money Button profile)
is handled automatically by Money Button, and the correct payment is transacted  in Bitcoin SV (BSV) based on current market rates between the customers currency and the stores requested currency.

== Installation ==
After installation you should proceed to configure the Payment Gateway settings. These can be reached through either a) the "Gateway Settings" plugin link on the "Installed Plugins" screen, or b) WooCommerce -> Settings -> Payments.
Please note that if you attempt to enable the plugin with missing or invalid settings, it will save your settings, but the plugin will remain disabled.

= Manual Installation =

1. Upload the entire `/wc-gateway-moneybutton` directory to the `/wp-content/plugins/` directory.
2. Activate Wc Gateway Moneybutton through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I sign up for a Money Button account to receive payments =
Anyone can sign up for a Money Button account at [https://www.moneybutton.com]


== Screenshots ==
[http://github.com/brightnet-io/wc-gateway-moneybutton/docs/screenshots/screen-checkout-1.png Checkout - Payment Method]
[http://github.com/brightnet-io/wc-gateway-moneybutton/docs/screenshots/screen-checkout-2.png Checkout - Pay with Money Button]
[http://github.com/brightnet-io/wc-gateway-moneybutton/docs/screenshots/screen-checkout-3.png Checkout - Payment Made]
[http://github.com/brightnet-io/wc-gateway-moneybutton/docs/screenshots/screen-order-mb-received.png Order - Money Button Payment Received]
[http://github.com/brightnet-io/wc-gateway-moneybutton/docs/screenshots/screen-order-mb-completed.png Order - Money Button Payment Completed]


== Changelog ==

= 1.0.0 =
* Initial Release





