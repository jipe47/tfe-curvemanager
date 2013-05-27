<?php
class FaqPage extends Page
{
	public function construct()
	{
		$this->setAccessName("Faq");
	}
	
	public function prerender()
	{
		$this->setTemplate($this->path_html."faq_page.html");
		$this->assign("array_faq", $this->model->getFaq());
	}
}