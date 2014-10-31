<?php

/**
 * Invalid Credentials Exception
 * @description Invalid credentials
 */

namespace Warp\Utils\Exceptions;

class InvalidCredentialsException extends AuthenticationException
{
	protected $message = "Invalid login credentials";
}

?>