<?php
class UserPlugin extends Plugin
{
	public function __construct()
	{
		$this->setPluginName("User");
		
		$this->addSqlTable("user_admin", "user");
		
		$this->addAdminLink("List users", "list");
		$this->addAdminLink("Add a user", "add");
	}
}