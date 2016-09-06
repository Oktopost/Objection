<?php
namespace Objection\Mapper\FieldMappers;


use Objection\Mapper\Base\Fields\IFieldMapper;
use Objection\Mapper\Fields\FirstToLower;


class FirstToLowerTest extends \PHPUnit_Framework_TestCase
{
	public function test_map_EmptyString_EmptyStringReturned()
	{
		$this->assertEquals('', (new FirstToLower())->map(''));
	}
	
	public function test_map_AllLowerCase()
	{
		$this->assertEquals('abc', (new FirstToLower())->map('abc'));
	}
	
	public function test_map_AllUpperCase()
	{
		$this->assertEquals('abc', (new FirstToLower())->map('ABC'));
	}
	
	public function test_map_FirstLetterUpperCaseOnly()
	{
		$this->assertEquals('abc', (new FirstToLower())->map('Abc'));
	}
	
	public function test_map_NumberOfFirstLettersUpperCase()
	{
		$this->assertEquals('aBcde', (new FirstToLower())->map('ABcde'));
	}
	
	public function test_map_HaveUpperCaseInTheMiddle()
	{
		$this->assertEquals('abCDe', (new FirstToLower())->map('abCDe'));
	}
	
	public function test_map_HaveNumberOfSeparateUpperCaseCharacters()
	{
		$this->assertEquals('aBcdEf', (new FirstToLower())->map('aBcdEf'));
	}
	
	public function test_map_OnlyFirstCharacterIsLowerCase()
	{
		$this->assertEquals('aBCDEF', (new FirstToLower())->map('aBCDEF'));
	}
}