<?php
/**
 * Represents a generic object of the architecture, with template and MySQL capabilities.
 * 
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */

class Object
{
	protected $request, $tpl, $chrono, $arg;
	private $objectName;
	
	// Boolean to make sure basic vars are assigned only once.
	private static $isTplAssigned = false;
	
	public function setArg($arg)
	{
		if($arg instanceof Arg || $arg instanceof ArrayProcessor)
			$this->arg = $arg;
		else
			$this->arg = new Arg($arg);
	}
	
	public function __construct()
	{	
		$this->objectName	= jphp_generateId(10);
		$this->request 		= Singleton::getInstance("SqlRequest");
		$this->tpl 			= Singleton::getInstance("Smarty");
		$this->chrono 		= Singleton::getInstance("Chrono");
		$this->arg 			= Singleton::getInstance("Arg");
		
		// Assignment of basic vars in the template.
		if(!self::$isTplAssigned)
		{
			self::$isTplAssigned = true;
			
			$this->assign("isAdmin", 		User::isAdmin());
			$this->assign("isConnected", 	User::isConnected());
			$this->assign("hasRight",		User::hasRight());
			
			$this->assign("id_user", 		User::getId());
			$this->assign("user_nickname", User::getNickname());
			$this->assign("user_avatar", 	User::getAvatar());
			
			$this->assign("TPL", 			TPL);
			$this->assign("COMMON", 		PATH_TPL_COMMON);
			$this->assign("PATH_PLUGIN", 	PATH_PLUGIN);
			$this->assign("PATH_UPLOAD", 	PATH_UPLOAD);
			
			$this->assign("DATE_FORMAT", 	DATE_FORMAT);
			$this->assign("TIME_FORMAT", 	TIME_FORMAT);
			
			$this->assign("STRUCTURE_NAME", STRUCTURE_NAME);
			
			$this->assign("URL_SITE", URL_SITE);
		}
	}
	
	
	/**
	 * Assigns a value to a variable in the template.
	 * @param string $name The variable name.
	 * @param mixed $value The variable value.
	 */
	public function assign($name, $value)
	{
		for($i = 0 ; $i < func_num_args() ; $i += 2)
			$this->tpl->assign(func_get_arg($i), func_get_arg($i + 1));
	}
	
	/**
	 * Assigns multiple variable values.
	 * @param array_mixed $array Array where keys are variable names and values are variable values.
	 */
	public function assignArray($array)
	{
		foreach($array as $name => $value)
			$this->assign($name, $value);
	}

	/**
	 * Returns the render of a template file, or an empty string if a location is specified.
	 * @param string $f the file to render.
	 */
	protected function renderFile($f)
	{
		$renderId = jphp_generateId(10);
		try
		{
			$this->chrono->start('File render (id: '.$renderId.'): ' . $f);
		}
		catch(Exception $e) { }
		
		$r = $this->tpl->fetch($f);
		
		try
		{
			$this->chrono->stop('File render (id: '.$renderId.'): ' . $f);
		}
		catch(Exception $e) { }
		
		return $r;
	}
	
	/**
	 * Mutator for the object name.
	 * @param string $objectName New object name.
	 */
	public function setObjectName($objectName)
	{
		$this->objectName = $objectName;
	}
	
	/**
	 * Accessor for the object name.
	 * @return The object name.
	 */
	public function getObjectName()
	{
		return $this->objectName;
	}
}