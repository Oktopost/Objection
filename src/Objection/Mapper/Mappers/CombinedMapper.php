<?php
namespace Objection\Mapper\Mappers;


use Objection\Mapper\Base\Fields\IFieldMapper;
use Objection\Mapper\Base\Fields\IBidirectionalMapper;
use Objection\Exceptions\LiteObjectException;


class CombinedMapper implements IBidirectionalMapper
{
	private $fromObject;
	private $toObject;
	
	
	/**
	 * @param IFieldMapper|null $fromObject
	 * @param IFieldMapper|null $toObject
	 */
	public function __construct(
		?IFieldMapper $fromObject = null,
		?IFieldMapper $toObject = null)
	{
		$this->fromObject = $fromObject;
		$this->toObject = $toObject;
	}
	
	
	/**
	 * @param IFieldMapper $fieldMapper
	 * @return static
	 */
	public function setFieldMapper(IFieldMapper $fieldMapper)
	{
		$this->toObject = $fieldMapper;
		$this->fromObject = $fieldMapper;
		return $this;
	}
	
	/**
	 * @param IFieldMapper $fieldMapper
	 * @return static
	 */
	public function setToObjectFieldMapper(IFieldMapper $fieldMapper)
	{
		$this->toObject = $fieldMapper;
		return $this;
	}
	
	/**
	 * @param IFieldMapper $fieldMapper
	 * @return static
	 */
	public function setFromObjectMapper(IFieldMapper $fieldMapper)
	{
		$this->toObject = $fieldMapper;
		return $this;
	}
	
	
	/**
	 * @param string $rowField
	 * @param string $className
	 * @return string
	 */
	public function mapToObjectField($rowField, $className)
	{
		if (!$this->toObject)
			throw new LiteObjectException('There is no data field to object field mapper set');
		
		return $this->toObject->map($rowField);
	}
	
	/**
	 * @param string $objectField
	 * @return string
	 */
	public function mapFromObjectField($objectField)
	{
		if (!$this->fromObject)
			throw new LiteObjectException('There is no object field to data field mapper set');
		
		return $this->fromObject->map($objectField);
	}
}