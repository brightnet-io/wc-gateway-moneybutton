<?php
/**
 * Utility class providing sort and filter function using version_compare
 *
 * @package WcGatewayMoneyButton\Utils
 */

namespace WcGatewayMoneyButton\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VersionSort
 *
 * @package WcGatewayMoneyButton\Utils
 */
class VersionSort {
	/**
	 * A comparator function for sorting arrays that will sort PHP-Standardized version
	 *
	 * @param string $a PHP-Standardized version.
	 * @param string $b PHP-Standardized version.
	 *
	 * @return mixed By default, version_compare returns
	 * falsey if the first version is lower than the second, or exactly equal
	 * truthy tif the first version is higher
	 */
	private static function order_by_version_asc( $a, $b ) {
		return version_compare( $a, $b, '>' );
	}


	/**
	 * Given an array with PHP-Standardized version numbers as keys will sort the array
	 * by ascending version order
	 *
	 * @param &array $array Any array that uses php standard version numbers as the key.
	 *
	 * @return bool true if successful
	 */
	public static function sort_version_keyed_array_asc( &$array ) {
		return uksort(
			$array,
			'self::order_by_version_asc'
		);
	}

	/**
	 * Given a PHP-Standardized version and an array with version numbers as the key return an array that excludes
	 * values for all versions older than the provided version param.
	 *
	 * @param string $version PHP-Standardized version.
	 * @param array  $array   an array of values keyed by version.
	 *
	 * @return array
	 */
	public static function filter_versions_older_than( $version, $array ) {
		return array_filter(
			$array,
			function ( $key ) use ( $version ) {
				return (bool) version_compare( $version, $key, '<=' );
			},
			ARRAY_FILTER_USE_KEY
		);

	}
}
