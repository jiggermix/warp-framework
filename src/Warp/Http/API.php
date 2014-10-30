<?php

/**
 * API Class
 * @author Jason Josol
 * @description Class that is responsible for API responses
 */

namespace Warp\Http;

use Warp\Http\Response;

class API
{
	public static function Request($parameters, $type = "JSON")
	{
		$response = Response::Make(APIStatus::Unknown, APIMessage::Unknown, array());
		$controllerClass = $parameters["class"];

		if(!$controllerClass->HasAPI()) return Response::Make(APIStatus::Unknown, APIMessage::Unknown, array())->ToJSON();
		
		switch($type)
		{
			case APIRequest::REST:
				try
				{
					// Retrieve Array
					$controller = $controllerClass;
					$actionName = $parameters["action"] . "Action";
					
					if(method_exists($controller, $actionName))
					{
						$results =  $controller->$actionName($parameters["parameters"]);

						if($results)
							$response = Response::Make(APIStatus::Success, APIMessage::Success, json_decode($results))->ToJSON();
						else
							$response = Response::Make(APIStatus::Blank, APIMessage::Blank, array())->ToJSON();	
					}
					else $response = Response::Make(APIStatus::Invalid, APIMessage::Invalid, array())->ToJSON();
				} 
				catch(Exception $ex)
				{    
					$response = Response::Make(APIStatus::Unknown, APIMessage::Unknown, array())->ToJSON();
				}
			break;
			
			case APIRequest::SOAP:
				// TO-DO: Reimplement SOAP
			break;
		}
		
		return $response;
	}
}

class APIStatus
{
	const Success = 200;
	const Unknown = 404;
	const Invalid = 405;
	const Blank = 406;
}

class APIMessage
{
	const Success = "Success";
	const Unknown = "Unknown request";
	const Invalid = "Invalid request";
	const Blank = "Blank result";
}
 
class APIRequest
{
	const REST = "JSON";
	const SOAP = "XML";
}

?>