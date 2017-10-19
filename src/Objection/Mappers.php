<?php
namespace Objection;


use Objection\Mapper\Mappers\HashMapMapper;
use Objection\Mapper\Mappers\SnakeCaseMapper;


class Mappers
{
	use \Traitor\TStaticClass;
	
	
	/**
	 * No changes are made to the fields map.
	 * @return Mapper
	 */
	public static function simple()
	{
		return Mapper::create();
	}
	
	/**
	 * @param array $objectFieldsToDataFields
	 * @param array $dataFieldsToObjectFields
	 * @return Mapper
	 */
	public static function mapped(array $objectFieldsToDataFields, array $dataFieldsToObjectFields = [])
	{
		return Mapper::create(new HashMapMapper($objectFieldsToDataFields, $dataFieldsToObjectFields));
	}
	
	/**
	 * Object "SomeID" to data "some_id"
	 * @return Mapper
	 */
	public static function snakeCase()
	{
		return Mapper::create(new SnakeCaseMapper());
	}
}