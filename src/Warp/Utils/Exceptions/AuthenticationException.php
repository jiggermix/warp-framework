<?php

/**
 * Authentication Exception
 * @description Base exception for authentication exceptions
 */

namespace Warp\Utils\Exceptions;

use Warp\Utils\Abstracts\CustomException;

class AuthenticationException extends CustomException 
{
	protected $message = "Authentication failed";
	protected $code = 401;
}

?>