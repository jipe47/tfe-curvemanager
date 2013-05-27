<?php
class UserAjax extends AjaxPage
{
	public function construct()
	{
		$this->setAccessName("User");
	}
	public function selfRender()
	{
		switch($this->arg->string(0))
		{
			case "search":
				$search = Post::value("search");
		
				$wheres = array();
				if($search == "")
					return "";
		
				if($search != "*")
					$wheres[] = "firstname REGEXP '".$search."' OR lastname REGEXP '".$search."'";
		
				$id_avoid = explode(";", Post::value("id_avoid", ""));
		
				if(count($id_avoid) > 0)
					$wheres[] = "id NOT IN ('". implode("', '", $id_avoid) ."')";
		
				$where = implode(" AND ", $wheres);
		
				if($where != "")
					$where = " WHERE ".$where;
		
				$array_result = $this->request->fetchQuery("SELECT * FROM " . TABLE_USER . $where);
				$this->assign("array_result", $array_result);
		
				$array_result_id = array();
				foreach($array_result as $r)
					$array_result_id[] = $r['id'];
				$this->assign("array_result_id", implode(";", $array_result_id));
		
				$array_avoid = (!empty($_POST['array_avoid'])) ? unserialize(Post::value("array_avoid")) : array();
				$this->assign("array_avoid", $array_avoid);
		
				$array_selected = (!empty($_POST['array_selected'])) ? unserialize(Post::value("array_selected")) : array();
				$this->assign("array_selected", $array_selected);
				$f = "widget_user_search_result";
				break;
			case "showmember":
		
				$ids = explode(";", Post::value("users"));
				$f = "widget_user_search_showselected";
		
				$a = array();
		
				foreach($ids as $id)
					if($id != 0 && $id != "")
					$a[] = intval($id);
				if(count($a) == 0)
					return "";
		
				$array_user = $this->request->fetchQuery("SELECT * FROM " . TABLE_USER . " WHERE id IN ('".implode("', '", $a)."') ORDER BY firstname, lastname");
				$this->assign("array_user", $array_user);
		
				break;
		
			case "verifylogin":
		
				$login = Post::value("login", "");
		
				if($login == "")
					return "-1";
		
				$verif = $this->request->firstQuery("
						SELECT COUNT(*) as nbr 
						FROM " . TABLE_USER . " 
						WHERE login='" . $login . "'");
		
				if($verif['nbr'] == 0)
					return "1";
				else
					return "0";
			default:
				return "";
		}
		return $this->renderFile(PATH_PLUGIN."user/html/".$f.".html");
		
	}
}