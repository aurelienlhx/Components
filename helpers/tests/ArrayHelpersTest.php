<?php

class ArrayHelpersTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test a copy of an array
	 */
	public function testArrayCopy(){

			$class = new stdClass();
			$class->name = 'Empty class';
			
			$array = [
				'foo' => 'bar',
				'faa' => $class,
				'fii' => [
					'beer' => 'zoo'
				]
			];
			$this->assertNotSame(array_copy($array),$array);
	}

	/**
	 * Test key numerization of an array
	 */
	public function testArrayNumerize(){
		$array = [
			'fii' => 3,
			'faa' => 2,
			'foo' => 1
		];

		$this->assertEquals(array_numerize($array),[
			0 => 3,
			1 => 2,
			2 => 1
 		]);
	}

	/**
	 * Test recursively if an array is empty
	 */
	public function testArrayEmpty(){
		$array1 = [
			'fii' => 3,
			'faa' => 2,
			'foo' => 1
		];
		$array2 = [];
		$array3 = [
			'fee' => false,
			'foo' => [
				'faa' => [
					'fii' => 0
				]
			],
			'fuu' => 0
		];
		$array4 = [
			'fee' => false,
			'foo' => [
				'faa' => [
					'fii' => 1
				]
			],
			'fuu' => 0
		];
		$this->assertEquals(array_empty($array1),false);
		$this->assertEquals(array_empty($array2),true);
		$this->assertEquals(array_empty($array3),true);
		$this->assertEquals(array_empty($array4),false);
	}

	/**
	 * Add an item in first position of an array with a specific key
	 */
	public function testArrayUnshiftAssoc(){
		$array = [
			'foo' => 1,
			0 => 'bar',
			'_zoo' => []
		];

		$this->assertEquals(array_unshift_assoc($array, 'unshifted_key','unshifted_value'),[
			'unshifted_key' => 'unshifted_value',
			'foo' => 1,
			0 => 'bar',
			'_zoo' => []
		]);
	}

	/**
	 * 
	 */
	public function testArrayUnsetNumericsKeys(){
		$array = [
			'foo' => 123,
			'0' => 456,
			999 => 567
		];

		$this->assertEquals(array_unset_numerics_keys($array),[
			'foo' => 123
		]);
	}

	/**
	 * Join an keys and values of an array
	 */
	public function testArrayJoinAssoc(){
		$array = [
			'foo' => 'bar',
			'faa' => 'bor'
		];
		$this->assertEquals(array_join_assoc($array,'=','&'),'foo=bar&faa=bor');
		$this->assertEquals(array_join_assoc($array,':',','),'foo:bar,faa:bor');
	}

	/**
	 * Test if an array is associative
	 */
	public function testArrayIsAssoc(){
		$array1 = [
			'foo' => 'bar',
			'faa' => 'bor'
		];
		$array2 = [
			0 => 'bar',
			1 => 'bor'
		];
		$array3 = [
			'foo' => 'bar',
			999 => 'bor'
		];

		$this->assertEquals(array_is_assoc($array1), true);
		$this->assertEquals(array_is_assoc($array2), false);
		$this->assertEquals(array_is_assoc($array3), true);
	}

	/**
	 * Test the depth of an array
	 */
	public function testArrayDepth(){
		$array1 = [
			'fii' => 3,
			'faa' => 2,
			'foo' => 1
		];
		$array2 = [];
		$array3 = [
			'fee' => false,
			'foo' => [
				'faa' => [
					'fii' => 0
				]
			],
			'fuu' => 0
		];
		$array4 = [
			'foo' => ['faa' => ['fii' => ['fuu' => 0]]],	
		];

		$this->assertEquals(array_depth($array1),0);
		$this->assertEquals(array_depth($array2),0);
		$this->assertEquals(array_depth($array3),2);
		$this->assertEquals(array_depth($array4),3);
	}

	/**
	 * 
	 */
	public function testArrayFilterKeys(){
		$array = [
			'fii' => 3,
			'faa' => 2,
			'foo' => 1
		];
		$this->assertEquals( array_filter_keys($array,['faa']) , ['faa' => 2] );
	}

	/**
	 * 
	 */
	public function testArrayJoinArray(){
		$keys = ['key1','key2','key3'];
		$values = ['value1','value2','value3'];

		$this->assertEquals(array_join_array($keys,$values,'=','&'), 'key1=value1&key2=value2&key3=value3');

		$keys = ['key1','key2','key3'];
		$values = ['value1'];

		$this->assertEquals(array_join_array($keys,$values,'=','&'), 'key1=value1&key2=&key3=');
	}

	/**
	 * 
	 */
	public function testArrayFlat(){
		$arr = [
			'foo' => 'bar',
			'123' => 456,
			'level1' => [
				'level20' => 'test',
				'level21' => [
					'level3' => 'value'
				]
			],
			456 => 'test'
		];

		$flatten = array_flatten($arr);

		$this->assertEquals($flatten,[
			'foo'=>'bar',
			'123'=>456,
			'level1.level20' => 'test',
			'level1.level21.level3' => 'value',
			456 => 'test'
		]);

		$inflate = array_inflate($flatten);

		$this->assertEquals($inflate,$arr);

	}
}