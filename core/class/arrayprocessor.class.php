<?php
class ArrayProcessor
{
	protected $array;
	
	const DEFAULT_VALUE = "";
	const DEFAULT_INT = -1;
	const DEFAULT_BOOL = false;
	const DEFAULT_STRING = "";
	const DEFAULT_RAW = -1;
	const DEFAULT_FLOAT = -1.0;
	
	public function __construct($array)
	{
		$this->array = $array;
	}
	
	public function setDefault($key, $value)
	{
		if(array_key_exists($key, $this->array))
			return;
		$this->array[$key] = $value;
	}
	public function set($key, $value)
	{
		$this->array[$key] = $value;
	}
	
	public function unsetValue($key)
	{
		unset($this->array[$key]);
	}
	
	public function getArray()
	{
		return $this->array;
	}
	
	public function setArray($array)
	{
		$this->array = $array;
	}
	
	public function keyExists($key)
	{
		return self::keyExistsStatic($this->array, $key);
	}
	public static function keyExistsStatic($array, $key)
	{
		return array_key_exists($key, $array);
	}
	public function raw($key, $default = self::DEFAULT_RAW)
	{
		return self::rawStatic($this->array, $key, $default);
	}
	
	public function string($field, $default = self::DEFAULT_STRING)
	{
		return self::stringStatic($this->array, $field, $default);
	}
	
	public function int($field, $default = self::DEFAULT_INT)
	{
		return self::intStatic($this->array, $field, $default);
	}
	
	public function float($field, $default = self::DEFAULT_FLOAT)
	{
		return self::floatStatic($this->array, $field, $default);
	}
	
	public function bool($field, $default = self::DEFAULT_BOOL)
	{
		return self::boolStatic($this->array, $field, $default);
	}
	
	public function boolValue($field, $trueValue, $default = self::DEFAULT_BOOL)
	{
		return self::boolValueStatic($this->array, $field, $trueValue, $default);
	}
	
	public function __get($var)
	{
		return $this->raw($var);
	}
	
	public function __set($k, $v)
	{
		$this->arg[$k] = $v;
	}
	
	public function getNbrEntry()
	{
		return count($this->array);
	}
	
	/*****************************/
	/*** Static Access Methods ***/
	/*****************************/
	
	public static function rawStatic($array, $key, $default = self::DEFAULT_RAW)
	{
		return array_key_exists($key, $array) ? $array[$key] : $default;
	}
	
	public static function stringStatic($array, $key, $default = self::DEFAULT_STRING)
	{
		return array_key_exists($key, $array) ? $array[$key] : $default;
	}
	
	public static function intStatic($array, $key, $default = self::DEFAULT_INT)
	{
		return !empty($array[$key]) ? intval($array[$key]) : $default;
	}
	
	public static function floatStatic($array, $key, $default = self::DEFAULT_FLOAT)
	{
		return !empty($array[$key]) ? floatval($array[$key]) : $default;
	}
	
	public static function boolStatic($array, $key, $default = self::DEFAULT_BOOL)
	{
		return !empty($array[$key]) ? (bool) $array[$key] : $default;
	}
	
	public static function boolValueStatic($array, $key, $trueValue, $default = self::DEFAULT_BOOL)
	{
		return !empty($array[$key]) ? $array[$key] == $trueValue : $default;
	}
}