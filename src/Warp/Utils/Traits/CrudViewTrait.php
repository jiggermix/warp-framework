<?php

/*
 * Crud View trait
 * @author Jake Josol
 * @description Trait for Crud Views
 */

namespace Warp\Utils\Traits;

use Warp\Foundation\ViewData;

trait CrudViewTrait
{
	protected static $INDEX_FILE = "index.php";
	protected static $CREATE_FILE = "add.php";
	protected static $READ_FILE = "view.php";
	protected static $UPDATE_FILE = "edit.php";
	protected static $DELETE_FILE = "delete.php";
	protected static $PAGE_FILE = "default.php";
	protected static $NUMBER_PATTERN = "[1-9][0-9]*";
	protected $crudFile;
		
	public function Crud($type, $parameters=null)
	{
		$viewData = new ViewData();
		
		switch($type)
		{
			case "create":
				$this->crudFile = static::$CREATE_FILE;
			break;

			case "read":
				$this->crudFile = static::$READ_FILE;			
			case "update":
				$this->crudFile = static::$UPDATE_FILE;
			case "destroy":
				$this->crudFile = static::$DELETE_FILE;				
				$modelName = "\\" . static::$model;
				$model = new $modelName;
				$model->SetKeyValue($parameters["id"]);
				$model->Fetch();			

				$viewData->Model = $model;
			break;

			default:
				$this->crudFile = static::$INDEX_FILE;
			break;
		}

		return $this;
	}

	public function Render()
	{
		$layout = static::GetLayout();
		$path = static::GetPath();

		if(!$this->crudFile) $this->crudFile = "index.php";

		return static::GetViewFile($layout, $path, static::$PAGE_FILE, $this->crudFile, $this->viewData);
	}
}

?>