<?php
/**
 * Interface with $_COOKIE.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage IO
 */
class Cookie
{
	const DEFAULT_VALUE = "";
	const DEFAULT_INT = -1;
	const DEFAULT_BOOL = false;
	const DEFAULT_STRING = "";
	const DEFAULT_RAW = -1;
	const DEFAULT_FLOAT = -1.0;
	
	public static function raw($key, $default = self::DEFAULT_RAW)
	{
		if(array_key_exists($key, $_COOKIE))
			return $_COOKIE[$key];
		else
			return $default;
	}
	public static function value($key, $default = self::DEFAULT_VALUE)
	{
		if(array_key_exists($key, $_COOKIE))
			if(!get_magic_quotes_gpc())
				return $_COOKIE[$key];
			else if(is_string($_COOKIE[$key]))
				return stripslashes($_COOKIE[$key]);
			else
				return $_COOKIE[$key];
		else
			return $default;
	}
	
	public static function exists($field)
	{
		return array_key_exists($field, $_COOKIE);
	}
	
	public static function string($field, $default = self::DEFAULT_STRING)
	{
		return self::value($field, $default);
	}
	
	public static function int($field, $default = self::DEFAULT_INT)
	{
		return !empty($_COOKIE[$field]) ? intval($_COOKIE[$field]) : $default;
	}
	
	public static function float($field, $default = self::DEFAULT_FLOAT)
	{
		return !empty($_COOKIE[$field]) ? floatval($_COOKIE[$field]) : $default;
	}
	
	public static function bool($field, $default = self::DEFAULT_BOOL)
	{
		return !empty($_COOKIE[$field]) ? (bool)$_COOKIE[$field] : $default;
	}
	
	public static function boolValue($field, $trueValue, $default = self::DEFAULT_BOOL)
	{
		return !empty($_COOKIE[$field]) ? $_COOKIE[$field] == $trueValue : $default;
	}
	
	public function __get($var)
	{
		return self::value($var);
	}
}
?>