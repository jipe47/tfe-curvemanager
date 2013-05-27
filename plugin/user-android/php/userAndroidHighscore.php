<?php
class Highscore extends AndroidPage
{
	public function prerender()
	{
		parent::prerender();
		$id_user = $this->arg->int(0);
		$this->assign("id_user", $id_user);
		
		$array_score = $this->model->getHighScore(10);
		$this->assign("array_score", $array_score);
		$this->setTemplate($this->path_html."stat_highscore.html");
	}
}