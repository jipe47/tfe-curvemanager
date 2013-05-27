<?php
class CaptchaWidget extends Widget
{
	const DEFAULT_FIELDNAME = "captcha_answer";
	public function render()
	{
		$fieldname = $this->arg->string("fieldname", self::DEFAULT_FIELDNAME);
		$this->tpl->assign("fieldname", $fieldname);
		return $this->renderFile($this->path_html."captcha.html");
	}
}