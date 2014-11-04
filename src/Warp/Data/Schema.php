<?php

/**
 * Schema class
 * @author Jake Josol
 * @description File that creates all database schemas
 */

namespace Warp\Data;

use Warp\Utils\Enumerations\SystemField;

class Schema
{
	public static function Table($table)
	{
		return new Table($table);
	}
}

class Table
{
	private $name;
	private $key;
	private $fields = array();
	private $indeces = array();

	public function __construct($name)
	{
		$this->name = $name;

		return $this;
	}

	public function Create()
	{
		$name = $this->name;
		$listFields = array();

		foreach($this->fields as $field => $details)
		{
			if($details["options"]) $options = implode(" ", $details["options"]);
			else $options = "";

			$listFields[] = "{$field} {$details["type"]} {$options}";
		}

		$fields = implode(",", $listFields);

		if($this->key)
			$fields .= ", PRIMARY KEY ({$this->key})";

		$query = "CREATE TABLE {$name} ({$fields})";
		Database::Execute($query);	

		if(count($this->indeces) > 0)
		{
			$queryIndex = "ALTER TABLE {$name}";

			foreach($this->indeces as $index)
				$queryIndex .= " ADD INDEX index_{$index} ON {$name}($index)";

			Database::Execute($queryIndex);
		}
	}

	public function Alter()
	{
		$name = $this->name;
		$listFields = array();

		foreach($this->fields as $field => $details)
		{
			if($details["options"][0] == "DELETE")
				$listFields[] = "DROP COLUMN {$field}";
			else if($details["options"][0] == "DELETE_UNIQUE")
				$listFields[] = "DROP INDEX unique_{$field}";
			else if($details["options"][0] == "UNIQUE")
				$listFields[] = "ADD CONSTRAINT unique_{$field} UNIQUE ($field)";
			else if($details["options"][0] == "FOREIGN")
				$listFields[] = "ADD CONSTRAINT foreign_{$name}_{$field} 
								FOREIGN KEY ({$field}) 
								REFERENCES {$details["options"][1]}({$details["options"][2]})
								{$details["options"][3]}";
			else if($details["options"][0] == "DELETE_FOREIGN")
				$listFields[] = "DROP CONSTRAINT foreign_{$name}_{$field}";
			else
			{
				if($details["options"]) $options = implode(" ", $details["options"]);
				else $options = "";
				
				$listFields[] = "ADD {$field} {$details["type"]} {$options}";
			}
		}

		$fields = implode(" ", $listFields);

		$query = "ALTER TABLE {$name} {$fields}";
		Database::Execute($query);
	}

	public function Drop()
	{
		$name = $this->name;
		$query = "DROP TABLE {$name}";
		Database::Execute($query);
	}

	public function Clear()
	{
		$name = $this->name;
		$query = "TRUNCATE TABLE {$name}";
		Database::Execute($query);
	}

	public function Key($field)
	{
		if(is_array($field))
			$field = implode(",", $field);

		$this->key = $field;
	}

	public function Delete($field)
	{
		$this->fields[$field] = array(
			"options" => array("DELETE")
		);

		return $this;
	}

	public function ID($field=SystemField::ID, $size=11)
	{
		$this->Key($field);
		$this->fields[$field] = array(
			"type" => "INT({$size})",
			"options" => array("AUTO_INCREMENT")
		);

		return $this;
	}

	public function Index($field)
	{
		$this->indeces[] = $field;
	}

	public function Foreign($field, $reference, $on, $options=null)
	{
		$this->fields[$field]["options"] = array("FOREIGN", $reference, $on, $options);

		return $this;
	}

	public function DeleteForeign($field)
	{
		$this->fields[$field]["options"] = array("DELETE_FOREIGN");

		return $this;		
	}

	public function Unique($field)
	{
		$this->fields[$field]["options"] = array("UNIQUE");

		return $this;
	}

	public function DeleteUnique($field)
	{
		$this->fields[$field]["options"] = array("DELETE_UNIQUE");

		return $this;
	}

	public function String($field, $size=30)
	{
		$this->fields[$field] = array(
			"type" => "VARCHAR({$size})"
		);

		return $this;
	}

	public function Password($field="password")
	{
		$this->String($field, 100);

		return $this;
	}

	public function SessionToken($field="sessionToken")
	{
		$this->String($field, 100);

		return $this;
	}

	public function Date($field)
	{
		$this->fields[$field] = array(
			"type" => "DATE",
			"options" => array("NULL", "DEFAULT NULL")
		);

		return $this;
	}

	public function DateTime($field)
	{
		$this->fields[$field] = array(
			"type" => "DATETIME",
			"options" => array("NULL", "DEFAULT NULL")
		);

		return $this;
	}

	public function Timestamps()
	{
		$this->DateTime(SystemField::CreatedAt);
		$this->DateTime(SystemField::UpdatedAt);
		$this->DateTime(SystemField::DeletedAt);

		return $this;
	}

	public function Text($field)
	{
		$this->fields[$field] = array(
			"type" => "TEXT"
		);

		return $this;
	}

	public function Integer($field, $size=11)
	{
		$this->fields[$field] = array(
			"type" => "INT({$size})"
		);

		return $this;
	}

	public function Float($field, $size=14.2)
	{
		$this->fields[$field] = array(
			"type" => "FLOAT({$size})"
		);

		return $this;
	}

	public function Decimal($field, $size=14.2)
	{
		$this->fields[$field] = array(
			"type" => "DECIMAL({$size})"
		);

		return $this;
	}

	public function Boolean($field)
	{
		$this->fields[$field] = array(
			"type" => "BIT"
		);

		return $this;
	}
}

?>