<?php
/**
 * A base abstract class for defining custom meta boxes
 *
 * @package WcGatewayMoneyButton
 */

namespace WcGatewayMoneyButton\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractMetaBox
 *
 * @package WcGatewayMoneyButton\Admin
 */
abstract class AbstractMetaBox {
	/**
	 * Screen context where the meta box should display.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var string
	 */
	private $context;

	/**
	 * The ID of the meta box.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $id;

	/**
	 * The display priority of the meta box.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string
	 */
	private $priority;

	/**
	 * Screens where this meta box will appear.
	 *
	 * @since  0.1.0
	 * @access private
	 *
	 * @var string[]
	 */
	private $screens;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @param string   $id       meta box id
	 * @param string   $title    meta box title
	 * @param string   $context  meta box context
	 * @param string   $priority meta box priority
	 * @param string[] $screens  meta box screens
	 */
	public function __construct( string $id, string $title, string $context = 'advanced', string $priority = 'default', array $screens = array() ) {
		if ( is_string( $screens ) ) {
			$screens = (array) $screens;
		}

		$this->context  = $context;
		$this->id       = $id;
		$this->priority = $priority;
		$this->screens  = $screens;
		$this->title    = $title;
	}

	/**
	 * Get the callable that will the content of the meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return callable
	 */
	public function get_callback(): array {
		return array( $this, 'render' );
	}

	/**
	 * Get the screen context where the meta box should display.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_context(): string {
		return $this->context;
	}

	/**
	 * Get the ID of the meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the display priority of the meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_priority(): string {
		return $this->priority;
	}

	/**
	 * Get the screen(s) where the meta box will appear.
	 *
	 * @since 0.1.0
	 *
	 * @return array|string|WP_Screen
	 */
	public function get_screens() {
		return $this->screens;
	}

	/**
	 * Get the title of the meta box.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Function called to render the contents of teh meta box
	 *
	 * @since 0.1.0
	 *
	 * @param \WP_Post $post Post to render data from
	 *
	 * @return mixed
	 */
	abstract public function render( \WP_Post $post );
}
