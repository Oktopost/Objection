<?php
namespace Objection;


use Objection\Enum\AccessRestriction;


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


class LiteObjectTest extends \PHPUnit_Framework_TestCase
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
	
	/**
	 * @expectedException \Objection\Exceptions\PropertyNotFoundException 
	 */
	public function test_PropertyDoesNotExits_ErrorThrown() 
	{
		$o = new TestObject_LiteObject();
		
		/** @noinspection PhpUndefinedFieldInspection */
		$o->PropNone = "1";
	}
	
	
	/**
	 * @expectedException \Objection\Exceptions\ReadOnlyPropertyException 
	 */
	public function test_SetGetOnlyProperty_ErrorThrown() 
	{
		$o = new TestObject_LiteObject();
		$o->PropGetOnly = "1";
	}
	
	public function test_Get_GetOnlyProperty() 
	{
		$o = new TestObject_LiteObject();
		$this->assertEquals(0, $o->PropGetOnly);
	}
	
	
	/**
	 * @expectedException \Objection\Exceptions\WriteOnlyPropertyException
	 */
	public function test_GetSetOnlyProperty_ErrorThrown() 
	{
		$o = new TestObject_LiteObject();
		
		/** @noinspection PhpUnusedLocalVariableInspection */
		$a = $o->PropSetOnly;
	}
	
	public function test_Set_SetOnlyProperty() 
	{
		$o = new TestObject_LiteObject();
		$o->PropSetOnly = 5;
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
}