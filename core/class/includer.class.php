<?php
class Includer
{
	// Cache related stuff
	
	private static $generateCaching = false; 
	
	private static $cache_files = array();
	private static $cache_plugins = array();
	private static $cache_loaded = false;
	
	public static function setGenerateCaching($b)
	{
		self::$generateCaching = $b;
	}
	
	public static function generateCaching()
	{
		return self::$generateCaching;
	}
	
	
	/**
	 * Includes the plugin situated in a path (php files as well as javascript
	 * and css folders) and register classes (based on their parent).
	 * @param string $path Path to the plugins.
	 * @todo Check if plugins alreay exists (I don't know what will happen!)
	 */
	
	public static function cacheLoad()
	{
		if(self::$cache_loaded)
			return;
		self::$cache_loaded = true;
		
		$cache = Cache::read("includer");
		if($cache != "")
		{
			self::$cache_files = $cache["files"];
			self::$cache_plugins = $cache["plugins"];
			self::$handlers_cache = $cache['handlers_cache'];
			self::$handlers_uncache = $cache['handlers_uncache'];
		}
	}
	
	public static function cacheSave()
	{
		Cache::write("includer", 
					array(	"files" => self::$cache_files, 
							"plugins" => self::$cache_plugins,
							"handlers_cache" => self::$handlers_cache,
							"handlers_uncache" => self::$handlers_uncache
						));
	}
	
	// Handling functions stuff
	
	
	private static $handlers_cache = array();
	private static $handlers_uncache = array();
	
	public static function addHandler($handlerName, $cache)
	{
		if($cache)
			self::$handlers_cache[] = $handlerName;
		else
			self::$handlers_uncache[] = $handlerName;
	}
	
	public static function includePlugins($path)
	{
		$array_htmlheaders = array("js", "css");
		$array_allowed_constant = array("js", "css", "php", "html");
		
		self::cacheLoad();
		
		// Using cache
		if(!self::generateCaching())
		{
			//out::message("Include Plugin cache: on");
			$chrono = Singleton::getInstance("Chrono");
			foreach(self::$cache_plugins as $p)
			{
				//$chrono->start("Plugin cache: including " . $p['pluginname']);
				$path = PATH_PLUGIN.$p['dirname']."/";
				
				foreach($array_allowed_constant as $c)
					if($p['folder_'.$c])
						define(strtoupper($p['dirname'])."_".strtoupper($c), $path.$c."/");
				
				if($p['folder_css'])
					HtmlHeaders::includeDir("css", $path."css");
				if($p['folder_js'])
					HtmlHeaders::includeDir("js", $path."js");
				self::includePath($path."php", false, false);
				
				$plugin_instance = $p['classname'] != "" ? new $p['classname'] : null;
				
				if($plugin_instance != null)
				{
					$plugin_instance->setPath($p['dirname']."/");
					Plugins::addPlugin($plugin_instance);
					foreach($p['models'] as $class)
					{
						$model_instance = new $class();
						$plugin_instance->addModel($model_instance);
					}
					
					foreach($p['widgets'] as $class)
					{
						$widget = new $class($plugin_instance);
						$plugin_instance->addWidget($widget);
					}
				}
				
				foreach($p['pages'] as $class)
					PageRegister::registerPage($class, $p['pluginname']);
				
				//$chrono->stop("Plugin cache: including " . $p['pluginname']);
			}
			
			foreach(self::$handlers_uncache as $h)
				call_user_func_array($h, array());
			
		}
		// Loading files
		else
		{
			self::$cache_plugins = array();
			self::$handlers_cache = array();
			self::$handlers_uncache = array();
			
			// Registering default handlers
			/*self::$handlers_cache[] = "Includer::handlerPage";
			self::$handlers_cache[] = "Includer::handlerWidget";
			*/
			self::addHandler("Includer::handlerPage", false);
			self::addHandler("Includer::handlerWidget", false);
			self::addHandler("Includer::handlerScript", false);
			
			//out::message("Include Plugin cache: off", Message::WARNING);
			$dir_handle= @opendir($path) or die("Cannot open <strong>" . $path . "</strong> for include.");
			
			$included_class = get_declared_classes();
		
			while($file = readdir($dir_handle))
			{
				if($file == "." || $file == ".." || $file == ".svn" || !is_dir($path."/".$file))
					continue;
				
				// Cache information
				$info_plugin = array(	"pluginname" => "",
										"classname" => "",
										"dirname" => $file, 
										"models" => array(), 
										"pages" => array(),
										"widgets" => array(),
										"scripts" => array());
				foreach($array_allowed_constant as $c)
					$info_plugin["folder_".$c] = false;
				
				$pdir = @opendir($path.$file) or die("Cannot open path of plugin <strong>" . $file . "</strong>.");
	
				// Include files
				while($f = readdir($pdir))
				{
					if($f == "." || $f == ".." || !is_dir($path.$file."/".$f))
						continue;
	
					if(in_array($f, $array_htmlheaders))
					{
						HtmlHeaders::includeDir($f, $path.$file."/".$f);
						$info_plugin["folder_".$f] = true;
					}
					else if($f == "php")
						self::includePath($path.$file."/".$f, false);
					
					if(in_array($f, $array_allowed_constant))
						define(strtoupper($f)."_".strtoupper($file), $path.$file."/".$f."/");
				}
				
				$new_included_class = array_diff(get_declared_classes(), $included_class);
				$included_class = get_declared_classes();
				
				// First find the subclass of Plugin
				$plugin_instance = null;
				foreach($new_included_class as $k => $class)
				{
					if(is_subclass_of($class, "Plugin"))
					{
						$plugin_instance = new $class();
						$plugin_instance->setPath($file."/");
						Plugins::addPlugin($plugin_instance);
						unset($new_included_class[$k]);
						
						$info_plugin['classname'] = $class;
						$info_plugin['pluginname'] = $plugin_instance->getPluginName();
						
						break;				
					}
				}
				
				// Find models of plugins, before link other ressources
				foreach($new_included_class as $k => $class)
				{
					if(is_subclass_of($class, "Model"))
					{
						if(is_null($plugin_instance))
							throw new Exception("Plugin subclass not found in " . $path.$file);
						
						$model_instance = new $class();
						$plugin_instance->addModel($model_instance);
						unset($new_included_class[$k]);
						
						$info_plugin['models'][] = $class;
					}
				}
				
				// Link components to plugin
				foreach($new_included_class as $k => $class)
				{
					foreach(self::$handlers_uncache as $handler)
						call_user_func_array($handler, array($class, &$plugin_instance, &$info_plugin));
				}
				
				// Include configuration file
				if(file_exists($path.$file."/config.ini"))
					JPHP::importIni($path.$file."/config.ini", $file);
				
				self::$cache_plugins[] = $info_plugin;
			}
		}
	}
	
	public static function handlerScript($class, &$plugin_instance, &$info_plugin)
	{
		if(!is_subclass_of($class, "ScriptPage"))
			return;
		$info_plugin['scripts'][] = $class;
		$plugin_instance->addScript($class);
		//PageRegister::registerPage($class, $info_plugin['pluginname']);
	}
	
	public static function handlerPage($class, &$plugin_instance, &$info_plugin)
	{
		if(!is_subclass_of($class, "Page") || is_subclass_of($class, "Widget"))
			return;

		PageRegister::registerPage($class, $info_plugin['pluginname']);
		$info_plugin['pages'][] = $class;
	}
	
	public static function handlerWidget($class, &$plugin_instance, &$info_plugin)
	{
		if(!is_subclass_of($class, "Widget"))
			return;
		
		if(is_null($plugin_instance))
			throw new Exception($class.": widget for undefined plugin");
		else
		{
			$widget = new $class($plugin_instance);
			$plugin_instance->addWidget($widget);
			$info_plugin['widgets'][] = $class;
		}
	}
	
	/**
	 * Includes recursively a directory. It skips svn folders, and executes a
	 * "require_once" on php files.
	 *
	 * @param string $path Path to the directory.
	 * @param boolean $recursive If set, the include is recursive.
	 */
	public static function includePath($path, $registerPage = true)
	{
		self::cacheLoad();
		//$chrono = Singleton::getInstance("Chrono");
		//$chrono->start("including " . $path);
		$phpFound = false;
		$included_class = get_declared_classes();
		
		if(!self::generateCaching() && array_key_exists($path, self::$cache_files))
		{
			
			foreach(self::$cache_files[$path]["dir"] as $f)
				self::includePath($path.$f);
			
			foreach(self::$cache_files[$path]["php"] as $f)
			{
				require_once $path."/".$f;
				$phpFound = true;
			}
			
		}
		else
		{
			self::$cache_files[$path] = array("dir" => array(), "php" => array());
			$dir_handle= @opendir($path) or die("Cannot open <strong>" . $path . "</strong> for include");
			
			while($file = readdir($dir_handle))
			{
				if($file == "." ||$file == ".." || $file == ".svn")
					continue;
		
				$pathinfo = pathinfo($path.$file, PATHINFO_EXTENSION);
		
				if(is_dir($path."/".$file))
				{
					self::$cache_files[$path]["dir"][] = $file;
					self::includePath($path."/".$file);
				}
				else if($pathinfo == "php")
				{
					self::$cache_files[$path]["php"][] = $file;
					require_once $path."/".$file;
					$phpFound = true;
				}
			}
			
			self::cacheSave();
		}

		if(!$phpFound)
		{
			//$time = $chrono->stop("including " .$path);
			return;
		}
		
		if($registerPage)
		{
			$new_included_class = array_diff(get_declared_classes(), $included_class);
			
			foreach($new_included_class as $c)
			{
				if(is_subclass_of($c, "Page") && $c != "AjaxPage" && $c != "RequestPage" && $c != "AdminPage" && $c != "Page" && $c != "ScriptPage" && $c != "HandlerPage" && $c != "Widget")
				{
					PageRegister::registerPage($c);
				}
			}
		}
		
		//$time = $chrono->stop("including " .$path);
	}
	
	/**
	 * Specifies a template and includes all required files.
	 * @param string $t the template name.
	 * @throws Exception if the template does not exist.
	 */
	public static function includeTemplate($t)
	{
		$folder = PATH_TPL.$t;
	
		if(!is_dir($folder))
			throw new Exception("The template does not exists");
	
		HtmlHeaders::includeDir("css", $folder."/css");
		HtmlHeaders::includeDir("js", $folder."/js");
	}
}