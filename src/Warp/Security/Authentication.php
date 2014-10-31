<?php

/*
 * Authentication class
 * @author Jake Josol
 * @description Class that is responsible for authentication
 */

namespace Warp\Security;

use Warp\Utils\Enumerations\SystemField;
use Warp\Utils\Exceptions\InvalidCredentialsException;
use Warp\Utils\Exceptions\PasswordNotFoundException;
use Warp\Utils\Exceptions\UserNotFoundException;
use Warp\Session\Session;

class Authentication
{
	const USER_PREFIX = "CURRENT_USER_";
	const TOKEN = "SESSION_TOKEN";
	protected static $user = "User";
	protected static $userModel;
	private function __construct() {}

	protected static function getUserModel()
	{
		$user = static::$user."Model";
		if(!static::$userModel) static::$userModel = new $user;
		return static::$userModel;
	}

	public static function SetModel($model)
	{
		static::$user = $model;
	}

	public static function Validate($credentials)
	{
		$query = static::getUserModel()->GetQuery();
		$query->WhereIsNull(SystemField::DeletedAt);

		foreach($credentials as $credential => $value)
		{
			if($credential == "password")
				$value = Security::Hash($value);

			if(substr($credential, 0, 1) == "#")				
				$value = Security::Hash($value);

			$query->WhereEqualTo($credential, $value);
		}

		$result = $query->First();

		if(!$result) throw new InvalidCredentialsException();

		return $result;
	}

	public static function LogIn($credentials)
	{
		if(!$credentials["password"])
			throw new PasswordNotFoundException();

		$user = static::Validate($credentials);

		if($user)
		{
			foreach($user as $field => $value)
				Session::Set(self::USER_PREFIX.strtoupper($field), $value);

			Security::GenerateToken();

			return true;
		}
		else
			throw new UserNotFoundException();
	}

	public static function LogOut()
	{
		$user = static::getUserModel();
		static::$userModel = null;

		foreach($user->GetFields() as $field => $details)
			Session::Delete(self::USER_PREFIX.strtoupper($field));

		Session::Delete(self::TOKEN);
	}

	public static function User()
	{
		$token = Session::Get(self::TOKEN);
		
		if($token)
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
		else
		{
			return null;
		}
	}
}

?>