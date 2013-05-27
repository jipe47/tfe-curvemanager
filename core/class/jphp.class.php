<?php
/**
 * Used to access and modify miscellaneous architecture properties:
 * 	- rights;
 * 	- on and unonload functions;
 * 	- buffer;
 * 	- debug state;
 * 	- INI files.
 *
 * It also allows recursive path inclusion.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */
class JPHP
{
	public static $PRODUCTION = "production";
	public static $DEV = "dev";
	
	private static $mode = "dev"; // self::$DEV does not work ?! :@
	private static $ini = array();

	/**
	 * Function names that will be added to the "onload" attribute of <body> tag.
	 * @var string_array
	 */
	private static $onload_functions = array();

	/**
	 * Function names that will be added to the "onunload" attribute of <body> tag.
	 * @var string_array
	 */
	private static $onunload_functions = array();

	/**
	 * List of declared SQL tables.
	 * @var string_array
	 */
	private static $sql_tables = array();

	/**
	 * Contains every declared rights.
	 * @var array_string
	 */
	private static $array_right = array();

	const ARG_SEPARATOR = "/";

	/**
	 * Is debug mode activated ?
	 * @var boolean
	 */
	private static $debug = DEBUG;

	/**
	 * Buffer hidden in the page.
	 * @var string
	 */
	private static $buffer = "";
	
	private static $arg = null;
	
	
	public static function setMode($m)
	{
		self::$mode = $m;
	}
	public static function isDev()
	{
		return self::$mode == self::$DEV;
	}
	
	public static function isProduction()
	{
		return self::$mode == self::$PRODUCTION;
	}
	
	/******************/
	/*** Arg Access ***/
	/******************/
	
	
	private static function loadArg()
	{
		if(!is_null(self::$arg))
			return;
		
		self::$arg = Singleton::getInstance("Arg");
		/*if(in_array(self::$arg->string(0), array("Ajax", "Request", "Admin")))
			self::$arg->unsetValue(1);
		self::$arg->unsetValue(0);
		self::$arg->normalize();*/
	}
	
	public static function getCurrentPage()
	{
		self::loadArg();
		return implode(self::ARG_SEPARATOR, self::$arg->getArray());
	}
	
	public static function setArg($arg)
	{
		self::loadArg();
		self::$arg->setArray($arg);
	}
	
	public static function arg($index)
	{
		self::loadArg();
		return self::$arg->string($index);
	}
	
	public static function argc()
	{
		self::loadArg();
		return self::$arg->argc();
	}
	
	public static function removeArg($index)
	{
		self::loadArg();
		//out::message("Before : " . implode("/", self::$arg->getArray()), Message::WARNING);
		self::$arg->unsetValue($index);
		self::$arg->normalize();
		//out::message("After : " . implode("/", self::$arg->getArray()), Message::WARNING);
	}
	
	public static function parseArg($removeArg = false)
	{
		$array = array("type" => -1, "page" => -1, "arg" => array());
		
		self::loadArg();
		
		$page = self::arg(0);
		$type = "page";
		
		if(in_array(strtolower($page), PageRegister::getTypes()))
		{
			$type = strtolower($page);
			$page = self::arg(1);
			
			if($removeArg)
			{	
				self::removeArg(0);
				self::removeArg(0);
			}
			
		}
		else if($removeArg)
			self::removeArg(0);
		
		$arg = array();
		for($i = 0; $i < self::argc() ; $i++)
		{
			$arg[] = self::arg($i);
		}
		$array["type"] = $type;
		$array["page"] = $page;
		$array["arg"] = $arg;
		
		return $array;
	}

	/**********************/
	/*** Buffer Methods ***/
	/**********************/

	/**
	 * Appends data to the buffer.
	 * @param string $s Content that will be append to the buffer.
	 */
	public static function addToBuffer($s)
	{
		self::$buffer .= $s;
	}

	/**
	 * Get the content of the buffer.
	 * @return Content of the buffer.
	 */
	public static function getBuffer()
	{
		return self::$buffer;
	}
	
	// TODO Fill this method in
	public static function bufferImage($src, $attr) // $attr = array('src' => .., 'onclick' => ..., ...)
	{
		
	}

	/*********************/
	/*** MySQL Methods ***/
	/*********************/

	/**
	 * Defines SQL constants that can be use in the architecture.
	 *
	 * @param string_or_string_array $t Array of sql table names, or a single table name.
	 * @param string $alias Optionnal alias for the table.
	 * @throws Exception if a table is already defined.
	 */
	public static function addSqlTable($t, $alias = '')
	{
		if(!is_array($t))
		{
			if($alias != '')
				$t = array($t => $alias);
			else
				$t = array($t);
		}
		foreach($t as $table_name => $alias_name)
		{
			if(is_int($table_name))
				$table_name = $alias_name;

			if(in_array($alias_name, self::$sql_tables))
			{
				Log::add(LogMessageType::Error, "SQL table " . $alias_name . " already declared.");
				throw new Exception("SQL table " . $alias_name . " already declared.");
			}
			else
				define(CONSTANT_PREFIX.strtoupper($alias_name), SQL_PREFIX.$table_name);
		}
	}

	
	/***************************/
	/*** Ini-related Methods ***/
	/***************************/
	
	/**
	 * For debug purpose ONLY.
	 */
	public static function getAllIni()
	{
		return self::$ini;
	}
	
	/**
	 * Imports an ini file.
	 * @param string $path Path to the ini file.
	 * @param string $name Name used to access to the file content.
	 */
	public static function importIni($path, $name)
	{
		if(!array_key_exists($name, self::$ini))
			self::$ini[$name] = array();
	
		$array = parse_ini_file($path, true);
	
		foreach($array as $section => $v)
		{
			if(!array_key_exists($section, self::$ini[$name]))
				self::$ini[$name][$section] = array();
	
			foreach($v as $key => $value)
				self::$ini[$name][$section][$key] = $value;
		}
	}
	
	/**
	 * Gets ini value from the configuration file (config/config.ini) in the 
	 * "config" section.
	 * @param string $field Field name.
	 * @param string $default Default value, if the field does not exist.
	 */
	public static function get($field, $default = '')
	{
		return self::getIni(STRUCTURE_NAME, "config", $field, $default);
	}

	/**
	 * Gets a value from an ini file.
	 * @param string $name Name to access to the ini file.
	 * @param string $section Section name.
	 * @param string $field Field name.
	 * @param string $default Default value if the file, section or field does
	 * 				 not exist.
	 */
	public static function getIni($name, $section, $field, $default = '')
	{
		if(	!array_key_exists($name, self::$ini) ||
				!array_key_exists($section, self::$ini[$name]) ||
				!array_key_exists($field, self::$ini[$name][$section]))
			return $default;
		else
			return self::$ini[$name][$section][$field];
	}
	
	/**
	 * Changes an ini value. The name or the section are created if they do
	 * not exist. 
	 * 
	 * @param string $name Name to access to the ini file.
	 * @param string $section Section name.
	 * @param string $field Field name.
	 * @param string $value New value of the field
	 */
	public static function setIni($name, $section, $field, $value)
	{
		if(!array_key_exists($name, self::$ini))
			self::$ini[$name] = array();
		
		if(!array_key_exists($section, self::$ini[$name]))
			self::$ini[$name][$section] = array();
		
		self::$ini[$sections][$field] = $value;
	}
	
	/*********************/
	/*** Right Methods ***/
	/*********************/
	
	/**
	 * Adds a right.
	 * @param string $r The right to add.
	 */
	public static function addRight($right)
	{
		self::$array_right[] = $right;
	}
	
	/**
	 * Adds an array of rights
	 * @param string_array $a An array of strings, the rights to add.
	 */
	public static function addArrayRight($rights)
	{
		self::$array_right = array_merge(self::$array_right, $rights);
	}
	
	/**
	 * Gets all declared rights.
	 * @return string_array Every declared rights.
	 */
	public static function getRight()
	{
		return self::$array_right;
	}
	
	/*********************/
	/*** Debug Methods ***/
	/*********************/
	
	/**
	 * Enables or disables the debug mode.
	 * @param boolean $b true to activate the debug mode, false otherwise.
	 */
	public static function setDebug($b)
	{
		self::$debug = $b;
	}
	
	/**
	 * Checks if the debug mode is enabled or not.
	 */
	public static function debug()
	{
		return self::$debug;
	}
	
	/*********************************************/
	/*** Onload and Unonload functions Methods ***/
	/*********************************************/
	
	/**
	 * Adds an onload function.
	 * @param string $fname Function name.
	 * @throw Exception if the function has already been entered.
	 */
	public static function addOnloadFunction($fname)
	{
		if(!in_array($fname, self::$onload_functions))
			self::$onload_functions[] = $fname;
		else
			throw new Exception("Onload function '".$fname."' already specified.");
	}
	
	/**
	 * Adds an onunload function.
	 * @param string $fname Function name.
	 * @throw Exception if the function has already been entered.
	 */
	public static function addOnunloadFunction($fname)
	{
		if(!in_array($fname, self::$onunload_functions))
			self::$onunload_functions[] = $fname;
		else
			throw new Exception("Onunload function '".$fname."' already specified.");
	}
	
	/**
	 * Erases onload functions that were previously added
	 */
	public static function flushOnloadFunction()
	{
		self::$onload_functions = array();
	}
	
	/**
	 * Erases onunload functions that were previously added
	 */
	public static function flushOnunloadFunction()
	{
		self::$onunload_functions = array();
	}
	
	/**
	 * Returns a string containing every specified onload functions.
	 * @return string Function calls separated by ";".
	 */
	public static function getOnloadFunctions()
	{
		return implode(";", self::$onload_functions);
	}
	
	/**
	 * Returns a string containing every specified onunload functions.
	 * @return string Function calls separated by ";".
	 */
	public static function getOnunloadFunctions()
	{
		return implode(";", self::$onunload_functions);
	}
	
	/********************/
	/*** Misc Methods ***/
	/*********************/
	
	/**
	 * Generates a link.
	 * @param string_array_or_string $arg Link data.
	 */
	public static function mkLink($arg)
	{
		if(is_array($arg))
			return "?".implode(self::ARG_SEPARATOR, $arg);
		else
			return "?".$arg;
	}
	
	/**
	 * Gets arguments sended to the current page.
	 * @return string_array The arguments of the page.
	 */
	public static function getPageArg()
	{
		return explode(self::ARG_SEPARATOR, $_SERVER['QUERY_STRING']);
	}
	
	public static function getArg($index)
	{
		$e = self::getPageArg();
		return $e[$index];
	}
}
?>