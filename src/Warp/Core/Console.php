<?php

/**
 * Console class
 * @author Jake Josol
 * @description Class that is responsible for the command line functions
 */

namespace Warp\Core;

use Warp\Data\Migration;
use Warp\Foundation\Model;
use Warp\Foundation\FoundationFactory;
use Warp\Utils\FileHandle;
use Warp\Utils\Enumerations\SystemField;

class Console
{
	protected $functions = array();

	public function __construct() 
	{
		$this->Register("foundation:make", function($parameters)
		{
			return FoundationFactory::Generate($parameters);
		});

		$this->Register("migrate:install", function($parameters)
		{
			return Migration::Install();
		});

		$this->Register("migrate:make", function($parameters)
		{
			return Migration::Make($parameters);
		});

		$this->Register("migrate:commit", function()
		{
			return Migration::Commit();
		});

		$this->Register("migrate:revert", function()
		{
			return Migration::Revert();
		});

		$this->Register("migrate:reset", function()
		{
			return Migration::Reset();
		});

		$this->Register("deploy", function($parameters)
		{
			// TO-DO Deployment
		});
	}

	// Start the Console
	public static function Start()
	{
		// Get the console variables
		$function = $argv[0];
		$rows = explode(",", $argv[1]);
		$vars = array();
		foreach($rows as $row)
		{
			$parts = explode("=", $row);
			$vars[$parts[0]] = $parts[1];
		}

		// Prepare the console
		$console = new Console;

		// Run the console
		return $console->Run($function, $vars);
	}

	// Generic function caller
	public function Run($functionName, $parameters)
	{
		$functionName = $this->functions[$functionName];
		$response = $functionName($parameters);
		return $response;
	}

	// Function registry
	public function Register($functionName, $function)
	{
		$this->functions[$functionName] = $function;
	}
}