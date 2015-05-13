<?php

/**
 * URL class
 * @author Jake Josol
 * @description Base class for all urls
 */

namespace Warp\Http;

use Warp\Core\Resource;
use Warp\Foundation\Model;

class URL
{
	private function __construct() {}

	public static function To($url)
	{
		if($url instanceof Model) $url = "api/1/".str_replace("Model", "", get_class($url));
		return "http://".Router::GetServer()."/".Router::GetPath().$url;
	}
}