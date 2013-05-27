<?php
class UserAndroidAdmin extends AdminPage{
	public function construct()
	{
		$this->setAccessName("UserAndroid");
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		switch($query)
		{
			case "add":
			case "edit":
				$info = array(
				"id" => -1,
				"name" => "",
				"tag" => "",
				"id_category" => -1,
				"priority" => false,
				"priority_threshold" => -1,
				"display" => true,
				"description" => "",
				"x_start" => "",
				"x_end" => "",
				"x_step" => "",
				"f" => "",
				"noise" => 0
				);
				
				$title = "New";
				$submit = "Add";
				
				if($query == "add")
				{
					if($this->arg->keyExists(1))
						$info["id_category"] = $this->arg->int(1);
				}
				else if($query == "edit")
				{
					$id = $this->arg->int(1);
					$info = $this->model->getCurveById($id);
					$title = "Editing";
					$submit = "Edit";
				}
				
				$this->assign("info", $info);
				$this->assign("submit", $submit);
				$this->assign("title", $title);
				
				$all = Plugins::getPlugin("Category")->getDefaultModel()->getCategories();
				$array_cat = $this->traverse($all);
				$this->tpl->assign("array_cat", $array_cat);
				
				$f = "addedit";
				break;
				
			case "list":
				$id_cat = $this->arg->int(1);
				$this->assign("array_curve", $this->model->getCurvesByCategory($id_cat));
				
				$this->assign("id_cat", $id_cat);
				$f = "list";
				break;
				
			case "view":
				$id = $this->arg->int(1);
				$info = $this->model->getCurveById($id);
				
				
				$predictions = $this->model->getPredictionsById($id);
				
				$this->assign("info", $info);
				$this->assign("predictions", $predictions);
				
				
				// Compute stuff for stat display
				$labels = array("p" => "+", "e" => "=", "m" => "-");
				$data_attheend_pm = array();
				$occ = array_count_values($predictions['ATTHEEND']['PM']);
				foreach($labels as $k => $l)
					
				
				$data_attheend_pm_data = array();
				$data_attheend_pm_label = array();
				
				foreach($labels as $k => $l)
				{
					if(!array_key_exists($k, $occ))
						$occ[$k] = 0;
					$data_attheend_pm_data[] = $occ[$k];
					$data_attheend_pm_label[] = $l . " : " . $occ[$k] . " pred.";
				}
				
				
				$this->assign("data_attheend_pm_data", $data_attheend_pm_data);
				$this->assign("data_attheend_pm_label", $data_attheend_pm_label);
				
				$f = "view";
				break;
		
			case "delete":
				$id = intval($arg[1]);
		
				$info = $this->model->getCurveById($id);
				$this->tpl->assign("info", $info);
				$f = "delete";
				break;
		}
		
		return $this->renderFile($this->path_html."admin/curve_".$f.".html");
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
}