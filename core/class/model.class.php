<?php
class Model extends PluginElement
{
	private $modelName = "";
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getModelName()
	{
		return $this->modelName;
	}
	
	public function setModelName($name)
	{
		$this->modelName = $name;
	}
}