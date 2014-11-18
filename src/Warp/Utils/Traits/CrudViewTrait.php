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
	const CREATE_FILE = "add.php";
	const READ_FILE = "view.php";
	const UPDATE_FILE = "edit.php";
	const DELETE_FILE = "delete.php";
	const PAGE_FILE = "default.php";
	const NUMBER_PATTERN = "[1-9][0-9]*";
	protected $crudFile;
		
	public function Crud($type, $parameters=null)
	{
		$viewData = new ViewData();
		
		switch($type)
		{
			case "create":
				$this->crudFile = self::CREATE_FILE;
			break;

			case "read":
				$this->crudFile = self::READ_FILE;			
			case "update":
				$this->crudFile = self::UPDATE_FILE;
			case "destroy":
				$this->crudFile = self::DELETE_FILE;
				$model = new static::$model();
				$model->SetKeyValue($parameters["id"]);
				$model->Fetch();			

				$viewData->Model = $model;
			break;
		}

		return $this;
	}

	public function Render()
	{
		$layout = static::GetLayout();
		$path = static::GetPath();
				
		if($this->crudFile) 
			return static::GetViewFile($layout, $path, self::PAGE_FILE, $this->crudFile, $this->viewData);
		
		return parent::Render();
	}
}

?>