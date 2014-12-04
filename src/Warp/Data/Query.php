<?php

/**
 * Query class
 * @author Jake Josol
 * @description Query class for all model queries
 */

namespace Warp\Data;

use Warp\Data\Database;
use Warp\Data\QueryObject;

class Query
{
	protected $select = array();
	protected $joins = array();
	protected $unions = array();
	protected $where = array();
	protected $orderBy = array();
	protected $skip = 0;
	protected $limit = 100;
	protected $from;
	protected static $JOIN_TYPE = array(
		"JOIN" => "JOIN",
		"INNER_JOIN" => "INNER JOIN",
		"LEFT_OUTER_JOIN" => "LEFT OUTER JOIN",
		"RIGHT_OUTER_JOIN" => "RIGHT OUTER JOIN"
	);
	protected static $WHERE_TYPE = array(
		"EQUAL_TO" => "=",
		"NOT_EQUAL_TO" => "<>",
		"GREATER_THAN" => ">",
		"GREATER_THAN_OR_EQUAL_TO" => ">=",
		"LESS_THAN" => "<",
		"LESS_THAN_OR_EQUAL_TO" => "<=",
		"MATCHES" => "LIKE",
		"MATCHES_QUERY" => "QUERY",
		"DOES_NOT_MATCH_QUERY" => "NOT QUERY",
		"CONTAINED_IN" => "IN",
		"NOT_CONTAINED_IN" => "NOT IN",
		"CONTAINS" => "HAS",
		"IS_NULL" => "IS NULL",
		"IS_NOT_NULL" => "IS NOT NULL"
	);
	protected static $ORDER_BY_TYPE = array(
		"ASCENDING" => "ASC",
		"DESCENDING" => "DESC"
	);
	
	public function __construct($source, $key=null)
	{
		$this->from = $source;
		
		if($key)
		{
			$this->orderBy[] = array(
				"field" => $key,
				"type" => static::$ORDER_BY_TYPE["ASCENDING"]
			);
		}
	}
	
	protected function addWhereClause($field, $value, $type)
	{
		$this->where[] = array(
			"field" => $field,
			"value" => $value,
			"type" => $type
		);
	}
	
	public function WhereEqualTo($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["EQUAL_TO"]);
		return $this;
	}

	public function WhereNotEqualTo($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["NOT_EQUAL_TO"]);
		return $this;
	}
	
	public function WhereGreaterThan($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["GREATER_THAN"]);
		return $this;
	}
	
	public function WhereGreaterThanOrEqualTo($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["GREATER_THAN_OR_EQUAL_TO"]);
		return $this;
	}

	public function WhereLessThan($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["LESS_THAN"]);
		return $this;
	}
	
	public function WhereLessThanOrEqualTo($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["LESS_THAN_OR_EQUAL_TO"]);
		return $this;
	}
	
	public function WhereMatches($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["MATCHES"]);
		return $this;
	}
	
	public function WhereMatchesQuery($field, $query)
	{
		$this->addWhereClause($field, $query, static::$WHERE_TYPE["MATCHES_QUERY"]);
		return $this;
	}
	
	public function WhereDoesNotMatchQuery($field, $query)
	{
		$this->addWhereClause($field, $query, static::$WHERE_TYPE["DOES_NOT_MATCH_QUERY"]);
		return $this;
	}
	
	public function WhereContainedIn($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["CONTAINED_IN"]);
		return $this;
	}

	public function WhereNotContainedIn($field, $value)
	{
		$this->addWhereClause($field, $value, static::$WHERE_TYPE["NOT_CONTAINED_IN"]);
		return $this;
	}
	
	public function WhereContains($field, $value)
	{
		$relation = $this->Model->GetRelation($field);
		$this->addWhereClause($relation, $value, static::$WHERE_TYPE["CONTAINS"]);
		return $this;
	}
	
	public function WhereIsNull($field)
	{
		$this->addWhereClause($field, null, static::$WHERE_TYPE["IS_NULL"]);
		return $this;
	}
	
	public function WhereIsNotNull($field)
	{
		$this->addWhereClause($field, null, static::$WHERE_TYPE["IS_NOT_NULL"]);
		return $this;
	}
	
	public function IncludeField($field, $value=null)
	{
		if($value) $field = "{$value} AS {$field}"; 
		$this->select[] = $field;
		return $this;
	}
	
	public function IncludeFields()
	{
		$fields = func_get_args();
		
		foreach($fields as $field) $this->select[] = $field;		
		return $this;
	}
	
	public function Join($source, $field, $connector, $type="INNER_JOIN")
	{
		$this->joins[] = array(
			"source" => $source,
			"field" => $field,
			"connector" => $connector,
			"type" => static::$JOIN_TYPE[$type]
		);

		return $this;
	}
	
	public function OrderBy($field)
	{
		$this->orderBy[] = array(
			"field" => $field,
			"type" => static::$ORDER_BY_TYPE["ASCENDING"]
		);

		return $this;
	}
	
	public function OrderByDescending($field)
	{
		$this->orderBy[] = array(
			"field" => $field,
			"type" => static::$ORDER_BY_TYPE["DESCENDING"]
		);

		return $this;
	}
	
	public function UniteWith($query)
	{
		$this->unions[] = $query;

		return $this;
	}
	
	protected function getWhereObject()
	{
		$listWhere = array();
		$listParameters = array();
		$uniqueBinding = "BIND".substr(uniqid(),0, rand(7,10))."QRY";
		$counterParameters = 0;
		
		foreach($this->where as $clause)
		{
			$clauseItem = "";
			switch($clause["type"])
			{
				case static::$WHERE_TYPE["MATCHES_QUERY"]:
					$queryObject = $clause["value"]->GetQueryObject();
					$clauseItem = "{$clause["field"]} IN ({$queryObject->QueryString})";
					foreach($queryObject->Parameters as $binding => $parameter) $listParameters[$binding] = $parameter;
				break;
				
				case static::$WHERE_TYPE["DOES_NOT_MATCH_QUERY"]:
					$queryObject = $clause["value"]->GetQueryObject();
					$clauseItem = "{$clause["field"]} NOT IN ({$queryObject->QueryString})";
					foreach($queryObject->Parameters as $binding => $parameter) $listParameters[$binding] = $parameter;
				break;
				
				case static::$WHERE_TYPE["CONTAINS"]:
					$clauseItem = ":{$uniqueBinding}{$counterParameters} IN ({$clause["field"]->GetQueryObject()->QueryString})";
					$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $clause["value"]);
					$counterParameters++;
				break;
				
				case static::$WHERE_TYPE["CONTAINED_IN"]:
				case static::$WHERE_TYPE["NOT_CONTAINED_IN"]:
					$listItems = array();
					foreach($clause["value"] as $listItem) 
					{
						$listItems[] = ":{$uniqueBinding}{$counterParameters}";
						$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $listItem);
						$counterParameters++;
					}
					$items = "'".implode("','", $listItems)."'";
					$clauseItem = "{$clause["field"]} {$clause["type"]} ({$items})";
				break;

				case static::$WHERE_TYPE["IS_NULL"]:
				case static::$WHERE_TYPE["IS_NOT_NULL"]:
					$clauseItem = "{$clause["field"]} {$clause["type"]}";
					$counterParameters++;
				break;
				
				default:
					$clauseItem = "{$clause["field"]} {$clause["type"]} :{$uniqueBinding}{$counterParameters}";
					$listParameters[":{$uniqueBinding}{$counterParameters}"] = array("value" => $clause["value"]);
					$counterParameters++;
				break;
			}
			
			$listWhere[] = $clauseItem;
		}
		$where = implode(" AND ", $listWhere);
		if(count($listWhere) > 0) $where = "WHERE {$where}";
	
		$queryObject = new QueryObject();
		$queryObject->QueryString = $where;
		$queryObject->Parameters = $listParameters;
		
		return $queryObject;
	}
	
	public function GetQueryObject()
	{
		$listFields = array();
		foreach($this->select as $field) $listFields[] = $field;
		$fields = implode(",", $listFields);
		if($fields=="") $fields = "*";
		
		$source = $this->from;
		
		$listJoins = array();
		foreach($this->joins as $join) $listJoins[] = "{$join["type"]} {$join["source"]} ON ({$join["source"]}.{$join["connector"]} = {$source}.{$join["field"]})";
		$joins = implode(" ", $listJoins);
		
		$whereObject = $this->getWhereObject();
		$where = $whereObject->QueryString;
		$listParameters = $whereObject->Parameters;
		
		$listOrderBy = array();
		foreach($this->orderBy as $order) $listOrderBy[] = $order["field"] . " " . $order["type"];
		$orderBy = implode(",", $listOrderBy);
		if($orderBy) $orderBy = "ORDER BY {$orderBy}";
		
		$listUnions = array();
		foreach($this->unions as $union) $listUnions[] = $union["field"];
		$unions = implode(",", $listUnions);

		$limit = "LIMIT {$this->skip}, {$this->limit}";
		
		$queryString = $this->createQuery($fields, $source, $joins, $where, $orderBy, $unions, $limit);
		$queryObject = new QueryObject();
		$queryObject->QueryString = $queryString;
		$queryObject->Parameters = $listParameters;
		
		return $queryObject;
	}

	protected function createQuery($fields, $source, $joins, $where, $orderBy, $unions, $limit)
	{
		return "SELECT {$fields} FROM {$source} {$joins} {$where} {$orderBy} {$unions} {$limit}";
	}
	
	public function Find()
	{
		$queryObject = $this->GetQueryObject();
		return Database::FetchAll($queryObject->QueryString, $queryObject->Parameters);
	}

	public function Count()
	{
		$this->select = array("COUNT(*) AS RESULT");
		$results = $this->Find();
		return $results[0]["RESULT"];
	}	

	public function Max($field)
	{
		$this->select = array("MAX({$field}) AS RESULT");
		$results = $this->Find();
		return $results[0]["RESULT"];
	}

	public function Min($field)
	{
		$this->select = array("MIN({$field}) AS RESULT");
		$results = $this->Find();
		return $results[0]["RESULT"];
	}

	public function Sum($field)
	{
		$this->select = array("SUM({$field}) AS RESULT");
		$results = $this->Find();
		return $results[0]["RESULT"];
	}

	public function Average($field)
	{
		$this->select = array("AVG({$field}) AS RESULT");
		$results = $this->Find();
		return $results[0]["RESULT"];
	}
	
	public function First()
	{
		$this->limit = 1;
		$results = $this->Find();
		return $results[0];
	}
 }

 
?>