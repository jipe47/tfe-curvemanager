<?php
class Header extends Page{
	public function construct()
	{
		$this->addAccess(self::ALL);
		$this->setTemplate(TPL."html/header.html");
		$this->setFullRender(false);
		$this->showHeaders(false);
	}
}