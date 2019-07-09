<?php

namespace WcGatewayMoneyButton\Utils;


/**
 * Class VersionSortTests
 * @package WcGatewayMoneyButton\Utils
 */
class VersionSortTests extends \PHPUnit\Framework\TestCase  {

	/**
	 * It should correctly sort an array of values that use versions as the key.
	 */
	public function test_sort_array_by_version_key_asc() {
		$test_array                 = [];
		$test_array['1.0.0']        = 'value';
		$test_array['1.0.0-dev.1']  = 'value';
		$test_array['3.0.1']        = 'value';
		$test_array['2.0.0-beta.2'] = 'value';

		$expect_key_array = array(
			'1.0.0-dev.1',
			'1.0.0',
			'2.0.0-beta.2',
			'3.0.1',
		);


		$result = VersionSort::sort_version_keyed_array_asc( $test_array );
		$this->assertTrue( $result, 'Sort function failed' );
		$keys = array_keys( $test_array );
		$this->assertEquals( $expect_key_array, $keys );
	}


	/**
	 * It should correctly filter out versions older than
	 */
	public function test_filter_versions_older_than() {
		$test_array                 = [];
		$test_array['1.0.0-dev.1']  = 'value';
		$test_array['1.0.0']        = 'value';
		$test_array['2.0.0-beta.2'] = 'value';
		$test_array['3.0.1']        = 'value';

		$expect_key_array = array(
			'2.0.0-beta.2',
			'3.0.1',
		);

		$filtered_array = VersionSort::filter_versions_older_than( '2.0.0-beta.2', $test_array );
		$keys           = array_keys( $filtered_array );
		$this->assertEquals( $expect_key_array, $keys );

	}
}