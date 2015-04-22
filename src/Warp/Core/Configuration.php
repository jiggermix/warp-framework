<?php

/**
 * Configuration class
 * @author Jake Josol
 * @description Base class that is used to bulk-set configurations
 */
 
namespace Warp\Core;

use Warp\Core\Application;
use Warp\Data\Database;
use Warp\Data\DatabaseConfiguration;

class Configuration implements IConfiguration
{
	public function SetPath($path)
	{
		Application::GetInstance()->SetPath($path);
	}

	public function SetDebugMode($debugMode)
	{
		Application::GetInstance()->SetDebugMode($debugMode);
	}
	
	public function SetTimezone($timezone)
	{
		Application::GetInstance()->SetTimezone($timezone);
	}

	public function AddDatabase($name, DatabaseConfiguration $databaseConfig)
	{
		Database::AddConfiguration($name, $databaseConfig);
	}

	public function SetDatabase($database)
	{
		Application::GetInstance()->SetDatabase($database);
	}

	public function Apply()
	{
		throw new \Exception("An apply method was not implemented for the configuration");
	}
}

?>