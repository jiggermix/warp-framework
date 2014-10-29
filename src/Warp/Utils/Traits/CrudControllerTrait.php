<?php

/*
 * Crud Controller trait
 * @author Jake Josol
 * @description Trait for Crud Controllers
 */

namespace Warp\Utils\Traits;

trait CrudControllerTrait
{
	public function CreateAction($parameters)
	{
		static::GetView()->Crud("create")->Render();
	}

	public function ReadAction($parameters)
	{
		static::GetView()->Crud("read", $parameters)->Render();
	}

	public function UpdateAction($parameters)
	{
		static::GetView()->Crud("update", $parameters)->Render();
	}

	public function DestroyAction($parameters)
	{
		static::GetView()->Crud("destroy", $parameters)->Render();
	}
}

?>