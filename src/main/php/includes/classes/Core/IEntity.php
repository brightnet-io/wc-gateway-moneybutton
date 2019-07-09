<?php
/**
 * Interface to be implemented by custom persistent entities.
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Core
 */

namespace WcGatewayMoneyButton\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface IEntity
 *
 * @since 0.1.0
 * @package WcGatewayMoneyButton\Core
 */
interface IEntity {

	/**
	 * Getter entity id (PK)
	 *
	 * @return int
	 */
	public function get_id(): ?int;

	/**
	 * Getter, entity type name, typically class name
	 *
	 * @return string
	 */
	public function get_type_name(): string;
}
