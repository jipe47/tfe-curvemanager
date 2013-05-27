<?php
class Singleton
{
	private static $instances = array();
	
	public static function getInstance($classname)
	{
		if(!array_key_exists($classname, self::$instances))
			self::$instances[$classname] = new $classname();
		return self::$instances[$classname]; 
	}
	
}