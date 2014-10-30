<?php

/*
 * API Controller trait
 * @author Jake Josol
 * @description Trait for API Controllers
 */

namespace Warp\Utils\Traits;

use Warp\Utils\Enumerations\FieldType;

trait APIControllerTrait
{
	public static $HasAPI = true;

	public function ViewAction($parameters)
	{
		if($parameters["id"])
		{
			$key = $parameters["id"];
			$model = static::GetModel();
			$model->SetKeyValue($key);
			$model->Fetch();
			$result = array();
			
			foreach($model->GetValues() as $key => $value) 
				if(!$model->GetFieldGuarded($key) && !$model->GetFieldHidden($key))
					$result[$key] = $value;

			return json_encode($result);				
		}
		else
		{
			$query = static::GetModel()->GetQuery();
			$query->OrderByDescending(static::GetModel()->GetKey());
			$results = $query->Find();
			
			$listRelations = array();
			foreach(static::GetModel()->GetFields() as $field => $details)
				if($details["type"] == FieldType::Relation)
					$listRelations[] = $field;
		
															
			foreach($results as $key => $result)
				foreach($listRelations as $itemRelation)
				{
					$model = static::GetModel();
					$model->SetKeyValue($result[static::GetModel()->GetKey()]);
					
					$results[$key][$itemRelation] = $model->GetRelation($itemRelation)->Find();
				}

			return json_encode($results);
		}
	}
	
	public function AddAction($parameters)
	{
		$model = static::GetModel();
		foreach($parameters as $parameter => $value) $model->Set($parameter, $value);

		$model->Save();
		return json_encode(array("key" => $model->GetKeyValue()));
	}
	
	public function EditAction($parameters)
	{
		$model = static::GetModel();
		foreach($parameters as $parameter => $value) $model->Set($parameter, $value);
		if(!$model->GetKeyValue()) return;
		
		$model->Save();
		return json_encode(array("key" => $model->GetKeyValue()));
	}
	
	public function DeletAction($parameters)
	{
		$model = static::GetModel();
		$key = static::GetModel()->GetKey();
		if(!isset($parameters[$key])) return;
		$model->SetKeyValue($parameters[$key]);
		
		$model->Delete();
		return json_encode(array("key" => $model->GetKeyValue()));
	}	
}

?>