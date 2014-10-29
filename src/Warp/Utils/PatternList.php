<?php

/**
 * PatternList class
 * @author Jake Josol
 * @description A list for holding Regular Expression patterns
 */

namespace Warp\Utils;
 
class PatternList
{
	protected $patterns = array();
	protected $aliases = array();
	protected $match;
		
	public function AddPattern($pattern, $action, $options=null)
	{
		foreach($this->patterns as $patternItem)
			if($patternItem["pattern"] == $pattern) return $this;
			
		$this->patterns[] = array(
			"pattern" => $pattern,
			"action" => $action,
			"options" => $options
		);

		return $this;
	}

	public function SetDefault($action)
	{
		if(count($this->match) == 0)
			$this->match = array(
				"pattern" => null,
				"action" => $action
			);
		
		return $this;
	}
	
	public function FindMatch($pattern, $filter=null)
	{
		$matches = array();

		foreach($this->patterns as $patternItem)
		{
			if(preg_match($patternItem["pattern"], $pattern, $matches))
			{
				if($filter)
				{
					if($filter($patternItem))
					{
						// If it matches, remove unnecessary numeric indexes
			 			foreach ($matches as $key => $value) 
			 				if (is_int($key)) 
			 					unset($matches[$key]);

			 			// Retrieve the parameters
			 			$patternItem["parameters"] = $matches;

						$this->match = $patternItem;
						return $this;
					}
				}
				else
				{
					$this->match = $patternItem;
					return $this;	
				}
			}
		}
		
		return $this;
	}
	
	public function Execute()
	{
		if(!$this->match) return;
		$action = $this->match["action"];
		return $action($this->match["parameters"]);
	}
}
 
?>