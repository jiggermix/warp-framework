<?php

/**
 * Debugger class
 * @author Jake Josol
 * @description Responsible for application debugging and error messages
 */

namespace Warp\Utils;

use Warp\UI\Controls\AlertBox;
 
class Debugger
{
	const SOURCE_LABEL = "[Application]";
	const ERROR_LABEL = " Error: ";
	const WARNING_LABEL = " Warning: ";
	
	public static function Log($message)
	{
		return self::SOURCE_LABEL . $message;
	}
	
	public static function LogError($message)
	{
		return static::Log(self::ERROR_LABEL . $message);
	}
	
	public static function LogWarning($message)
	{
		return static::Log(self::WARNING_LABEL . $message);
	}
	
	public static function Write($message, $type)
	{
		$alertBox = AlertBox::Create();
		$alertBox->SetText($message);
		$alertBox->AddClass("warp-{$type}");
		
		return $alertBox->Render();
	}
	
	public static function WriteError($message)
	{
		$message = static::LogError($message);
		return static::Write($message, "error");
	}
	
	public static function WriteWarning($message)
	{
		$message = static::LogWarning($message);
		return static::Write($message,"warning");
	}
}

?>