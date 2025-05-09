<?php
namespace Objection\Structure;


class HashSetTest extends \PHPUnit_Framework_TestCase
{
	public function test_constructor()
	{
		$h = new HashSet();
		$this->assertEquals(0, $h->count());
	}
	
	public function test_constructor_KeysPassed()
	{
		$h = new HashSet(['A', 'B']);
		$this->assertSame(['A', 'B'], $h->getKeys());
	}
	
	public function test_constructor_SingleIntKeyPassed()
	{
		$h = new HashSet(2);
		$this->assertSame([2], $h->getKeys());
	}
	
	public function test_constructor_SingleStringKeyPassed()
	{
		$h = new HashSet('a');
		$this->assertSame(['a'], $h->getKeys());
	}
	
	
	public function test_add_AddIntPassed()
	{
		$h = new HashSet();
		$h->add(2);
		$this->assertSame([2], $h->getKeys());
	}
	
	public function test_add_AddStringPassed()
	{
		$h = new HashSet();
		$h->add('b');
		$this->assertSame(['b'], $h->getKeys());
	}
	
	public function test_add_AddArray()
	{
		$h = new HashSet();
		$h->add(['b', 'c']);
		$this->assertSame(['b', 'c'], $h->getKeys());
	}
	
	public function test_add_AddMixedArray()
	{
		$h = new HashSet();
		$h->add(['b', 1]);
		$this->assertSame(['b', 1], $h->getKeys());
	}
	
	public function test_add_AddWiredNumbers()
	{
		$h = new HashSet();
		$h->add([0, -2]);
		$this->assertSame([0, -2], $h->getKeys());
	}
	
	public function test_add_HashSetNotEmpty_ValueUpended()
	{
		$h = new HashSet(['a', 'b']);
		$h->add(['v']);
		$this->assertSame(['a', 'b', 'v'], $h->getKeys());
	}
	
	public function test_add_ValuesAlreadyExistedInHash()
	{
		$h = new HashSet(['a', 'b']);
		$h->add(['b', 'c']);
		$this->assertSame(['a', 'b', 'c'], $h->getKeys());
	}
	
	public function test_add_IntegerValuesAlreadyExistedInHash()
	{
		$h = new HashSet(['a', 'b', 3]);
		$h->add([3, 'c']);
		$this->assertSame(['a', 'b', 3, 'c'], $h->getKeys());
	}
	
	
	public function test_remove_ObjectNotFound()
	{
		$h = new HashSet(['a', 'b']);
		$h->remove('c');
		$this->assertSame(['a', 'b'], $h->getKeys());
	}
	
	public function test_remove_ObjectInHashSet()
	{
		$h = new HashSet(['a', 'b']);
		$h->remove('b');
		$this->assertSame(['a'], $h->getKeys());
	}
	
	public function test_remove_RemoveSetOfKeys()
	{
		$h = new HashSet(['a', 'b', 'c']);
		$h->remove(['b', 'd']);
		$this->assertSame(['a', 'c'], $h->getKeys());
	}
	
	public function test_remove_RemoveInt()
	{
		$h = new HashSet(['a', 1]);
		$h->remove(1);
		$this->assertSame(['a'], $h->getKeys());
	}
	
	
	public function test_has_NotFound_ReturnFalse()
	{
		$h = new HashSet('a');
		$this->assertFalse($h->has('b'));
	}
	
	public function test_has_Found_ReturnTrue()
	{
		$h = new HashSet('a');
		$this->assertTrue($h->has('a'));
	}
	
	public function test_has_EmptySet_ReturnFalse()
	{
		$h = new HashSet();
		$this->assertFalse($h->has('a'));
	}
	
	public function test_has_Number()
	{
		$h = new HashSet([0, 1, 2]);
		$this->assertTrue($h->has(0));
		$this->assertTrue($h->has(2));
		$this->assertFalse($h->has(4));
	}
	
	
	public function test_hasAll_Empty_ReturnFalse()
	{
		$h = new HashSet();
		$this->assertFalse($h->hasAll(['a', 'b']));
	}
	
	public function test_hasAll_MissingAFew_ReturnFalse()
	{
		$h = new HashSet(['a', 'c']);
		$this->assertFalse($h->hasAll(['a', 'b']));
	}
	
	public function test_hasAll_AllKeysFound_ReturnTrue()
	{
		$h = new HashSet(['a', 'c', 'b']);
		$this->assertTrue($h->hasAll(['a', 'b']));
	}
	
	
	public function test_hasAny_Empty_ReturnFalse()
	{
		$h = new HashSet();
		$this->assertFalse($h->hasAny(['a', 'b']));
	}
	
	public function test_hasAny_HasAtLeastOne_ReturnTrue()
	{
		$h = new HashSet(['a', 'c']);
		$this->assertTrue($h->hasAny(['a', 'b']));
	}
	
	public function test_hasAny_HasAll_ReturnTrue()
	{
		$h = new HashSet(['a', 'c', 'b']);
		$this->assertTrue($h->hasAll(['a', 'b']));
	}
	
	
	public function test_count_None_ReturnZero()
	{
		$h = new HashSet();
		$this->assertEquals(0, $h->count());
	}
	
	public function test_count_HasSome()
	{
		$h = new HashSet(['a', 'b']);
		$this->assertEquals(2, $h->count());
	}
	
	
	public function test_isEmpty_Empty_ReturnTrue()
	{
		$h = new HashSet();
		$this->assertTrue($h->isEmpty());
	}
	
	public function test_isEmpty_NotEmpty_ReturnFalse()
	{
		$h = new HashSet('a');
		$this->assertFalse($h->isEmpty());
	}
	
	
	public function test_getKeys_Empty()
	{
		$h = new HashSet();
		$this->assertSame([], $h->getKeys());
	}
	
	public function test_getKeys_NotEmpty()
	{
		$h = new HashSet(['a', 'b', 2]);
		$this->assertSame(['a', 'b', 2], $h->getKeys());
	}
	
	
	public function test_getIterator_Empty()
	{
		$h = new HashSet();
		
		foreach ($h as $key)
		{
			$this->fail('Should be empty, got: ' . $key);
		}
	}
	
	public function test_getIterator_NotEmpty()
	{
		$h = new HashSet(['a', 'b', 1]);
		$d = [];
		
		foreach ($h as $key)
		{
			$d[] = $key;
		}
		
		$this->assertEquals(['a', 'b', 1], $d);
	}
	
	
	public function test_externalCount_Empty()
	{
		$h = new HashSet();
		$this->assertEquals(0, count($h));
	}
	
	public function test_externalCount_Full()
	{
		$h = new HashSet(['a', 'b', 1]);
		$this->assertEquals(3, count($h));
	}
	
	
	public function test_serialize()
	{
		$h = new HashSet(['a', 'b', 1]);
		$data = serialize($h);
		
		/** @var HashSet $object */
		$object = unserialize($data);
		
		$this->assertInstanceOf(HashSet::class, $object);
		$this->assertEquals($h->getKeys(), $object->getKeys());
	}
	
	
	public function test_isset()
	{
		$h = new HashSet(['a', 'b', 1]);
		
		$this->assertTrue(isset($h['b']));
		$this->assertFalse(isset($h['c']));
	}
	
	
	public function test_unset()
	{
		$h = new HashSet(['a', 'b', 1]);
		
		unset($h['b']);
		unset($h['c']);
		
		$this->assertEquals(['a', 1], $h->getKeys());
	}
	
	
	public function test_ArrayAccess_GetValue()
	{
		$h = new HashSet(['a', 'b', 1]);
		
		$this->assertTrue($h['a']);
		$this->assertFalse($h['c']);
	}
	
	
	public function test_ArrayAccess_SetValue()
	{
		$h = new HashSet(['a', 'b', 1]);
		
		$h['d'] = true;
		$h['a'] = false;
		
		$this->assertTrue($h->has('d'));
		$this->assertFalse($h->has('a'));
	}
}