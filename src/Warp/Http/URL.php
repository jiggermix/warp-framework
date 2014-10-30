<?php

/**
 * URL class
 * @author Jake Josol
 * @description Base class for all urls
 */

namespace Warp\Http;

use Warp\Core\Resource;

class URL
{
	private function __construct() {}

	public static function To($url)
	{
		return "//".Router::GetServer()."/".Router::GetURL().$url;
	}
}

?>