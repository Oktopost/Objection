<?php
namespace Objection;


use Objection\Exceptions;
use Objection\Enum\SetupFields;
use Objection\Enum\AccessRestriction;
use Objection\Utils\ArrayParser;
use Objection\Utils\PrivateFields;
use Objection\Setup\Container;
use Objection\Setup\ValueValidation;


abstract class LiteObject
{
	/** @var array */
	private $data;
	
	/** @var array */
	private $values;
	
	private $ignoreSet;
	
	
	/** @var static */
	protected $_p;
	
	
	/**
	 * @param string $field
	 * @param mixed $value
	 */
	private function invokeOnSet($field, $value) 
	{
		$field = ucfirst($field);
		$method = [$this, "onSet$field"];
		
		if (is_callable($method)) 
		{
			$method($value);
		}
	}
	
	/**
	 * @param string $field
	 * @param int|bool $accessRestriction
	 * @throws \Exception
	 */
	private function validateFieldAccess($field, $accessRestriction = false)
	{
		if (!isset($this->data[$field]))
			throw new Exceptions\PropertyNotFoundException($this, $field);
		
		if ($accessRestriction !== false && isset($this->data[$field][SetupFields::ACCESS][$accessRestriction]))
		{
			if ($accessRestriction == AccessRestriction::NO_GET)
			{
				throw new Exceptions\WriteOnlyPropertyException($this, $field);
			}
			else
			{
				throw new Exceptions\ReadOnlyPropertyException($this, $field);
			}
		}
	}
	
	/**
	 * Keep only declared object fields before tolerant hydration.
	 */
	private function filterArrayForHydration(array|\stdClass $source): array
	{
		return array_intersect_key((array)$source, $this->data);
	}
	
	
	/**
	 * @return array
	 */
	protected abstract function _setup();
	
	
	public function __construct() 
	{
		$className = get_class($this);
		
		if (!Container::instance()->has($className))
			Container::instance()->set($className, $this->_setup());
		
		$this->data = Container::instance()->get($className);
		$this->values = Container::instance()->getValues($className);
		
		$this->_p = new PrivateFields($this->values, $this->data, $this);
	}
	
	
	/**
	 * @param array|\stdClass $source
	 * @param bool $ignoreGetOnly Don't thrown an exception if Get only property found in the array
	 * @return static
	 */
	public function fromArray($source, $ignoreGetOnly = true)
	{
		ArrayParser::fromArray($this, $source, $ignoreGetOnly);
		return $this;
	}
	
	/**
	 * Hydrate declared fields and ignore unknown source keys.
	 * 
	 * @param array|\stdClass $source
	 * @param bool $ignoreGetOnly Don't thrown an exception if Get only property found in the array
	 * @return static
	 */
	public function fromArrayFiltered(array|\stdClass $source, bool $ignoreGetOnly = true): static
	{
		$this->fromArray($this->filterArrayForHydration($source), $ignoreGetOnly);
		return $this;
	}
	
	/**
	 * @param array $filter
	 * @param array $exclude If set, $filter is ignored
	 * @return array
	 */
	public function toArray(array $filter = [], array $exclude = [])
	{
		return ArrayParser::toArray($this, $filter, $exclude);
	}
	
	/**
	 * @param string $field
	 * @return bool
	 */
	public function isRestricted($field)
	{
		$this->validateFieldAccess($field);
		return isset($this->data[$field][SetupFields::ACCESS]);
	}
	
	/**
	 * @param array $exclude
	 * @return array
	 */
	public function getPropertyNames(array $exclude = [])
	{
		if (!$exclude) return array_keys($this->data);
		
		return array_values(array_diff(array_keys($this->data), $exclude));
	}
	
	
	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name) 
	{
		$this->validateFieldAccess($name, AccessRestriction::NO_GET);
		
		// Prevent returning parameter by reference
		if (isset($this->data[$name][SetupFields::ACCESS][AccessRestriction::NO_SET]))
		{
			$data = $this->values[$name];
			return $data;
		}
		
		return $this->values[$name];
	}
	
	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value) 
	{
		if ($this->ignoreSet)
		{
			$this->$name = $value;
			return;
		}
		
		$this->validateFieldAccess($name, AccessRestriction::NO_SET);
		
		$value = ValueValidation::fixValue($this->data[$name], $value);
		$this->values[$name] = $value;
		
		$this->invokeOnSet($name, $value);
	}
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name) 
	{
		return isset($this->data[$name]);
	}
	
	/**
	 * @return array
	 */
	public function __debugInfo()
	{
		return $this->values;
	}
	
	public function __clone()
	{
		$values = $this->values;
		
		unset($this->values);
		unset($this->_p);
		
		$this->ignoreSet = true;
		$this->values = unserialize(serialize($values));
		$this->_p = new PrivateFields($this->values, $this->data, $this);
		$this->ignoreSet = false;
	}


	/**
	 * @param LiteObject[] $objects
	 * @param array $filter
	 * @param array $exclude If set, $filter is ignored
	 * @return array
	 */
	public static function allToArray(array $objects, array $filter = [], array $exclude = [])
	{
		return ArrayParser::allToArray($objects, $filter, $exclude);
	}
	
	/**
	 * @param array $mapsSet
	 * @return LiteObject[]
	 */
	public static function allFromArray(array $mapsSet)
	{
		return ArrayParser::allFromArray(static::class, $mapsSet);
	}
	
	/**
	 * Hydrate multiple objects while ignoring unknown source keys.
	 * 
	 * @param array $mapsSet
	 * @return LiteObject[]
	 */
	public static function allFromArrayFiltered(array $mapsSet): array
	{
		return ArrayParser::allFromArrayFiltered(static::class, $mapsSet);
	}
}