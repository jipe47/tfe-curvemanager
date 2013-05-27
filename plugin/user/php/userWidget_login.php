<?php
class UserWidgetLogin extends Widget
{
	public function construct()
	{
		$this->setAccessName("login");
		$this->addAccess(self::ALL);
		$this->setTemplate($this->path_html."widget/user_widget_login.html");
	}
	
	public function prerender()
	{
		$this->assign("back", $this->arg->string("back"));
	}
}