<?php

class CollectionTest extends PHPUnit_Framework_TestCase{

	private $collection = [
		'foo' => 'bar',
		'123' => 456,
		'level1' => [
			'level2' => [
				'level3' => 'value'
			]
		]
	];

	/**
	 * 
	 */
	public function testGet(){
		$collection = new Collection($this->collection);

		$this->assertEquals($collection->get('foo'),'bar');
		$this->assertEquals($collection->get('level1','level2','level3'),'value');
		$this->assertEquals($collection->get('path','not','exists'), null);
	}

	/**
	 *
	 */
	public function testSet(){
		$collection = new Collection($this->collection);

		$collection->set('key1','key2','test');
		$this->assertEquals($collection->get('key1','key2'),'test');
		
		$this->setExpectedException(\InvalidArgumentException::class);
		$collection->set('reset');
	}
	
	/**
	 * 
	 */
	public function testDelete(){

		$collection = new Collection($this->collection);
		$collection->delete('foo');

		$this->assertEquals($collection->get(),[
			'123' => 456,
			'level1' => [
				'level2' => [
					'level3' => 'value'
				]
			]
		]);

		$collection->delete('level1','level2');
		$this->assertEquals($collection->get(),[
			'123' => 456,
			'level1' => []
		]);

		$collection->delete();
		$this->assertEquals($collection->get(),[]);
	}

	/**
	 *
	 */
	public function testHas(){
		$collection = new Collection($this->collection);

		$this->assertEquals($collection->has('level1','level2'),true);
		$this->assertEquals($collection->has('do','not','exists'),false);

		$this->setExpectedException(\InvalidArgumentException::class);
		$this->assertEquals($collection->has(),false);
	}

	/**
	 *
	 */
	public function testAdd(){
		$collection = new Collection($this->collection);

		$collection->add(['another' => 'array']);
		$this->assertEquals($collection->get('another'),'array');

		$this->assertEquals($collection->get(),[
		'foo' => 'bar',
		'123' => 456,
		'level1' => [
			'level2' => [
				'level3' => 'value'
			]
		],
		'another' => 'array'
		]);

		$collection2 = clone $collection;

		//test simple add
		$collection->add([
			'level1' => [
				'level2' => 'foo',
				'level22'=> 'bar'
			]
		]);
		$this->assertEquals($collection->get(),[
			'foo' => 'bar',
			'123' => 456,
			'level1' => [
				'level2' => 'foo',
				'level22'=> 'bar'
			],
			'another' => 'array'
		]);

		//test recursive add
		$collection2->add([
			'level1' => [
				'level2' => ['level3' => 'newvalue'],
				'level22'=> 'bar'
			]
		],true);

		$this->assertEquals($collection2->get(),[
			'foo' => 'bar',
			'123' => 456,
			'level1' => [
				'level2' => [
					'level3' => 'newvalue'
				],
				'level22'=> 'bar'
			],
			'another' => 'array'
		]);
	}

	/**
	 * 
	 */
	public function testCount(){
		$collection = new Collection($this->collection);

		$this->assertEquals($collection->count(),3);
		$this->assertEquals($collection->count('level1','level2'),1);

		//invalid count
		$this->setExpectedException(\DomainException::class);
		$collection->count('invalid','key','path');
	}


}