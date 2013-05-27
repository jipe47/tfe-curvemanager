<?php
/**
 * Edition Page
 * This page displays an editor for a textarea.
 * @author Jean-Philippe Collette
 * @package Plugins
 * @subpackage Editor
 */
class EditorPage extends Page
{
	private $default_enable_preview = false;
	private $default_enable_toolbar = false;
	private $default_enable_formtag = false;
	
	private $default_textarea_col = 50;
	private $default_textarea_row = 7;
	
	public function constructArg($arg)
	{
		//$arg = new ArrayProcessor($arg);
		$arg = $arg[0]; // TODO Retirer cette horreur
		$this->showHeaders(false);
		//$enable_formtag = (array_key_exists("enable_formtag", $arg)) ? $arg["enable_formtag"] : $this->default_enable_formtag;
		$enable_formtag = $arg->bool("enable_formtag", $this->default_enable_formtag);
		$this->assign("enable_formtag", $enable_formtag);
		
		//$textarea_name = (array_key_exists("textarea_name", $arg)) ? $arg["textarea_name"] : "bbcode_textarea";
		$textarea_name = $arg->string("textarea_name", "bbcode_textarea");
		$this->assign("textarea_name", $textarea_name);
		
		//$textarea_value = (array_key_exists("textarea_value", $arg)) ? $arg['textarea_value'] : "";
		$textarea_value = $arg->string("textarea_value");
		$this->assign("textarea_value", $textarea_value);
		
		//$enable_toolbar = (!array_key_exists("enable_toolbar", $arg) && $this->default_enable_toolbar) || (array_key_exists("enable_toolbar", $arg) && $arg["enable_toolbar"] == 1);
		$enable_toolbar = $arg->bool("enable_toolbar", $this->default_enable_toolbar);
		$this->assign("enable_toolbar", $enable_toolbar);
		
		//$textarea_col = (array_key_exists("textarea_col", $arg)) ? $arg['textarea_col'] : $this->default_textarea_col;
		$textarea_col = $arg->int("textarea_col", $this->default_textarea_col);
		$this->assign("textarea_col", $textarea_col);
		
		//$textarea_row = (array_key_exists("textarea_row", $arg)) ? $arg['textarea_row'] : $this->default_textarea_row;
		$textarea_row = $arg->int("textarea_row", $this->default_textarea_row);
		$this->assign("textarea_row", $textarea_row);
		
		$this->assign("token", jphp_generateId(10));
		
		$this->setTemplate(PATH_PLUGIN."editor/html/editor.html");
	}
}