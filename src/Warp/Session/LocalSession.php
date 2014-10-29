<?php

/**
 * Local Session class
 * @author Jake Josol
 * @description Utility class for implementing local sessions
 */

namespace Warp\Session;

use Warp\Utils\Interfaces\ISession;

class LocalSession implements ISession
{
	public static function Has($key)
	{
		return isset($_SESSION[$key]);
	}
	
	public static function Set($key, $value)
	{
		$_SESSION[$key] = $value;
	}
	
	public static function Get($key)
	{
		return $_SESSION[$key];
	}
	
	public static function Destroy()
	{
		session_unset();
	}
}

?>