<?php

/**
 * Layout class
 * @author Jake Josol
 * @description Base class for all layout
 */
 
namespace Warp\UI;

use Warp\UI\Page;
use Warp\Core\Reference;

class Layout extends Page
{
	protected $page;
	
	public function SetPage($page)
	{
		$this->page = $page;
		
		return $this;
	}
	
	public function GetPage()
	{
		return $this->page;
	}

	public function Render()
	{
		$data = (object) $this->data;
		if($this->page) $data->Page = $this->page;

		Reference::Import("layout", $this->file);
	}
}

?>