<?php
class Bugtracker extends HandlerPage
{
	public function construct()
	{
		$this->setTemplate($this->path_html."bugtracker_page.html");
		$this->registerHandler("add", "handler_add");
		$this->registerHandler("list", "handler_list");
		$this->registerHandler("view", "handler_view");
		$this->registerDefaultHandler("handler_list");
	}
	
	public function handler_add()
	{
		$this->assign("array_android", $this->plugin->getAndroidVersion());
		$this->setTemplate($this->path_html."bugtracker_add.html");
		return $this->renderTemplate();
	}
	
	public function handler_list()
	{
		$this->assign("array_bug", $this->model->getBugs());
		$this->assign("array_status", $this->plugin->getStatus());
		$this->setTemplate($this->path_html."bugtracker_list.html");
		return $this->renderTemplate();
	}
	
	public function handler_view()
	{
		$id = $this->arg->int(0);
		$this->assign("info", $this->model->getBug($id));
		$this->assign("array_status", $this->plugin->getStatus());
		$this->setTemplate($this->path_html."bugtracker_view.html");
		return $this->renderTemplate();
	}
	
}