<?php
/**
 * Interface with $_POST.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage IO
 */
class Post
{
	const DEFAULT_VALUE = "";
	const DEFAULT_INT = -1;
	const DEFAULT_BOOL = false;
	const DEFAULT_STRING = "";
	const DEFAULT_RAW = -1;
	const DEFAULT_FLOAT = -1.0;
	const DEFAULT_DOUBLE = -1.0;
	
	public static function raw($key, $default = self::DEFAULT_RAW)
	{
		if(array_key_exists($key, $_POST))
			return $_POST[$key];
		else
			return $default;
	}
	public static function value($key, $default = self::DEFAULT_VALUE)
	{
		if(array_key_exists($key, $_POST))
			if(!get_magic_quotes_gpc())
				return $_POST[$key];
			else if(is_string($_POST[$key]))
				return stripslashes($_POST[$key]);
			else
				return $_POST[$key];
		else
			return $default;
	}
	
	public static function exists($field)
	{
		return array_key_exists($field, $_POST);
	}
	
	public static function string($field, $default = self::DEFAULT_STRING)
	{
		return self::value($field, $default);
	}
	
	public static function int($field, $default = self::DEFAULT_INT)
	{
		return !empty($_POST[$field]) ? intval($_POST[$field]) : $default;
	}
	
	public static function float($field, $default = self::DEFAULT_FLOAT)
	{
		return !empty($_POST[$field]) ? floatval($_POST[$field]) : $default;
	}
	
	public static function double($field, $default = self::DEFAULT_DOUBLE)
	{
		return !empty($_POST[$field]) ? doubleval($_POST[$field]) : $default;
	}
	
	public static function bool($field, $default = self::DEFAULT_BOOL)
	{
		return !empty($_POST[$field]) ? (bool)$_POST[$field] : $default;
	}
	
	public static function boolValue($field, $trueValue, $default = self::DEFAULT_BOOL)
	{
		return !empty($_POST[$field]) ? $_POST[$field] == $trueValue : $default;
	}
	
	public function __get($var)
	{
		return self::value($var);
	}
}
?>