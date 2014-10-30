<?php

/**
 * Navigate class
 * @author Jake Josol
 * @description Base class for all navigation
 */

namespace Warp\Http;

class Navigate
{
	private function __construct() {}

	public static function To($url)
	{
		$path = $url;

		header("Location: {$path}");
	}

	public static function Within($url)
	{
		$path = URL::To($url);

		static::To($path);
	}
}

?>