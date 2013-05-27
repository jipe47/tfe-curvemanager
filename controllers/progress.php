<?php
class Progress extends AndroidPage
{
	public function prerender()
	{
		parent::prerender();
		$this->setTemplate(TPL."html/progress.html");
		
		$id_user = $this->arg->int(0);
		
		$model = Plugins::getPlugin("Curve")->getDefaultModel();
		$array_group = $model->getGroups();
		$user_group = $model->getGroupByUser($id_user);
		$nbr_group = count($array_group);
		$this->assign("nbr_group", $nbr_group);
	
		/*
		$sql_remaining = $this->request->fetchQuery("SELECT COUNT(*) as nbr_unpredicted
		
													SELECT COUNT(*) as cnt, predictionInput
													FROM " . TABLE_PREDICTION . "
													WHERE id_user='" . $id_user . "'
													GROUP BY predictionInput
		
														");
		*/
				
		foreach($user_group as $k => $g)
		{
			if(is_null($g))
			{
				$user_group[$k] = array();
				$user_group[$k]['percentage'] = 0;
				$user_group[$k]['position'] = 0;
			}
			else
			{
				$user_group[$k]['percentage'] = round(($g['nbr_curve_predicted']/$g['nbr_curve'])*100, 1);
			}
		}
		
		
		$this->assign("user_group", $user_group);
		
		$this->assign("array_title", array("VALUE" => "Value Prediction", "PM" => "Plus/Minus Prediction", "TREND" => "Trend Prediction"));
	}
}