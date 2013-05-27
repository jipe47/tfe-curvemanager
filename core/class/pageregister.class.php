<?php
class PageRegister
{
	private static $pageRegister = array(
			'page' => array(),
			'admin' => array(),
			'request' => array(),
			'ajax' => array());
	private static $cache_loaded = false;
	private static $cache_pages_reset = false;
	private static $cache_pages = array();
	
	public static function cacheSave()
	{
		Cache::write("PageRegister", array("pages" => self::$cache_pages));
	}
	
	private static function cacheLoad()
	{
		if(self::$cache_loaded)
			return;
		self::$cache_loaded = true;
		$cache = Cache::read("PageRegister");
		
		if($cache != ""){			
			self::$cache_pages = $cache["pages"];
		}
	}
	public static function registerPage($classname, $pluginname = '')
	{
		/*
		self::cacheLoad();
		if(array_key_exists($classname, self::$cache_pages))
		{
			self::insertInRegister(self::$cache_pages[$classname]['accessname'], $classname, null, $pluginname, self::$cache_pages[$classname]['type']);
		}
		else {
		*/
			if(!self::$cache_pages_reset)
			{
				self::$cache_pages = array();
				self::$cache_pages_reset = true;
			}
	
			$page_instance = $pluginname == '' ? new $classname() : new $classname(Plugins::getPlugin($pluginname));
			$page_type = $page_instance->getPageType();
	
			$accessName = $page_instance->getAccessName();
	
			if($accessName == "")
				$accessName = $classname;
			self::insertInRegister($accessName, $classname, $page_instance, $pluginname, $page_type);
			self::$cache_pages[$classname] = array("accessname" => $accessName, "type" => $page_type, "pluginname" => $pluginname, "plugin_usage" => array());
			self::cacheSave();
		//}
	}
	
	public static function getTypes()
	{
		return array_keys(self::$pageRegister);
	}
	/**
	 * Returns the page register, for debug purpose ONLY.
	 */
	public static function getRegister()
	{
		return self::$pageRegister;
	}
	
	public static function getPagesByType($type)
	{
		return self::$pageRegister[$type];
	}
	
	/**
	 * Register a page in a category.
	 * @param string $name Page name
	 * @param string $classname Class name associated to the page name
	 * @param string $type Type of page (admin, request or ajax)
	 * @throws Exception Throwned if the page name is reserved or already registered.
	 */
	public static function insertInRegister($name, $classname, $page_instance, $pluginname)
	{
		$type = is_null($page_instance) ? "page" : $page_instance->getPageType();
		if(!array_key_exists($type, self::$pageRegister))
			self::$pageRegister[$type] = array();
			
		if(!array_key_exists($name, self::$pageRegister[$type]) && !in_array($classname, self::$pageRegister[$type]))
			self::$pageRegister[$type][$name] = array('classname' => $classname, 'instance' => $page_instance, 'pluginname' => $pluginname);
		else if($type == 'page' && in_array($name, array("Admin", "Request", "Ajax", "Script")))
			throw new Exception("Reserved page name:" . $name);
		else if(array_key_exists($name, self::$pageRegister[$type]))
			throw new Exception('Already registered name: "' . $name . '" for type "'.$type.'"');
		else if(in_array($classname, self::$pageRegister[$type]))
			throw new Exception('Already registered class: "' . $classname . '"');
		else
			throw new Exception('Page register: undefined error.');
	}
	
	/**
	 * Get the class name associated to a page.
	 * @param string $name Page name.
	 * @param string $type Page type.
	 * @throws Exception Throwned if the page does not exist.
	 */
	public static function getPageClass($name, $type = 'page')
	{
		if(array_key_exists($name, self::$pageRegister[$type]))
			return self::$pageRegister[$type][$name]['classname'];
		else
			throw new Exception("Unregistered page: '".$name."' ('".$type."')");
	}
	
	/**
	 * Get the instance associated to a page.
	 * @param string $name Page name.
	 * @param string $type Page type.
	 * @throws Exception Throwned if the page does not exist.
	 */
	public static function getPageInstance($name, $type = 'page')
	{
		if(array_key_exists($name, self::$pageRegister[$type]))
		{
			$inst = self::$pageRegister[$type][$name]['instance'];
			if($inst == null)
			{
				$pluginname = self::$pageRegister[$type][$name]['pluginname'];
				$classname = self::$pageRegister[$type][$name]['classname'];
	
				if($pluginname != '')
					$inst = new $classname(Plugins::getPlugin($pluginname));
				else
					$inst = new $classname();
				self::$pageRegister[$type][$name]['instance'] = $inst;
				
			}
			return $inst;
		}
		else
			throw new Exception("Unregistered page: '".$name."' ('".$type."')");
	}
	
	/**
	 * Shortcut to register a RequestPage.
	 * @param string $name Page name.
	 * @param string $classname Class name.
	 */
	public static function pageRegisterRequest($name, $classname) {
		self::pageRegister($name, $classname, 'request');
	}
	/**
	 * Shortcut to register an AdminPage.
	 * @param string $name Page name.
	 * @param string $classname Class name.
	 */
	public static function pageRegisterAdmin($name, $classname) {
		self::pageRegister($name, $classname, 'admin');
	}
	/**
	 * Shortcut to register an AjaxPage.
	 * @param string $name Page name.
	 * @param string $classname Class name.
	 */
	public static function pageRegisterAjax($name, $classname) {
		self::pageRegister($name, $classname, 'ajax');
	}
	/**
	 * Shortcut to register a "normal" Page.
	 * @param string $name Page name.
	 * @param string $classname Class name.
	 */
	public static function pageRegisterPage($name, $classname) {
		self::pageRegister($name, $classname, 'page');
	}
	
}