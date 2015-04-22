<?php

/**
 * Foundation Factory class
 * @author Jake Josol
 * @description Base class for all models
 */

namespace Warp\Foundation;

use Warp\Core\Reference;
use Warp\Http\Response;
use Warp\Utils\FileHandle;
use Warp\Utils\Enumerations\SystemField;

class FoundationFactory
{
	public static function Generate($parameters)
	{
		try
		{
			static::MakeModel($parameters);
			static::MakeController($parameters);
			static::MakeView($parameters);

			return Response::Make(200, "Success", array("file" => $filename, "class" => $class, "source" => $source))->ToJSON();
		}
		catch(Exception $ex)
		{
			return Response::Make(405, "Error", $ex->getMessage())->ToJSON();
		}
	}

	protected static function replaceTemplateVars($vars, $templateContents)
	{
		$class = $vars["class"];
		$source = $vars["source"];
		$id = $vars["id"];
		$layout = $vars["layout"]? $vars["layout"] : "default.php";
		$page = $vars["page"];

		return str_replace("{{page}}", $page,
					str_replace("{{layout}}", $layout, 
						str_replace("{{id}}",  $id, 
							str_replace("{{source}}", $source, 
								str_replace("{{class}}", $class, $templateContents)))));
	}

	protected static function MakeModel($parameters)
	{
		$class = $parameters["class"] . "Model";
		$source = $parameters["source"];
		$id = $parmeters["id"]? $parameters["id"] : SystemField::ID;
		$directory = Reference::Path("model");
		$filename = "{$class}.php";

		$template = new FileHandle("model.tpl", __DIR__."/Templates");
		$templateContents = $template->Contents();
		$template->Close();
		$vars = array(
			"class" => $class, 
			"source" => $source, 
			"id" => $id
		);
		$fileContents = static::replaceTemplateVars($vars, $templateContents);

		$file = new FileHandle($filename, $directory);
		$file->Write($fileContents);
		$file->Close();
	}

	protected static function MakeController($parameters)
	{
		$class = $parameters["class"] . "Controller";
		$directory = Reference::Path("controller");
		$filename = "{$class}.php";

		$template = new FileHandle("controller.tpl", __DIR__."/Templates");
		$templateContents = $template->Contents();
		$template->Close();
		$vars = array(
			"class" => $class
		);
		$fileContents = static::replaceTemplateVars($vars, $templateContents);

		$file = new FileHandle($filename, $directory);
		$file->Write($fileContents);
		$file->Close();
	}	

	protected static function MakeView($parameters)
	{
		$class = $parameters["class"] . "View";
		$directory = Reference::Path("view");
		$filename = "{$class}.php";

		$template = new FileHandle("view.tpl", __DIR__."/Templates");
		$templateContents = $template->Contents();
		$template->Close();
		$vars = array(
			"class" => $class,
			"layout" => $layout,
			"page" => $page
		);
		$fileContents = static::replaceTemplateVars($vars, $templateContents);

		$file = new FileHandle($filename, $directory);
		$file->Write($fileContents);
		$file->Close();

		$pageDirectory = Reference::Path("page");
		$pageFilename = "default.php";

		$pageFile = new FileHandle($pageFilename, $pageDirectory);
		$pageFile->Write("<!-- Create your page here -->");
		$pageFile->Close();
	}
}