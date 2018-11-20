<?php
namespace Objection\Exceptions;


class MapperException extends LiteObjectException
{
	/**.
	 * @param string $originalData
	 * @param \Exception $previous
	 */
	public function __construct($originalData, \Exception $previous)
	{
		if (!is_string($originalData))
			$originalData = jsonencode($originalData);
		
		parent::__construct("An error was caught while mapping object: {$previous->getMessage()}. " . 
			"Json of source data: '$originalData'", 42, $previous);
	}
}