<?php

/**
 * Response class
 * @author Jake Josol
 * @description Responsible for the application responses
 */

namespace Warp\Http;

class Response
{
	public static function Make($status, $message, $result)
	{
		if(!$message && !$result)
			return new ResponseObject($status, "Unknown format", null);
		else
			return new ResponseObject($status, $message, $result);			
	}
}

class ResponseObject
{
	private $status;
	private $message;
	private $result;

	public function __construct($status, $message, $result)
	{
		$this->status = (int) $status;
		$this->message = str_replace('"',"'", $message);
		$this->result = is_string($result) ? preg_replace("/['|\"|:]/", "", utf8_decode($result)) : $result;
	}

	public function ToJSON()
	{
		return json_encode((object) array(
				"status" => $this->status,
				"message" => $this->message,
				"result" => $this->result
			));
	}

	public function __toString()
	{
		return $message;
	}
}

?>