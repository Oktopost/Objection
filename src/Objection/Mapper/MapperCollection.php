<?php
namespace Objection\Mapper;


use Objection\Mapper\Base\IMapperCollection;
use Objection\Mapper\Base\Fields\IBidirectionalMapper;
use Objection\Exceptions\LiteObjectException;


class MapperCollection implements IMapperCollection
{
	/** @var IBidirectionalMapper */
	private $default;
	
	/** @var IBidirectionalMapper[] */
	private $mappers = [];
	
	
	/**
	 * @param string $class
	 * @return string
	 */
	private function getClassName($class)
	{
		return (is_string($class) ?
			$class :
			get_class($class ?? $this));
	}
	
	
	/**
	 * @param IBidirectionalMapper|null $default
	 */
	public function __construct(IBidirectionalMapper $default = null)
	{
		$this->default = $default;
	}
	
	
	/**
	 * @return IBidirectionalMapper
	 */
	public function getDefault()
	{
		return $this->default;
	}
	
	/**
	 * @param IBidirectionalMapper $mapper
	 * @return static
	 */
	public function setDefault(IBidirectionalMapper $mapper)
	{
		$this->default = $mapper;
		return $this;
	}
	
	/**
	 * @param string $className
	 * @return bool
	 */
	public function has($className)
	{
		$className = $this->getClassName($className);
		
		return isset($this->mappers[$className]);
	}
	
	/**
	 * @param string $className
	 * @return IBidirectionalMapper|null
	 */
	public function get($className)
	{
		$className = $this->getClassName($className);
		
		return (isset($this->mappers[$className]) ?
			$this->mappers[$className] :
			null);
	}
	
	/**
	 * @param string $className
	 * @return IBidirectionalMapper
	 */
	public function getOrDefault($className)
	{
		$className = $this->getClassName($className);
		
		if (isset($this->mappers[$className]))
		{
			return $this->mappers[$className];
		}
		else if ($this->default)
		{
			return $this->default;
		}
		else
		{
			throw new LiteObjectException("No mapper defined for class '$className'");
		}
	}
	
	/**
	 * @param string $className
	 * @param IBidirectionalMapper $mapper
	 * @return static
	 */
	public function set($className, IBidirectionalMapper $mapper)
	{
		$className = $this->getClassName($className);
		
		$this->mappers[$className] = $mapper;
		return $this;
	}
}