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
		if(!static::$userModel) static::$userModel = new static::$user."Model";
		return static::$userModel;
	}

	public static function Check($credentials)
	{
		$query = static::getUserModel()->GetQuery();

		foreach($credentials as $credential => $value)
		{
			if($credential == "password")
				continue;

			$query->WhereEqualTo($credential, $value);
		}

		$result = $query->First();

		if($credentials["password"])
			$verified = Security::CheckHash($result->password, $credentials["password"]);
		else
			throw new Exception("Password is required");

		if(!$verified) throw new Exception("Invalid password");

		return $result;
	}

	public static function LogIn($credentials)
	{
		$user = static::Check($credentials);

		if($user)
		{
			foreach($user->GetValues() as $field => $value)
				Session::Set(self::USER_PREFIX.strtoupper($field), $value);

			Security::GenerateToken();
		}
		else
			throw new Exception("The specified user does not exist");
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
		
		return &static::$userModel;
	}
}

?>