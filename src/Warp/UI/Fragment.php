<?php

/**
 * Fragment class
 * @author Jake Josol
 * @description Base class for all fragments
 */
 
namespace Warp\UI;

use Warp\Utils\Interfaces\IElement;
use Warp\Core\Reference;

class Fragment implements IElement
{
	protected $file;
	protected $data;
		
	public function SetFile($file)
	{
		$this->file = str_replace(".php", "", $file);	
		
		return $this;
	}
	
	public function SetData($data)
	{
		$this->data = $data;
		
		return $this;
	}
		
	public function Initialize($id, $parameters=array())
	{
		$this->path = $parameters["path"];
		$this->SetData($parameters["data"]);
		$this->SetFile($parameters["file"]);
	}
	
	public function Render()
	{
		$data = $this->data;
		
		include Reference::Path("page") . $this->path . "/fragments/" . $this->file . ".php";
	}
}

?>