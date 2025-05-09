<?php
namespace Objection\Utils;


use Objection\LiteObject;
use Objection\LiteSetup;
use Objection\Enum\AccessRestriction;


/**
 * @property int	$PropInt
 * @property string	$PropString
 * @property array	$PropArray
 * @property int	$PropGetOnly
 * @property int 	$PropSetOnly
 * @property int	$OnSetProperty
 */
class TestObject_ArrayParser extends LiteObject
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



class ArrayParserTest extends \PHPUnit_Framework_TestCase
{
	public function test_toArray()
	{
		$o = new TestObject_ArrayParser();
		$this->assertEquals(
			[
				'PropInt'		=> $o->PropInt,
				'PropGetOnly'	=> $o->PropGetOnly,
				'PropArray'		=> $o->PropArray,
				'PropString'	=> $o->PropString,
				'OnSetProperty'	=> $o->OnSetProperty
			],
			ArrayParser::toArray($o));
	}
	
	public function test_toArray_WithFiler()
	{
		$o = new TestObject_ArrayParser();
		$this->assertEquals(
			[
				'PropString'	=> $o->PropString,
				'PropInt'		=> $o->PropInt
			],
			ArrayParser::toArray($o, ['PropString', 'PropInt']));
	}
	
	public function test_toArray_WithFiler_OrderSameAsFilter()
	{
		$o = new TestObject_ArrayParser();
		$this->assertEquals(
			['PropString', 'PropInt'],
			array_keys(ArrayParser::toArray($o, ['PropString', 'PropInt'])));
	}
	
	public function test_toArray_WithExclude()
	{
		$o = new TestObject_ArrayParser();
		$this->assertEquals(
			[
				'PropInt'		=> $o->PropInt,
				'PropGetOnly'	=> $o->PropGetOnly,
				'PropArray'		=> $o->PropArray
			],
			ArrayParser::toArray($o, [], ['OnSetProperty', 'PropString']));
	}
	
	/**
	 * @expectedException \Objection\Exceptions\PropertyNotFoundException
	 */
	public function test_toArray_FilterForInvalidProperty_ErrorThrown()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::toArray($o, ['a']);
	}
	
	public function test_toArray_FilterForSetOnlyProperty_PropertyIgnored()
	{
		$o = new TestObject_ArrayParser();
		$d = ArrayParser::toArray($o, ['PropSetOnly']);
		$this->assertFalse(isset($d['PropGetOnly']));
	}
	
	
	public function test_fromArray()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['PropInt' => "5", 'PropString' => "A"]);
		
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}
	
	public function test_fromArray_ParameterIsObject()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, (object)['PropInt' => "5", 'PropString' => "A"]);
		
		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}
	
	/**
	 * @expectedException \Objection\Exceptions\PropertyNotFoundException
	 */
	public function test_fromArray_DoesNotExists_ErrorThrown()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['dd' => "5"]);
	}
	
	public function test_fromArray_GetOnlyPropertyIgnored()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['PropGetOnly' => "5"]);
	}
	
	/**
	 * @expectedException \Objection\Exceptions\ReadOnlyPropertyException
	 */
	public function test_fromArray_IgnoreFlagIsFalse_GetOnlyPropertyNotIgnored()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['PropGetOnly' => "5"], false);
	}
	
	
	public function test_allToArray()
	{
		$data = [
			new TestObject_ArrayParser([
				'PropString'	=> 'str1'
			]),
			new TestObject_ArrayParser([
				'PropString'	=> 'str2'
			])
		];
		
		$result = ArrayParser::allToArray($data);
		
		$this->assertCount(2, $result);
		$this->assertEquals([$data[0]->toArray(), $data[1]->toArray()], $result);
	}
	
	public function test_allToArray_WithFilters()
	{
		$data = [
			new TestObject_ArrayParser([
				'PropString'	=> 'str1'
			]),
			new TestObject_ArrayParser([
				'PropString'	=> 'str2'
			])
		];
		
		$result = ArrayParser::allToArray($data, ['PropString']);
		
		$this->assertCount(2, $result);
		$this->assertEquals([$data[0]->toArray(['PropString']), $data[1]->toArray(['PropString'])], $result);
	}
	
	public function test_allToArray_WithExclude()
	{
		$data = [
			new TestObject_ArrayParser([
				'PropString'	=> 'str1'
			]),
			new TestObject_ArrayParser([
				'PropString'	=> 'str2'
			])
		];
		
		$result = ArrayParser::allToArray($data, [], ['PropString']);
		
		$this->assertCount(2, $result);
		$this->assertEquals([$data[0]->toArray([], ['PropString']), $data[1]->toArray([], ['PropString'])], $result);
	}
	
	public function test_allFromArray()
	{
		$a = new TestObject_ArrayParser(['PropString' => 'str1']);
		$b = new TestObject_ArrayParser(['PropString' => 'str2']);
		
		$data = [$a->toArray([], ['PropGetOnly']), $b->toArray([], ['PropInt', 'PropGetOnly'])];
		
		$result = ArrayParser::allFromArray(TestObject_ArrayParser::class, $data);
		
		$this->assertCount(2, $result);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
		$this->assertEquals($b->toArray(), $result[1]->toArray());
	}
}
