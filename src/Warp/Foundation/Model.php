<?php

/**
 * Model class
 * @author Jake Josol
 * @description Base class for all models
 */

namespace Warp\Foundation;

use Warp\Data\Query;
use Warp\Data\CommandQuery;
use Warp\Security\Security;
use Warp\Utils\Enumerations\FieldType;
use Warp\Utils\Enumerations\CommandType;
use Warp\Utils\Enumerations\SystemField;

class Model
{
	protected static $source;
	protected static $key;
	protected static $fields = array();
	protected static $scopes = array();
	protected static $timestamps = true;
	protected $dirty = array();
	protected $values = array();
	
	/***
	 * Class construct
	 * @param string $key
	 */
	public function __construct($key=null)
	{
		static::build();
		if(static::$timestamps) static::addTimestamps();
		foreach(static::$fields as $field => $value) $this->values[$field] = null;
		static::SetKeyValue($key);
	}
	
	/***
	 * Model builder
	 */
	protected static function build() 
	{
		throw new \Exception("A build method was not defined for the model");
	}

	protected function validate($errors=array()) 
	{
		foreach(static::$fields as $field => $options)
		{
			//if($options["required"] && $this->values[$field] == null) $errors[] = $field . " is a required field"; 
		}

		return $errors;
	}
	
	protected static function addTimestamps()
	{
		static::Has(SystemField::CreatedAt)->DateTime()->Guarded();
		static::Has(SystemField::UpdatedAt)->DateTime()->Guarded();
		static::Has(SystemField::DeletedAt)->DateTime()->Guarded();
	}
	
	/***
	 * Getter
	 * @param string $name
	 * @return string $value
	 */
	public function __get($name)
	{
		if(!isset(static::$fields[$name])) return null;
		return $this->values[$name];		
	}

	/***
	 * Call catch-all
	 * @param string $name
	 * @return string $value
	 */
	public function __call($name, $parameters)
	{
		return $this->GetRelation($name);
	}

	/***
	 * CallStatic catch-all
	 * @param string $name
	 * @return string $value
	 */
	public static function __callStatic($name, $parameters)
	{
		$method = "GetField" . $name;
		return static::$method;
	}
	
	public function GetFieldType($name)
	{
		return static::$fields[$name]["type"];
	}
	
	public function GetFieldInput($name)
	{
		$input = static::$fields[$name]["input"];
		if(!$input) $input = "text";
		
		return $input;
	}
	
	public function GetFieldLabel($name)
	{
		return static::$fields[$name]["label"];
	}

	public function GetFieldRequired($name)
	{
		return static::$fields[$name]["required"];
	}

	public function GetFieldGuarded($name)
	{
		return static::$fields[$name]["guarded"];
	}

	public function GetFieldHidden($name)
	{
		return static::$fields[$name]["hidden"];
	}

	public function GetFieldLookup($name)
	{
		return static::$fields[$name]["lookup"] ? static::$fields[$name]["lookup"] : array();
	}

	public function GetFieldDisplayOnly($name)
	{
		return static::$fields[$name]["displayOnly"];		
	}

	public function GetFieldData($name, $property)
	{
		return static::$fields[$name]["data-".$property];
	}
	
	/***
	 * Setter
	 * @param string $name, string $value
	 */
	public function __set($name,$value)
	{
		$this->Set($name, $value);
	}
	
	public function Set($name,$value)
	{
		if(!isset(static::$fields[$name])) return;
		if(static::$fields[$name]["displayOnly"]) return;

		switch(static::$fields[$name]["type"])
		{
			case FieldType::Integer:
				$value = (int) $value;
			break;
			
			case FieldType::Float:
				$value = (float) $value;
			break;
			
			case FieldType::Password:
				$value = Security::Hash($value);
			break;
		}
		
		$this->dirty[$name] = $value;
		$this->values[$name] = $value;
	}

	// Added alias for GetSource
	public static function Source()
	{
		return static::GetSource();
	}
	
	public static function GetSource()
	{
		return static::$source;
	}

	// Added alias for GetKey
	public static function Key()
	{
		return static::GetKey();
	}
	
	public static function GetKey()
	{
		return static::$key;
	}

	// Added alias for GetKeyValue and SetKeyValue
	public function KeyValue($value=null)
	{
		return ($value) ? $this->SetKeyValue($value) : $this->GetKeyValue();
	}
	
	public function GetKeyValue()
	{
		return $this->values[static::GetKey()];
	}
	
	public function SetKeyValue($value)
	{
		$this->Set(static::GetKey(), $value);
	}

	// Added alias for GetRelation
	public function Relation($field)
	{
		return $this->GetRelation($field);
	}
	
	public function GetRelation($field)
	{
		$relation = static::$fields[$field]["relation"];
		$key = static::$fields[$field]["key"];
		$modelName = $relation . "Model";
		$query = $modelName::GetQuery();
		$query->WhereEqualTo($key, $this->values[static::GetKey()]);
		
		return $query;
	}

	// Added alias for GetFields
	public function Fields()
	{
		return $this->GetFields();
	}
	
	public function GetFields()
	{
		return static::$fields;
	}

	// Added alias for GetValues
	public function Values()
	{
		return $this->GetValues();
	}
	
	public function GetValues()
	{
		return $this->values;
	}

	// Added alias for timestamp functions
	public function CreatedAt()
	{
		return $this->GetCreatedAt();
	}

	// Added alias for timestamp functions
	public function UpdatedAt()
	{
		return $this->GetUpdatedAt();
	}

	// Added alias for timestamp functions
	public function DeletedAt()
	{
		return $this->GetDeletedAt();
	}
	
	public function GetCreatedAt()
	{
		return $this->fields[SystemField::CreatedAt];
	}
	
	public function GetUpdatedAt()
	{
		return $this->fields[SystemField::UpdatedAt];
	}
	
	public function GetDeletedAt()
	{
		return $this->fields[SystemField::DeletedAt];
	}

	// Added as an alias to GetQuery
	public static function Query()
	{
		$scopes = func_get_args();
		return static::GetQuery($scopes);
	}
	
	public static function GetQuery()
	{		
		$query =  new Query(static::GetSource());

		if(count(static::$fields) == 0) static::build();
		
		foreach(static::$fields as $field => $details)
		{
			if(!$details["hidden"] && !$details["pointer"] && $field && !$details["displayOnly"])
				$query->IncludeField($field, static::GetSource().".".$field);

			if($details["pointer"])
			{
				$pointer = $details["pointer"];
				$pointerModel = new $pointer;
				$query->Join($pointerModel->GetSource(), $details["key"], $pointerModel->GetKey(), "LEFT_OUTER_JOIN");
				
				foreach($pointerModel->GetFields() as $pointerField => $pointerDetails)
				{
					if(!$pointerDetails["hidden"] && !$pointerDetails["pointer"] && $pointerField)
						$query->IncludeField(
							'"'.$pointerModel->GetSource().".".$pointerField.'"', 
							$pointerModel->GetSource().".".$pointerField
						);
				}
			}
		}

		// Retrieve scopes
		$scopes = func_get_args();
		
		if($scopes)
		{
			if(is_array($scopes[0])) $scopes = $scopes[0];

			foreach($scopes as $scope)
			{
				$scopeAction = static::$scopes[$scope];
				$query = $scopeAction($query);
			}
		}
		
		return $query;
	}

	public function Fill($input)
	{
		foreach($input as $field => $value)
			if(isset(static::$fields[$field]) && !$this->GetFieldGuarded($field))
				$this->Set($field, $value);
	}
	
	public function Fetch()
	{
		$query = static::GetQuery();
		$query->WhereEqualTo(static::GetSource().".".static::GetKey(), static::GetKeyValue());
		
		$result = $query->First();

		if($result) foreach($result as $key => $item) $this->values[$key] = $item;
		
		return $result;
	}

	public static function SaveAll()
	{
		$arguments = func_get_args();
		$models = is_array($arguments[0])? $arguments[0] : $arguments;
		$commands = array();

		foreach($models as $model) $commands[] = $model->SaveCommand();

		CommandQuery::ExecuteAll($commands);
	}

	public static function SaveEach($modelItems)
	{
		$commands = array();

		foreach($modelItems as $modelItem)
		{
			$model = $modelItem["model"];
			$to = $modelItem["to"];
			
			$commands[] = function($result) use ($model, $to)
			{
				if($to) $model->Set($to, $result->lastInsertID);

				return $model->SaveCommand();
			};
		}

		CommandQuery::ExecuteEach($commands);
	}

	public function SaveCommand()
	{
		$command = new CommandQuery(static::GetSource(), static::GetKey());
		$now = date("Y-m-d H:i:s");
		
		if(static::GetKeyValue() == null)
		{
			$command->SetType(CommandType::Add);
			$this->Set(SystemField::CreatedAt, $now);
			$this->Set(SystemField::UpdatedAt, $now);
		}
		else
		{
			$command->SetType(CommandType::Edit);
			$command->WhereEqualTo(static::GetKey(), static::GetKeyValue());
			$this->Set(SystemField::UpdatedAt, $now);
		}
		
		foreach($this->dirty as $field => $value)
		{
			$details = static::$fields[$field];

			switch($this->GetFieldType($field))
			{
				case FieldType::Pointer:
					$command->BindParameter($field, $this->values[$field]->GetKeyValue(), $details["type"]);
				break;

				default:
					$command->BindParameter($field, $this->values[$field], $details["type"]);
				break;
			}
		}

		return $command;
	}

	public function Touch()
	{
		if(static::GetKeyValue())
		{
			$this->Set(SystemField::UpdatedAt, date("Y-m-d H:i:s"));
			$this->Save();
		}
		else return;
	}
	
	public function Save()
	{
		$errors = $this->validate(); 
		
		if(count($errors) == 0)
		{
			// Execute before save
			$this->beforeSave($this);

			// Prepare command
			$command = $this->SaveCommand();
			$commandReturn = $command->Execute();
			
			// Reset dirty fields
			$this->dirty = array();
			
			// Set key value
			if(static::GetKeyValue() == null) $this->SetKeyValue($commandReturn->lastInsertID);

			// Execute after save
			$this->afterSave($this);

			return $commandReturn->rowsAffected;
		}
		else
		{
			return $errors;
		}
	}

	protected function beforeSave($model)
	{
		return;
	}

	protected function afterSave($model)
	{
		return;
	}
	
	public function Delete()
	{
		$command = new CommandQuery(static::GetSource(), static::GetKey());
		$command->WhereEqualTo(static::GetKey(), static::GetKeyValue());
		$command->SetType(CommandType::Delete);
		$command->Execute();
	}

	public function SoftDelete()
	{
		// SoftDelete only works on tables with "deletedAt" column.
		$command = new CommandQuery(static::GetSource(), static::GetKey());
		$command->WhereEqualTo(static::GetKey(), $this->GetKeyValue());
		$command->SetType(CommandType::Edit);
		$command->BindParameter(SystemField::DeletedAt, date("Y-m-d H:i:s"), null);
		$command->BindParameter(SystemField::UpdatedAt, date("Y-m-d H:i:s"), null);
		$command->Execute();
	}

	public function Restore()
	{
		// Restore only works on tables with "deletedAt" column.
		$command = new CommandQuery(static::GetSource(), static::GetKey());
		$command->WhereEqualTo(static::GetKey(), $this->GetKeyValue());
		$command->SetType(CommandType::Edit);
		$command->BindParameter(SystemField::DeletedAt, null, null);
		$command->BindParameter(SystemField::UpdatedAt, date("Y-m-d H:i:s"), null);
		$command->Execute();
	}
	
	public static function Has($field)
	{
		static::$fields[$field] = array();
		$fieldObject = new Field(get_called_class(), $field);
		return $fieldObject;
	}
	
	public static function HasMany($model, $key=null)
	{
		if(!$key) $key = $model."_id";
		$fieldObject = static::Has($model)
						->Relation($model, $key);
		return $fieldObject;
	}
	
	public static function BelongsTo($model, $key=null)
	{
		if(!$key) $key = $model."_id";
		$fieldObject = static::Has($model)
						->Pointer($model, $key);
		return $fieldObject;
	}

	public static function BelongsToMany($model, $join=null)
	{
		$fieldObject = static::Has($model)
						->MultiPointer($model, $join);
		return $fieldObject;
	}
	
	public static function Translates($model, $key=null)
	{
		if(!$key) $key = $model;
		$fieldObject = static::Has($model)
						->Translate($model, $key);
		return $fieldObject;
	}
	
	public static function SetOption($field, $option, $value)
	{
		static::$fields[$field][$option] = $value;
	}
	
	public static function Scope($name, $action)
	{
		static::$scopes[$name] = $action;
	}
}
