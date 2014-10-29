<?php

/**
 * View class
 * @author Jake Josol
 * @description Base class for all views
 */

namespace Warp\Foundation;

use Warp\UI\Fragment;
use Warp\UI\Page;
use Warp\UI\Layout;

class View
{	
	const VIEW_FILE_DIRECTORY = "/application/design/";
	const DEFAULT_FILE = "default.php";
	protected static $layout;
	protected static $path;
		
	protected static function GetLayout()
	{
		return static::$layout;
	}
	
	protected static function GetPath()
	{
		return static::$path;
	}
	
	public function Render()
	{
		$layout = static::GetLayout();
		$path = static::GetPath();
		$view = static::GetDefaultViewFile($layout, $path);	
		
		return $view;
	}
	
	protected static function GetViewFile($layout, $path, $page, $fragment=null, $data=null)
	{			 	
		$viewFragment = new Fragment();
		$viewFragment->SetFile($path."/fragments/".$fragment)
					 ->SetData($data);
					 
		$viewPage = new Page();
		$viewPage->SetFile($path."/".$page)
				 ->SetData($data)
				 ->SetFragment($viewFragment);
		
		$viewLayout = new Layout();
		$viewLayout->SetFile($layout)
				   ->SetData($data)
				   ->SetPage($viewPage);
		
		if($layout) return $viewLayout->Render();
		else $viewPage->Render();
	}
	
	protected static function GetDefaultViewFile($layout, $path)
	{
		return static::GetViewFile($layout, $path, self::DEFAULT_FILE);
	}

	public static function Make()
	{
		$class = get_called_class();
		return new $class();
	}
}

?>