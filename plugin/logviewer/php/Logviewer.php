<?php
/**
 * Logviewer
 * This page gives an interface for the xml files generated by the Log class.
 * @author Jean-Philippe Collette
 * @package Plugins
 * @subpackage Logviewer
 */
class Logviewer extends Page
{
	public function construct()
	{
		$this->setAccessName("Logviewer");
		$this->setTitle("Log Viewer");
		$this->setTemplate(PATH_PLUGIN."logviewer/html/logviewer.html");
		$this->showHeaders(true);
		$this->addAccess(Page::ADMIN);
	}
}
?>