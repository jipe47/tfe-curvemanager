<?php
class userLoginPage extends Page
{
	public static $BAD_LOGIN;
	public static $BAD_PASSWORD;
	public static $BAD_FORM;
	public static $NOT_CONFIRMED;
	
	public function construct()
	{
		$this->addAccess(self::ALL);
		$this->setContainer(TPL."html/container.html");
		$this->setAccessName("Login");
		
		$this->setTemplate($this->path_html."user_login.html");
		$this->showHeaders(true);
		
		if($this->arg->argc > 0)
		{
			$first_arg = $this->arg->string(0);
			if($first_arg == "retreive")
			{
				$this->setTemplate($this->path_html."user_loginretreive.html");
			}
		}
		$this->assign("back", "AdminPanel");
	}
}