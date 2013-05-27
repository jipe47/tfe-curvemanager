<?php
/**
 * Represents a page executing requests, based on handler definition.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */
abstract class RequestPage extends HandlerPage
{
	public function __construct()
	{
		if(func_num_args() == 0)
			parent::__construct();
		else
			call_user_func_array("parent::__construct", func_get_args());
		$this->setPageType("request");
	}
}