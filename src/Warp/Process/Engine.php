<?php

/**
 * Engine class
 * @author Jake Josol
 * @description Class that is responsible for all engine programs
 */

namespace Warp\Process;
 
class Engine
{	
	const ENGINE_DIRECTORY = "/application/build/engines/";
	const PARAMETER_DELIMITER = '$param%';
	public $Title;
	public $Type;
	public $File;
	protected $owner;
	protected $uniqueID; 
	protected $processInstance;
	protected $parameters;
	protected static $engines;
	
	public function GetOwner()
	{
		return $this->owner;
	}
	
	public function GetUniqueID()
	{
		return $this->uniqueID;
	}
	
	public function GetProcessInstance()
	{
		return $this->processInstance;
	}
	
	public static function GetCurrentDateTime()
	{
		return date("Y-m-d H:i");
	}
	
	public static function Load($name)
	{
		if(!static::$engines[$name]) return null;
		
		$engine = new Engine();
		$engine->Title = static::$engines[$name]["title"];
		$engine->Type = static::$engines[$name]["type"];
		$engine->File = static::$engines[$name]["file"];
		
		return $engine;
	}
	
 	public function Run($parameters)
	{		
		// URL encode post parameters
		foreach($parameters as $key => $value) $parameters[$key] = urlencode($value);
		$this->parameters = $parameters;
			
		// Authenticate user
		$isAuthenticated = $this->authenticateUser($parameters["username"], $parameters["secretKey"]);
		if(!$isAuthenticated) return static::ShowError(403, "Access denied");
		$ownerID = $this->GetOwner()->objectID;
				
		// Store unique ID
		$this->uniqueID = $this->generateUniqueID();
		$this->parameters["uniqueID"] = $this->GetUniqueID();
		
		// Retrieve Process Instance until no match exists
		$this->processInstance = $this->generateProcessInstance();
		$this->parameters["processInstance"] = $this->GetProcessInstance();
		
		// Set Run Paramaters
		$runParameters = $this->serializeParameters();
		
		// Prepare the engine details
		$engine = new EngineModel();
		$engine->ownerID = $ownerID;
		$engine->uniqueID = $this->GetUniqueID();
		$engine->processInstance = $this->GetProcessInstance();
		$engine->title = $this->Title;
		$engine->type = $this->Type;
		$engine->status = "Queued";
		
		// Save the engine
		$engine->Save();
		$engineID = $engine->objectID;
		
		chdir(getcwd());
		$runFile = getcwd() . self::ENGINE_DIRECTORY . $this->File;
		$cmd = "php \"{$runFile}\" {$ownerID} {$engineID} {$runParameters}";
		
		$logFilename = "log-".date("Ymd-Hi").".txt";
		pclose(popen("start ".$cmd." >> ".$logFilename, 'r'));
		
		return static::createResponseObject(200, "Success", array(
			"ownerID" => $this->GetOwner()->objectID,
			"uniqueID" => $this->GetUniqueID(),
			"processInstance" => $this->GetProcessInstance(),
			"startedAt" => static::GetCurrentDateTime()
		));
	}
	
	public static function ShowError($status, $message)
	{
		return static::createResponseObject($status, $message, array());
	}
	
	private static function createResponseObject($status, $message, $result)
	{
		return json_encode(array(
			"status" => $status,
			"message" => $message,
			"result" => $result					
		));
	}
	
	private function serializeParameters()
	{
		$listParameters = array(
			$this->parameters["username"],
			$this->parameters["secretKey"],
			$this->parameters["uniqueID"],
			$this->parameters["processInstance"]
		);
		
		return join(self::PARAMETER_DELIMITER, $listParameters);
	}
	
	public static function DeserializeParameters($stringParameters)
	{
		$listParameters = explode(self::PARAMETER_DELIMITER, $stringParameters);
		$parameters = array(
			"username" => $listParameters[0],
			"secretKey" => $listParameters[1],
			"uniqueID" => $listParameters[2],
			"processInstance" => $listParameters[3]
		);
		
		return $parameters;
	}
	
	private function authenticateUser($username, $apiKey)
	{
		// Authenticate user
		$userQuery = UserModel::GetQuery()
						->WhereEqualTo("username", $username)
						->WhereEqualTo("secretKey", $apiKey);
						
		$resultUser = $userQuery->Find();
		
		if($resultUser)
		{
			$userID = $resultUser[0]["objectID"];
			$this->owner = new UserModel();
			$this->owner->SetKeyValue($userID);
			$this->owner->Fetch();
		}
		
		return ($this->owner) ? true : false;
	} 
	
	private function generateUniqueID()
	{
		$engineQuery = EngineModel::GetQuery();
		$uniqueID = "";
		
		do
		{	
			while (strlen($uniqueID) < 11 ) 
				if (ctype_alnum($str=chr(mt_rand(48,122)))) $uniqueID .= $str;
			
			$uniqueID = str_shuffle($uniqueID);
			$engine = $engineQuery->WhereEqualTo("uniqueID", $uniqueID)->Find();
		}
		while($engine);
		
		return $uniqueID;
	}
	
	private function generateProcessInstance()
	{
		$engineQuery = EngineModel::GetQuery();
		$processInstance = 0;
		
		do
		{
			$processInstance = rand(12345678,87654321);
			$engine = $engineQuery
						->WhereEqualTo("ownerID", $this->GetOwner()->objectID)
						->WhereEqualTo("processInstance", $processInstance)
						->Find();
		}
		while($engine);
		
		return $processInstance;
	}
	
	public static function Add($name, $title, $type, $file)
	{
		static::$engines[$name] = array(
			"title" => $title,
			"type" => $type,
			"file" => $file . "Engine.php"
		);
	}	 
	
	public function Publish($id)
	{
		$engine = new EngineModel();
		$engine->SetKeyValue($id);
		$engine->Fetch();
		$engine->status = "Success";
		$engine->Save();
		
		return $engine;
	}
}

?>