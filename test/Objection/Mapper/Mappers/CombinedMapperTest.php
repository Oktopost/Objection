<?php
namespace Objection\Mapper\Mappers;


use Objection\Mapper\Base\Fields\IFieldMapper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;


class CombinedMapperTest extends TestCase
{
	/**
	 * @return MockObject|IFieldMapper
	 */
	private function mockIFieldMapper()
	{
		return $this->getMockForAbstractClass(IFieldMapper::class);
	}
	
	
	public function test_mapToObjectField_ToObjectMapperCalled()
	{
		$from	= $this->mockIFieldMapper();
		$to		= $this->mockIFieldMapper();
		
		$to->expects($this->once())->method('map')->with('abcd')->willReturn('123');
		
		$this->assertEquals('123', (new CombinedMapper($from, $to))->mapToObjectField('abcd', \stdClass::class));
	}
	
	public function test_mapFromObjectField_FromObjectMapperCalled()
	{
		$from	= $this->mockIFieldMapper();
		$to		= $this->mockIFieldMapper();
		
		$from->expects($this->once())->method('map')->with('abcd')->willReturn('123');
		
		$this->assertEquals('123', (new CombinedMapper($from, $to))->mapFromObjectField('abcd'));
	}
}