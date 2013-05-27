<?php
class FaqAdmin extends AdminPage
{
	public function construct()
	{
		$this->setAccessName("Faq");
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		$f = $query;
		switch($query)
		{
			case "category":
				$this->setLocation("Admin/Category/list/faq");
				return "";
				
			case "list":
				$this->assign("array_faq", $this->model->getFaq());
				break;
				
			case "add":
			case "edit":
				$f = "addedit";
				$id = $this->arg->int(1);
				$title = $id == -1 ? "New" : "Editing";
				$submit = $id == -1 ? "Add" : "Update";
				$question = "";
				$answer = "";
				if($id != -1)
				{
					$info 		= $this->model->getFaqById($id);
					$question 	= $info['question'];
					$answer 	= $info['answer'];
				}
				$this->assignArray(array("id" => $id, "question" => $question, "answer" => $answer, "title" => $title, "submit" => $submit));
				break;
		}
		$this->setTemplate($this->path_html."admin/faq_".$f.".html");
		return $this->renderTemplate();
	}
}