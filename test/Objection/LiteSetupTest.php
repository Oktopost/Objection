<?php
namespace Objection;


use Objection\Enum\AccessRestriction;
use Objection\Enum\VarType;
use Objection\Enum\SetupFields;


class Test_LiteSetup_CreateEnum_ConstsClass
{
	use \Traitor\TConstsClass;

	const A = 'a';
	const B = 'b';
}


class Test_LiteSetup_CreateEnum_EnumClass
{
	use \Traitor\TEnum;
	
	const A = 'a';
	const B = 'b';
}


class LiteSetupTest extends \PHPUnit_Framework_TestCase
{
	private function assertCreateOfType($type, $value, $isNull, array $actual)
	{
		$expected = [SetupFields::TYPE => $type, SetupFields::VALUE => $value];
		
		if ($isNull) $expected[SetupFields::IS_NULL] = true;
		
		$this->assertEquals($expected, $actual);
	}
	
	private function assertHasAccessRestriction($expectedRestriction, $data)
	{
		$this->assertTrue($data[SetupFields::ACCESS][$expectedRestriction]);
	}
	
	
	public function test_createInt()
	{
		$this->assertCreateOfType(VarType::INT, 0, false, LiteSetup::createInt());
		$this->assertCreateOfType(VarType::INT, -123, false, LiteSetup::createInt(-123));
		$this->assertCreateOfType(VarType::INT, null, true, LiteSetup::createInt(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createInt(null, AccessRestriction::NO_GET));
	}
	
	public function test_createString()
	{
		$this->assertCreateOfType(VarType::STRING, '', false, LiteSetup::createString());
		$this->assertCreateOfType(VarType::STRING, 'Hello World', false, LiteSetup::createString('Hello World'));
		$this->assertCreateOfType(VarType::STRING, null, true, LiteSetup::createString(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createString(null, AccessRestriction::NO_GET));
	}
	
	public function test_createDouble()
	{
		$this->assertCreateOfType(VarType::DOUBLE, 0.0, false, LiteSetup::createDouble());
		$this->assertCreateOfType(VarType::DOUBLE, 12.23, false, LiteSetup::createDouble(12.23));
		$this->assertCreateOfType(VarType::DOUBLE, null, true, LiteSetup::createDouble(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createDouble(null, AccessRestriction::NO_GET));
	}
	
	public function test_createBool()
	{
		$this->assertCreateOfType(VarType::BOOL, false, false, LiteSetup::createBool());
		$this->assertCreateOfType(VarType::BOOL, 12.23, false, LiteSetup::createBool(true));
		$this->assertCreateOfType(VarType::BOOL, null, true, LiteSetup::createBool(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createBool(null, AccessRestriction::NO_GET));
	}
	
	public function test_createArray()
	{
		$this->assertCreateOfType(VarType::ARR, [], false, LiteSetup::createArray());
		$this->assertCreateOfType(VarType::ARR, ['a', 'b'], false, LiteSetup::createArray(['a', 'b']));
		$this->assertCreateOfType(VarType::ARR, ['element'], false, LiteSetup::createArray('element'));
		$this->assertCreateOfType(VarType::ARR, null, true, LiteSetup::createArray(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createArray(null, AccessRestriction::NO_GET));
	}
	
	public function test_createMixed()
	{
		$this->assertCreateOfType(VarType::MIXED, null, false, LiteSetup::createMixed());
		$this->assertCreateOfType(VarType::MIXED, $this, false, LiteSetup::createMixed($this));
		$this->assertCreateOfType(VarType::MIXED, null, true, LiteSetup::createMixed(null));
		$this->assertCreateOfType(VarType::MIXED, null, true, LiteSetup::createMixed(null));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createMixed(null, AccessRestriction::NO_GET));
	}
	
	public function test_createInstanceOf_UsingClassName()
	{
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::INSTANCE,
				SetupFields::VALUE			=> null,
				SetupFields::INSTANCE_TYPE	=> \stdClass::class,
				SetupFields::IS_NULL		=> true
			],
			LiteSetup::createInstanceOf(\stdClass::class));
	}
	
	public function test_createInstanceOf_UsingInstance()
	{
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::INSTANCE,
				SetupFields::VALUE			=> $this,
				SetupFields::INSTANCE_TYPE	=> self::class,
				SetupFields::IS_NULL		=> true
			],
			LiteSetup::createInstanceOf($this));
	}
	
	public function test_createInstanceOf_AccessRestrictionSet()
	{
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET, 
			LiteSetup::createInstanceOf($this, AccessRestriction::NO_GET));
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_SET,
			LiteSetup::createInstanceOf($this, AccessRestriction::NO_SET));
	}
	
	public function test_create()
	{
		$this->assertCreateOfType(VarType::BOOL, 12, false, LiteSetup::create(VarType::BOOL, 12, false));
		$this->assertCreateOfType(VarType::DOUBLE, 15.0, false, LiteSetup::create(VarType::DOUBLE, 15.0, false));
		$this->assertCreateOfType(VarType::MIXED, '', true, LiteSetup::create(VarType::MIXED, '', true));
		$this->assertCreateOfType(VarType::MIXED, null, true, LiteSetup::create(VarType::MIXED, null, true));
		
		$this->assertCreateOfType(VarType::STRING, null, true, LiteSetup::create(VarType::STRING, null));
	}
	
	
	public function test_create_WithoutAccess()
	{
		$without = LiteSetup::create(VarType::BOOL, 12, false, false);
		$this->assertTrue(!isset($without[SetupFields::ACCESS]));
	}
	
	public function test_create_WithAccess()
	{
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_SET,
			LiteSetup::create(VarType::BOOL, 12, false, AccessRestriction::NO_SET)
		);
		
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_SET,
			LiteSetup::create(VarType::BOOL, 12, false, AccessRestriction::NO_SET)
		);
	}
	
	
	public function test_createEnum()
	{
		$enum = ['a', 'b', 'c'];
		$flipped = array_flip($enum);
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> 'a',
				SetupFields::VALUES_SET		=> $flipped,
			],
			LiteSetup::createEnum($enum));
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> 'b',
				SetupFields::VALUES_SET		=> $flipped,
			],
			LiteSetup::createEnum($enum, 'b'));
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> 'b',
				SetupFields::VALUES_SET		=> $flipped,
				SetupFields::IS_NULL		=> true
			],
			LiteSetup::createEnum($enum, 'b', true));
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> null,
				SetupFields::VALUES_SET		=> $flipped,
				SetupFields::IS_NULL		=> true
			],
			LiteSetup::createEnum($enum, null));
	}
	
	public function test_createEnum_WithoutAccessRestriction()
	{
		$result = LiteSetup::createEnum(['a', 'b', 'c']);
		$this->assertTrue(!isset($result[SetupFields::ACCESS]));
	}
	
	public function test_createEnum_WithAccessRestriction()
	{
		$result = LiteSetup::createEnum(['a', 'b', 'c'], null, false, AccessRestriction::NO_GET);
		$this->assertTrue(isset($result[SetupFields::ACCESS][AccessRestriction::NO_GET]));
		
		$result = LiteSetup::createEnum(['a', 'b', 'c'], null, false, AccessRestriction::NO_SET);
		$this->assertTrue(isset($result[SetupFields::ACCESS][AccessRestriction::NO_SET]));
	}
	
	public function test_createEnum_CreateUsingTConstsClass()
	{
		$enum = ['a', 'b'];
		$flipped = array_flip($enum);
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> 'a',
				SetupFields::VALUES_SET		=> $flipped,
			],
			LiteSetup::createEnum(Test_LiteSetup_CreateEnum_ConstsClass::class));
	}
	
	public function test_createEnum_CreateUsingTEnumClass()
	{
		$enum = ['a', 'b'];
		$flipped = array_flip($enum);
		
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::ENUM,
				SetupFields::VALUE			=> 'a',
				SetupFields::VALUES_SET		=> $flipped,
			],
			LiteSetup::createEnum(Test_LiteSetup_CreateEnum_EnumClass::class));
	}

	/**
	 * @expectedException \Objection\Exceptions\InvalidPropertySetupException
	 */
	public function test_createEnum_InvalidClassNamePassed()
	{
		LiteSetup::createEnum('not_a_class');
	}

	/**
	 * @expectedException \Objection\Exceptions\InvalidPropertySetupException
	 */
	public function test_createEnum_CreateUsingNotTConstsClass()
	{
		LiteSetup::createEnum(\stdClass::class);
	}
	
	
	public function test_createDateTime_UsingDateTimeObject()
	{
		$d = new \DateTime();
		
		$result = LiteSetup::createDateTime($d);
		
		$this->assertEquals(VarType::DATE_TIME, $result[SetupFields::TYPE]);
		$this->assertEquals($d,					$result[SetupFields::VALUE]);
	}
	
	public function test_createDateTime_UsingString()
	{
		$d = new \DateTime('2015-03-06 00:01:02');
		
		$result = LiteSetup::createDateTime('2015-03-06 00:01:02');
		
		$this->assertEquals(VarType::DATE_TIME, $result[SetupFields::TYPE]);
		$this->assertEquals($d,					$result[SetupFields::VALUE]);
	}
	
	public function test_createDateTime_UsingInt()
	{
		$time = time() - 1000;
		$d = (new \DateTime())->setTimestamp($time);
		
		$result = LiteSetup::createDateTime($time);
		
		$this->assertEquals(VarType::DATE_TIME, $result[SetupFields::TYPE]);
		$this->assertEquals($d,					$result[SetupFields::VALUE]);
	}
	
	/**
	 * @expectedException \Objection\Exceptions\InvalidDatetimeValueTypeException
	 */
	public function test_createDateTime_InvalidType_ErrorThrown()
	{
		LiteSetup::createDateTime(0.5);
	}
	
	/**
	 * @expectedException \Objection\Exceptions\InvalidDatetimeValueTypeException
	 */
	public function test_createDateTime_InvalidObject_ErrorThrown()
	{
		LiteSetup::createDateTime(new \stdClass());
	}
	
	public function test_createDateTime_SetAsNull_NullFlagIsActive()
	{
		$result = LiteSetup::createDateTime('now', true);
		$this->assertTrue($result[SetupFields::IS_NULL]);
	}
	
	public function set_createInstanceArray()
	{
		$this->assertEquals(
			[
				SetupFields::TYPE			=> VarType::INSTANCE_ARRAY,
				SetupFields::VALUE			=> [],
				SetupFields::INSTANCE_TYPE	=> self::class
			],
			LiteSetup::createInstanceArray(self::class));
	}
	
	public function set_createInstanceArray_RestrictedAccess_NoGet()
	{
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_GET,
			LiteSetup::createInstanceArray(self::class, AccessRestriction::NO_GET));
	}
	
	public function set_createInstanceArray_RestrictedAccess_NoSet()
	{
		$this->assertHasAccessRestriction(
			AccessRestriction::NO_SET,
			LiteSetup::createInstanceArray(self::class, AccessRestriction::NO_SET));
	}
}