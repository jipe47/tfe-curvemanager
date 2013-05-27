<?php
class FaqPlugin extends Plugin
{
	public function __construct()
	{
		$this->setPluginName("Faq");
		$this->addAdminLink("Manage FAQ", "list");
		$this->addSqlTable("faq");
	}
}