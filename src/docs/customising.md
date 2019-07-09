# Customising

## Order Notes

Most of the notes added to an order when processing a payment update from money apply a filter with the default message allowing a store to customise the notes if they find them too verbose.

Note: If you plan to use these filters only for translation please consider contributing  translations for your language to default messages for the project rather than using this approach.



| Filter                                                       | Description                                                  | Arguments                                            | Customer  can see |
| ------------------------------------------------------------ | ------------------------------------------------------------ | ---------------------------------------------------- | ----------------- |
| Note all filters below are prefixed with ```wc_gateway_moneybutton_``` |                                                              |                                                      |                   |
| payment_received_note                                        | Added to order when Money Button payment is 'RECEIVED'       | $default_note, $order_id, $payment                   | No                |
| payment_completed_note                                       | Added to order when Money Button payment is 'COMPLETED'      | $default_note, $order_id, $payment                   | Yes               |
| payment_completed_when_cancelled_note                        | Added to order when Money Button payment is 'COMPLETED' after the order has already been cancelled | $default_note, $order_id, $payment                   | No                |
| payment_completed_incorrect_note                             | Add to order when Money Button payment is 'COMPLETED' but the payment amount no longer matches the order amount | $default_note, $order_id, $payment                   | No                |
| payment_completed_when_paid_note                             | Added to order when Money Button payment is 'COMPLETED' and order is already paid or does not require payment. | $default_note, $order_id, $payment                   | No                |
| payment_completed_error_note                                 | Added to order when Money Button payment is 'COMPLETED' successfully but there was some other WooCommerce error advancing the order state automatically. | $default_note, $order_id, $payment                   | No                |
| payment_failed_note                                          | Added to order when Money Button payment is 'FAILED' after successful button swipe. | $default_note, $order_id, $payment                   | Yes               |
| payment_potential_double_pay                                 | Filter to customise the note added when a potential duplicate payment is detected. | $default_note, $order_id, $payment, $different_tx_id | No                |
| payment\_<event status>\_when\_<current status>\_note        | A note added to the order if an unexpected status change is received from Money Button. ie: a 'completed' payment update is received when the same Payment ID has already received a 'failed' update. | $default_note, $order_id, $payment                   | No                |
|                                                              |                                                              |                                                      |                   |
|                                                              |                                                              |                                                      |                   |

## Order Received Text

The text that is displayed on the 'Order Received' screen, eg: 

> Thank you. Your order has been received. It has been placed on hold pending confirmation of payment from Money Button.

Uses the standard ```woocommerce_thankyou_order_received_text``` filter with default priority to change the message. You can customize this message by adding a your own filter.  Note that as there is no specific filter per payment method (unlike the action) you should test against the payment method ('moneybutton') set on the $order argument if you want a Money Button specific method. 



## Hooks

### Actions

| Hook                                     | Description                                                  | Arguments                           |
| ---------------------------------------- | ------------------------------------------------------------ | ----------------------------------- |
| wc_gateway_moneybutton_loaded            | Fires when plugin is loaded. ie: Gateway and all internal hooks registered. |                                     |
| wc_gateway_moneybutton_payment_failed    | Fires when Money Button payment failed                       | $order, $payment_event              |
| wc_gateway_moneybutton_payment_completed | Fires when receive 'COMPLETED' payment from Money Button     | $order, $payment_event              |
| wc_gateway_moneybutton_payment_received  | Fires when receive 'RECEIVED' payment from Money Button      | $order_id, $payment_event, $payment |
| wc_gateway_moneybutton_payment_pending   | Fires when receive 'PENDING' payment from Money Button       | $order_id, $payment_event, $payment |
| wc_gateway_moneybutton_payment_incorrect | Fires IF Money Button payment is complete but the amount does not match the order total | $order, $payment                    |

### Filters

| Hook                                  | Description                                                  | Arguments    |
| ------------------------------------- | ------------------------------------------------------------ | ------------ |
| wc_gateway_moneybutton_webhook_params | Advanced Users Only: Fires when Webhook request received. Filter is applied after secret is validated and raw payment event is stored (if enabled) | $json_params |

