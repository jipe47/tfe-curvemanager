<?php
class AndroidPage extends Page
{
	protected $androidView;
	public function __construct()
	{
		if(func_num_args() == 0)
			parent::__construct();
		else
			call_user_func_array("parent::__construct", func_get_args());
	}
	
	public function prerender()
	{
		$this->androidView = $this->arg->string(0) == "android";
		$this->assign("androidView", $this->androidView);
		
		if($this->androidView)
		{			
			$array = $this->arg->getArray();
			unset($array[0]);
			$array = array_values($array);
			$this->arg = new Arg($array);
		}
		
		if($this->androidView)
		{
			HtmlHeaders::flushCssHeaders();
			HtmlHeaders::addCssFile(TPL."android/android.css?".time());
			$this->setFullRender(false);
		}
	}
}