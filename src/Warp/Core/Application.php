<?php

/**
 * Application class
 * @author Jake Josol
 * @description Class that is responsible for the entire application
 */

namespace Warp\Core;

use Warp\Http\Router;
use Warp\Data\Database;
use Warp\Session\Session;
use Warp\Utils\Debugger;
use Warp\Utils\Enumerations\DebugMode;
 
class Application
{
	protected static $instance;
	protected $title;
	protected $subtitle;
	protected $description;
	protected $keywords;
	protected $debugMode;
	protected $directory;
	
	private function __construct() {}

	public static function GetInstance()
	{
		return static::$instance;
	}
	
	public static function Initialize()
	{		
		static::$instance = new Application;
		static::GetInstance()->SetTimezone("UTC");

		Session::Start();

		return static::$instance;
	}
	
	public function SetTimezone($timezone)
	{
		date_default_timezone_set($timezone);
	}

	public function SetPath($path=null)
	{
		if($path) Router::SetPath($path);

		return $this;
	}
	
	public function GetPath()
	{
		return Router::GetPath();
	}
	
	public function SetTitle($title)
	{
		$this->title = $title;
		return $this;
	}
	
	public function GetTitle()
	{
		return $this->title;
	}
	
	public function SetSubtitle($subtitle)
	{
		$this->subtitle = $subtitle;
		return $this;
	}
	
	public function GetSubtitle()
	{
		return $this->subtitle;
	}
	
	public function SetDescription($description)
	{
		$this->description = $description;
		return $this;
	}
	
	public function GetDescription()
	{	
		return $this->description;
	}
	
	public function SetKeywords($keywords)
	{
		$this->keywords = implode(",", $keywords);
		return $this;
	}
	
	public function GetKeywords()
	{
		return $this->keywords;
	}
	
	public function GetKeywordsList()
	{
		return explode(",", $this->keywords);
	}
	
	public function SetDebugMode($debugMode)
	{
		$this->debugMode = $debugMode;
		
		switch($this->debugMode)
		{
			case DebugMode::Development:
				error_reporting(E_ERROR | E_WARNING | E_PARSE);
			break;
			
			case DebugMode::Production:
				error_reporting(E_ERROR);
			break;
		}
		
		return $this;
	}

	public function GetDebugMode()
	{
		return $this->debugMode;
	}
	
	public function SetConfiguration($configuration)
	{
		$configuration->Apply();

		return $this;
	}
	
	public function SetDatabase($name)
	{
		Database::Set($name);
		return $this;
	}
	
	public function Start()
	{
		try
		{
			Reference::Import("route", "routes");
			Reference::Import("resource", "resources");

			echo Router::Fetch();
		}
		catch (\Exception $ex)
		{
			$trace = "";

			if(static::GetInstance()->GetDebugMode() == DebugMode::Development)
				$trace = ": " . json_encode($ex->getTrace());

			echo Debugger::WriteError($ex->getMessage() . $trace);
		}
	}
}

?>