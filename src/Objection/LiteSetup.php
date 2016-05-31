<?php
namespace Objection;


use Objection\Enum\VarType;
use Objection\Enum\SetupFields;


class LiteSetup 
{
	use \Objection\TSingleton;
	
	
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
		
		return self::create($class, $default, true, $access);
	}
	
	/**
	 * @param array|string $set All possible values for this field.
	 * @param string|null|bool $default
	 * @param bool $isNull
	 * @param int|bool $access
	 * @return array
	 * @throws \Exception
	 */
	public static function createEnum($set, $default = false, $isNull = false, $access = false)
	{
		if (!is_string($set) && !is_array($set)) throw new \Exception('$set must be array or class name.');

		if (is_string($set) && class_exists($set) && in_array(TConstsClass::class, class_uses($set))) $set = $set::getConstValues();

		if ($default === false) $default = $set[0];
		
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
}