<?php
class CategoryAdmin extends AdminPage{
	public function construct()
	{
		$this->setAccessName("Category");
	}
	
	public function traverse($n)
	{
		return self::static_traverse($n);
	}
	
	public static function static_traverse($n)
	{
		$a = array();
	
		if($n['id'] != 0)
			$a[] = array('id' => $n['id'], 'value' => $n['value'], 'level' => $n['level'], 'id_parent' => $n['id_parent']);
	
		foreach($n['children'] as $c)
			$a = array_merge($a, self::static_traverse($c));
		return $a;
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		switch($query)
		{
			case "add":
			case "edit":
				$id = -1;
				$name = "";
				$id_parent = -1;
				$description = "";
				$icon = "";
				
				$title = "New";
				$submit = "Add";
				
				if($query == "add")
				{
					$info = array("name" => $name, "id" => $id, "id_parent" => $id_parent,
							"description" => $description, "icon" => $icon);
					if($this->arg->keyExists(1))
						$info["id_parent"] = $this->arg->int(1);
				}else if($query == "edit")
				{
					$id = $this->arg->int(1);
					$info = $this->model->getCategoryById($id);
					$title = "Editing";
					$submit = "Edit";
				}
				
				
				$this->assign("info", $info);
				$this->assign("submit", $submit);
				$this->assign("title", $title);
				
				$all = $this->model->getCategories($id);
				$array_cat = $this->traverse($all);
				$this->tpl->assign("array_cat", $array_cat);
				
				$f = "addedit";
				break;
				
			case "list":
				$all = $this->model->getCategories();
				$array_cat = $this->traverse($all);
				$this->tpl->assign("array_cat", $array_cat);
				
				$all = $this->model->getRawCategories();
				$array_cat_byid = array();
				$array_cat_nbrchild = array();
				$array_cat_parentof = array();
				foreach($all as $c)
				{
					if(!array_key_exists($c['id_parent'], $array_cat_nbrchild))
						$array_cat_nbrchild[$c['id_parent']] = 1;
					else
						$array_cat_nbrchild[$c['id_parent']]++;
					$array_cat_byid[$c['id']] = $c;
				}
				$this->assign("array_cat_byId", $array_cat_byid);
				$this->assign("array_cat_nbrchild", $array_cat_nbrchild);			
				
				$f = "list";
				break;
		
			case "delete":
				$id = intval($arg[1]);
		
				$info = $this->request->firstQuery("SELECT * FROM " . TABLE_CATEGORY . " WHERE id='" . $id . "'");
				$this->tpl->assign("info", $info);
				$f = "delete";
				break;
		}
		
		return $this->renderFile($this->path_html."admin/category_".$f.".html");
	}
}