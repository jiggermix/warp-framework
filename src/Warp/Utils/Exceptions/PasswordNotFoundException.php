<?php

/**
 * Password Not Found Exception
 * @description Password Not Found
 */

namespace Warp\Utils\Exceptions;

class PasswordNotFoundException extends AuthenticationException
{
	protected $message = "Password is required";
}

?>