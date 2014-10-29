<?php

/*
 * Session interface
 * @author Jake Josol
 * @description Interface used for implementing sessions
 */

namespace Warp\Utils\Interfaces;

interface ISession
{
	public static function Has($key);
	public static function Set($key, $value);
	public static function Get($key);
	public static function Destroy();
}

?>