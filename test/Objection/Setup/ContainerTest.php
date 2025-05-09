<?php
namespace Objection\Setup;


use Objection\Enum\SetupFields;


class ContainerTest extends \PHPUnit_Framework_TestCase 
{
	/**
     * @runInSeparateProcess
     */
	public function test_has()
	{
		Container::instance()->set('b', []);
		Container::instance()->set('c', []);
		
		$this->assertFalse(Container::instance()->has('a'));
		$this->assertTrue(Container::instance()->has('c'));
	}
	
	
	/**
     * @runInSeparateProcess
     */
	public function test_set()
	{
		$this->assertFalse(Container::instance()->has('b'));
		Container::instance()->set('b', []);
		$this->assertTrue(Container::instance()->has('b'));
	}
	
	/**
     * @runInSeparateProcess
	 * @expectedException \Objection\Exceptions\LiteObjectException
     */
	public function test_set_ClassAlreadyDefined_ErrorThrown()
	{
		Container::instance()->set('b', []);
		Container::instance()->set('b', []);
	}
	
	/**
     * @runInSeparateProcess
     */
	public function test_get()
	{
		$data = ['a' => [], 'b' => []];
		Container::instance()->set('b', $data);
		
		$this->assertSame($data, Container::instance()->get('b'));
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function test_get_UnExistingProperty_ReturnNull()
	{
		$data = ['a' => [], 'b' => []];
		Container::instance()->set('b', $data);
		
		$this->assertEquals(null, Container::instance()->get('c'));
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function test_get_ValueFieldsAreNotReturned()
	{
		$data = ['a' => [SetupFields::VALUE => 1, SetupFields::ACCESS => 2], 'b' => []];
		Container::instance()->set('b', $data);
		
		unset($data['a'][SetupFields::VALUE]);
		
		$this->assertSame($data, Container::instance()->get('b'));
	}
	
	
	/**
	 * @runInSeparateProcess
	 */
	public function test_getValues()
	{
		$data = ['a' => [SetupFields::VALUE => 1], 'b' => [SetupFields::VALUE => 2]];
		Container::instance()->set('b', $data);
		
		$this->assertEquals(['a' => 1, 'b' => 2], Container::instance()->getValues('b'));
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function test_getValues_ValueIsNull_ValueReturned()
	{
		$data = ['a' => [SetupFields::VALUE => null]];
		Container::instance()->set('b', $data);
		
		$this->assertEquals(['a' => null], Container::instance()->getValues('b'));
	}
	
	/**
	 * @runInSeparateProcess
	 */
	public function test_getValues_FieldWithoutValueNotReturned()
	{
		$data = ['a' => [], 'b' => [SetupFields::VALUE => 2]];
		Container::instance()->set('b', $data);
		
		$this->assertEquals(['b' => 2], Container::instance()->getValues('b'));
	}
}