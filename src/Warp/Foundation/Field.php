<?php

/**
 * Field class
 * @author Jake Josol
 * @description Class for creating fields
 */

namespace Warp\Foundation;

use Warp\Utils\Enumerations\FieldType;
use Warp\Utils\Enumerations\InputType;

class Field
{
	protected $model;
	protected $name;
		
	public function __construct($model, $name)
	{
		$this->model = $model;
		$this->name = $name;
	}
	
	public function Increment($value=true)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "increment", $value);
		$this->Guarded();
		$this->Integer();
		
		return $this;
	}
	
	public function Type($fieldType)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "type", $fieldType);

		return $this;
	}
	
	public function Size($min, $max=null)
	{
		$modelName = $this->model;

		if($max != null)
		{
			$modelName::SetOption($this->name, "min", $min);
			$modelName::SetOption($this->name, "max", $max);
		}
		else
		{
			$modelName::SetOption($this->name, "max", $min);
		}
		
		return $this;
	}

	public function Range($least, $greatest)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "least", $least);
		$modelName::SetOption($this->name, "greatest", $greatest);

		return $this;
	}
	
	public function Label($label)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "label", $label);
		
		return $this;
	}
	
	public function Input($input)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "input", $input);
		
		return $this;
	}

	public function Guarded()
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "guarded", true);

		return $this;
	}

	public function Hidden()
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "hidden", true);

		return $this;
	}
	
	public function Lookup()
	{
		$list = func_get_args();
		$modelName = $this->model;
		$modelName::SetOption($this->name, "lookup", $list);
		
		return $this;
	}
	
	public function Relation($model, $key)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "relation", $model . "Model");
		$modelName::SetOption($this->name, "key", $key);
		$this->Guarded();
		
		return $this;
	}

	public function Pointer($model, $key)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "pointer", $model . "Model");
		$modelName::SetOption($this->name, "key", $key);
		$this->Guarded();
		
		return $this;
	}

	public function MultiPointer($model, $join=null)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "multipointer", $model . "Model");
		if($join) $modelName::SetOption($this->name, "join", $join);				
		$this->Guarded();
		
		return $this;
	}
	
	public function Translate($model, $key)
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "translate", $model . "Model");
		$modelName::SetOption($this->name, "key", $key);
		$this->Guarded();
		
		return $this;
	}

	public function Unique()
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "unique", true);
		
		return $this;
	}

	public function Integer()
	{
		$this->Type(FieldType::Integer);
		$this->Input(InputType::Integer);		
		return $this;
	}

	public function Float()
	{
		$this->Type(FieldType::Float);
		$this->Input(InputType::Float);		
		return $this;
	}

	public function Decimal()
	{
		$this->Type(FieldType::Decimal);
		$this->Input(InputType::Float);		
		return $this;
	}

	public function DateTime()
	{
		$this->Type(FieldType::DateTime);
		return $this;
	}

	public function Date()
	{
		$this->Type(FieldType::Date);		
		$this->Input(InputType::Date);
		return $this;
	}

	public function Password()
	{
		$this->Type(FieldType::Password);
		$this->Input(InputType::Password);
		$this->Hidden();
		return $this;
	}

	public function String($size)
	{
		$this->Type(FieldType::String);
		$this->Input(InputType::Text);	
		$this->Size($size);

		return $this;
	}

	public function Text()
	{
		$this->Type(FieldType::Text);
		$this->Input(InputType::Text);

		return $this;
	}

	public function Email($size)
	{
		$this->Input(InputType::Email);
		$this->Size($size);

		return $this;
	}

	public function Required()
	{
		$modelName = $this->model;
		$modelName::SetOption($this->name, "required", true);
		return $this;
	}
}
