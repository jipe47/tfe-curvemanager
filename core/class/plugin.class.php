<?php
/**
 * Represents a plugin.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Plugin
 */
abstract class Plugin extends Object
{	
	public static $rights = null;
	public static $ini = null;
	public $adminLinks = array();
	protected $scripts = array();
	
	protected $pluginName = "";
	protected $defaultModel = null;
	protected $models = array();
	protected $path = "";
	
	private $widgets = array();
	private $defaultWidget = null;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getWidget($widgetName)
	{
		if($widgetName == "")
			return $this->getDefaultWidget();
		else if(array_key_exists($widgetName, $this->widgets))
			return $this->widgets[$widgetName];
		else
			throw new Exception("Undefined widget '". $widgetName . "' for plugin '" . $this->getPluginName(). "'");
			
	}
	
	public function getDefaultWidget()
	{
		return $this->defaultWidget;
	}
	
	public function addWidget($w)
	{
		if($this->defaultWidget == null)
			$this->defaultWidget = $w;
		$this->widgets[$w->getAccessName()] = $w;
	}
	
	public function setPath($path)
	{
		$this->path = $path;
	}
	
	public function getPath()
	{
		return $this->path;
	}
	
	public function setPluginName($n)
	{
		$this->pluginName = $n;
	}
	public function getPluginName()
	{
		return $this->pluginName;
	}
	
	public function addModel($m)
	{
		if(count($this->models) == 0)
			$this->defaultModel = $m;
		// TODO VÃ©rifier unicitÃ© du nom
		$this->models[$m->getModelName()] = $m;
	}
	
	public function getModel($name)
	{
		if(array_key_exists($name, $this->models))
			return $this->models[$name];
		else
			throw new Exception("Undefined model '".$name."' for plugin " .$this->getPluginName());
	}
	
	public function getDefaultModel()
	{
		return $this->defaultModel;
	}
	
	public function addSqlTable($table, $alias = '')
	{
		JPHP::addSqlTable($table, $alias);
	}
	
	public function addAdminLink($name, $link, $right = '')
	{
		$this->adminLinks[] = array('name' => $name, 'link' => $link, 'right' => $right);
	}
	public function getAdminLinks()
	{
		return $this->adminLinks;
	}
	
	public function addScript($classname)
	{
		$this->scripts[] = $classname;
	}
	
	public function getScripts()
	{
		$array_script = array();
		
		foreach($this->scripts as $s)
		{
			$script_instance = new $s();
			$array_script[] = array('arg' => $script_instance->getScriptArg(),
									'name' => $script_instance->getScriptName(),
									'accessname' => $script_instance->getAccessName()
									);
		}
		return $array_script;
	}
	
	public function addRight($name)
	{
		
	}
	
	public function prerender()
	{
		// To be filled by specialized classes
	}
	
	
}