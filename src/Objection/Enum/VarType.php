<?php
namespace Objection\Enum;


class VarType 
{
	use \Traitor\TEnum;
	
	
	const INT				= 'int';
	const STRING			= 'string';
	const DOUBLE			= 'double';
	const BOOL				= 'bool';
	const MIXED				= 'mixed';
	const ENUM				= 'enum';
	const ARR				= 'array';
	const DATE_TIME			= 'DateTime';
	const CUSTOM			= 'custom';
	const INSTANCE			= 'instance';
	const INSTANCE_ARRAY	= 'instance_array';
}