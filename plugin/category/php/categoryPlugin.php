<?php
class CategoryPlugin extends Plugin
{
	private $maxDepth = 20;
	
	public function __construct()
	{
		parent::__construct();
		$this->setPluginName("Category");
		$this->addAdminLink("Add a category", "add");
		$this->addAdminLink("List categories", "list");
		$this->addSqlTable("category");
	}
	
	public function prerender()
	{
		$this->assign("maxDepth", $this->maxDepth);
	}
	
}