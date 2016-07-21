<?php

class CaseHelpersTest extends PHPUnit_Framework_TestCase {

	/**
	 * 
	 */
	public function testNormalizeCase(){
		$test1 = 'slug-case-to-normal-case';
		$test2 = 'snake_case_to_normal_case';
		$test3 = 'camelCaseToNormalCase';
		$test4 = 'CamelCaseToNormalCase';

		$this->assertEquals(normalize($test1),'Slug case to normal case');
		$this->assertEquals(normalize($test2),'Snake case to normal case');
		$this->assertEquals(normalize($test3),'Camel case to normal case');
		$this->assertEquals(normalize($test4),'Camel case to normal case');
	}

	/**
	 * 
	 */
	public function testSlugCase(){
		$test1 = '  Normal case to slug case';
		$test2 = 'Nõrmàl çĀse to   slûg çasé';

		$this->assertEquals(slugify($test1),'normal-case-to-slug-case');
		$this->assertEquals(slugify($test2),'normal-case-to-slug-case');
	}

	/**
	 * 
	 */
	public function testDasherizeCase(){
		$test = ' Normal case to dasherized case ';

		$this->assertEquals(dasherize($test,'-'),'normal-case-to-dasherized-case');
		$this->assertEquals(spinalize($test),'normal-case-to-dasherized-case');
		$this->assertEquals(trainify($test),'normal-case-to-dasherized-case');
		
		$this->assertEquals(dasherize($test,'_'),'normal_case_to_dasherized_case');
		$this->assertEquals(snakify($test),'normal_case_to_dasherized_case');
	}

}