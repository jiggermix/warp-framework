<?php

/**
 * View Data class
 * @author Jake Josol
 * @description Used to store data passed into views
 */

namespace Warp\Foundation;

class ViewData
{
	protected $details = array();
	
	/***
	 * Getter
	 * @params string name
	 * @return string value
	 */
	public function __get($name)
	{
		if(!isset($this->details[$name])) return null;
		return $this->details[$name];		
	}
	
	/***
	 * Setter
	 * @params string name, string value
	 */
	public function __set($name,$value)
	{
		$this->details[$name] = $value;
	}
}

?>