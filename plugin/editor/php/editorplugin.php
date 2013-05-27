<?php
/**
 * Editor Plugin
 * This plugin provides an (simple) advanced text editing toolbar.
 * @author Jean-Philippe Collette
 * @package Plugins
 * @subpackage Editor
 */
class EditorPlugin extends Plugin
{
	public function __construct()
	{
		parent::__construct();
		$this->setPluginName("Editor");
	}
	public function render($arg)
	{
		$e = new EditorPage($arg);
		return $e->render();
	}
}