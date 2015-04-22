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
		static::Register("foundation:make", function($parameters)
		{
			FoundationFactory::Generate($parameters);
		});

		static::Register("migrate:install", function($parameters)
		{
			Migration::Install();
		});

		static::Register("migrate:make", function($parameters)
		{
			Migration::Make($parameters);
		});

		static::Register("migrate:commit", function()
		{
			Migration::Commit();
		});

		static::Register("migrate:revert", function()
		{
			Migration::Revert();
		});

		static::Register("migrate:reset", function()
		{
			Migration::Reset();
		});

		static::Register("deploy", function($parameters)
		{

		});
	}

	// Generic function caller
	public function Run($functionName, $parameters)
	{
		$response = static::$functions[$functionName]($parameters);
		return $response;
	}

	// Function registry
	public function Register($functionName, $function)
	{
		static::$functions[$functionName] = $function;
	}
}