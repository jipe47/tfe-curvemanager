<?php
class CategoryWidget extends Widget
{
	public function prerender()
	{
		$this->assign("categories", $this->model->getCategoriesInTreeStructure());
		$this->setTemplate($this->path_html."widget.html");
		$this->assign("id_selected", $this->arg->int("id_selected"));
		$this->assign("array_highlight", $this->arg->raw("highlight", array()));
	}
}