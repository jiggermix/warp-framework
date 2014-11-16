<?php

/**
 * File Handle class
 * @author Jake Josol
 * @description Class for handling files
 */

namespace Warp\Utils;

class FileHandle
{
	protected $handle;

	public function __construct($name, $path=null)
	{
		$this->handle = fopen($path."/".$name, "a");
	}

	public static function Exists($name, $path)
	{
		return file_exists($path."/".$name);
	}

	public static function Delete($name, $path)
	{
		unlink($path."/".$name);
	}

	public function Read()
	{
		return fread($this->handle);
	}

	public function Write($text)
	{
		fwrite($this->handle, $text);
	}

	public function WriteLine($text)
	{
		fwrite($this->handle, $text . "\n");
	}

	public function Close()
	{
		fclose($this->handle);
	}
}

?>