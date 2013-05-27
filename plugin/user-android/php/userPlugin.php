<?php
class UserAndroidPlugin extends Plugin
{	
	public function __construct()
	{
		parent::__construct();
		$this->setPluginName("UserAndroid");
		//$this->addAdminLink("Manage users", "list");
		$this->addSqlTable("user_android");
	}
}
