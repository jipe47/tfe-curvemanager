<?php
class UserAndroidRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("UserAndroid");
		$this->addAccess(Page::ADMIN);
	}
	
	public function handler()
	{
		$back = "Admin/AndroidUser/list";
		switch($this->arg->string(0))
		{		
			default:
				Messages::add("Undefined operation.", Message::ERROR);
		}
		
		$this->setLocation($back);
	}
}