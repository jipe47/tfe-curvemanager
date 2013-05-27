<?php
class Editorwidget extends Widget
{
	public function render()
	{
		$e = new EditorPage($this->arg);
		return $e->render();
	}
}