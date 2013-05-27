<?php
/**
 * Menu used in the full render of a page.
 * 
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class Menu extends Page
{
	public function construct()
	{
		$this->addAccess(self::ALL);
		$this->setTemplate("tpl/default/html/menu.html");
		$this->setFullRender(false);
		$this->showHeaders(false);
	}
}
?>