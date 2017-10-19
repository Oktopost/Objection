<?php
namespace Objection;


use Objection\Extensions;
use Objection\Enum\VarType;
use Objection\Enum\SetupFields;


class LiteSetup 
{
	use \Traitor\TStaticClass;
	
	
	/**
	 * @param \Traitor\TConstsClass|array $set
	 * @return mixed
	 * @throws Exceptions\InvalidPropertySetupException
	 */
	private static function getValuesFromConstsClass($set)
	{
		if (is_string($set) && class_exists($set))
		{
			$traits = class_uses($set);
			
			if (in_array(\Traitor\TConstsClass::class, $traits))
			{
				/** @var \Traitor\TConstsClass $set */
				return $set::getConstValues();
			}
			else if (in_array(\Traitor\TEnum::class, $traits))
			{
				/** @var \Traitor\TEnum $set */
				return $set::getAll();
			}
		}
		
		throw new Exceptions\InvalidPropertySetupException(
			'createEnum accepts only array of values, TConstsClass class or TEnum class');
	}
	
	
	/**
	 * @param string $type
	 * @param mixed $default
	 * @param bool $isNull
	 * @param int|bool $access
	 * @return array
	 */
	public static function create($type, $default, $isNull = false, $access = false)
	{
		$data = [
			SetupFields::TYPE => $type, 
			SetupFields::VALUE => $default
		];
		
		if (is_null($default) || $isNull)
			$data[SetupFields::IS_NULL] = true;
		
		if ($access !== false) 
			$data[SetupFields::ACCESS] = [$access => true];
		
		return $data;
	}
	
	public static function createInt($default = 0, $access = false) 
	{
		return self::create(VarType::INT, $default, false, $access);
	}
	
	public static function createString($default = '', $access = false)
	{
		return self::create(VarType::STRING, $default, false, $access);
	}
	
	public static function createDouble($default = 0.0, $access = false)
	{
		return self::create(VarType::DOUBLE, $default, false, $access);
	}
	
	public static function createBool($default = false, $access = false) 
	{
		return self::create(VarType::BOOL, $default, false, $access);
	}
	
	public static function createArray($default = [], $access = false)
	{
		if (!is_null($default) && !is_array($default)) $default = [$default];
		
		return self::create(VarType::ARR, $default, false, $access);
	}
	
	public static function createMixed($default = false, $access = false)
	{
		return self::create(VarType::MIXED, $default, false, $access);
	}
	
	public static function createInstanceOf($class, $access = false)
	{
		$default = null;
		
		if (!is_string($class))
		{
			$default = $class;
			$class = get_class($class);
		}
		
		$data = [
			SetupFields::TYPE			=> VarType::INSTANCE,
			SetupFields::VALUE 			=> $default,
			SetupFields::IS_NULL		=> true,
			SetupFields::INSTANCE_TYPE	=> $class
		];
		
		if ($access !== false)
		{
			$data[SetupFields::ACCESS] = [$access => true];
		}
		
		return $data;
	}
	
	public static function createInstanceArray($class, $access = false)
	{
		$data = [
			SetupFields::TYPE			=> VarType::INSTANCE_ARRAY,
			SetupFields::VALUE 			=> [],
			SetupFields::INSTANCE_TYPE	=> $class
		];
		
		if ($access !== false)
		{
			$data[SetupFields::ACCESS] = [$access => true];
		}
		
		return $data;
	}
	
	/**
	 * @param array|string $set All possible values for this field.
	 * @param string|null|bool $default
	 * @param bool $isNull
	 * @param int|bool $access
	 * @return array
	 */
	public static function createEnum($set, $default = false, $isNull = false, $access = false)
	{
		if (!is_array($set))
			$set = self::getValuesFromConstsClass($set);

		if ($default === false)
			$default = $set[0];
		
		$set = array_flip($set);
		
		$data = [
			SetupFields::TYPE			=> VarType::ENUM,
			SetupFields::VALUE			=> $default,
			SetupFields::VALUES_SET		=> $set,
		];
		
		if (is_null($default) || $isNull)
			$data[SetupFields::IS_NULL] = true;
		
		if ($access !== false)
			$data[SetupFields::ACCESS] = [$access => true];
		
		return $data;
	}
	
	public static function createDateTime($default = 'now', $isNull = false, $access = false)
	{
		if (is_int($default))
		{
			$default = (new \DateTime())->setTimestamp($default);
		}
		else if (is_string($default))
		{
			$default = new \DateTime((string)$default);
		}
		else if (is_null($default))
		{
			$isNull = true;
		}
		else if (!$default instanceof \DateTime)
		{
			throw new Exceptions\InvalidDatetimeValueTypeException($default);
		}
		
		return self::create(VarType::DATE_TIME, $default, $isNull, $access);
	}
	
	/**
	 * @param Extensions\ICustomProperty $customProperty
	 * @return array
	 */
	public static function createCustomProperty(Extensions\ICustomProperty $customProperty)
	{
		return [
			SetupFields::TYPE			=> VarType::CUSTOM,
			SetupFields::CUSTOM_HANDLER	=> $customProperty
		];
	}
}