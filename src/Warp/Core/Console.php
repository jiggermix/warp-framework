<?php

/**
 * Console class
 * @author Jake Josol
 * @description Class that is responsible for the command line functions
 */

namespace Warp\Core;

use Warp\Foundation\Model;
use Warp\Utils\FileHandle;
use Warp\Utils\Enumerations\SystemField;

class Console
{
	protected $functions = array();

	public function __construct() 
	{
		static::Register("model:make", function($parameters)
		{
			try
			{
				$name = $parameters["name"];
				$directory = Reference::Path("model"); 
				$filename = "{$name}Model.php";

				if(file_exists($directory.$filename)) throw new \Exception("The model already exists!");

				$file = new FileHandle($filename, $directory);
				$file->WriteLine("<?php");
				$file->WriteLine("");
				$file->WriteLine("/*");
				$file->WriteLine("* {$name} model");
				$file->WriteLine("* @author ---");
				$file->WriteLine("* @description ---");
				$file->WriteLine("*/");
				$file->WriteLine("");
				$file->WriteLine("use Warp\\Utils\\Enumerations\\SystemField;");
				$file->WriteLine("use Warp\\Utils\\Enumerations\\InputType;");
				$file->WriteLine("");
				$file->WriteLine("class {$name}Model extends Model");
				$file->WriteLine("{");
				$file->WriteLine("\tprotected static $source = \"{$name}\";");
				$file->WriteLine("\tprotected static $key = \"id\";");
				$file->WriteLine("\tprotected static $fields = array();");
				$file->WriteLine("");
				$file->WriteLine("\tprotected static function build()");
				$file->WriteLine("\t{");
				$file->WriteLine("\t\tself::Has(SystemField::ID)->Increment();");
				$file->WriteLine("\t\tself::Has(\"FIELD-NAME\")->String(30);");
				$file->WriteLine("\t}");
				$file->WriteLine("}");
				$file->Close();

				return Response::Make(200, "Success", array("file" => $filename, "model" => $name))->ToJSON();
			}
			catch (Exception $ex)
			{
				return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
			}
		});

		static::Register("migrate:make", function($parameters)
		{
			try
			{
				$name = date("YmdHis");
				$table = $parameters["name"];
				$directory = Reference::Path("migration"); 
				$filename = "migration_{$name}.php";

				$model = new MigrationModel;
				$model->name = $name;
				$model->type = MigrationType::Make;
				$model->Save();

				$file = new FileHandle($filename, $directory);
				$file->WriteLine("<?php");
				$file->WriteLine("");
				$file->WriteLine("use Warp\\Utils\\Interfaces\\IMigration;");
				$file->WriteLine("");
				$file->WriteLine("class migration_{$name} implements IMigration");
				$file->WriteLine("{");
				$file->WriteLine("\tpublic function Up()");
				$file->WriteLine("\t{");
				$file->WriteLine("\t\tSchema::Table(\"{$table}\")");
				$file->WriteLine("\t\t\t->ID()");
				$file->WriteLine("\t\t\t->Create();");
				$file->WriteLine("\t}");
				$file->WriteLine("");
				$file->WriteLine("\tpublic function Down()");
				$file->WriteLine("\t{");
				$file->WriteLine("\t\tSchema::Table(\"{$table}\")->Drop();");
				$file->WriteLine("\t}");
				$file->WriteLine("}");
				$file->Close();

				return Response::Make(200, "Success", array("file" => $filename, "name" => $name, "table" => $table))->ToJSON();
			}
			catch(Exception $ex)
			{
				return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
			}
		});

		static::Register("migrate:commit", function($parameters=null)
		{
			try
			{
				$migrated = array();
				$model = new MigrationModel;
				$listMigrations = $model->GetQuery("pending")->Find();

				foreach($listMigrations as $itemMigration)
				{
					$name = "migration_" . $itemMigration["name"];
					$migrated[] = $name;

					if(!class_exists($name)) throw new \Exception("The specified migration class does not exist: {$name}");

					$itemModel = new MigrationModel($itemMigration["id"]);
					$itemModel->SoftDelete();

					$migration = new $name;
					$migration->Up();
				}

				return Response::Make(200, "Success", array("migrated" => $migrated))->ToJSON();
			}
			catch(Exception $ex)
			{
				return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
			}

		});

		static::Register("migrate:revert", function($parameters=null)
		{
			try
			{
				$model = new MigrationModel;
				$itemMigration = $model->GetQuery("migrated")->First();

				if(!$itemMigration) throw new \Exception("All migrations have already been reverted");

				$name = "migration_" . $itemMigration["name"];

				if(!class_exists($name)) throw new \Exception("The specified migration class does not exist: {$name}");

				$itemModel = new MigrationModel($itemMigration["id"]);
				$itemModel->Restore();

				$migration = new $name;
				$migration->Down();

				return Response::Make(200, "Success", array("name" => $name))->ToJSON();
			}
			catch(Exception $ex)
			{
				return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
			}

		});

		static::Register("migrate:reset", function($parameters=null)
		{
			try
			{
				$reset = array();
				$model = new MigrationModel;
				$listMigrations = $model->GetQuery("migrated")->Find();

				if(!$listMigrations) throw new \Exception("All migrations have already been reverted");

				foreach($listMigrations as $itemMigration)
				{
					$name = "migration_" . $itemMigration["name"];
					$reset[] = $name;

					if(!class_exists($name)) throw new \Exception("The specified migration class does not exist : {$name}");

					$itemModel = new MigrationModel($itemMigration["id"]);
					$itemModel->Restore();

					$migration = new $name;
					$migration->Down();
				}

				return Response::Make(200, "Success", array("reset" => $reset))->ToJSON();
			}
			catch(Exception $ex)
			{
				return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
			}
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

class MigrationType
{
	const Make = "MAKE";
	const Up = "UP";
	const Down = "DOWN";
}

class MigrationModel extends Model
{
	protected static $source = "_migration";
	protected static $key = "id";
	protected static $fields = array();

	protected static function build()
	{
		self::Has(SystemField::ID)->Increment();
		self::Has("name")->String(30);		
		self::Has("type")->String(30);

		self::Scope("pending", function($query)
		{
			$query->WhereIsNull(SystemField::DeletedAt);
			$query->OrderBy(SystemField::CreatedAt);
			return $query;
		});
		self::Scope("migrated", function($query)
		{
			$query->WhereIsNotNull(SystemField::DeletedAt);
			$query->OrderByDescending(SystemField::CreatedAt);
			return $query;
		});
	}
}
