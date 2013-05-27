<?php
/**
 * Home page
 * 
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class Home extends Page
{
	public function construct()
	{
		$this->setTemplate(TPL."html/home.html");
	}
}
?>