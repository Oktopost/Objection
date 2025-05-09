<?php
namespace Objection\Mapper\Fields;



class CaseInsensitiveMatchTest extends \PHPUnit_Framework_TestCase
{
	public function test_map_NotFound_ReturnEmptyString()
	{
		$this->assertFalse((new CaseInsensitiveMatch(['A', 'B']))->map('c'));
	}
	
	public function test_map_ExactMatch()
	{
		$this->assertEquals('a', (new CaseInsensitiveMatch(['a', 'B']))->map('a'));
	}
	
	public function test_map_Match()
	{
		$this->assertEquals('Bcd', (new CaseInsensitiveMatch(['A', 'Bcd']))->map('BcD'));
	}
	
	public function test_map_SimilarFieldsProvided_ReturnLastField()
	{
		$this->assertEquals('bCd', (new CaseInsensitiveMatch(['A', 'Bcd', 'bCd']))->map('bcd'));
	}
}