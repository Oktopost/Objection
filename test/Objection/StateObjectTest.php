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
class TestObject_StateObject extends StateObject 
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


class StateObjectTest extends \PHPUnit_Framework_TestCase
{
	public function test_isModified() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = 4;
		
		$this->assertTrue($a->isModified('PropInt'));
		$this->assertFalse($a->isModified('PropString'));
	}
	
	/**
	 * @expectedException \Objection\Exceptions\PropertyNotFoundException
	 */
	public function test_isModified_PropertyNotFound_ErrorThrown() 
	{
		$a = new TestObject_StateObject();
		$a->isModified('NotAProperty');
	}
	
	
	public function test_hasModified() 
	{
		$modified = new TestObject_StateObject();
		$notModified = new TestObject_StateObject();
		
		$modified->PropInt = 4;
		
		$this->assertTrue($modified->hasModified());
		$this->assertFalse($notModified->hasModified());
	}
	
	
	public function test_getModifiedProperties_None_ReturnEmptyArray() 
	{
		$a = new TestObject_StateObject();
		$this->assertEmpty($a->getModifiedProperties());
	}
	
	public function test_getModifiedProperties_ModifiedReturned() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = 5;
		$a->PropString = 'asd';
		
		// Note that order is important
		$this->assertEquals(['PropInt', 'PropString'], $a->getModifiedProperties());
	}
	
	
	public function test_getModified_None_ReturnEmptyArray() 
	{
		$a = new TestObject_StateObject();
		$this->assertEmpty($a->getModified());
	}
	
	public function test_getModified_ModifiedReturned() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = 5;
		$a->PropString = 'asd';
		
		$this->assertEquals(['PropInt' => 5, 'PropString' => 'asd'], $a->getModified());
	}
	
	
	public function test_commit_All_NoModifiedFields() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = $a->PropInt + 1;
		$a->commit();
		
		$this->assertFalse($a->hasModified());
	}
	
	public function test_commit_FilterFields_OnlyFilteredAreCommitted() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = $a->PropInt + 1;
		$a->PropString = $a->PropString . "D";
		$a->commit(['PropInt']);
		
		$this->assertTrue($a->isModified('PropString'));
		$this->assertFalse($a->isModified('PropInt'));
	}
	
	public function test_commit_SingleFilledFilter() 
	{
		$a = new TestObject_StateObject();
		$a->PropInt = $a->PropInt + 1;
		$a->commit('PropInt');
		$this->assertFalse($a->isModified('PropInt'));
	}
	
	public function test_commit_ExcludeFields_OnlyFilteredAreCommitted() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = $a->PropInt + 1;
		$a->PropString = $a->PropString . "d";
		$a->commit(false, ['PropInt']);
		
		$this->assertFalse($a->isModified('PropString'));
		$this->assertTrue($a->isModified('PropInt'));
	}
	
	public function test_commit_SingleFilledExclude() 
	{
		$a = new TestObject_StateObject();
		$a->PropInt = $a->PropInt + 1;
		$a->commit(false, 'PropString');
		$this->assertFalse($a->isModified('PropInt'));
	}
	
	
	public function test_rollback_All_NoModifiedFields() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropInt = $a->PropInt + 1;
		$a->rollback();
		
		$this->assertFalse($a->hasModified());
	}
	
	public function test_rollback_FieldsAreResetToPreviousValue() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt;
		$a->PropInt = $a->PropInt + 1;
		$a->rollback();
		
		$this->assertEquals($original, $a->PropInt);
	}
	
	public function test_rollback_Filter_OnlyFilteredReset() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt; 
		$a->PropInt = $a->PropInt + 1;
		$a->PropString = $a->PropString . "D";
		$newValue = $a->PropString; 
		
		$a->rollback(['PropInt']);
		
		$this->assertTrue($a->isModified('PropString'));
		$this->assertFalse($a->isModified('PropInt'));
		
		$this->assertEquals($newValue, $a->PropString);
		$this->assertEquals($original, $a->PropInt);
	}
	
	public function test_rollback_SingleFilledFilter() 
	{
		$a = new TestObject_StateObject();
		$original = $a->PropInt;  
		$a->PropInt = $a->PropInt + 1;
		$a->rollback('PropInt');
		$this->assertEquals($original, $a->isModified('PropInt'));
	}
	
	public function test_rollback_Exclude_OnlyFilteredReset() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt; 
		$a->PropInt = $a->PropInt + 1;
		$a->PropString = $a->PropString . "D";
		$newValue = $a->PropString; 
		
		$a->rollback([], ['PropString']);
		
		$this->assertTrue($a->isModified('PropString'));
		$this->assertFalse($a->isModified('PropInt'));
		
		$this->assertEquals($newValue, $a->PropString);
		$this->assertEquals($original, $a->PropInt);
	}
	
	public function test_rollback_SingleFilledExcluded() 
	{
		$a = new TestObject_StateObject();
		$original = $a->PropInt;  
		$a->PropInt = $a->PropInt + 1;
		$a->rollback(false, 'PropString');
		$this->assertEquals($original, $a->isModified('PropInt'));
	}
	
	
	public function test_getOriginalValue_OnModifiedField() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt; 
		$a->PropInt = $a->PropInt + 1; 
		
		$this->assertEquals($original, $a->getOriginalValue('PropInt'));
	}
	
	public function test_getOriginalValue_OnNotModifiedField() 
	{
		$a = new TestObject_StateObject();
		
		$this->assertEquals($a->PropString, $a->getOriginalValue('PropString'));
	}
	
	public function test_getOriginalValue_UseArrayOfFields_AllFieldsReturned() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt; 
		$a->PropInt = $a->PropInt + 1; 
		
		$this->assertEquals(
			[
				'PropInt'		=> $original,
				'PropString'	=> $a->PropString
			], 
			$a->getOriginalValue(['PropInt', 'PropString']));
	}
	
	public function test_getOriginalValue_UseArrayOfFields_OrderIfKeysIsSameAsFilter() 
	{
		$a = new TestObject_StateObject();
		
		$this->assertEquals(
			['PropString', 'PropInt'], 
			array_keys($a->getOriginalValue(['PropString', 'PropInt'])));
	}
	
	
	public function test_set_ValueChanged_PropertyMarkedAsModified() 
	{
		$a = new TestObject_StateObject();
		$a->PropInt = $a->PropInt + 4;
		$this->assertTrue($a->isModified('PropInt'));
	}
	
	public function test_set_ValueChanged_OnlyModifiedPropertyMarkedAsModified() 
	{
		$a = new TestObject_StateObject();
		$a->PropInt = $a->PropInt + 4;
		$this->assertFalse($a->isModified('PropString'));
	}
	
	public function test_set_ValueChangedToOriginalValue_ValuesNotMarkedAsModified() 
	{
		$a = new TestObject_StateObject();
		
		$original = $a->PropInt;
		$a->PropInt = $a->PropInt + 4;
		$a->PropInt = $original;
		
		$this->assertFalse($a->isModified('PropString'));
	}
	
	public function test_set_ValueModifiedMoreThanOnce_OriginalValueStillRemains() 
	{
		$a = new TestObject_StateObject();
		
		$originalValue = $a->PropInt;
		
		$a->PropInt = 11;
		$a->PropInt = 13;
		$a->PropInt = 12;
		
		$this->assertEquals($originalValue, $a->getOriginalValue('PropInt'));
	}
	
	public function test_set_ModifySetOnlyProperty_PropertyNotMarkedModified() 
	{
		$a = new TestObject_StateObject();
		
		$a->PropSetOnly = 11;
		$a->PropSetOnly = 13;
		
		$this->assertFalse($a->isModified('PropSetOnly'));
	}
}