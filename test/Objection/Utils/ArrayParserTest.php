<?php
namespace Objection\Utils;


use Objection\LiteObject;
use Objection\LiteSetup;
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

/**
 * @property string $Name
 */
class TestObject_ArrayParser_StrictOverride extends LiteObject
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
class TestObject_ArrayParser_HydrationSpy extends LiteObject
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



class ArrayParserTest extends TestCase
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
	
	public function test_toArray_FilterForInvalidProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
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
	
	public function test_fromArray_DoesNotExists_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['dd' => "5"]);
	}
	
	public function test_fromArray_GetOnlyPropertyIgnored()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArray($o, ['PropGetOnly' => "5"]);
	}
	
	public function test_fromArray_IgnoreFlagIsFalse_GetOnlyPropertyNotIgnored()
	{
		$this->expectException(\Objection\Exceptions\ReadOnlyPropertyException::class);
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
	
	public function test_allFromArray_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$a = new TestObject_ArrayParser(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true]];
		
		ArrayParser::allFromArray(TestObject_ArrayParser::class, $data);
	}
	
	public function test_allFromArrayFiltered()
	{
		$a = new TestObject_ArrayParser(['PropString' => 'str1']);
		$b = new TestObject_ArrayParser(['PropString' => 'str2']);
		
		$data = [
			$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true],
			$b->toArray([], ['PropInt', 'PropGetOnly']) + ['UnknownProperty' => true]
		];
		
		$result = ArrayParser::allFromArrayFiltered(TestObject_ArrayParser::class, $data);

		$this->assertCount(2, $result);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
		$this->assertEquals($b->toArray(), $result[1]->toArray());
	}

	public function test_fromArrayStrict()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayStrict($o, ['PropInt' => "5", 'PropString' => "A"]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayStrict_ParameterIsObject()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayStrict($o, (object)['PropInt' => "5", 'PropString' => "A"]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayStrict_DoesNotExists_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayStrict($o, ['dd' => "5"]);
	}

	public function test_fromArrayStrict_GetOnlyPropertyIgnored()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayStrict($o, ['PropGetOnly' => "5"]);

		$this->assertSame(0, $o->PropGetOnly);
	}

	public function test_fromArrayStrict_IgnoreFlagIsFalse_GetOnlyPropertyNotIgnored()
	{
		$this->expectException(\Objection\Exceptions\ReadOnlyPropertyException::class);
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayStrict($o, ['PropGetOnly' => "5"], false);
	}

	public function test_allFromArrayStrict()
	{
		$a = new TestObject_ArrayParser(['PropString' => 'str1']);
		$b = new TestObject_ArrayParser(['PropString' => 'str2']);

		$data = [$a->toArray([], ['PropGetOnly']), $b->toArray([], ['PropInt', 'PropGetOnly'])];

		$result = ArrayParser::allFromArrayStrict(TestObject_ArrayParser::class, $data);

		$this->assertCount(2, $result);
		$this->assertEquals($a->toArray(), $result[0]->toArray());
		$this->assertEquals($b->toArray(), $result[1]->toArray());
	}

	public function test_allFromArrayStrict_UnknownProperty_ErrorThrown()
	{
		$this->expectException(\Objection\Exceptions\PropertyNotFoundException::class);
		$a = new TestObject_ArrayParser(['PropString' => 'str1']);
		$data = [$a->toArray([], ['PropGetOnly']) + ['UnknownProperty' => true]];

		ArrayParser::allFromArrayStrict(TestObject_ArrayParser::class, $data);
	}

	public function test_allFromArrayStrict_FromArrayStrictOverridden_OverrideApplied()
	{
		$result = ArrayParser::allFromArrayStrict(TestObject_ArrayParser_StrictOverride::class, [['Name' => ' a ']]);

		$this->assertSame('a', $result[0]->Name);
	}

	public function test_allFromArrayStrict_CallsFromArrayStrictDirectly()
	{
		$result = ArrayParser::allFromArrayStrict(TestObject_ArrayParser_HydrationSpy::class, [['Name' => 'a']]);

		$this->assertSame(['fromArrayStrict'], $result[0]->calls);
	}

	public function test_fromArrayFiltered_UnknownPropertySkipped()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayFiltered($o, ['PropInt' => "5", 'PropString' => "A", 'UnknownProperty' => true]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame("A", $o->PropString);
	}

	public function test_fromArrayFiltered_ParameterIsObject()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayFiltered($o, (object)['PropInt' => "5", 'UnknownProperty' => true]);

		$this->assertSame(5, $o->PropInt);
	}

	public function test_fromArrayFiltered_GetOnlyPropertyIgnored()
	{
		$o = new TestObject_ArrayParser();
		ArrayParser::fromArrayFiltered($o, ['PropInt' => "5", 'PropGetOnly' => "7"]);

		$this->assertSame(5, $o->PropInt);
		$this->assertSame(0, $o->PropGetOnly);
	}

	public function test_fromArrayFiltered_FromArrayStrictOverridden_OverrideApplied()
	{
		$o = new TestObject_ArrayParser_StrictOverride();
		ArrayParser::fromArrayFiltered($o, ['Name' => ' a ', 'UnknownProperty' => true]);

		$this->assertSame('a', $o->Name);
	}
}
