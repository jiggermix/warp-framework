<?php

/**
 * User Not Found Exception
 * @description Password Not Found
 */

namespace Warp\Utils\Exceptions;

class UserNotFoundException extends AuthenticationException
{
	protected $message = "The specified user does not exist";
}

?>