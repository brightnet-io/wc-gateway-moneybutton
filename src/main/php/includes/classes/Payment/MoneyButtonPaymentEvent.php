<?php
/**
 * A class definition that includes attributes and function for working with moneybutton
 * payment data response.
 *
 * @package WcGatewayMoneyButton\Payment
 */

namespace WcGatewayMoneyButton\Payment;

use WcGatewayMoneyButton\Core\WcGatewayMoneyButtonLogger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MoneyButtonPaymentEvent
 *
 * @package WcGatewayMoneyButton\Payment
 *
 * @since   0.1.0
 */
class MoneyButtonPaymentEvent implements \JsonSerializable {
	/** @var WcGatewayMoneyButtonLogger */
	private $logger;
	/**
	 * The MoneyButton Payment ID
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $payment_id
	 */

	private $payment_id;

	/**
	 * Date when payment was created.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $created_at
	 */
	private $created_at;
	/**
	 * Last update for payment id
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $updated_at ;
	 */
	private $updated_at;

	/**
	 * The BSV Transaction ID;
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $transaction_id
	 */

	private $transaction_id;

	/**
	 * Normalized BSV Trransaction ID
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $normalized_txid
	 */
	private $normalized_txid;

	/**
	 * MoneyButton Payment status
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $status
	 */
	private $status;

	/**
	 * WooCommerce order_key that was initially submitted in buttonData
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $order_key
	 */
	private $order_key;

	/**
	 * WooCommerce order_id that was initially submitted in buttonData
	 *
	 * @since  0.1.0
	 * @access private
	 * @var int order_id;
	 */
	private $order_id;
	/**
	 * WooCommerce cart hash that was initially submitted in buttonData
	 *
	 * @since  0.1.0
	 * @access private
	 * @var string $cart_hash
	 */
	private $cart_hash;

	/**
	 * Outputs from moneybutton payment
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      MoneyButtonPaymentOutput[] $outputs
	 */
	private $outputs;


	/**
	 * MoneyButtonPaymentEvent constructor.
	 *
	 * @param object|array $payment_data PHP Object of JSON Data
	 */
	private function __construct( $payment_data ) {

		$this->logger          = WcGatewayMoneyButtonLogger::get_logger();
		$this->payment_id      = isset( $payment_data['id'] ) ? $payment_data['id'] : null;
		$this->created_at      = isset( $payment_data['createdAt'] ) ? $payment_data['createdAt'] : null;
		$this->updated_at      = isset( $payment_data['updatedAt'] ) ? $payment_data['updatedAt'] : null;
		$this->transaction_id  = isset( $payment_data['txid'] ) ? $payment_data['txid'] : null;
		$this->normalized_txid = isset( $payment_data['normalizedTxid'] ) ? $payment_data['normalizedTxid'] : null;
		$this->status          = isset( $payment_data['status'] ) ? $payment_data['status'] : null;

		if ( isset( $payment_data['buttonData'] ) ) { // The button data is a stringified JSON object
			$button_data = json_decode( $payment_data['buttonData'], true );
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( null === $button_data && json_last_error() !== JSON_ERROR_NONE ) {
			$this->logger->error( 'Error decoding button data string. JSON Error: ' . json_last_error_msg() );
		} else {
			$this->order_key = isset( $button_data['order_key'] ) ? $button_data['order_key'] : null;
			$this->order_id  = isset( $button_data['order_id'] ) ? (int) $button_data['order_id'] : null;
			$this->cart_hash = isset( $button_data['cart_hash'] ) ? $button_data['cart_hash'] : null;

		}
		$this->outputs = array();

		// loop through the outputs

		if ( isset( $payment_data['paymentOutputs'] ) ) {
			$outputs = $payment_data['paymentOutputs'];
			foreach ( $outputs as $source_output ) {
				array_push( $this->outputs, MoneyButtonPaymentOutput::from_payment_response_object( $source_output ) );
			}
		} else {
			WcGatewayMoneyButtonLogger::get_logger()->warning(
				sprintf(
					'Received Payment Event from Money Button with no transaction outputs. Payment ID: %s, TXID: %s',
					$this->get_payment_id(),
					$this->get_transaction_id()
				)
			);
		}

	}


	/**
	 * Constructs a MoneyButtonPaymentEvent from from JSON data posted to the webook
	 *
	 * @param array $webhook_object decoded json object
	 *
	 * @return MoneyButtonPaymentEvent
	 */
	public static function from_webhook_object(
		$webhook_object
	) {
		return new self( $webhook_object['payment'] );
	}

	/**
	 * Serialize to json
	 *
	 * @return array
	 */
	public function jsonSerialize() {
		return get_object_vars( $this );
	}

	/**
	 * To JSON
	 *
	 * @return bool|false|mixed|string|void
	 */
	public function toJSON() {
		$encoded = wp_json_encode( $this );
		if ( null === $encoded && json_last_error() !== JSON_ERROR_NONE ) {
			WcGatewayMoneyButtonLogger::get_logger()->warning( 'Error encoding payment data to json string. JSON Error: ' . json_last_error_msg() );

			return false;
		} else {
			return $encoded;
		}
	}

	/**
	 * Getter PaymentId
	 *
	 * @return string
	 */
	public function get_payment_id(): string {
		return $this->payment_id;
	}

	/**
	 * Getter CreatedAt
	 *
	 * @return string
	 */
	public function get_created_at(): string {
		return $this->created_at;
	}

	/**
	 * Getter UpdatedAt
	 *
	 * @return string
	 */
	public function get_updated_at(): string {
		return $this->updated_at;
	}

	/**
	 * Getter TransactionId
	 *
	 * @return string|null
	 */
	public function get_transaction_id(): ?string {
		return $this->transaction_id;
	}

	/**
	 * Getter NormalizedTxid
	 *
	 * @return string|null
	 */
	public function get_normalized_txid(): ?string {
		return $this->normalized_txid;
	}

	/**
	 * Getter Status
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Getter OrderKey
	 *
	 * @return string
	 */
	public function get_order_key(): string {
		return $this->order_key;
	}

	/**
	 * Getter Outputs
	 *
	 * @return MoneyButtonPaymentOutput[]
	 */
	public function get_outputs(): array {
		return $this->outputs;
	}

	/**
	 * Getter OrderId
	 *
	 * @return int | null
	 */
	public function get_order_id(): ?int {
		return $this->order_id;
	}

	/**
	 * Getter CartHash
	 *
	 * @return string|null
	 */
	public function get_cart_hash(): ?string {
		return $this->cart_hash;
	}


}
