<?php
namespace Objection;


class ObjectUtils
{
	use \Traitor\TStaticClass;


	/**
	 * @param string $name
	 * @param LiteObject[] $objects
	 * @return mixed[]
	 */
	public static function getProperty(string $name, array $objects): array
	{
		$result = [];
		
		foreach ($objects as $object)
		{
			$result[] = $object->$name;
		}
		
		return $result;
	}
	
	/**
	 * @param string[] $names
	 * @param LiteObject[] $objects
	 * @return mixed[]
	 */
	public static function getProperties(array $names, array $objects): array
	{
		$result = [];
		
		foreach ($objects as $object)
		{
			$result[] = $object->toArray($names);
		}
		
		return $result;
	}


	/**
	 * @param LiteObject[] $source
	 * @param string[] $propertiesMap
	 * @param string $targetClassName
	 * @return array|LiteObject[]
	 */
	public static function map(array $source, array $propertiesMap, string $targetClassName): array
	{
		$fields		= LiteObject::allToArray($source, array_keys($propertiesMap));
		$result		= [];
		
		foreach ($fields as $singleObjectFields)
		{
			/** @var LiteObject $object */
			$object = new $targetClassName;
			$result[] = $object;
			
			$object->fromArray(
				array_combine(
					$propertiesMap, 
					$singleObjectFields
				)
			);
		}
		
		return $result;
	}
}