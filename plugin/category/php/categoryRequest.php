<?php
class CategoryRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("Category");
		$this->addAccess(Page::ADMIN);
	}
	
	public function handler()
	{
		$this->setLocation("Home");
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		
		switch($this->arg->string(0))
		{
			case "addedit":
				$a = array(	'name' => Post::string("name"), 
							'description' => Post::string("description"),
							'id_parent' => Post::string("id_parent"));
		
				if(Post::boolValue("icon_delete", "on"))
					$a['icon'] = "";
				else
				{
					$icon = FileUpload::moveFile("icon", "category", false);
					if(!FileUpload::isError($icon))
					{
						$a['icon'] = $icon;
						ImageProcessing::redim(PATH_UPLOAD."category/".$icon, PATH_UPLOAD."category/mini/".$icon, 210, 120);
					}
				}
				$id = Post::int("id", -1);
		
				if($id == -1)
				{
					$a["position"]= $this->model->getNbrCategory($a['id_parent']);
					$this->request->insert(TABLE_CATEGORY, $a);
					$m = "The category has been added.";
				}
				else
				{
					$this->request->update(TABLE_CATEGORY, "id='".$id."'", $a);
					$m = "The category has been edited.";
				}
		
				Messages::add($m, Message::SUCCESS);
				break;
		
			case "delete":
				$id = $this->arg->int(1);
				$info = $this->model->getCategoryById($id);
				$this->request->query("	UPDATE " . TABLE_CATEGORY . " 
										SET position = position - 1 
										WHERE id_parent='" . $info['id_parent'] . "' 
											AND position > '" . $info['position'] . "'");
				$this->request->query("DELETE FROM " . TABLE_CATEGORY . " WHERE id='" .$id . "'");
				Messages::add("The category has been deleted.", Message::SUCCESS);
				break;
				
			case "move":
				$id = $this->arg->int(1);
				$direction = $this->arg->string(2);
				$inc = $direction == "up" ? -1 : 1;
				$info = $this->model->getCategoryById($id);
				$newposition = $info['position'] + $inc;
				$where = $info['id_parent'] == "" ? "id_parent IS NULL" : "id_parent='" . $info['id_parent'] . "'";
				$this->request->query("UPDATE " . TABLE_CATEGORY . " SET position = " . $info['position'] . " WHERE position='" . $newposition . "' AND ".$where);
				$this->request->query("UPDATE " . TABLE_CATEGORY . " SET position = " . $newposition . " WHERE id='" .$id."'");
				break;
		
			default:
				Messages::add("Undefined operation.", Message::ERROR);
		}
		
		$this->setLocation("Admin/Category/list");
	}
}