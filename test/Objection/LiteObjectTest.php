<?php
namespace Objection;


use Objection\Enum\AccessRestriction;
use PHPUnit\Framework\TestCase;


/**
 * @property int	$PropInt
 * @property string	$PropString
 * @property array	$PropArray
 * @property int	$PropGetOnly
 * @property int 	$PropSetOnly
 * @property int	$OnSetProperty
 */
class TestObject_LiteObject extends LiteObject 
{
	public $onSetValue = null;
	
	
	/**
	 * @return array
	 */
	protected function _setup()
	{
		return [
			'PropInt'		=> LiteSetup::createInt(),
			'PropString'	=> LiteSetup::createString('a'),
			'PropArray'		=> LiteSetup::createArray([]),
			'PropGetOnly'	=> LiteSetup::createInt(0, AccessRestriction::NO_SET),
			'PropSetOnly'	=> LiteSetup::createInt(0, AccessRestriction::NO_GET),
			'OnSetProperty'	=> LiteSetup::createInt()
		];
	}
	
	
	/**
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		parent::__construct();
		$this->fromArray($data);
	}
	
	
	/**
	 * @return static
	 */
	public function getPrivateAccess() 
	{
		return $this->_p;
	}
	
	public function onSetOnSetProperty($value)
	{
		$this->onSetValue = $value;
	}
}

/**
 * @property string $Name
 */
class TestObject_LiteObject_StrictOverride extends LiteObject
{
	protected function _setup()
	{
		return [
			'Name' => LiteSetup::createString()
		];
	}

	public function fromArrayStrict($source, $ignoreGetOnly = true): static
	{
		$result = parent::fromArrayStrict($source, $ignoreGetOnly);
		$result->Name = trim($result->Name);

		return $result;
	}
}

/**
 * @property string $Name
 */
class TestObject_LiteObject_HydrationSpy extends LiteObject
{
	public array $calls = [];


	protected function _setup()
	{
		return [
			'Name' => LiteSetup::createString()
		];
	}

	public function fromArray($source, $ignoreGetOnly = true)
	{
		$this->calls[] = 'fromArray';
		return parent::fromArray($source, $ignoreGetOnly);
	}

	public function fromArrayStrict($source, $ignoreGetOnly = true): static
	{
		$this->calls[] = 'fromArrayStrict';
		return parent::fromArrayStrict($source, $ignoreGetOnly);
	}
}

/**
 * @property string $Name
 */
class TestObject_LiteObject_FromArrayOverride extends LiteObject
{
	protected function _setup()
	{
		return [
			'Name' => LiteSetup::createString()
		];
	}

	public function fromArray($source, $ignoreGetOnly = true)
	{
		$result = parent::fromArray($source, $ignoreGetOnly);
		$result->Name = trim($result->Name);

		return $result;
	}
}


class LiteObjectTest extends TestCase
{
	public function test_PropertyExists_PropertyModified() 
	{
		$o = new TestObject_LiteObject();
		$o->PropString = "abc";
		
		$this->assertEquals("abc", $o->PropString);
	}
	
	public function test_PropertyExists_PropertyTypeFixed() 
	{
		$o = new TestObject_LiteObject();
		$o->PropString = 1;
		
		$this->assertSame("1", $o->PropString);
	}
	
	public function test_PropertyDoesNotExits_ErrorThrown() 
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$o = new TestObject_LiteObject();
		
		/** @noinspection PhpUndefinedFieldInspection */
		$o->PropNone = "1";
	}
	
	
	public function test_SetGetOnlyProperty_ErrorThrown() 
	{
		$this->expectException(\Objection\Exceptions\ReadOnlyPropertyException::class);
		$o = new TestObject_LiteObject();
		$o->PropGetOnly = "1";
	}
	
	public function test_Get_GetOnlyProperty() 
	{
		$o = new TestObject_LiteObject();
		$this->assertEquals(0, $o->PropGetOnly);
	}
	
	
	public function test_GetSetOnlyProperty_ErrorThrown() 
	{
		$this->expectException(\Objection\Exceptions\WriteOnlyPropertyException::class);
		$o = new TestObject_LiteObject();
		
		/** @noinspection PhpUnusedLocalVariableInspection */
		$a = $o->PropSetOnly;
	}
	
	public function test_Set_SetOnlyProperty() 
	{
		$o = new TestObject_LiteObject();
		$o->PropSetOnly = 5;
		$this->addToAssertionCount(1);
	}
	
	
	public function test_Set_CallbackMethodCalled() 
	{
		$o = new TestObject_LiteObject();
		$o->OnSetProperty = 5;
		$this->assertEquals(5, $o->onSetValue);
	}
	
	public function test_Set_CallbackMethodCalledWithCorrectValueType() 
	{
		$o = new TestObject_LiteObject();
		$o->OnSetProperty = "5";
		$this->assertEquals(5, $o->onSetValue);
	}
	
	
	public function test_ReturnByReferenceWorks() 
	{
		$o = new TestObject_LiteObject();
		$o->PropArray[] = 5;
		$o->PropArray[] = "6";
		$this->assertEquals([5, "6"], $o->PropArray);
	}
	
	public function test_GetOnlyProperty_NotReturnedByReference() 
	{
		$o = new TestObject_LiteObject();
		
		$startValue = $o->PropGetOnly;
		
		$a =& $o->PropGetOnly;
		$a++;
		
		$this->assertEquals($startValue, $o->PropGetOnly);
		
	}
	
	public function test_PrivateModifier() 
	{
		$o = new TestObject_LiteObject();
		$p = $o->getPrivateAccess();
		$p->PropInt = "4";
		
		$this->assertSame(4, $o->PropInt);
	}
	
	
	public function test_getPropertyNames() 
	{
		$o = new TestObject_LiteObject();
		$this->assertEquals(
			[
				'PropInt',
				'PropString',
				'PropArray',
				'PropGetOnly',
				'PropSetOnly',
				'OnSetProperty'
			],
			$o->getPropertyNames());
	}
	
	public function test_getPropertyNames_WithExclude() 
	{
		$o = new TestObject_LiteObject();
		$this->assertEquals(
			['PropInt', 'PropArray', 'PropGetOnly' ],
			$o->getPropertyNames(['PropSetOnly', 'OnSetProperty', 'PropString']));
	}
	
	
	public function test_constructor_ValuesLoaded() 
	{
		$o = new TestObject_LiteObject(['PropInt' => 5, 'PropString' => 6]);
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("6", $o->PropString);
	}
	
	
	public function test_isRestricted() 
	{
		$o = new TestObject_LiteObject();
		$this->assertTrue($o->isRestricted('PropGetOnly'));
		$this->assertFalse($o->isRestricted('PropInt'));
	}
	
	
	public function test_isset() 
	{
		$o = new TestObject_LiteObject();
		$this->assertTrue(isset($o->PropGetOnly));
		$this->assertFalse(isset($o->NotFound));
	}
	
	
	public function test_toArray_Sanity()
	{
		$o = new TestObject_LiteObject();
		$this->assertEquals(['PropInt' => $o->PropInt], $o->toArray(['PropInt']));
	}
	
	public function test_fromArray_Sanity()
	{
		$o = new TestObject_LiteObject();
		$o->fromArray(['PropInt' => "5", 'PropString' => "A"]);
		
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}
	
	public function test_fromArray_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$o = new TestObject_LiteObject();
		
		$o->fromArray(['PropInt' => "5", 'UnknownProperty' => true]);
	}
	
	public function test_fromArrayFiltered_SkipsUnknownProperties()
	{
		$o = new TestObject_LiteObject();
		$o->fromArrayFiltered(['PropInt' => "5", 'PropString' => "A", 'UnknownProperty' => true]);
		
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}
	
	public function test_fromArrayFiltered_ParameterIsObject()
	{
		$o = new TestObject_LiteObject();
		$o->fromArrayFiltered((object)['PropInt' => "5", 'PropString' => "A", 'UnknownProperty' => true]);
		
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}
	
	public function test_allToArray_Sanity()
	{
		$data = [new TestObject_LiteObject(['PropString' => 'str1'])];
		
		$result = TestObject_LiteObject::allToArray($data, [], ['PropString']);
		
		$this->assertEquals([$data[0]->toArray([], ['PropString'])], $result);
	}
	
	public function test_allFromArray_Sanity()
	{
		$a = new TestObject_LiteObject(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly'])];
		
		$result = TestObject_LiteObject::allFromArray($data);
		
		$this->assertCount(1, $result);
		$this->assertInstanceOf(TestObject_LiteObject::class, $result[0]);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
	}
	
	public function test_allFromArray_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$a = new TestObject_LiteObject(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true]];
		
		TestObject_LiteObject::allFromArray($data);
	}
	
	public function test_allFromArrayFiltered_Sanity()
	{
		$a = new TestObject_LiteObject(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true]];
		
		$result = TestObject_LiteObject::allFromArrayFiltered($data);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(TestObject_LiteObject::class, $result[0]);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
	}

	public function test_fromArray_ParameterIsTraversable_StillAccepted()
	{
		$o = new TestObject_LiteObject();
		$o->fromArray(new \ArrayObject(['PropInt' => "5", 'PropString' => "A"]));

		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayStrict_Sanity()
	{
		$o = new TestObject_LiteObject();
		$result = $o->fromArrayStrict(['PropInt' => "5", 'PropString' => "A"]);

		$this->assertSame($o, $result);
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayStrict_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$o = new TestObject_LiteObject();

		$o->fromArrayStrict(['PropInt' => "5", 'UnknownProperty' => true]);
	}

	public function test_fromArrayStrict_ParameterIsObject()
	{
		$o = new TestObject_LiteObject();
		$o->fromArrayStrict((object)['PropInt' => "5", 'PropString' => "A"]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayStrict_GetOnlyProperty_Ignored()
	{
		$o = new TestObject_LiteObject();
		$o->fromArrayStrict(['PropInt' => "5", 'PropGetOnly' => "7"]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame(0, $o->PropGetOnly);
	}

	public function test_fromArrayStrict_IgnoreFlagIsFalse_GetOnlyPropertyNotIgnored()
	{
		$this->expectException(\Objection\Exceptions\ReadOnlyPropertyException::class);
		$o = new TestObject_LiteObject();

		$o->fromArrayStrict(['PropGetOnly' => "7"], false);
	}

	public function test_allFromArrayStrict_Sanity()
	{
		$a = new TestObject_LiteObject(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly'])];

		$result = TestObject_LiteObject::allFromArrayStrict($data);

		$this->assertCount(1, $result);
		$this->assertInstanceOf(TestObject_LiteObject::class, $result[0]);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
	}

	public function test_allFromArrayStrict_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$a = new TestObject_LiteObject(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true]];

		TestObject_LiteObject::allFromArrayStrict($data);
	}

	public function test_fromArray_FromArrayStrictOverridden_OverrideApplied()
	{
		$o = (new TestObject_LiteObject_StrictOverride())->fromArray(['Name' => ' a ']);
		$this->assertSame('a', $o->Name);
	}

	public function test_fromArrayStrict_FromArrayStrictOverridden_OverrideApplied()
	{
		$o = (new TestObject_LiteObject_StrictOverride())->fromArrayStrict(['Name' => ' a ']);
		$this->assertSame('a', $o->Name);
	}

	public function test_fromArrayFiltered_FromArrayStrictOverridden_OverrideApplied()
	{
		$o = (new TestObject_LiteObject_StrictOverride())->fromArrayFiltered(['Name' => ' a ', 'UnknownProperty' => true]);
		$this->assertSame('a', $o->Name);
	}

	public function test_allFromArray_FromArrayStrictOverridden_OverrideApplied()
	{
		$result = TestObject_LiteObject_StrictOverride::allFromArray([['Name' => ' a ']]);
		$this->assertSame('a', $result[0]->Name);
	}

	public function test_allFromArrayStrict_FromArrayStrictOverridden_OverrideApplied()
	{
		$result = TestObject_LiteObject_StrictOverride::allFromArrayStrict([['Name' => ' a ']]);
		$this->assertSame('a', $result[0]->Name);
	}

	public function test_allFromArrayFiltered_FromArrayStrictOverridden_OverrideApplied()
	{
		$result = TestObject_LiteObject_StrictOverride::allFromArrayFiltered([['Name' => ' a ', 'UnknownProperty' => true]]);
		$this->assertSame('a', $result[0]->Name);
	}

	public function test_allFromArrayStrict_CallsFromArrayStrictDirectly()
	{
		$result = TestObject_LiteObject_HydrationSpy::allFromArrayStrict([['Name' => 'a']]);
		$this->assertSame(['fromArrayStrict'], $result[0]->calls);
	}

	public function test_fromArrayFiltered_FromArrayOverridden_OverrideApplied()
	{
		$o = (new TestObject_LiteObject_FromArrayOverride())->fromArrayFiltered(['Name' => ' a ', 'UnknownProperty' => true]);
		$this->assertSame('a', $o->Name);
	}

	public function test_allFromArrayFiltered_FromArrayOverridden_OverrideApplied()
	{
		$result = TestObject_LiteObject_FromArrayOverride::allFromArrayFiltered([['Name' => ' a ', 'UnknownProperty' => true]]);
		$this->assertSame('a', $result[0]->Name);
	}
}
