<?php
namespace Objection\Setup;


use Objection\Enum\SetupFields;
use Objection\Exceptions\LiteObjectException;


class Container 
{
	use \Traitor\TSingleton;
	
	
	/** @var array[] */
	private $setup = [];
	
	/** @var array */
	private $values = [];
	
	
	/**
	 * @param string $className
	 */
	private function extractValues($className)
	{
		$this->values[$className] = [];
		
		foreach ($this->setup[$className] as $propertyName => $propertySetup)
		{
			if (key_exists(SetupFields::VALUE, $propertySetup))
			{
				$this->values[$className][$propertyName] = $propertySetup[SetupFields::VALUE];
				unset($this->setup[$className][$propertyName][SetupFields::VALUE]);
			}
		}
	}
	
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function has($className) 
	{
		return isset($this->setup[$className]);
	}
	
	/**
	 * @param string $className
	 * @param array $setup
	 */
	public function set($className, array $setup) 
	{
		if (isset($this->setup[$className]))
			throw new LiteObjectException("The class [$className] is already defined!");
		
		$this->setup[$className] = $setup;
		
		$this->extractValues($className);
	}
	
	/**
	 * @param string $className
	 * @return array
	 */
	public function get($className) 
	{
		if (!isset($this->setup[$className])) return null;
		
		return $this->setup[$className];
	}
	
	/**
	 * @param string $className
	 * @return array
	 */
	public function getValues($className)
	{
		if (!isset($this->values[$className])) return null;
		
		return $this->values[$className];
	}
}