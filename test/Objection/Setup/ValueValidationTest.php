<?php
namespace Objection\Setup;


use Objection\LiteSetup;
use Objection\Enum\VarType;

use PHPUnit\Framework\TestCase;


class ValueValidationTest extends TestCase
{
	public function test_fixValue_Scalars() 
	{
		$this->assertSame(1.0, ValueValidation::fixValue(LiteSetup::createDouble(), "1"));
		$this->assertSame(23, ValueValidation::fixValue(LiteSetup::createInt(), "23"));
		$this->assertSame("1", ValueValidation::fixValue(LiteSetup::createString(), 1));
		$this->assertSame(false, ValueValidation::fixValue(LiteSetup::createBool(), []));
	}
	
	public function test_fixValue_Array() 
	{
		$this->assertEquals([1], ValueValidation::fixValue(LiteSetup::createArray(), 1));
		$this->assertEquals([1, 2], ValueValidation::fixValue(LiteSetup::createArray(), [1, 2]));
	}
	
	public function test_fixValue_Mixed() 
	{
		$this->assertEquals($this, ValueValidation::fixValue(LiteSetup::createMixed(), $this));
	}
	
	
	public function test_fixValue_Enum() 
	{
		$this->assertSame('a', ValueValidation::fixValue(LiteSetup::createEnum(['a', 'b']), 'a'));
	}
	
	
	public function test_fixValue_DateTime_DatePassed()
	{
		$d = new \DateTime('2015-03-06 00:01:02');
		$this->assertEquals($d, ValueValidation::fixValue(LiteSetup::createDateTime(), $d));
	}
	
	public function test_fixValue_DateTime_ObjectIsCloned()
	{
		$d = new \DateTime('2015-03-06 00:01:02');
		$this->assertNotSame($d, ValueValidation::fixValue(LiteSetup::createDateTime(), $d));
	}
	
	public function test_fixValue_DateTime_StringPassedAndConvertedToDateObject()
	{
		$d = new \DateTime('2015-03-06 00:01:02');
		$this->assertEquals($d, ValueValidation::fixValue(LiteSetup::createDateTime(), '2015-03-06 00:01:02'));
	}
	
	public function test_fixValue_DateTime_IntPassedAndUsedAsUnixtimestmap()
	{
		$timestamp = strtotime('2015-03-06 00:01:02');
		$d = new \DateTime('2015-03-06 00:01:02');
		$this->assertEquals($d, ValueValidation::fixValue(LiteSetup::createDateTime(), $timestamp));
	}
	
	public function test_fixValue_DateTime_InvalidTypePassed_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidDatetimeValueTypeException::class);
		
		ValueValidation::fixValue(LiteSetup::createDateTime(), 0.4);
	}
	
	public function test_fixValue_DateTime_InvalidObjectPassed_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidDatetimeValueTypeException::class);
		
		ValueValidation::fixValue(LiteSetup::createDateTime(), new \stdClass());
	}
	
	public function test_fixValue_InvalidEnumValue_ThrowException()
	{
		$this->expectException(\Objection\Exceptions\InvalidEnumValueTypeException::class);
		
		ValueValidation::fixValue(LiteSetup::createEnum(['a', 'b']), 'c');
	}
	
	
	public function test_fixValue_InstanceArray_FalsePassed_EmptyArrayReturned()
	{
		$setup = LiteSetup::createInstanceArray(self::class);
		
		$this->assertEquals([], ValueValidation::fixValue($setup, false));
		$this->assertEquals([], ValueValidation::fixValue($setup, null));
	}
	
	public function test_fixValue_InstanceArray_EmptyArray_EmptyArrayReturned()
	{
		$setup = LiteSetup::createInstanceArray(self::class);
		$this->assertEquals([], ValueValidation::fixValue($setup, []));
	}
	
	public function test_fixValue_InstanceArray_InstancePassed_ArrayWithInstanceReturned()
	{
		$setup = LiteSetup::createInstanceArray(self::class);
		$this->assertEquals([$this], ValueValidation::fixValue($setup, $this));
	}
	
	public function test_fixValue_InstanceArray_ArrayOfInstancesPassed_ArrayOfInstancesReturned()
	{
		$setup = LiteSetup::createInstanceArray(self::class);
		$this->assertEquals([$this, $this], ValueValidation::fixValue($setup, [$this, $this]));
	}
	
	public function test_fixValue_InstanceArray_InvalidInstanceType_ExceptionThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidValueTypeException::class);
		
		$setup = LiteSetup::createInstanceArray(self::class);
		ValueValidation::fixValue($setup, new \stdClass());
	}
	
	public function test_fixValue_InstanceArray_ScalarType_ExceptionThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidValueTypeException::class);
		
		$setup = LiteSetup::createInstanceArray(self::class);
		ValueValidation::fixValue($setup, true);
	}
	
	public function test_fixValue_InstanceArray_ArrayWithOneInvalidInstanceType_ExceptionThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidValueTypeException::class);
		
		$setup = LiteSetup::createInstanceArray(self::class);
		ValueValidation::fixValue($setup, [$this, new \stdClass(), $this]);
	}
	
	public function test_fixValue_InstanceArray_ArrayWithOneScalar_ExceptionThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidValueTypeException::class);
		
		$setup = LiteSetup::createInstanceArray(self::class);
		ValueValidation::fixValue($setup, [$this, "hello", $this]);
	}
	
	
	
	public function test_fixValue_Null() 
	{
		$this->assertNull(ValueValidation::fixValue(LiteSetup::create(VarType::BOOL, true, true), null));
		$this->assertNull(ValueValidation::fixValue(LiteSetup::createEnum(['a', 'b'], null, true), null));
		$this->assertNull(ValueValidation::fixValue(LiteSetup::create(VarType::MIXED, $this, true), null));
	}
	
	
	public function test_fixValue_InstanceOf() 
	{
		$this->assertSame($this, ValueValidation::fixValue(LiteSetup::createInstanceOf(self::class), $this));
	}
	
	public function test_fixValue_InvalidInstanceType_ExceptionThrown()
	{
		$this->expectException(\Objection\Exceptions\InvalidValueTypeException::class);
		
		ValueValidation::fixValue(LiteSetup::createInstanceOf(self::class), new \stdClass());
	}
}