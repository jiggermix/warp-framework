<?php

/**
 * Control class
 * @author Jake Josol
 * @description Base class for all controls
 */

namespace Warp\UI;

use Warp\Utils\Interfaces\IElement;

class Control implements IElement
{
	protected $id;
	protected $type;
	protected $isParent = true;
	protected $classes = array();
	protected $properties = array();
	protected $children = array();
	protected $data = array();
	protected $definition;
	protected static $name;

	private function __construct()
	{
	}
	
	public static function Create($id="")
	{
		$control = new static::$name();
		$control->Initialize($id);
		return $control;
	}
		
	public function Initialize($id="", $parameters=array())
	{
		$this->SetID($id);
	}
	
	public function SetID($id)
	{
		$this->id = $id;
	}
	
	public function GetID()
	{
		return $this->id;
	}
	
	public function AddClass($class)
	{
		$this->classes[] = htmlentities($class);
		return $this;
	}
	
	public function RemoveClass($class)
	{
		foreach($this->classes as $key => $value)
		{
			if($value == $class)
			{
				unset($this->classes[$key]);
			}
		}
		return $this;
	}
	
	public function SetProperty($key, $value)
	{
		$this->properties[$key] = htmlentities($value);
		return $this;
	}
	
	public function RemoveProperty($key)
	{
		unset($this->properties[$key]);
		return $this;
	}
	
	public function AddChild($value)
	{
		$this->children[] = $value;
		return $this;
	}
	
	public function RemoveChild($id)
	{
		foreach($this->children as $key => $value)
		{
			if($value->GetID() == $id)
			{
				unset($this->children[$key]);
			}
		}
		return $this;
	}
	
	public function FindChildByID($id)
	{
		foreach($this->children as $key => $value)
			if($value->GetID() == $id)
				return $this->children[$key];
				
		return null;
	}

	public function SetColumnSpan($width, $offset=null, $size="md")
	{
		$this->AddClass("col-{$size}-{$width}");
		if($offset) $this->AddClass("col-{$size}-offset-{$offset}");
		
		return $this;
	}
	
	public function Render()
	{
		$id = ($this->id) ? " id='{$this->id}'" : "";
		
		$classes = " class='".implode(" ", $this->classes)."'";
		if(count($this->classes) == 0) $classes = "";
		
		$listProperties = array();
		foreach($this->properties as $key => $value) $listProperties[] = "{$key}=\"{$value}\"";
		$properties = " ".implode(" ", $listProperties);
		
		$listChildren = array();
		foreach($this->children as $child) 
			if($child)
				$listChildren[] = $child->Render();

		$children = implode(PHP_EOL, $listChildren);
		
		$enclosingTag = ($this->isParent) ? "</{$this->type}>" : "";
		
		return "<{$this->type}{$id}{$classes}{$properties}>{$children}{$enclosingTag}";
	}

}
 
?>