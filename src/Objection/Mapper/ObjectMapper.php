<?php
namespace Objection\Mapper;


use Objection\LiteObject;
use Objection\Enum\VarType;
use Objection\Enum\SetupFields;
use Objection\Setup\Container;
use Objection\Mapper\Base\IMapperCollection;
use Objection\Mapper\Base\IObjectToTargetBuilder;
use Objection\Mapper\Base\Extensions\IMappedObject;
use Objection\Mapper\Base\Values\IValuesProcessorContainer;
use Objection\Mapper\Loaders\MapperLoadHelpers;
use Objection\Mapper\Loaders\LoadHelpersProcessor;
use Objection\Exceptions\LiteObjectException;


class ObjectMapper
{
	use \Traitor\TStaticClass;
	
	
	/**
	 * @param LiteObject|IMappedObject $object
	 * @param IValuesProcessorContainer $container
	 * @return array
	 */
	private static function getFields(LiteObject $object, IValuesProcessorContainer $container)
	{
		if ($object instanceof IMappedObject)
		{
			$data = $object->getObjectData();
		}
		else
		{
			$data = $object->toArray();
		}
		
		$className = get_class($object);
		
		if ($container->has($className))
		{
			$data = $container->get($className)->toRawValues($data);
		}
		
		return $data;
	}
	
	/**
	 * @param LiteObject $object
	 * @param array $data
	 * @return LiteObject
	 */
	private static function setFields(LiteObject $object, $data)
	{
		if ($object instanceof IMappedObject)
		{
			$object->setObjectData($data);
		}
		else
		{
			$data = array_intersect_key($data, array_flip($object->getPropertyNames()));
			$object->fromArray($data);
		}
		
		return $object;
	}
	
	
	/**
	 * @param LiteObject $object
	 * @param IMapperCollection $collection
	 * @param \stdClass|array|null $value
	 * @param MapperLoadHelpers $loaders
	 * @param IValuesProcessorContainer $container
	 * @return mixed
	 */
	private static function getObjectFromData(LiteObject $object, 
		IMapperCollection $collection, $value, MapperLoadHelpers $loaders, 
		IValuesProcessorContainer $container)
	{
		if (is_string($value))
			$value = json_decode($value);
		
		$data = [];
		$className = get_class($object);
		$fieldMapper = $collection->getOrDefault($object);
		$setup = Container::instance()->get(get_class($object));
	
		$processor = new LoadHelpersProcessor();
		$processor->setLoadersContainer($loaders);
		
		$value = $processor->preMapProcess($className, $value);
		
		$containerHasClassName = $container->has($className);
		
		foreach ($value as $dataName => $dataValue)
		{
			$fieldName = $fieldMapper->mapToObjectField($dataName, get_class($object));
			
			if (!$fieldName || !isset($setup[$fieldName]))
			{
				continue;
			}
			
			$fieldSetup = $setup[$fieldName];
			
			if ($containerHasClassName)
			{
				$dataValue = $container->get($className)->toObjectValues([$fieldName => $dataValue]);
				$fieldValue = $dataValue[$fieldName];
			}
			else if (isset($fieldSetup[SetupFields::INSTANCE_TYPE]))
			{
				$instanceType = $fieldSetup[SetupFields::INSTANCE_TYPE];
				
				if ($fieldSetup[SetupFields::TYPE] == VarType::INSTANCE)
				{
					if (is_null($dataValue))
					{
						$fieldValue = null;
					}
					else
					{
						$fieldValue = new $instanceType;
						self::getObjectFromData($fieldValue, $collection, $dataValue, $loaders, $container);
					}
				}
				else
				{
					$fieldValue = [];
					
					foreach ($dataValue as $itemKey => $item)
					{
						$instance = new $instanceType;
						self::getObjectFromData($instance, $collection, $item, $loaders, $container);
						$fieldValue[$itemKey] = $instance;
					}
				}
			}
			else if ($fieldSetup[SetupFields::TYPE] == VarType::ARR && $dataValue instanceof \stdClass)
			{
				$fieldValue = (array)$dataValue;
			}
			else
			{
				$fieldValue = $dataValue;
			}
			
			$data[$fieldName] = $fieldValue;
		}
		
		$data = $processor->postMapProcess($className, $data);
		
		return self::setFields($object, $data);
	}
	
	private static function getDataFromLiteObject(IMapperCollection $collection, 
		IObjectToTargetBuilder $builder, LiteObject $value, IValuesProcessorContainer $container)
	{
		$fieldMapper = $collection->getOrDefault($value);
		$fields = self::getFields($value, $container);
		
		foreach ($fields as $fieldName => $fieldValue)
		{
			$dataField = $fieldMapper->mapFromObjectField($fieldName);
			
			if (!is_object($fieldValue) && !is_array($fieldValue))
			{
				$builder->set($dataField, $fieldValue);
			}
			else 
			{
				$builder->set($dataField, self::getDataFromValue($collection, $builder, $fieldValue, $container));
			}
		}
		
		return $builder->get();
	}
	
	private static function getDataFromArray(IMapperCollection $collection, 
		IObjectToTargetBuilder $builder, array $value, IValuesProcessorContainer $container)
	{
		$result = [];
		
		foreach ($value as $key => $item)
		{
			$result[$key] = self::getDataFromValue($collection, $builder, $item, $container);
		}
		
		return $result;
	}
	
	private static function getDataFromValue(IMapperCollection $collection, 
		IObjectToTargetBuilder $builder, $value, IValuesProcessorContainer $container)
	{
		if (is_array($value))
		{
			return self::getDataFromArray($collection, $builder, $value, $container);
		}
		else if (!is_object($value))
		{
			return $value;
		}
		else if ($value instanceof LiteObject)
		{
			$childBuilder = $builder->createBuilder();
			return self::getDataFromLiteObject($collection, $childBuilder, $value, $container);
		}
		else if ($value instanceof \DateTime)
		{
			return $value->format('Y-m-d H:i:s');
		}
		
		throw new LiteObjectException("Unsupported object in map: " . get_class($value));
	}
	
	
	/**
	 * @param LiteObject|LiteObject[] $object
	 * @param IMapperCollection $collection
	 * @param IObjectToTargetBuilder $builder
	 * @param IValuesProcessorContainer $container
	 * @return mixed
	 */
	public static function fromObject($object, IMapperCollection $collection, 
		IObjectToTargetBuilder $builder, IValuesProcessorContainer $container)
	{
		return self::getDataFromValue($collection, $builder, $object, $container);
	}
	
	/**
	 * @param string $className
	 * @param array|\stdClass|string $data
	 * @param IMapperCollection $collection
	 * @param MapperLoadHelpers $loaders
	 * @param IValuesProcessorContainer $container
	 * @return LiteObject
	 */
	public static function toObject($className, $data, 
		IMapperCollection $collection, MapperLoadHelpers $loaders,
		IValuesProcessorContainer $container)
	{
		if (is_string($data))
		{
			$decodedData = json_decode($data);
			
			if (!$decodedData)
			{
				$errorData = substr($data, 0, 32);
				
				if (strlen($data) > 32) $errorData .= '...';
				
				throw new LiteObjectException("Data is not a json string: '$errorData'");
			}
			
			$data = $decodedData;
		}
		
		return self::getObjectFromData(new $className(), $collection, $data, $loaders, $container);
	}
}