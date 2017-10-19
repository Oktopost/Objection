<?php
namespace Objection\Mapper\Fields;


use Objection\Mapper\Base\Fields\IFieldMapper;


class SnakeCase implements IFieldMapper
{
	use \Traitor\TSingleton;
	
	
	/**
	 * @param string $field
	 * @return string|bool
	 */
	public function map($field)
	{
		return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $field));
	}
}