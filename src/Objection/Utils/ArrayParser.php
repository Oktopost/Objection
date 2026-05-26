<?php
namespace Objection\Utils;


use Objection\LiteObject;
use Objection\Enum\SetupFields;
use Objection\Enum\AccessRestriction;
use Objection\Setup\Container;


class ArrayParser
{
	/**
	 * @param LiteObject $object
	 * @return array
	 */
	private static function getObjectSetup(LiteObject $object)
	{
		return Container::instance()->get(get_class($object));
	}
	
	
	/**
	 * @param LiteObject $object
	 * @param array|\stdClass $source
	 * @param bool $ignoreGetOnly Don't thrown an exception if Get only property found in the array
	 * @return static
	 */
	public static function fromArray(LiteObject $object, $source, $ignoreGetOnly = true)
	{
		$objectSetup = self::getObjectSetup($object);
		
		foreach ($source as $property => $value)
		{
			if ($ignoreGetOnly && isset($objectSetup[$property][SetupFields::ACCESS][AccessRestriction::NO_SET]))
				continue;
			
			$object->$property = $value;
		}
	}
	
	/**
	 * @param LiteObject $object
	 * @param array $filter
	 * @param array $exclude If set, $filter is ignored
	 * @return array
	 */
	public static function toArray(LiteObject $object, array $filter = [], array $exclude = [])
	{
		$objectSetup = self::getObjectSetup($object);
		
		$result = [];
		
		if ($exclude) $filter = $object->getPropertyNames($exclude);
		
		if ($filter)
		{
			foreach ($filter as $property)
			{
				if (!isset($objectSetup[$property][SetupFields::ACCESS][AccessRestriction::NO_GET]))
					$result[$property] = $object->$property;
			}
		}
		else
		{
			foreach ($objectSetup as $property => $data)
			{
				if (!isset($objectSetup[$property][SetupFields::ACCESS][AccessRestriction::NO_GET]))
					$result[$property] = $object->$property;
			}
		}
		
		return $result;
	}
	
	/**
	 * @param LiteObject[] $objects
	 * @param array $filter
	 * @param array $exclude If set, $filter is ignored
	 * @return array
	 */
	public static function allToArray(array $objects, array $filter = [], array $exclude = [])
	{
		array_walk($objects,
			function(&$object)
				use ($filter, $exclude)
			{
				/** @var LiteObject $object */
				$object = self::toArray($object, $filter, $exclude);
			});
		
		return $objects;
	}
	
	/**
	 * @param string $className
	 * @param array $mapsSet
	 * @return LiteObject[]
	 */
	public static function allFromArray($className, array $mapsSet)
	{
		array_walk($mapsSet,
			function(&$map)
				use ($className)
			{
				/** @var LiteObject $object */
				$object = new $className; 
				$map = $object->fromArray($map);
			});
		
		return $mapsSet;
	}
	
	/**
	 * Hydrate multiple objects through LiteObject's filtered hydration path.
	 * 
	 * @param string $className
	 * @param array $mapsSet
	 * @return LiteObject[]
	 */
	public static function allFromArrayFiltered(string $className, array $mapsSet): array
	{
		array_walk($mapsSet,
			function(&$map)
				use ($className)
			{
				/** @var LiteObject $object */
				$object = new $className;
				$map = $object->fromArrayFiltered($map);
			});
		
		return $mapsSet;
	}
}