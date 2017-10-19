<?php
namespace Objection\Setup;


use Objection\Exceptions;
use Objection\Enum\VarType;
use Objection\Enum\SetupFields;


class ValueValidation 
{
	use \Traitor\TStaticClass;
	
	
	/**
	 * @param string $instanceType
	 * @param mixed $value
	 * @throws Exceptions\InvalidValueTypeException
	 */
	private static function throwInvalidValueForInstanceArray($instanceType, $value)
	{
		throw new Exceptions\InvalidValueTypeException(
			$instanceType . '[]',
			$value);
	}
	
	/**
	 * @param array $fieldData
	 * @param string $value
	 * @return array
	 */
	private static function fixInstanceArray($fieldData, $value)
	{
		$type = $fieldData[SetupFields::INSTANCE_TYPE];
		
		if (!$value)
		{
			return [];
		}
		else if ($value instanceof $type)
		{
			return [$value];
		}
		else if (!is_array($value))
		{
			self::throwInvalidValueForInstanceArray($type, $value);
		}
			
		foreach ($value as $element)
		{
			if (!($element instanceof $type))
			{
				self::throwInvalidValueForInstanceArray($type, $value);
			}
		}
		
		return $value;
	}
	
	/**
	 * @param string $value
	 * @return \DateTime
	 */
	private static function fixDateTime($value)
	{
		if ($value instanceof \DateTime)
		{
			$value = clone $value;
		}
		else if (is_string($value))
		{
			$value = new \DateTime($value);
		}
		else if (is_int($value))
		{
			$value = (new \DateTime())->setTimestamp($value);
		}
		else
		{
			throw new Exceptions\InvalidDatetimeValueTypeException($value);
		}
		
		return $value;
	}
	
	/**
	 * @param array $fieldData
	 * @param mixed $value
	 * @return mixed
	 */
	public static function fixValue($fieldData, $value) 
	{
		if (is_null($value) && isset($fieldData[SetupFields::IS_NULL]))
			return null;
		
		switch ($fieldData[SetupFields::TYPE]) 
		{
			case VarType::MIXED:
				break;
			
			case VarType::BOOL:
				$value = (bool)$value;
				break;
			
			case VarType::INT:
				$value = (int)$value;
				break;
			
			case VarType::STRING:
				$value = (string)$value;
				break;
			
			case VarType::DOUBLE:
				$value = (double)$value;
				break;
			
			case VarType::ARR:
				if (!is_array($value))
					$value = [$value];
				
				break;
			
			case VarType::ENUM:
				if (!isset($fieldData[SetupFields::VALUES_SET][$value]))
					throw new Exceptions\InvalidEnumValueTypeException($fieldData[SetupFields::VALUES_SET], $value);
				
				break;
			
			case VarType::DATE_TIME:
				$value = self::fixDateTime($value);
					
				break;	
			
			case VarType::INSTANCE:
				if (!$value instanceof $fieldData[SetupFields::INSTANCE_TYPE])
					throw new Exceptions\InvalidValueTypeException($fieldData[SetupFields::INSTANCE_TYPE], $value);
				
				break;
			
			case VarType::INSTANCE_ARRAY:
				$value = self::fixInstanceArray($fieldData, $value);
				break;
			
			default:
				throw new Exceptions\InvalidValueTypeException($fieldData[SetupFields::TYPE], $value);
		}
		
		return $value;
	}
}