<?php
namespace Objection\Structure;


class HashSet implements \IteratorAggregate, \Countable, \ArrayAccess
{
	private $set = [];
	
	
	/**
	 * @param int|string|array $keys
	 */
	public function __construct($keys = []) 
	{
		$this->add($keys);
	}
	
	
	/**
	 * @param string|int|array $key Single element or array of elements. 
	 */
	public function add($key)
	{
		if (!is_array($key))
		{
			$keys = [$key => null];
		}
		else
		{
			$keys = array_combine($key, array_fill(0, count($key), null));
		}
		
		$this->set += $keys;
	}
	
	/**
	 * @param string|int|array $key
	 */
	public function remove($key)
	{
		if (!is_array($key))
		{
			unset($this->set[$key]);
		}
		else
		{
			foreach ($key as $singleKey)
			{
				unset($this->set[$singleKey]);
			}
		}
	}
	
	/**
	 * @param string|int $key
	 * @return bool
	 */
	public function has($key)
	{
		return array_key_exists($key, $this->set);
	}
	
	/**
	 * @param array $keys
	 * @return bool
	 */
	public function hasAll(array $keys)
	{
		foreach ($keys as $key)
		{
			if (!array_key_exists($key, $this->set))
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * @param array $keys
	 * @return bool
	 */
	public function hasAny(array $keys)
	{
		foreach ($keys as $key)
		{
			if (array_key_exists($key, $this->set))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * @return int
	 */
	public function count(): int
	{
		return count($this->set);
	}
	
	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return !((bool)$this->set);
	}
	
	/**
	 * @return array
	 */
	public function getKeys()
	{
		return array_keys($this->set);
	}
	
	/**
	 * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return \Traversable
	 */
	public function getIterator(): \Traversable
	{
		return new \ArrayIterator($this->getKeys());
	}
	
	/**
	 * @link http://php.net/manual/en/serializable.serialize.php
	 * @return string
	 */
	public function __serialize()
	{
		return serialize(array_keys($this->set));
	}
	
	/**
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 * @param string $serialized
	 */
	public function __unserialize($serialized)
	{
		$keys = unserialize($serialized);
		$this->set = array_combine($keys, array_fill(0, count($keys), null));
	}
	
	
	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return $this->has($offset);
	}
	
	/**
	 * @param string|int $offset
	 * @return bool
	 */
	public function offsetGet($offset): mixed
	{
		return $this->has($offset);
	}
	
	/**
	 * @param string|int $offset <p>
	 * @param bool $value
	 */
	public function offsetSet($offset, $value): void
	{
		if ($value)
		{
			$this->add($offset);
		}
		else
		{
			$this->remove($offset);
		}
	}
	
	/**
	 * @param string|int $offset
	 */
	public function offsetUnset($offset): void
	{
		$this->remove($offset);
	}
}