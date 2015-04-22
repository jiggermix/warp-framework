<?php

/*
 * {{class}}
 * @author {{author}}
 * @description {{class}}
 */
 
use Warp\Utils\Enumerations\SystemField;
use Warp\Utils\Enumerations\InputType;

class {{class}} extends Model
{
	protected static $source = "{{source}}";
	protected static $key = "{{id}}";
	protected static $fields = array();

	protected static function build()
	{
		self::Has(SystemField::ID)->Increment();
	}
}