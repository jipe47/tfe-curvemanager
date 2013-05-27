<?php
/**
 * Generates subpanels based on admin links (defined by plugins).
 *
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class AdminPanel extends Page
{
	public function construct()
	{
		$this->setContainer(TPL."html/container.html");
		$this->setTitle("Admin Menu");
	}
	
	public function prerender()
	{
		if(!isAdmin())
		{
			out::message("You must be identified to access to the administration panel.", Message::ERROR);
			$this->setLocation("Login");
		}
		else
		{
			$this->setTemplate(PATH_TPL_COMMON."html/admin.html");
			$this->assign('array_plugin', Plugins::getAdminInfos());
			JPHP::addOnloadFunction("adminInitScript()");
		}
	}
}