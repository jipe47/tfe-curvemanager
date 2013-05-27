<?php
abstract class ScriptPage extends Page
{
	protected $scriptName = "";
	
	protected $script_arg = array();
	
	public function __construct()
	{
		if(func_num_args() == 0)
			parent::__construct();
		else
			call_user_func_array("parent::__construct", func_get_args());
		$this->setFullRender(false);
		$this->showHeaders(false);
		$this->setPageType("script");
		$this->addAccess(Page::ADMIN);
	}
	public function prerender(){
		$this->exec();
	}
	public function getScriptName()
	{
		return $this->scriptName;
	}
	public function setScriptName($n)
	{
		$this->scriptName = $n;
	}
	
	public function addScriptArg($field, $friendlyname, $default = "")
	{
		$this->script_arg[] = array('friendlyname' => $friendlyname, 'fieldname' => $field, 'default' => $default);
	}
	
	public function getScriptArg()
	{
		return $this->script_arg;
	}
	
	public abstract function exec();
}