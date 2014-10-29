<?php

/***
  * Input class
  * @author Jake Josol
  * @description Responsible for all inputs
  */

namespace Warp\Http;

class Input
{
	public static function Of($parameter)
	{
		return htmlentities($_REQUEST[$parameter]);
	}

	public static function All()
	{
		$input = array();

		foreach($_REQUEST as $key => $value)
			$input[$key] = htmlentities($value);

		return $input;
	}

	public static function FromGet($parameter)
	{
		$input = array();

		if($parameter)
			return htmlentities($_GET[$parameter]);
		else
		{
			foreach($_GET as $key => $value)
				$input[$key] = htmlentities($value);

			return $input;
		}
	}	

	public static function FromPost($parameter)
	{
		$input = array();

		if($parameter)
			return htmlentities($_POST[$parameter]);
		else
		{
			foreach($_POST as $key => $value)
				$input[$key] = htmlentities($value);

			return $input;
		}
	}

	public static function FromFile($parameter)
	{
		if($parameter)
			return $_FILES[$parameter];
		else
			return $_FILES;
	}

	public static function FromRaw($parameter)
	{
		if($parameter)
			return $_REQUEST[$parameter];
		else
			$_REQUEST;
	}

	public static function FromGetRaw($parameter)
	{
		if($parameter)
			return $_GET[$parameter];
		else
			$_GET;
	}

	public static function FromPostRaw($parameter)
	{
		if($parameter)
			return $_POST[$parameter];
		else
			$_POST;
	}
}

?>