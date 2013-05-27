<?php
abstract class Widget extends Page
{
	public function __construct()
	{
		if(func_num_args() > 0)
			//parent::__construct(func_get_args());
			call_user_func_array("parent::__construct", func_get_args());
		else
			parent::__construct();
		$this->setFullRender(false);
		$this->showHeaders(false);
	}
}