<?php
namespace Objection\Mapper;


use Objection\Mapper\Base\IMapperCollection;
use Objection\Mapper\Base\Fields\IFieldMapper;
use Objection\Mapper\Base\Fields\IBidirectionalMapper;
use Objection\Mapper\Mappers\CombinedMapper;
use Objection\Exceptions\LiteObjectException;
use Objection\Mapper\Mappers\DummyMapper;


class MapperCollectionBuilder
{
	use \Traitor\TStaticClass;
	
	
	/**
	 * @param array ...$mapperData
	 * @return IMapperCollection
	 */
	public static function createFrom(...$mapperData)
	{
		if (count($mapperData) == 0) 
		{
			return new MapperCollection(new DummyMapper());
		}
		else if (count($mapperData) == 1)
		{
			$item = $mapperData[0]; 
			
			if ($item instanceof IMapperCollection)
			{
				return $item;
			}
			else if ($item instanceof IFieldMapper)
			{
				return new MapperCollection(new CombinedMapper($item, $item));
			}
			else if ($item instanceof IBidirectionalMapper)
			{
				return new MapperCollection($item);
			}
		}
		else if (count($mapperData) == 2)
		{
			$itemA = $mapperData[0];
			$itemB = $mapperData[1];
			
			if ($itemA instanceof IFieldMapper && $itemB instanceof IFieldMapper)
			{
				return new MapperCollection(new CombinedMapper($itemA, $itemB));
			}
		}
		
		throw new LiteObjectException('Invalid parameters supplied. Expecting one of: ' .
			'Empty set, ' . 
			'[IMapperCollection instance], ' .
			'[IFieldMapper instance], ' .
			'[IFieldMapper instance "from object mapper", IFieldMapper instance "to object mapper"], ' .
			'[IBidirectionalMapper] '
		);
	}
}