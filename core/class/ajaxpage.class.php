<?php
/**
 * Represents an page designed for Ajax interaction. It is based on handler definition.
 * 
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */
abstract class AjaxPage extends HandlerPage
{
	public function __construct()
	{
		if(func_num_args() == 0)
			parent::__construct();
		else
			call_user_func_array("parent::__construct", func_get_args());
		$this->setFullRender(false);
		$this->showHeaders(false);
		$this->setPageType("ajax");
	}
}
