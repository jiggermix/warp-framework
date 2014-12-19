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
		return Response::Make(100, "Sample", 0);
	}
}

class APIStatus
{
	const Success = 200;
	const Unknown = 404;
	const Invalid = 400;
	const Blank = 204;
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