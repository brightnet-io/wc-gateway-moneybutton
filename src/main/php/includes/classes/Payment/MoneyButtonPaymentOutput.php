<?php
/**
 * Class MoneyButtonPaymentOutput
 *
 * @package WcGatewayMoneyButton\Payment
 */

namespace WcGatewayMoneyButton\Payment;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MoneyButtonPaymentOutput
 *
 * @package WcGatewayMoneyButton\Payment
 */
class MoneyButtonPaymentOutput {
	/**
	 * This is unique identifier of the output within payment
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $output_id
	 */
	private $output_id;

	/**
	 * The MoneyButton Payment ID
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $payment_id
	 */
	private $payment_id;

	/**
	 * Created
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $created_at
	 */
	private $created_at;
	/**
	 * Updated
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $updated_at ;
	 */
	private $updated_at;

	/**
	 *  Sent to User or an Address
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $send_to_type Either 'USER' or 'ADDRESS'
	 */

	private $send_to_type;

	/**
	 * Output target. Related to $send_to_type. Note sending to script currently is not supported by the plugin.
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $send_to_address MoneyButton UserId or BSV Address
	 */
	private $send_to_address;

	/**
	 * The currency from which the number of satoshis was calculated
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $currency
	 */
	private $currency; // This was the requested currency

	/**
	 * The amount from which the number of satoshis was calculated
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $currency
	 */
	private $amount; // This is the actual payment amount in target currency


	/**
	 * The total number of satoshis sent for this output
	 *
	 * @since    0.1.0
	 * @access   private
	 * @var      string $satoshis
	 */
	private $satoshis; // This is the actual number of satoshis paid.


	/**
	 * MoneyButtonPaymentOutput constructor.
	 *
	 * @param object $source_output output json data
	 *
	 * @since 0.1.0
	 */
	private function __construct( $source_output ) {
		// TODO handle exceptions thrown

		$this->output_id    = isset( $source_output['id'] ) ? $source_output['id'] : null;
		$this->payment_id   = isset( $source_output['paymentId'] ) ? $source_output['paymentId'] : null;
		$this->created_at   = isset( $source_output['createdAt'] ) ? $source_output['createdAt'] : null;
		$this->updated_at   = isset( $source_output['updatedAt'] ) ? $source_output['updatedAt'] : null;
		$this->send_to_type = isset( $source_output['type'] ) ? $source_output['type'] : null;
		if ( 'USER' === $this->send_to_type ) {
			$this->send_to_address = $source_output['userId'];
		} else {
			$this->send_to_address = $source_output['address'];
		}

		$this->currency = isset( $source_output['currency'] ) ? $source_output['currency'] : null;
		$this->amount   = isset( $source_output['amount'] ) ? $source_output['amount'] : null;
		$this->satoshis = isset( $source_output['satoshis'] ) ? $source_output['satoshis'] : null;

	}

	/**
	 * Utility method to construct payment data class from a json string
	 *
	 * @param object $source_output PHP Object representing output resulting from json_decode of of paymentData
	 *
	 * @since 0.1.0
	 */
	public static function from_payment_response_object( $source_output ) {

		return new self( $source_output );

	}

	/**
	 * Getter OutputId
	 *
	 * @return string
	 */
	public function get_output_id(): string {
		return $this->output_id;
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
	 * Getter SendToType
	 *
	 * @return string
	 */
	public function get_send_to_type(): string {
		return $this->send_to_type;
	}

	/**
	 * Getter SendToAddress
	 *
	 * @return string
	 */
	public function get_send_to_address(): string {
		return $this->send_to_address;
	}

	/**
	 * Getter Currency
	 *
	 * @return string
	 */
	public function get_currency(): string {
		return $this->currency;
	}

	/**
	 * Getter Amount
	 *
	 * @return string
	 */
	public function get_amount(): string {
		return $this->amount;
	}

	/**
	 * Getter Satoshis
	 *
	 * @return string
	 */
	public function get_satoshis(): ?string {
		return $this->satoshis;
	}


}
