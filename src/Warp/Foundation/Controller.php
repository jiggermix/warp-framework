<?php

/**
 * Controller class
 * @author Jake Josol
 * @description Base class for all controllers
 */

namespace Warp\Foundation;

use Warp\Foundation\Model;
 
class Controller
{
	protected static $model = null;
	protected static $view = null;
	protected static $patterns;
	
	public static function GetModel()
	{
		$modelName = static::$model;
		if(!$modelName) $modelName = str_replace("Controller", "Model", get_called_class());
		return new $modelName();
	}
	
	public static function GetClass()
	{
		$modelName = static::$model;
		if(!$modelName) $modelName = str_replace("Controller", "Model", get_called_class());
		return $modelName;
	}
	
	public static function GetView()
	{
		$viewName = static::$view;
		if(!$viewName) $viewName = str_replace("Controller", "View", get_called_class());
		return new $viewName();
	}
	
	public function IndexAction($parameters)
	{
		return static::GetView()->Render();
	}
}
 
?>