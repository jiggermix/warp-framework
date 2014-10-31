<?php

/**
 * Authentication Exception
 * @description Base exception for authentication exceptions
 */

namespace Warp\Utils\Exceptions;

class AuthenticationException extends CustomException 
{
	protected $message = "Authentication failed";
	protected $code = 401;
}

?>