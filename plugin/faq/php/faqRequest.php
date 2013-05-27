<?php
class FaqRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("Faq");
		$this->addAccess(Page::ADMIN);
	}
	
	public function render()
	{
		$this->setLocation("Home");
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		
		$query = $this->arg->string(0);
		$back = "Admin/Faq/list";
		
		switch($query)
		{
			case "delete":
				$id = $this->arg->int(1);
				$info = $this->model->getFaqById($id);
				$this->request->query("UPDATE " . TABLE_FAQ . " SET position = position - 1 WHERE position > " . $info['position']);
				$this->request->query("DELETE FROM " . TABLE_FAQ . " WHERE id='" . $id . "'");
				out::message("Question deleted");
				break;
				
			case "addedit":
				$id = Post::int("id");
				$question = Post::string("question");
				$answer = Post::string("answer");
				
				$a = array("question" => $question, "answer" => $answer);
				if($id == -1)
				{
					$a['position'] = $this->model->getNbrFaq();
					$this->request->insert(TABLE_FAQ, $a);
				}
				else
					$this->request->update(TABLE_FAQ, "id='".$id."'", $a);
				$m = $id == -1 ? "Question added" : "Question updated";
				out::message($m);
				break;
			
			case "move":
				$id = $this->arg->int(1);
				$direction = $this->arg->string(2);
				$inc = $direction == "up" ? -1 : 1;
				$info = $this->model->getFaqById($id);
				$newposition = $info['position'] + $inc;
				$this->request->query("UPDATE " . TABLE_FAQ . " SET position = " . $info['position'] . " WHERE position='" . $newposition . "'");
				$this->request->query("UPDATE " . TABLE_FAQ . " SET position = " . $newposition . " WHERE id='" .$id."'");
				break;
		}
		
		$this->setLocation($back);
	}
}