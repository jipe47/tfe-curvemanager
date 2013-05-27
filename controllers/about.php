<?php
class About extends AndroidPage
{
	public function prerender()
	{
		parent::prerender();
		
		if($this->androidView)
			$this->setTemplate(TPL."html/about_android.html");
		else
			$this->setTemplate(TPL."html/about.html");
	}
}