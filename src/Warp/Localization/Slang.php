<?php

/**
 * Slang Class
 * @author Jason Josol
 * @description Class that is responsible for String languages
 */

namespace Warp\Localization;

use Warp\Core\Reference;
use Warp\Utils\FileHandle;

class Slang
{
	protected static $locale = "en";
	protected static $translator = array();

	/**
	 * Factory Pattern
	 * Cannot instantiate an object of this class
	 */
	private function __construct() {}

	public static function Locale($locale)
	{
		static::$locale = $locale;
	}

	public static function Of($key, $options=null)
	{
		if(!static::$translator)
		{
			$directory = Reference::Path("resource")  . "slang/" . static::$locale;
			$file = new FileHandle("default.json", $directory);
			$contents = (array) json_decode($file->Contents());
			$file->Close();

			foreach($contents as $key => $value)
				$contents[$key] = (array) $value;

			static::$translator = $contents;
		}

		if(!array_key_exists($key, static::$translator))
			return null;
		else
			if(!$options)
				if(is_array(static::$translator[$key]))
						if(count(static::$translator[$key]) > 0)
							return static::$translator[$key][array_keys(static::$translator[$key])[0]];
						else
							return null;
					else
						return static::$translator[$key];
			else
				if(array_key_exists($options, static::$translator[$key]))
					return static::$translator[$key][$options];
				else
					if(is_array(static::$translator[$key]))
						if(count(static::$translator[$key]) > 0)
							return static::$translator[$key][array_keys(static::$translator[$key])[0]];
						else
							return null;
					else
						return static::$translator[$key];
	}
}

?>