<?php

/**
 * Security class
 * @author Jake Josol
 * @description Utility class for all security tiers
 */

namespace Warp\Security;
 
class Security
{
    const HASH_COST_LOG2 = 8;
    const HASH_PORTABLE = false;
    
    public static function Hash($password)
    {
        $hasher = new \PasswordHash(self::HASH_COST_LOG2, self::HASH_PORTABLE);
        $spassword = $hasher->HashPassword($password);
        if(strlen($spassword) < 20) throw new \Exception("Failed to secure the password.");
        unset($hasher);
        
        return $spassword;
    }
    
    public static function CheckHash($password, $hash)
    {
        $hasher = new \PasswordHash(self::HASH_COST_LOG2, self::HASH_PORTABLE);
        return $hasher->CheckPassword($password,$hash);
    }
    
    public static function GenerateToken()
    {
        $token = \md5(\uniqid(\mt_rand(), true));
        Session::Set("SESSION_TOKEN", $token);
    }
    
    public static function GetToken()
    {
        return Session::Get("SESSION_TOKEN");
    }
    
    public static function ValidateToken($token)
    {
        $sessionToken = Session::Get("SESSION_TOKEN");
        if($sessionToken == $token)
            return true;
        else
            return false;
    }
}

?>