<?php
class BugtrackerAdmin extends AdminPage
{
	public function construct()
	{
		$this->setAccessName("Bugtracker");
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		$f = $query;
		
		$this->assign("array_status", $this->plugin->getStatus());
		$this->assign("array_android", $this->plugin->getAndroidVersion());
		
		switch($query)
		{
			case "edit":
				$id = $this->arg->int(1);
				$info = $this->model->getBug($id);
				$this->assign("info", $info);
				break;
		}
		
		$this->setTemplate($this->path_html."admin/bugtracker_".$f.".html");
		return $this->renderTemplate();
	}
}