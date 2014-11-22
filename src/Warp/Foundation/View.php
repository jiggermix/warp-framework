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
use Warp\Templating\Morpheus;
use Warp\Utils\Interfaces\IElement;

class View implements IElement
{	
	protected static $DEFAULT_FILE = "default.php";
	protected static $layout = "default.php";
	protected static $page;
	protected $fragment;
	protected $data;
		
	protected static function getLayout()
	{
		return static::$layout;
	}
	
	protected static function getPage()
	{
		return static::$page;
	}

	protected function getFragment()
	{
		return $this->fragment;
	}

	protected function getData()
	{
		return $this->data;
	}

	public function Layout($layout)
	{
		static::$layout = $layout;

		return $this;
	}

	public function Page($page)
	{
		static::$page = $page;

		return $this;
	}

	public function Fragment($fragment)
	{
		$this->fragment = $fragment;

		return $this;
	}

	public function Data($data)
	{
		$this->data = $data;

		return $this;
	}
	
	public function Render()
	{
		$layout = static::getLayout();
		$page = static::getPage();
		$fragment = $this->getFragment();
		$data = $this->getData();

		if(Morpheus::Accepts($layout))
			$view = Morpheus::Make()
					->Layout($layout)
					->Page($page."/default".Morpheus::FILE_EXTENSION)
					->Fragment($page."/fragments/".$fragment)
					->Data($data)
					->Compile();
		else
			$view = static::getDefaultViewFile($layout, $page, $fragment, $data);
		
		return $view;
	}
	
	protected static function getViewFile($layout, $page, $file, $fragment=null, $data=null)
	{			 	
		$viewFragment = new Fragment();
		$viewFragment->SetFile($page."/fragments/".$fragment)
					 ->SetData($data);
					 
		$viewPage = new Page();
		$viewPage->SetFile($page."/".$file)
				 ->SetData($data)
				 ->SetFragment($viewFragment);
		
		$viewLayout = new Layout();
		$viewLayout->SetFile($layout)
				   ->SetData($data)
				   ->SetPage($viewPage);
		
		if($layout) return $viewLayout->Render();
		else $viewPage->Render();
	}
	
	protected static function getDefaultViewFile($layout, $page, $fragment=null, $data=null)
	{
		return static::getViewFile($layout, $page, static::$DEFAULT_FILE, $fragment, $data);
	}

	public static function Make()
	{
		$class = get_called_class();
		return new $class();
	}
}

?>