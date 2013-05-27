<?php
class UserPageLogout extends Page
{
	public function construct()
	{
		$this->addAccess(self::ALL);
	}
	public function prerender()
	{
		$_SESSION = array();
		session_destroy();
		setcookie(STRUCTURE_NAME."_login", 0, 0);
		setcookie(STRUCTURE_NAME."_password", 0, 0);
		out::message("You have been disconnected.", Message::SUCCESS);
		$back = implode(JPHP::ARG_SEPARATOR, $this->arg->getArray());
		
		if($back == "")
			$back = JPHP::get("default_page");
		$this->setLocation($back);
	}
}