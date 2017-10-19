<?php
namespace Objection\Enum;


class AccessRestriction
{
	use \Traitor\TConstsClass;
	
	
	const NO_SET = 0;
	const NO_GET = 1;
}