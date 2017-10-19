<?php
namespace Objection\Mapper\Utils;


use Objection\LiteObject;
use Objection\Setup\Container;


class ObjectFieldsContainer
{
	use \Traitor\TSingleton;
	
	
	private $lowerCaseFieldsByClassName = [];
	
	
	/**
	 * @param string $className
	 * @return array
	 */
	private function getObjectFields($className)
	{
		$setup = Container::instance()->get($className);
		
		if (!$setup)
		{
			/** @var LiteObject $instance */
			$instance = new $className;
			$fields = $instance->getPropertyNames();
		}
		else
		{
			$fields = array_keys($setup);
		}
		
		return $fields;
	}
	
	/**
	 * @param string $className
	 */
	private function loadObjectLowerCaseFields($className)
	{
		$fields = $this->getObjectFields($className);
		array_walk($fields, 'strtolower');
		
		$this->lowerCaseFieldsByClassName[$className] = $fields;
	}
	
	
	/**
	 * @param string $className
	 * @return array
	 */
	public function getLowerCaseFieldsFor($className)
	{
		if (isset($this->lowerCaseFieldsByClassName[$className]))
		{
			$this->loadObjectLowerCaseFields($className);
		}
		
		return $this->lowerCaseFieldsByClassName[$className];
	}
}