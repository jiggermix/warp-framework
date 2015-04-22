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
	protected $filename;

	public function __construct($name, $path=null)
	{
		if($path)
			if(substr($path, strlen($path)-1, 1) != "/") 
				$path = $path."/";

		$this->filename = $path.$name;
		$this->handle = fopen($this->filename, "a");
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
		return fread($this->handle, filesize($this->filename));
	}

	public function Contents()
	{
		return file_get_contents($this->filename);
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