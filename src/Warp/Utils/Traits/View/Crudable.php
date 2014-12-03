<?php

/*
 * Crud View trait
 * @author Jake Josol
 * @description Trait for Crud Views
 */

namespace Warp\Utils\Traits\View;

use Warp\Foundation\ViewData;

trait Crudable
{
	protected static $INDEX_FILE = "index.php";
	protected static $CREATE_FILE = "add.php";
	protected static $READ_FILE = "view.php";
	protected static $UPDATE_FILE = "edit.php";
	protected static $DELETE_FILE = "delete.php";
		
	public function Crud($type, $parameters=null)
	{
		$viewData = new ViewData();
		
		switch($type)
		{
			case "create":
				$this->Fragment(static::$CREATE_FILE);
			break;

			case "read":
				$this->Fragment(static::$READ_FILE);
			break;

			case "update":
				$this->Fragment(static::$UPDATE_FILE);
			break;

			case "destroy":
				$this->Fragment(static::$DELETE_FILE);
			break;
		}

		if($parameters)
		{
			$modelName = "\\" . static::$model;
			$model = new $modelName;
			$model->SetKeyValue($parameters["id"]);
			$model->Fetch();			

			$viewData->Model = $model;
		}

		$this->Data($viewData);

		return $this;
	}

	public function Render()
	{
		$layout = static::getLayout();
		$page = static::getPage();
		$fragment = $this->getFragment();
		$data = $this->getData();

		if(!$fragment)
		{
			$modelName = "\\" . static::$model;
			$model = new $modelName;
			$viewData = $data? $data : new ViewData();
			$viewData->Model = $model;
			$viewData->Rows = $model->GetQuery()->Find();
			$this->Fragment(static::$INDEX_FILE);
			$this->Data($viewData);
		}

		return parent::Render();
	}
}

?>