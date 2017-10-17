<?php
namespace Objection;


/**
 * @deprecated use Traitor::TConstsClass instead
 */
trait TConstsClass 
{
	use TStaticClass;
	
	
	/** @var array */
	private static $constsCollection = false;
	
	
	private static function loadConsts()
	{
		if (!self::$constsCollection)
			self::$constsCollection = (new \ReflectionClass(__CLASS__))->getConstants();
	}
	
	
	public static function getConstsAsArray() 
	{
		self::loadConsts();
		return self::$constsCollection;
	}
	
	public static function getConstNames() 
	{
		self::loadConsts();
		return array_keys(self::$constsCollection);
	}
	
	public static function getConstValues() 
	{
		self::loadConsts();
		return array_values(self::$constsCollection);
	}
	
	public static function getConstsCount() 
	{
		self::loadConsts();
		return count(self::$constsCollection);
	}
	
	public static function isConstExists($name)
	{
		self::loadConsts();
		return isset(self::$constsCollection[$name]);
	}
	
	public static function isConstValueExists($value)
	{
		self::loadConsts();
		return in_array($value, self::$constsCollection, true);
	}
}