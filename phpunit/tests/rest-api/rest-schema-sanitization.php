<?php
/**
 * Unit tests covering schema validation and sanitization functionality.
 *
 * @package WordPress
 * @subpackage REST API
 */

/**
 * @group restapi
 */
class WP_Test_REST_Schema_Sanitization extends WP_UnitTestCase {

	public function test_type_number() {
		$schema = array(
			'type' => 'number',
		);
		$this->assertEquals( 1, rest_sanitize_value_from_schema( 1, $schema ) );
		$this->assertEquals( 1.10, rest_sanitize_value_from_schema( '1.10', $schema ) );
		$this->assertEquals( 1, rest_sanitize_value_from_schema( '1abc', $schema ) );
		$this->assertEquals( 0, rest_sanitize_value_from_schema( 'abc', $schema ) );
		$this->assertEquals( 0, rest_sanitize_value_from_schema( array(), $schema ) );
	}

	public function test_type_integer() {
		$schema = array(
			'type' => 'integer',
		);
		$this->assertEquals( 1, rest_sanitize_value_from_schema( 1, $schema ) );
		$this->assertEquals( 1, rest_sanitize_value_from_schema( '1.10', $schema ) );
		$this->assertEquals( 1, rest_sanitize_value_from_schema( '1abc', $schema ) );
		$this->assertEquals( 0, rest_sanitize_value_from_schema( 'abc', $schema ) );
		$this->assertEquals( 0, rest_sanitize_value_from_schema( array(), $schema ) );
	}

	public function test_type_string() {
		$schema = array(
			'type' => 'string',
		);
		$this->assertEquals( 'Hello', rest_sanitize_value_from_schema( 'Hello', $schema ) );
		$this->assertEquals( '1.10', rest_sanitize_value_from_schema( '1.10', $schema ) );
		$this->assertEquals( '1.1', rest_sanitize_value_from_schema( 1.1, $schema ) );
		$this->assertEquals( '1', rest_sanitize_value_from_schema( 1, $schema ) );
	}

	public function test_type_boolean() {
		$schema = array(
			'type' => 'boolean',
		);
		$this->assertEquals( true, rest_sanitize_value_from_schema( '1', $schema ) );
		$this->assertEquals( true, rest_sanitize_value_from_schema( 'true', $schema ) );
		$this->assertEquals( true, rest_sanitize_value_from_schema( '100', $schema ) );
		$this->assertEquals( true, rest_sanitize_value_from_schema( 1, $schema ) );
		$this->assertEquals( false, rest_sanitize_value_from_schema( '0', $schema ) );
		$this->assertEquals( false, rest_sanitize_value_from_schema( 'false', $schema ) );
		$this->assertEquals( false, rest_sanitize_value_from_schema( 0, $schema ) );
	}

	public function test_format_email() {
		$schema = array(
			'type'   => 'string',
			'format' => 'email',
		);
		$this->assertEquals( 'email@example.com', rest_sanitize_value_from_schema( 'email@example.com', $schema ) );
		$this->assertEquals( 'a@b.c', rest_sanitize_value_from_schema( 'a@b.c', $schema ) );
		$this->assertEquals( 'invalid', rest_sanitize_value_from_schema( 'invalid', $schema ) );
	}

	public function test_format_ip() {
		$schema = array(
			'type'   => 'string',
			'format' => 'ip',
		);

		$this->assertEquals( '127.0.0.1', rest_sanitize_value_from_schema( '127.0.0.1', $schema ) );
		$this->assertEquals( 'hello', rest_sanitize_value_from_schema( 'hello', $schema ) );
		$this->assertEquals( '2001:DB8:0:0:8:800:200C:417A', rest_sanitize_value_from_schema( '2001:DB8:0:0:8:800:200C:417A', $schema ) );
	}

	/**
	 * @ticket 49270
	 */
	public function test_format_hex_color() {
		$schema = array(
			'type'   => 'string',
			'format' => 'hex-color',
		);
		$this->assertEquals( '#000000', rest_sanitize_value_from_schema( '#000000', $schema ) );
		$this->assertEquals( '#FFF', rest_sanitize_value_from_schema( '#FFF', $schema ) );
		$this->assertEquals( '', rest_sanitize_value_from_schema( 'WordPress', $schema ) );
	}

	/**
	 * @ticket 50053
	 */
	public function test_format_uuid() {
		$schema = array(
			'type'   => 'string',
			'format' => 'uuid',
		);
		$this->assertEquals( '44', rest_sanitize_value_from_schema( 44, $schema ) );
		$this->assertEquals( 'hello', rest_sanitize_value_from_schema( 'hello', $schema ) );
		$this->assertEquals(
			'123e4567-e89b-12d3-a456-426655440000',
			rest_sanitize_value_from_schema( '123e4567-e89b-12d3-a456-426655440000', $schema )
		);
	}

	public function test_type_array() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'number',
			),
		);
		$this->assertEquals( array( 1 ), rest_sanitize_value_from_schema( array( 1 ), $schema ) );
		$this->assertEquals( array( 1 ), rest_sanitize_value_from_schema( array( '1' ), $schema ) );
	}

	public function test_type_array_nested() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type'  => 'array',
				'items' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertEquals( array( array( 1 ), array( 2 ) ), rest_sanitize_value_from_schema( array( array( 1 ), array( 2 ) ), $schema ) );
		$this->assertEquals( array( array( 1 ), array( 2 ) ), rest_sanitize_value_from_schema( array( array( '1' ), array( '2' ) ), $schema ) );
	}

	public function test_type_array_as_csv() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'number',
			),
		);
		$this->assertEquals( array( 1, 2 ), rest_sanitize_value_from_schema( '1,2', $schema ) );
		$this->assertEquals( array( 1, 2, 0 ), rest_sanitize_value_from_schema( '1,2,a', $schema ) );
		$this->assertEquals( array( 1, 2 ), rest_sanitize_value_from_schema( '1,2,', $schema ) );
	}

	public function test_type_array_with_enum() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'enum' => array( 'chicken', 'ribs', 'brisket' ),
				'type' => 'string',
			),
		);
		$this->assertEquals( array( 'ribs', 'brisket' ), rest_sanitize_value_from_schema( array( 'ribs', 'brisket' ), $schema ) );
		$this->assertEquals( array( 'coleslaw' ), rest_sanitize_value_from_schema( array( 'coleslaw' ), $schema ) );
	}

	public function test_type_array_with_enum_as_csv() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'enum' => array( 'chicken', 'ribs', 'brisket' ),
				'type' => 'string',
			),
		);
		$this->assertEquals( array( 'ribs', 'chicken' ), rest_sanitize_value_from_schema( 'ribs,chicken', $schema ) );
		$this->assertEquals( array( 'chicken', 'coleslaw' ), rest_sanitize_value_from_schema( 'chicken,coleslaw', $schema ) );
		$this->assertEquals( array( 'chicken', 'coleslaw' ), rest_sanitize_value_from_schema( 'chicken,coleslaw,', $schema ) );
	}

	public function test_type_array_is_associative() {
		$schema = array(
			'type'  => 'array',
			'items' => array(
				'type' => 'string',
			),
		);
		$this->assertEquals(
			array( '1', '2' ),
			rest_sanitize_value_from_schema(
				array(
					'first'  => '1',
					'second' => '2',
				),
				$schema
			)
		);
	}

	public function test_type_object() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertEquals( array( 'a' => 1 ), rest_sanitize_value_from_schema( array( 'a' => 1 ), $schema ) );
		$this->assertEquals( array( 'a' => 1 ), rest_sanitize_value_from_schema( array( 'a' => '1' ), $schema ) );
		$this->assertEquals(
			array(
				'a' => 1,
				'b' => 1,
			),
			rest_sanitize_value_from_schema(
				array(
					'a' => '1',
					'b' => 1,
				),
				$schema
			)
		);
	}

	public function test_type_object_strips_additional_properties() {
		$schema = array(
			'type'                 => 'object',
			'properties'           => array(
				'a' => array(
					'type' => 'number',
				),
			),
			'additionalProperties' => false,
		);
		$this->assertEquals( array( 'a' => 1 ), rest_sanitize_value_from_schema( array( 'a' => 1 ), $schema ) );
		$this->assertEquals( array( 'a' => 1 ), rest_sanitize_value_from_schema( array( 'a' => '1' ), $schema ) );
		$this->assertEquals(
			array( 'a' => 1 ),
			rest_sanitize_value_from_schema(
				array(
					'a' => '1',
					'b' => 1,
				),
				$schema
			)
		);
	}

	public function test_type_object_nested() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type'       => 'object',
					'properties' => array(
						'b' => array( 'type' => 'number' ),
						'c' => array( 'type' => 'number' ),
					),
				),
			),
		);

		$this->assertEquals(
			array(
				'a' => array(
					'b' => 1,
					'c' => 3,
				),
			),
			rest_sanitize_value_from_schema(
				array(
					'a' => array(
						'b' => '1',
						'c' => '3',
					),
				),
				$schema
			)
		);
		$this->assertEquals(
			array(
				'a' => array(
					'b' => 1,
					'c' => 3,
					'd' => '1',
				),
				'b' => 1,
			),
			rest_sanitize_value_from_schema(
				array(
					'a' => array(
						'b' => '1',
						'c' => '3',
						'd' => '1',
					),
					'b' => 1,
				),
				$schema
			)
		);
		$this->assertEquals( array( 'a' => array() ), rest_sanitize_value_from_schema( array( 'a' => null ), $schema ) );
	}

	public function test_type_object_stdclass() {
		$schema = array(
			'type'       => 'object',
			'properties' => array(
				'a' => array(
					'type' => 'number',
				),
			),
		);
		$this->assertEquals( array( 'a' => 1 ), rest_sanitize_value_from_schema( (object) array( 'a' => '1' ), $schema ) );
	}

	/**
	 * @ticket 42961
	 */
	public function test_type_object_accepts_empty_string() {
		$this->assertEquals( array(), rest_sanitize_value_from_schema( '', array( 'type' => 'object' ) ) );
	}

	public function test_type_unknown() {
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array(
			'type' => 'lalala',
		);
		$this->assertEquals( 'Best lyrics', rest_sanitize_value_from_schema( 'Best lyrics', $schema ) );
		$this->assertEquals( 1.10, rest_sanitize_value_from_schema( 1.10, $schema ) );
		$this->assertEquals( 1, rest_sanitize_value_from_schema( 1, $schema ) );
	}

	public function test_no_type() {
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array(
			'type' => null,
		);
		$this->assertEquals( 'Nothing', rest_sanitize_value_from_schema( 'Nothing', $schema ) );
		$this->assertEquals( 1.10, rest_sanitize_value_from_schema( 1.10, $schema ) );
		$this->assertEquals( 1, rest_sanitize_value_from_schema( 1, $schema ) );
	}

	public function test_nullable_date() {
		$schema = array(
			'type'   => array( 'string', 'null' ),
			'format' => 'date-time',
		);

		$this->assertNull( rest_sanitize_value_from_schema( null, $schema ) );
		$this->assertEquals( '2019-09-19T18:00:00', rest_sanitize_value_from_schema( '2019-09-19T18:00:00', $schema ) );
		$this->assertEquals( 'lalala', rest_sanitize_value_from_schema( 'lalala', $schema ) );
	}

	/**
	 * @ticket 50189
	 */
	public function test_format_validation_is_skipped_if_non_string_type() {
		$schema = array(
			'type'   => 'array',
			'format' => 'hex-color',
		);
		$this->assertEquals( array( '#fff' ), rest_sanitize_value_from_schema( '#fff', $schema ) );
		$this->assertEquals( array( '#qrst' ), rest_sanitize_value_from_schema( '#qrst', $schema ) );
	}

	/**
	 * @ticket 50189
	 */
	public function test_format_validation_is_applied_if_missing_type() {
		$this->expectException( 'PHPUnit_Framework_Error_Notice' ); // For the undefined index.
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array( 'format' => 'hex-color' );
		$this->assertEquals( '#abc', rest_sanitize_value_from_schema( '#abc', $schema ) );
		$this->assertEquals( '', rest_sanitize_value_from_schema( '#jkl', $schema ) );
	}

	/**
	 * @ticket 50189
	 */
	public function test_format_validation_is_applied_if_unknown_type() {
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array(
			'format' => 'hex-color',
			'type'   => 'str',
		);
		$this->assertEquals( '#abc', rest_sanitize_value_from_schema( '#abc', $schema ) );
		$this->assertEquals( '', rest_sanitize_value_from_schema( '#jkl', $schema ) );
	}

	public function test_object_or_string() {
		$schema = array(
			'type'       => array( 'object', 'string' ),
			'properties' => array(
				'raw' => array(
					'type' => 'string',
				),
			),
		);

		$this->assertEquals( 'My Value', rest_sanitize_value_from_schema( 'My Value', $schema ) );
		$this->assertEquals( array( 'raw' => 'My Value' ), rest_sanitize_value_from_schema( array( 'raw' => 'My Value' ), $schema ) );
		$this->assertEquals( array( 'raw' => '1' ), rest_sanitize_value_from_schema( array( 'raw' => 1 ), $schema ) );
	}

	public function test_object_or_bool() {
		$schema = array(
			'type'       => array( 'object', 'boolean' ),
			'properties' => array(
				'raw' => array(
					'type' => 'boolean',
				),
			),
		);

		$this->assertTrue( rest_sanitize_value_from_schema( true, $schema ) );
		$this->assertTrue( rest_sanitize_value_from_schema( '1', $schema ) );
		$this->assertTrue( rest_sanitize_value_from_schema( 1, $schema ) );

		$this->assertFalse( rest_sanitize_value_from_schema( false, $schema ) );
		$this->assertFalse( rest_sanitize_value_from_schema( '0', $schema ) );
		$this->assertFalse( rest_sanitize_value_from_schema( 0, $schema ) );

		$this->assertEquals( array( 'raw' => true ), rest_sanitize_value_from_schema( array( 'raw' => true ), $schema ) );
		$this->assertEquals( array( 'raw' => true ), rest_sanitize_value_from_schema( array( 'raw' => '1' ), $schema ) );
		$this->assertEquals( array( 'raw' => true ), rest_sanitize_value_from_schema( array( 'raw' => 1 ), $schema ) );

		$this->assertEquals( array( 'raw' => false ), rest_sanitize_value_from_schema( array( 'raw' => false ), $schema ) );
		$this->assertEquals( array( 'raw' => false ), rest_sanitize_value_from_schema( array( 'raw' => '0' ), $schema ) );
		$this->assertEquals( array( 'raw' => false ), rest_sanitize_value_from_schema( array( 'raw' => 0 ), $schema ) );

		$this->assertEquals( array( 'raw' => true ), rest_sanitize_value_from_schema( array( 'raw' => 'something non boolean' ), $schema ) );
	}

	/**
	 * @ticket 50300
	 */
	public function test_multi_type_with_no_known_types() {
		$this->setExpectedIncorrectUsage( 'rest_handle_multi_type_schema' );
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array(
			'type' => array( 'invalid', 'type' ),
		);

		$this->assertEquals( 'My Value', rest_sanitize_value_from_schema( 'My Value', $schema ) );
	}

	/**
	 * @ticket 50300
	 */
	public function test_multi_type_with_some_unknown_types() {
		$this->setExpectedIncorrectUsage( 'rest_handle_multi_type_schema' );
		$this->setExpectedIncorrectUsage( 'rest_sanitize_value_from_schema' );

		$schema = array(
			'type' => array( 'object', 'type' ),
		);

		$this->assertEquals( 'My Value', rest_sanitize_value_from_schema( 'My Value', $schema ) );
	}

	/**
	 * @ticket 50300
	 */
	public function test_multi_type_returns_null_if_no_valid_type() {
		$schema = array(
			'type' => array( 'number', 'string' ),
		);

		$this->assertNull( rest_sanitize_value_from_schema( array( 'Hello!' ), $schema ) );
	}

	/**
	 * @ticket 48821
	 */
	public function test_unique_items_after_sanitization() {
		$schema = array(
			'type'        => 'array',
			'uniqueItems' => true,
			'items'       => array(
				'type'   => 'string',
				'format' => 'uri',
			),
		);

		$data = array(
			'https://example.org/hello%20world',
			'https://example.org/hello world',
		);

		$this->assertTrue( rest_validate_value_from_schema( $data, $schema ) );
		$this->assertWPError( rest_sanitize_value_from_schema( $data, $schema ) );
	}
}
