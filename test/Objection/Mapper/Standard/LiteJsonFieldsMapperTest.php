<?php
namespace Objection\Mapper\Mappers;


use Objection\LiteSetup;
use Objection\LiteObject;


class LiteJsonFieldsMapperTest extends \PHPUnit_Framework_TestCase
{
	public function test_passFieldsAsArray()
	{
		$obj = new JsonFieldsMapper(['A', 'B']);
		$this->assertEquals('A', $obj->mapToObjectField('a', \stdClass::class));
	}
	
	public function test_passFieldsAsObject()
	{
		$obj = new JsonFieldsMapper(new Test_LiteJsonMapperTest_Helper());
		$this->assertEquals('A', $obj->mapToObjectField('a', \stdClass::class));
	}
	
	public function test_sanity()
	{
		$obj = new JsonFieldsMapper(['ABcdE', 'efgh']);
		
		$this->assertEquals('ABcdE', $obj->mapToObjectField('aBcdE', \stdClass::class));
		$this->assertEquals('aBcdE', $obj->mapFromObjectField('ABcdE'));
	}
}


class Test_LiteJsonMapperTest_Helper extends LiteObject
{
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'A'	=> LiteSetup::createString(),
			'B'	=> LiteSetup::createString()
		];
	}
}