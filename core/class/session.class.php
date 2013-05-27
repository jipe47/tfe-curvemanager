<?php
/**
 * Interface with $_SESSION.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage IO
 */
class Session
{
	const DEFAULT_VALUE = "";
	const DEFAULT_INT = -1;
	const DEFAULT_BOOL = false;
	const DEFAULT_STRING = "";
	const DEFAULT_RAW = -1;
	const DEFAULT_FLOAT = -1.0;
	
	public static function keyExists($key)
	{
		return isset($_SESSION[$key]);
	}
	
	public static function raw($key, $default = self::DEFAULT_RAW)
	{
		if(array_key_exists($key, $_SESSION))
			return $_SESSION[$key];
		else
			return $default;
	}
	public static function value($key, $default = self::DEFAULT_VALUE)
	{
		if(array_key_exists($key, $_SESSION))
			if(!get_magic_quotes_gpc())
				return $_SESSION[$key];
			else if(is_string($_SESSION[$key]))
				return stripslashes($_SESSION[$key]);
			else
				return $_SESSION[$key];
		else
			return $default;
	}
	
	public static function string($field, $default = self::DEFAULT_STRING)
	{
		return self::value($field, $default);
	}
	
	public static function int($field, $default = self::DEFAULT_INT)
	{
		return !empty($_SESSION[$field]) ? intval($_SESSION[$field]) : $default;
	}
	
	public static function float($field, $default = self::DEFAULT_FLOAT)
	{
		return !empty($_SESSION[$field]) ? floatval($_SESSION[$field]) : $default;
	}
	
	public static function bool($field, $default = self::DEFAULT_BOOL)
	{
		return !empty($_SESSION[$field]) ? (bool)$_SESSION[$field] : $default;
	}
	
	public static function boolValue($field, $trueValue, $default = self::DEFAULT_BOOL)
	{
		return !empty($_SESSION[$field]) ? $_SESSION[$field] == $trueValue : $default;
	}
	
	public function __get($var)
	{
		return self::value($var);
	}
}
?>