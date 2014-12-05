<?php

/**
 * Command Query class
 * @author Jake Josol
 * @description Command query class for all models
 */

namespace Warp\Data;

use Warp\Data\Database;
use Warp\Data\Query;
use Warp\Data\QueryObject;
use Warp\Utils\Enumerations\CommandType;
use PDO;
 
 class CommandQuery extends Query
 {
	protected $target;
	protected $key;
	protected $type;
	protected $bindings = array();
	
	public function __construct($target, $key=null)
	{
		$this->target = $target;
		$this->key = $key;
	}
	
	public function BindParameter($field, $value, $type=PDO::PARAM_STR)
	{
		$this->bindings[$field] = array(
			"value" => $value,
			"type" => $type
		);
	}
	
	public function SetType($type)
	{
		$this->type = $type;
	}
	
	public function GetQueryObject()
	{
		$type = $this->type;
		$target = $this->target;
		
		$listParameters = array();
		$uniqueBinding = "BIND".substr(uniqid(),0, rand(7,10))."CMD";
		$counterParameters = 0;
		$bindings = "";
		
		$where = "";
		
		switch($type)
		{
			case CommandType::Add:
				$listBindingFields = array();
				$listBindingValues = array();
				$listBindingRows = array();

				if(!is_array($this->bindings[0]["value"]))
				{
					foreach($this->bindings as $field => $details) 
					{
						$listBindingFields[] = $field;
						$listBindingValues[] = ":{$uniqueBinding}{$counterParameters}";
						$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $details["value"]);
						$counterParameters++;
					}

					$bindings = "(" . implode(",", $listBindingFields) . ") VALUES (" . implode(",", $listBindingValues) . ")";
				}
				else
				{
					$rowCount = count($this->bindings[0]["value"]);

					for($rowIndex = 0; $rowIndex < $rowCount; $rowIndex++)
					{
						$listBindingFields = array();
						$listBindingValues = array();

						foreach($this->bindings as $field => $details) 
						{
							$listBindingFields[] = $field;
							$listBindingValues[] = ":{$uniqueBinding}{$counterParameters}";
							$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $details["value"][$rowIndex]);
							$counterParameters++;

							$listBindingRows[] = "(" . implode(",", $listBindingFields) . ") VALUES (" . implode(",", $listBindingValues) . ")";
						}

						$bindings = implode(",", $listBindingRows);
					}
				}
			break;
			
			case CommandType::Edit:
				$listBindings = array();
				foreach($this->bindings as $field => $details) 
				{
					$listBindings[] = "{$field} = :{$uniqueBinding}{$counterParameters}";					
					$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $details["value"]);
					$counterParameters++;
				}
				$bindings = "SET " . implode(",", $listBindings);
			
			case CommandType::Delete:	
				$whereObject = $this->getWhereObject();
				$where = $whereObject->QueryString;
				foreach($whereObject->Parameters as $binding => $parameter) $listParameters[$binding] = $parameter;
			break;
		}
		
		$commandString = "{$type} {$target} {$bindings} {$where}";
		$queryObject = new QueryObject();
		$queryObject->QueryString = $commandString;
		$queryObject->Parameters = $listParameters;
		
		return $queryObject;
	}
	
	public function Execute()
	{
		$queryObject = $this->GetQueryObject();
		return Database::Execute($queryObject->QueryString, $queryObject->Parameters, true);
 	}

 	public static function ExecuteAll($commands)
 	{
 		$queries = array();

 		foreach($commands as $command)
 		{
 			$queryObject = $command->GetQueryObject();
 			$statement = $queryObject->QueryString;
 			$parameters = $queryObject->Parameters;

 			$queries[] = array(
 				"statement" => $statement,
 				"parameters" => $parameters
 			);
 		}

 		return Database::ExecuteAll($queries);
 	}
}

 
?>