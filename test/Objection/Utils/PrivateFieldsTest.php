<?php
namespace Objection\Utils;


use Objection\LiteSetup;
use Objection\Enum\SetupFields;
use Objection\Enum\AccessRestriction;


class PrivateFieldsTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @expectedException \Exception
	 */
	public function test_get_NoProperty_ErrorThrown() 
	{
		$data = [];
		$values = [];
		$p = new PrivateFields($values, $data, $this);
		
		/** @noinspection PhpUnusedLocalVariableInspection */
		$a = $p->n;
	}
	
	public function test_get_PropertyExists_PropertyReturned() 
	{
		$data = ['n' => [], 'a' => []];
		$values = ['n' => 12, 'a' => 13];
		$p = new PrivateFields($values, $data, $this);
		
		$this->assertEquals(12, $p->n);
		$this->assertEquals(13, $p->a);
	}
	
	public function test_get_PropertyReturnedByReference() 
	{
		$data = ['n' => []];
		$values = ['n' => 12];
		$p = new PrivateFields($values, $data, $this);
		
		$val =& $p->n;
		$val = 13;
		
		$this->assertEquals(13, $p->n);
	}
	
	public function test_get_PropertyIsGetOnly_PropertyStillCanBeAccessed() 
	{
		$data = ['n' => [SetupFields::ACCESS => AccessRestriction::NO_GET]];
		$values = ['n' => 13];
		
		$p = new PrivateFields($values, $data, $this);
		$this->assertEquals(13, $p->n);
	}
	
	
	/**
	 * @expectedException \Exception
	 */
	public function test_set_NoProperty_ErrorThrown() 
	{
		$values = [];
		$p = new PrivateFields($values, [], $this);
		$p->n = 5;
	}
	
	public function test_get_PropertyExists_PropertyModified() 
	{
		$data = [
			'n' => LiteSetup::createInt(12), 
			'a' => LiteSetup::createInt(13)
		];
		
		unset($data['n'][SetupFields::VALUE]);
		unset($data['a'][SetupFields::VALUE]);
		
		$values = ['n' => 12, 'a' => 12];
		
		$p = new PrivateFields($values, $data, $this);
		
		$p->n = 24;
		$p->a = 25;
		
		$this->assertEquals(24, $p->n);
		$this->assertEquals(25, $p->a);
	}
	
	public function test_get_PropertyValueIsFixed() 
	{
		$data = [
			'n' => LiteSetup::createInt(12),
			'a' => LiteSetup::createArray([])
		];
		
		unset($data['n'][SetupFields::VALUE]);
		unset($data['a'][SetupFields::VALUE]);
		
		$values = ['n' => 12, 'a' => []];
		
		$p = new PrivateFields($values, $data, $this);
		
		$p->n = "24";
		$p->a = 25;
		
		$this->assertSame(24, $p->n);
		$this->assertEquals([25], $p->a);
	}
	
	public function test_get_ValueIsNull()
	{
		$data = ['n' => LiteSetup::createInt(null)];
		unset($data['n'][SetupFields::VALUE]);
		$values = ['n' => null];
		
		$p = new PrivateFields($values, $data, $this);
		
		$this->assertNull($p->n);
	}
	
	
	public function test_isset_ValidatedUsingData() 
	{
		$data = ['n' => []];
		$values = [];
		
		$p = new PrivateFields($values, $data, $this);
		
		$this->assertTrue(isset($p->n));
		$this->assertFalse(isset($p->a));
	}
}