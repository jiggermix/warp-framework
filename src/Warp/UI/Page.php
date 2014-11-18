<?php

/**
 * Page class
 * @author Jake Josol
 * @description Base class for all pages
 */
 
namespace Warp\UI;

use Warp\UI\Fragment;
use Warp\Core\Reference;

class Page extends Fragment
{
	protected $fragment;
	
	public function SetFragment($fragment)
	{
		$this->fragment = $fragment;
		
		return $this;
	}
	
	public function GetFragment()
	{
		return $this->fragment;
	}

	public function Render()
	{
		$data = (object) $this->data;

		if($this->fragment) $data->Fragment = $this->fragment;

		include Reference::Path("page") . $this->file . ".php";
	}
}

?>