<?php

/*
 * Authentication class
 * @author Jake Josol
 * @description Class that is responsible for authentication
 */

namespace Warp\Security;

use Warp\Utils\Enumeration\SystemField;

class Authentication
{
	const USER_PREFIX = "CURRENT_USER_";
	protected static $user = "User";
	protected static $userModel;
	private function __construct() {}

	protected static function getUserModel()
	{
		$user = static::$user."Model";
		if(!static::$userModel) static::$userModel = new $user;
		return static::$userModel;
	}

	public static function Check($credentials)
	{
		$query = static::getUserModel()->GetQuery();

		foreach($credentials as $credential => $value)
		{
			if($credential == "password")
				$value = Security::Hash($value);

			$query->WhereEqualTo($credential, $value);
		}

		$result = $query->First();

		if(!$credentials["password"])
			throw new \Exception("Password is required");

		if(!$result) throw new \Exception("Invalid login credentials");

		return $result;
	}

	public static function LogIn($credentials)
	{
		$user = static::Check($credentials);

		if($user)
		{
			foreach($user as $field => $value)
				Session::Set(self::USER_PREFIX.strtoupper($field), $value);

			Security::GenerateToken();
		}
		else
			throw new \Exception("The specified user does not exist");
	}

	public static function User()
	{
		$user = static::getUserModel();
		
		if(!$user->GetKeyValue())
		{
			$key = Session::Get(self::USER_PREFIX.strtoupper($user->GetKey()));
			static::$userModel->SetKeyValue($key);
			static::$userModel->Fetch();
		}
		
		return static::$userModel;
	}
}

?>