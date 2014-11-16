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
		$this->filename = $path."/".$name;
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

	public function Write($text)
	{
		fwrite($this->handle, $text);
	}

	public function WriteLine($text)
	{
		fwrite($this->handle, $text . "\n");
	}

	public function Download()
	{
		ob_end_clean();
		
		if (!is_file($this->filename) or connection_status()!=0) return FALSE;
		
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
		header("Content-Type: application/octet-stream");
		header("Content-Length: ".(string)(filesize($path)));
		header("Content-Disposition: inline; filename=$name");
		header("Content-Transfer-Encoding: binary\n");
		
		$file = $this->handle;

		if ($file) 
		{
		    while(!feof($file) and (connection_status()==0)) 
		    {
		    	print(fread($file, 1024*8));
		    	flush();
		    }
		    
		    fclose($file);
		}
		
		return((connection_status()==0) and !connection_aborted());
	}

	public function Close()
	{
		fclose($this->handle);
	}
}

?>