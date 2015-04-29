<?php

/**
 * Migration class
 * @author Jake Josol
 * @description Utility class for all migrations
 */

namespace Warp\Data;

use Warp\Core\Reference;
use Warp\Foundation\Model;
use Warp\Http\Response;
use Warp\Utils\Enumerations\SystemField;
use Warp\Utils\FileHandle;
use Warp\Utils\Interfaces\IMigration;

class Migration
{
	public function Make($parameters)
	{
		$factory = new MigrationFactory;
		return $factory->Generate($parameters);
	}

	public function Commit()
	{
		try
		{
			$migrated = array();

			// Get list of completed migrations
			$completedMigrations = MigrationModel::Query()->OrderBy("name")->Find();
			$completedMigrations = array_map(function($migrationItem)
			{
				return $migrationItem["name"];
			}, $completedMigrations);

			// Get list of all migrations
			$listMigrations = glob(Reference::Path("migration")."W*_migration.php");
			$listMigrations = array_map(function($migrationItem)
			{
				return str_replace("_migration.php", "", str_replace("W", "", basename($migrationItem)));
			}, $listMigrations);
			sort($listMigrations);

			// Get pending migrations
			$pendingMigrations = array_diff($listMigrations, $completedMigrations);

			foreach($pendingMigrations as $itemMigration)
			{
				$name = "\\W" . $itemMigration . "_migration";

				$migrated[] = $name;

				if(!class_exists($name)) throw new \Exception("The specified migration class does not exist: {$name}");

				$migration = new $name;
				if(!($migration instanceof IMigration)) throw new \Exception("The specified migration class does not implement IMigration: {$name}");
				$migration->Up();

				$model = new MigrationModel;
				$model->name = $itemMigration;
				$model->status = MigrationStatus::Committed;
				$model->Save();
			}

			return Response::Make(200, "Success", array("migrated" => $migrated))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Base()
	{
		try
		{
			$migration = new \WBASE_migration;
			$migration->Up();

			$model = new MigrationModel;
			$model->name = "BASE";
			$model->status = MigrationStatus::Committed;
			$model->Save();

			return Response::Make(200, "Success", array("installedAt" => date("Y-m-d H:i:s")))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Destroy($parameters)
	{
		try
		{
			$name = "W" . $parameters["name"]."_migration";
			$filename = "{$name}.php";
			$directory = Reference::Path("migration");

			$itemMigration = MigrationModel::Query()->WhereEqualTo("name", $parameters["name"])->First();

			if($itemMigration)
			{
				FileHandle::Delete($filename, $directory);
				$migration = new MigrationModel($itemMigration["id"]);
				$migration->Delete();
			}

			return Response::Make(200, "Success", array("file" => $filename, "name" => $name))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Revert()
	{
		try
		{
			$model = new MigrationModel;
			$itemMigration = $model->Query("migrated")->First();

			if(!$itemMigration) throw new \Exception("All migrations have already been reverted");

			$name = "\\W" . $itemMigration["name"] . "_migration";

			if(!class_exists($name)) throw new \Exception("The specified migration class does not exist: {$name}");

			$itemModel = new MigrationModel($itemMigration["id"]);
			$itemModel->Revert();

			$migration = new $name;
			$migration->Down();

			return Response::Make(200, "Success", array("name" => $name))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Reset()
	{
		try
		{
			$reset = array();
			$model = new MigrationModel;
			$listMigrations = $model->Query("migrated")->Find();

			if(!$listMigrations) throw new \Exception("All migrations have already been reverted");

			foreach($listMigrations as $itemMigration)
			{
				$name = "\\W" . $itemMigration["name"] . "_migration";
				$reset[] = $name;

				if(!class_exists($name)) throw new \Exception("The specified migration class does not exist : {$name}");

				$itemModel = new MigrationModel($itemMigration["id"]);
				$itemModel->Revert();

				$migration = new $name;
				$migration->Down();
			}

			return Response::Make(200, "Success", array("reset" => $reset))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Install()
	{
		try
		{
			Schema::Table("_migration")
				->ID()
				->String("name")
				->String("status")
				->Timestamps()
				->Create();
			
			return Response::Make(200, "Success", array("installedAt" => date("Y-m-d H:i:s")))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	public function Uninstall()
	{
		try
		{
			Schema::Table("_migration")
				->Drop();
			
			return Response::Make(200, "Success", array("uninstalledAt" => date("Y-m-d H:i:s")))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}
}

class MigrationStatus
{
	const Pending = "pending";
	const Committed = "comitted";
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
		self::Has("status")->String(30);

		self::Scope("pending", function($query)
		{
			$query->WhereEqualTo("status", MigrationStatus::Pending);
			$query->OrderBy(SystemField::CreatedAt);
			return $query;
		});
		self::Scope("migrated", function($query)
		{
			$query->WhereEqualTo("status", MigrationStatus::Committed);
			$query->OrderByDescending(SystemField::CreatedAt);
			return $query;
		});
	}

	public function Commit()
	{
		$this->status = MigrationStatus::Committed;
		$this->Save();
	}

	public function Revert()
	{
		$this->Delete();
	}
}

class MigrationFactory
{	
	public function Generate($parameters)
	{
		try
		{
			$name = date("YmdHis");
			$table = $parameters["table"];
			$directory = Reference::Path("migration");
			$className = "W{$name}_migration";
			$filename = "{$className}.php";

			$template = new FileHandle("base.tpl", __DIR__."/Templates");
			$templateContents = $template->Contents();
			$template->Close();
			$fileContents = $this->replaceTemplateVars($className, $table, $templateContents);

			$file = new FileHandle($filename, $directory);
			$file->Write($fileContents);
			$file->Close();

			return Response::Make(200, "Success", array("file" => $filename, "name" => $name, "table" => $table))->ToJSON();
		}
		catch(\Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	protected function replaceTemplateVars($className, $table, $templateContents)
	{
		return str_replace("{{table}}", $table, 
					str_replace("{{class}}", $className, $templateContents));
	}
}