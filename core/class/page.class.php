<?php
/**
 * Represents a HTML page.
 * 
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */
abstract class Page extends Object
{
	protected $model = null;
	protected $plugin = null;
	protected $path_html = null;
	protected $path_css = null;
	protected $path_js = null;
	protected $path_images = null;
	
	protected $container = "";
	
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
		$this->model = $plugin->getDefaultModel();
		if(file_exists(PATH_PLUGIN.$plugin->getPath()."html/"))
			$this->path_html = PATH_PLUGIN.$plugin->getPath()."html/";
		if(file_exists(PATH_PLUGIN.$plugin->getPath()."css/"))
			$this->path_css = PATH_PLUGIN.$plugin->getPath()."css/";
		if(file_exists(PATH_PLUGIN.$plugin->getPath()."js/"))
			$this->path_js = PATH_PLUGIN.$plugin->getPath()."js/";
		if(file_exists(PATH_PLUGIN.$plugin->getPath()."images/"))
			$this->path_js = PATH_PLUGIN.$plugin->getPath()."images/";
	}
	
	protected $insertInRegister = true;
	
	public function setInsertInRegister($b)
	{
		$this->insertInRegister = $b;
	}
	public function insertInRegister()
	{
		return $this->insertInRegister;
	}
	
	private $pageType = "page";
	public function setPageType($t)
	{
		$this->pageType = $t;
	}
	
	public function getPageType()
	{
		return $this->pageType;
	}
	
	/**
	 * Contains a location at which the user will be redirected.
	 * @var string
	 */
	private $location = -1;
	
	/** Page title
	* @var string */
	private $title = "";
	
	/** Template file path.
	* @var string */
	protected $template_file;
	
	/** Id groups that are allowed to access to this page.
	* @var int array */
	protected $array_group = array();
	
	/** Rights required to access to this page.
	* @var string array */
	protected $array_right = array();
	
	/** If set, *only* admin will be able to see this page.
	* @var boolean */
	protected $access_admin_only = false;
	
	/** If set, specified groups will be able to see this page.
	* @var boolean */
	protected $access_group = false;
	
	/** If set, specified rights will be able to see this page.
	* @var boolean */
	protected $access_right = false;
	
	protected $access_anonymous = true;
	
	/** If set, HTML headers will be included in the render of this page.
	 * @var boolean
	* */
	private $showHeaders = true;
	
	/**
	 * If not set, renders only html headers and main content 
	 * @var boolean
	 */
	private $fullRender = true;

	/** Types of access.
	* @var string */
	const GROUP = "group", RIGHT = "right", ADMIN = "admin", MEMBER = "member", ALL = "all";
	
	
	private $accessName = null;
	
	
	public function __construct()
	{
		parent::__construct();
		
// 		echo 'Page constructor of ' . $this->getObjectName() . '<br />';
		if(func_num_args() == 1 && func_get_arg(0) instanceof Plugin)
		{
// 			echo 'Plugin constructor for ' . $this->getObjectName().'<br />';
			$this->setPlugin(func_get_arg(0));
			$this->construct();
		}
		else if(func_num_args() > 0)
		{
// 			echo 'Arg constructor for ' . $this->getObjectName().' ('.get_class(func_get_arg(0)).')<br />';
			$this->constructArg(func_get_args());
		}
		else
		{
// 			echo 'Empty constructor for ' . $this->getObjectName().' (argc = ' . func_num_args() . ')<br />';
			$this->construct();
		}
		
	}
	
	public function constructArg($arg)
	{
		$this->setArg($arg);
	}
	
	public function construct()
	{
		
	}
	
	public function setAccessName($n)
	{
		$this->accessName = $n;
	}
	
	public function getAccessName()
	{
		return $this->accessName;
	}
	
	/**
	 * Method called when the page is registered.
	 */
	public static function init()
	{
		
	}
	
	/***********************************/
	/*** Template and Render Methods ***/
	/***********************************/
	
	public function prerender()
	{
		
	}
	
	/**
	 * Generates the HTML code for the page.
	 * @return string HTML code.
	 */
	public function render() {
		
		if($this->userCanAccess())
		{
			if($this->plugin != null)
				$this->plugin->prerender();
				
			$this->prerender();
			$render = $this->selfrender();
			
			if($this->hasLocation())
				return "";

			if($this->showHeaders)
			{
				// Footer
				$footer = $this->hasFullRender() ? $this->renderClass("Footer") : "";
				$this->assign("footer", $footer);
				
				// Menu
				$menu = $this->hasFullRender() ? $this->renderClass("Menu") : "";
				$this->assign("menu", $menu);
				
				// Header
				$header = $this->hasFullRender() ? $this->renderClass("Header") : "";
				$this->assign("header", $header);
	
				// Messages
				$messages = $this->renderClass("Messages");
				$this->assign("messages", $messages);
	
				// Debug div
				$debug = (JPHP::debug() && $this->hasFullRender()) ? $this->renderClass("Debugger") : "";
				$this->assign("debug", $debug);
				
				// Buffer
				$buffer = JPHP::getBuffer();
				$this->assign("buffer", $buffer);
	
				// Html Headers
				$this->assign("htmlheaders", HtmlHeaders::getHeaders());
	
				$prefix = JPHP::get("title_prefix");
				$title = $this->title != "" ? $prefix.$this->title : JPHP::get("title") ;
				$this->assign("pagetitle", $title);
	
				// Assign page content
				if($this->container != "")
				{
					$this->assign("contained", $render);
					$render = $this->renderFile($this->container);
				}
				$this->assign("content", $render);
	
				// Loading of on(un)Load Javascript functions
				$this->assign("onloadFunctions", JPHP::getOnloadFunctions());
				$this->assign("onunloadFunctions", JPHP::getOnunloadFunctions());
	
				// Final render!
				$render = $this->renderFile(TPL."html/main.html");
			}
		}
		else
		{
		//	Log::add("Access Restricted : " . User::getId() . " in " . $this->getObjectName().  " (arg: ".implode(",", $this->arg).")", Log::ERROR);
			$error = new AccessError(42, "You are not allowed to access to this page", $this->getAccess());
			$error->showHeaders($this->showHeaders);
			$render = $error->render();
		}
		
		return $render;
	}
	
	/**
	 * Changes the page template file.
	 * @param string $t The new template. */
	public function setTemplate($t)
	{
		$this->template_file = $t;
	}
	
	/**
	 * Activates or disables the full render mode.
	 * @param boolean $b True to activate the full render mode,
	 * 					 false otherwise.
	 */
	public function setFullRender($b)
	{
		$this->fullRender = $b;
	}
	
	/**
	 * Returns true if the full render mode is activated, false otherwise.
	 */
	public function hasFullRender()
	{
		return $this->fullRender;
	}
	
	/**
	 * Renders a class if it is defined.
	 * @param string $class Classname.
	 * @param string Class render.
	 */
	public function renderClass($class)
	{
		$this->chrono->start("Class render : ".$class);
		if(class_exists($class))
		{
			$f = new $class();
			//$f = PageRegister::getPageInstance($class); // TODO: replace the previous line by this one
			return $f->render();
		}
		else
			return "";
		$this->chrono->stop("Class render : ".$class);
	}
	
	/**
	 * If a ($)template is specified, renders it.
	 * @return string Render of $this->template_file.
	 * @see $template_file
	 */
	public function selfRender()
	{
		return (!empty($this->template_file)) ? $this->renderTemplate() : "";
	}
	
	/**
	 * Displays or not HTML headers when the page is rendered.
	 * @param boolean $b True to display headers, false otherwise.
	 */
	public function showHeaders($b)
	{
		$this->showHeaders = $b;
	}
	
	/**
	 * Renders a template file, specified in argument or set by setTemplate, using Smarty.
	 * @param string $t Template path.
	 * @return string If a file is specified, renders it, otherwise if a file is set by setTemplate, renders it, otherwise returns an empty string.
	 * @see setTemplate
	 */
	public function renderTemplate()
	{
		if(func_num_args() == 0)
		{
			if($this->template_file != "")
				$t = $this->template_file;
			else
				return "";
		}
		else
			$t = func_get_arg(0);
			
		if($this->plugin != null)
		{
			foreach(array("css", "image", "html", "images") as $d)
			{
				$pa = PATH_PLUGIN.$this->plugin->getPath().$d."/";
				$const = file_exists($pa) ? $pa : null;
				$this->assign("PATH_".strtoupper($d), $const); 
			}
		}
	
		return $this->renderFile($t);
	}
	
	/**********************/
	/*** Access Methods ***/
	/**********************/
	
	/** Checks if the user can access to this page.
	 * @return boolean True if the user can access to this page, false otherwise.
	 */
	private function userCanAccess()
	{
		if(ALL_ACCESS || User::isAdmin() || (!$this->access_admin_only && !$this->access_group && !$this->access_right && $this->access_anonymous))
			return true;
		if(!$this->access_anonymous && User::isConnected())
			return true;
	
		if($this->access_admin_only)
			return jphp_isAdmin();
	
		if($this->access_group && User::inGroup($this->array_group))
			return true;
	
		if($this->access_right && count($this->array_right) == 0 && User::hasRight())
			return true;
	
		if($this->access_right && User::hasRight($this->array_right))
			return true;
		return false;
	}
	
	/** Allows to add an access to a page. Other arguments will be used to specify groups or rights, exception if the access is set to "admin only".
	 * @param string $a Access.
	 */
	public function addAccess($a)
	{
		switch($a)
		{
			case self::GROUP:
				$this->access_group = true;
				$this->array_group = array_merge($this->array_group, array_slice(func_get_args(), 1));
				$this->access_anonymous = false;
				break;
	
			case self::RIGHT:
				$this->access_right = true;
				$rights = array_slice(func_get_args(), 1);
	
				if(count($rights) > 0)
					$this->array_right = array_merge($this->array_right, $rights);
				$this->access_anonymous = false;
				break;
	
			case self::ADMIN:
				$this->access_admin_only = true;
				$this->access_anonymous = false;
				break;
	
			case self::ALL:
				$this->access_admin_only = false;
				$this->access_group = false;
				$this->access_right = false;
				$this->access_anonymous = true;
				break;
				
			case self::MEMBER:
				$this->access_anonymous = false;
				break;
		}
	}
	
	/**
	 * Removes an access to this page.
	 * @param string $a The access to remove (see Page::ADMIN|GROUP|RIGHT)
	 * @param boolean $clear_array Resets array containing allowed groups or rights.
	 */
	public function removeAccess($a, $clear_array = false)
	{
		switch($a)
		{
			case self::GROUP:
				$this->access_group = false;
				if($clear_array)
					$this->array_group = array();
				break;
	
			case self::RIGHT:
				$this->access_right = false;
				if($clear_array)
					$this->array_right = array();
				break;
	
			case self::ADMIN:
				$this->access_admin_only = false;
				break;
		}
	}
	
	/**
	 * Returns access settings.
	 * @return array Array with three keys : "admin_only" => boolean, "group" => id array, "right" => id array.
	 */
	public function getAccess()
	{
		return array("admin_only" => $this->access_admin_only, "group" => $this->array_group, "right" => $this->array_right, "anonymous" => $this->access_anonymous);
	}

	/*********************/
	/*** Title Edition ***/
	/*********************/
	
	/**
	 * Changes the page title.
	 * @param string $t New title. */
	public function setTitle($title)
	{
		$this->title = $title;
	}
	
	public function getTitle()
	{
		return $this->title;
	}
	/**
	 * Appends a string at the end of the title.
	 * @param string $t The string to append. */
	public function appendTitle($title)
	{
		$this->title = $this->title . $title;
	}
	
	/**
	 * Appends a string at the beginning of the title.
	 * @param string $t The string to append. */
	public function prependTitle($title)
	{
		$this->title = $title.$this->title;
	}
	
	/************************************/
	/*** Location/Redirection Methods ***/
	/************************************/
	
	/**
	 * Specifies a location at which the user will be redirected.
	 * @param string $l The location.
	 */
	public function setLocation($location)
	{
		$this->location = !empty($location) ? $location : JPHP::get("default_page");
	}
	
	/**
	 * Returns the location, an empty string if nothing is specified.
	 * @return string The location or an empty string if nothing is specified.
	 */
	public function getLocation()
	{
		return $this->location;
	}
	
	/**
	 * Returns if a location is specified.
	 * @return boolean true if a location is specified, false otherwise.
	 */
	public function hasLocation()
	{
		return $this->location != -1;
	}

	
	public function setContainer($c)
	{
		$this->container = $c;
	}
	public function getContainer()
	{
		return $this->container;
	}
}
?>
