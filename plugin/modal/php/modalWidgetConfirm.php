<?php
class ModalWidgetConfirm extends Widget
{
	public function construct()
	{
		$this->setAccessName("confirm");
		$this->addAccess(self::ALL);
	}
	
	public function render()
	{
		$this->arg->setDefault("title", "Confirmation");
		$this->arg->setDefault("handler_yes", "");
		$this->arg->setDefault("handler_no", "");
		
		$this->arg->set('confirm_message',  $this->arg->string('message'));
		$this->arg->set('content_template', $this->path_html."content_confirm.html");
		$this->arg->set('bufferize', true);
		$widget = $this->plugin->getWidget("basic");
		$widget->setArg($this->arg->getArray());
		return $widget->render();
	}
}