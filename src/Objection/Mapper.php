<?php
namespace Objection;


use Objection\Mapper\MapperCollectionBuilder;
use Objection\Mapper\Base\IMapperCollection;
use Objection\Mapper\Base\IObjectToTargetBuilder;
use Objection\Mapper\DataBuilders\ArrayTargetBuilder;
use Objection\Mapper\DataBuilders\StdClassTargetBuilder;
use Objection\Exceptions\LiteObjectException;
use Objection\Mapper\ObjectMapper;


class Mapper
{
	private $className;
	
	/** @var IMapperCollection */
	private $collection = null;
	
	
	private function __constructor() {}
	
	
	/**
	 * @param string $className
	 * @return string
	 */
	private function validateClassNameSet($className)
	{
		$className = ($className ?: $this->className);
		
		if (!$className)
			throw new LiteObjectException("Default class name not set. See method Mapper::setDefaultClassName.");  
		
		if (!class_exists($className))
			throw new LiteObjectException("Class $className does not exists");
		
		return $className;
	}
	
	/**
	 * @param string $className
	 */
	private function validateCollection($className)
	{
		if (!$this->collection)
			throw new LiteObjectException("Collection of mappers must be set. See method Mapper::setMappersCollection.");
		
		if (!$this->collection->has($className) && !$this->collection->getDefault())
			throw new LiteObjectException("Mappers collection missing map for target class name: $className. " .
				'And no default mapper defined');
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param IObjectToTargetBuilder $builder
	 * @return mixed
	 */
	private function getData($object, IObjectToTargetBuilder $builder)
	{
		if (is_array($object))
		{
			$result = [];
			
			foreach ($object as $singleItem)
			{
				$result[] = $this->getData($singleItem, $builder);
			}
			
			return $result;
		}
		
		$this->validateCollection(get_class($object));
		
		return ObjectMapper::fromObject($object, $this->collection, $builder);
	}
	
	/**
	 * @param string|LiteObject|LiteObject[] $data
	 * @return array
	 */
	private static function getClassName($data)
	{
		if (is_string($data))
		{
			return $data;
		}
		else if ($data instanceof LiteObject)
		{
			return get_class(current($object));
		}
		else if (is_array($data) && reset($object) && reset($object) instanceof LiteObject) 
		{
			return get_class(reset($object));
		}
		
		throw new LiteObjectException('Unexpected input: Parameter must be LiteObject, LiteObject[] or string');
	}
	
	
	/**
	 * @param string $className
	 * @return static
	 */
	public function setDefaultClassName($className)
	{
		$this->className = $className;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDefaultClassName()
	{
		return $this->className;
	}
	
	/**
	 * @param IMapperCollection $collection
	 * @return static
	 */
	public function setMappersCollection(IMapperCollection $collection)
	{
		$this->collection = $collection;
		return $this;
	}
	
	/**
	 * @return IMapperCollection
	 */
	public function getMappersCollection()
	{
		return $this->collection;
	}
	
	/**
	 * @param string|array|\stdClass $data
	 * @param string|bool $className
	 * @return LiteObject
	 */
	public function getObject($data, $className = false)
	{
		$className = $this->validateClassNameSet($className);
		$this->validateCollection($this->className);
		
		ObjectMapper::toObject($className, $data, $this->collection);
	}
	
	/**
	 * @param string[]|array[]|\stdClass[] $data
	 * @param string|bool $className
	 * @return LiteObject
	 */
	public function getObjects(array $data, $className = false)
	{
		$result = [];
		
		foreach ($data as $singleItem)
		{
			$result[] = $this->getObject($singleItem, $className);
		}
		
		return $result;
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param int $flags Optional JSON_* flags.
	 * @return string
	 */
	public function getJson($object, $flags = 0)
	{
		return json_encode($this->getData($object, new StdClassTargetBuilder()), $flags);
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @return \stdClass|\stdClass[]
	 */
	public function getStdClass($object)
	{
		return $this->getData($object, new StdClassTargetBuilder());
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @return array|array[]
	 */
	public function getArray($object)
	{
		return $this->getData($object, new ArrayTargetBuilder());
	}
	
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param array ...$mappersData
	 * @return array|array[]
	 */
	public static function getArrayFor($object, ...$mappersData)
	{
		return self::create(...$mappersData)->getArray($object);
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param array ...$mappersData
	 * @return \stdClass|\stdClass[]
	 */
	public static function getStdClassFor($object, ...$mappersData)
	{
		return self::create(...$mappersData)->getJson($object);
	}
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param array ...$mappersData
	 * @return string
	 */
	public static function getJsonFor($object, ...$mappersData)
	{
		return self::create(...$mappersData)->getJson($object);
	}
	
	/**
	 * @param string $className
	 * @param string|array|\stdClass $data
	 * @param array ...$mappersData
	 * @return LiteObject
	 */
	public static function getObjectFrom($className, $data, ...$mappersData)
	{
		return self::createFor($className, ...$mappersData)->getObject($data);
	}
	
	/**
	 * @param string $className
	 * @param string[]|array[]|\stdClass[] $data
	 * @param array ...$mappersData
	 * @return LiteObject[]
	 */
	public static function getObjectsFrom($className, $data, ...$mappersData)
	{
		return self::createFor($className, ...$mappersData)->getObjects($data);
	}
	
	/**
	 * @param string $className Object or class name.
	 * @param array ...$mappersData
	 * @return Mapper
	 */
	public static function createFor($className, ...$mappersData)
	{
		return self::create(...$mappersData)->setDefaultClassName(self::getClassName($className));
	}
	
	/**
	 * @param array ...$mappersData
	 * @return Mapper
	 */
	public static function create(...$mappersData)
	{
		return (new Mapper())->setMappersCollection(MapperCollectionBuilder::createFrom(...$mappersData));
	}
}