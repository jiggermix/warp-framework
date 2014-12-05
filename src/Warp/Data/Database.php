<?php

/**
 * Database class
 * @author Jake Josol
 * @description Utility class for all database transactions
 */

namespace Warp\Data;

use PDO;
use Warp\Data\DatabaseConfiguration;
use Warp\Utils\Enumerations\DatabaseVendor;
use Warp\Utils\Enumerations\DatabaseReturn;
 
class Database
{
	protected static $currentDatabase;
	
	/***
	 * Set Database Configurations
	 */
	protected static $configurations = array();
	
	/***
	 * Set the Database to be used
	 */
	public static function Set($name)
	{
		static::$currentDatabase = $name;
	}
	
	public static function AddConfiguration($key, DatabaseConfiguration $configuration)
	{		
		static::$configurations[$key] = $configuration;
	}
	
	/***
	 * Connect to the Database
	 */
	protected static function connect()
	{
		try 
		{			
			$configuration = static::$configurations[static::$currentDatabase];
			if(static::$currentDatabase == null) $configuration = static::$configurations[0];
			if(!$configuration) throw new \Exception("Sorry, could not find the database configuration.");
			
			$db = null;
			
			switch($configuration->GetVendor())
			{								
				case DatabaseVendor::MYSQL:
				$db = new PDO("mysql:host={$configuration->GetServer()};dbname=".$configuration->GetDatabase(),
						$configuration->GetUsername(),
						$configuration->GetPassword());
				break;
				
				case DatabaseVendor::SQLServer:
				default:
				$db = new PDO("sqlsrv:server={$configuration->GetServer()};database=".$configuration->GetDatabase(),
						$configuration->GetUsername()."@".$configuration->GetServer(),
						$configuration->GetPassword());
				break;			
			}
			
			$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} 
		catch (PDOException $e)
		{
			$exception = "Sorry, could not connect to the database. Please try again. " . $e->getMessage();
		}

		if($exception) throw new \Exception($exception);
	
		return $db;
	}
	
	/***
	 * Create and execute query for a single row
	 * @output Array Row
	 */
	public static function Fetch($statement, $parameters=array(), $fetchMode=PDO::FETCH_BOTH)
	{
		$db = self::connect($fetchMode);
		
		try
		{
			$query = $db->prepare($statement);
			
			if($query)
			{
				foreach($parameters as $key => $parameter)
				{
					if(!isset($parameter["type"]))
						$parameter["type"] = PDO::PARAM_STR;
	
					$query->bindParam($key,$parameter["value"],$parameter["type"]);
				}
				
				$query->execute();
			}
			else
			{
				throw new \Exception("Sorry, there was a problem with the query statement.");
			}
		}
		catch (PDOException $e)
		{
			$exception = "Sorry, there was a problem with the query. ({$e->getMessage()})";
		}

		if($exception) throw new \Exception($exception);
		
		return $query->fetch($fetchMode);
	}
	
	/***
	 * Create and execute query for multiple rows
	 * @output Array Table
	 */
	public static function FetchAll($statement, $parameters = array(), $fetchMode = PDO::FETCH_ASSOC)
	{
		$db = self::connect($fetchMode);

		try
		{
			$query = $db->prepare($statement);
			
			if($query)
			{
				foreach($parameters as $key => $parameter)
				{
					if(!isset($parameter["type"]))
						$parameter["type"] = PDO::PARAM_STR;
					
					$query->bindParam($key,$parameter["value"],$parameter["type"]);
				}
				
				$query->execute();
			}
			else
			{
				throw new \Exception("Sorry, there was a problem with the query statement.");
			}
		}
		catch (PDOException $e)
		{
			$exception = "Sorry, there was a problem with the query. ({$e->getMessage()})";
		}

		if($exception) throw new \Exception($exception);
		
		return $query->fetchAll($fetchMode);
	}
	
	/***
	 * Execute a Create, Insert, Update or Delete Query
	 * @output String Rows affected
	 */
	public static function Execute($statement, $parameters = array(), $return=false)
	{
		$db = self::connect();
		$rowsAffected = 0;
		$lastID = 0;
		
		try
		{
			$query = $db->prepare($statement);
			
			foreach($parameters as $key => $parameter)
			{
				if(!isset($parameter["type"]))
					$parameter["type"] = PDO::PARAM_STR;
				
				$query->bindParam($key,$parameter["value"],$parameter["type"]);
			}
			
			$db->beginTransaction();
			$query->execute();
			$rowsAffected += $query->rowCount();
			$lastID = $db->lastInsertId();
			$db->commit();
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			$exception = "Sorry, there was a problem with query execution. ({$e->getMessage()})";
		}

		if($exception) throw new \Exception($exception);
		
		$returnObject = (object) array(
			DatabaseReturn::RowsAffected => $rowsAffected,
			DatabaseReturn::LastInsertID => $lastID
		);
		
		if($return) return $returnObject;
		else return $rowsAffected;
	}
	
	/*** 
	 * Execute multiple queries
	 * @output String Rows affected
	 */
	public static function ExecuteAll($executeQueries)
	{
		$db = self::connect();
	
		$db->beginTransaction();
		$rowsAffected = 0;
		
		try
		{
			foreach($executeQueries as $executeQuery)
			{
				$query = $db->prepare($executeQuery["statement"]);
			
				foreach($executeQuery["parameters"] as $key => $parameter)
				{
					if(!isset($parameter["type"]))
						$parameter["type"] = PDO::PARAM_STR;
						
					$query->bindParam($key,$parameter["value"],$parameter["type"]);
				}
					
				$query->execute();
				$rowsAffected += $query->rowCount();	
			}
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			$exception = "Sorry, there was a problem with query execution. ({$e->getMessage()})";
		}

		if($exception) throw new \Exception($exception);
		
		$db->commit();
		return $rowsAffected;
	}

	/*** 
	 * Execute sequential queries
	 * @output String Rows affected
	 */
	public static function ExecuteEach()
	{
		$arguments = func_get_args();
		$executeQueryBuilders = is_array($arguments[0])? $arguments[0] : $arguments;

		$db = self::connect();
		$db->beginTransaction();
		
		try
		{
			$results = null;

			foreach($executeQueryBuilders as $executeQueryBuilder)
			{				
				$executeQuery = $executeQueryBuilder($results);
				$query = $db->prepare($executeQuery["statement"]);
			
				foreach($executeQuery["parameters"] as $key => $parameter)
				{
					if(!isset($parameter["type"]))
						$parameter["type"] = PDO::PARAM_STR;
						
					$query->bindParam($key,$parameter["value"],$parameter["type"]);
				}
					
				$query->execute();
				$rowsAffected = $query->rowCount();
				$lastID = $db->lastInsertId();

				$results = (object) array(
					DatabaseReturn::RowsAffected => $rowsAffected,
					DatabaseReturn::LastInsertID => $lastID
				);
			}
		}
		catch (PDOException $e)
		{
			$db->rollBack();
			$exception = "Sorry, there was a problem with query execution. ({$e->getMessage()})";
		}

		if($exception) throw new \Exception($exception);
		
		$db->commit();
		return $results;
	}
}

?>