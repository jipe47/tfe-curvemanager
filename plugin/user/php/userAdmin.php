<?php
class UserAdmin extends AdminPage
{
	public function construct()
	{
		$this->setAccessName("User");
	}
	
	public function handler()
	{
		$f = $this->arg->string(0);
		switch($f)
		{
			case "add":
			case "edit":
				$id = $this->arg->int(1);
				$array = array('firstname' => '', 'lastname' => '', 'email' => '', 'login' => '', 'id' => $id, 'title' => "New", 'submit' => "Create");
				
				if($id != -1)
				{
					$array = $this->model->getUser($id);
					$array['title'] = "Editing";
					$array['submit'] = "Update";
				}
				$this->assign("info", $array);
				$f = "addedit";
				break;
				
			case "list":
				$array_user = $this->model->searchUser();
				$this->assign("array_user", $array_user);
				break;
		}
	
		$this->setTemplate($this->path_html."admin/user_".$f.".html");
		return $this->renderTemplate();
	}
}