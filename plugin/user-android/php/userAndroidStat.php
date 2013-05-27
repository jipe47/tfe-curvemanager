<?php
class Stats extends AndroidPage
{
	public function prerender()
	{
		parent::prerender();
		$id_user = $this->arg->int(1);
		$this->assign("id_user", $id_user);
		
		$array_score = $this->model->getHighScore();
		$this->assign("array_score", $array_score);
		
		$nbr_user = $this->model->getNbrUser();
		$this->assign("nbr_user", $nbr_user);
		
		// Count users per country
		$counts = $this->request->fetchQuery("
		SELECT COUNT(*) as nbr, country
		FROM " . TABLE_USER_ANDROID . " 
		GROUP BY country");
		
		$array_country = array();
		foreach($counts as $c)
			$array_country[$c['country']] = $c['nbr'];
		arsort($array_country, true);
		$this->assign("array_country", $array_country);
		
		// Count predictions per input type
		$counts = $this->request->fetchQuery("
		SELECT COUNT(*) as nbr, input 
		FROM " . TABLE_PREDICTION . " 
		GROUP BY input");
		
		$nbr_prediction = array(
		"trend" => 0, 
		"value" => 0, 
		"pm" => 0, 
		"total" => 0);
		
		foreach($counts as $c)
		{
			$nbr_prediction["total"] += intval($c['nbr']);
			$nbr_prediction[strtolower($c['input'])] = intval($c['nbr']);
		}
		
		// Compute means
		$nbr_prediction["trend_mean_per_user"] 	= $nbr_prediction["trend"] / $nbr_user;
		$nbr_prediction["value_mean_per_user"] 	= $nbr_prediction["value"] / $nbr_user;
		$nbr_prediction["pm_mean_per_user"] 	= $nbr_prediction["pm"] / $nbr_user;
		$nbr_prediction["total_mean_per_user"] 	= $nbr_prediction["total"] / $nbr_user;
		
		// Compute medians
		
		$array_counts = $this->request->fetchQuery("
		SELECT COUNT(*) as cnt, p.input, p.id_user, u.nickname, u.country FROM
		" . TABLE_PREDICTION . " p
		LEFT JOIN " . TABLE_USER_ANDROID . " u ON u.id = p.id_user
		GROUP BY p.id_user, p.input");
		
		$array_counts_perinput = array("TREND" => array(), "PM" => array(), "VALUE" => array());
		$array_counts_peruser = array(); // id_user => array('trend' => x, 'vlaue'= > x
		
		foreach($array_counts as $c)
		{
			if($c['id_user'] == "")
				continue;
			$array_counts_perinput[$c['input']][] = $c['cnt'];
			
			if(!array_key_exists($c['id_user'], $array_counts_peruser))
				$array_counts_peruser[$c['id_user']] = array("TREND" => 0, "VALUE" => 0, "PM" => 0, "total" => 0, "nickname" => $c['nickname'], "country" => $c['country']);
				
			$array_counts_peruser[$c['id_user']][$c['input']] = $c['cnt'];
			$array_counts_peruser[$c['id_user']]["total"] += $c['cnt'];
		}
		
		$array_counts_total = array();
		foreach($array_counts_peruser as $u => $d)
		{
			$array_counts_total[] = $d['total'];
		}
		foreach($array_counts_perinput as $k => $c)
		{
			sort($c);
			
			$nbr = count($c);
			$half = $nbr / 2;
			$median = $nbr % 2 == 0 ? ($c[$half] + $c[$half + 1])/2 : $c[floor($half) + 1];
			$nbr_prediction[strtolower($k)."_median_per_user"] = $median;
			$nbr_prediction[strtolower($k)."_biggest_nbr_prediction"] = $c[$nbr - 1];
		}
		
		$c = $array_counts_total;
		sort($c);
		$nbr = count($c);
		$half = $nbr / 2;
		$median = $nbr % 2 == 0 ? ($c[$half] + $c[$half + 1])/2 : $c[floor($half) + 1];
		$nbr_prediction["total_median_per_user"] = $median;
		$nbr_prediction["total_biggest_nbr_prediction"] = $c[$nbr - 1];
		
		foreach($array_counts_peruser as $id_user => $d)
		{
			$user = $d["country"] == "" ? "" : '<img src="'.Country::getFlag($d["country"]).'" alt="'.Country::getName($d["country"]).'" title="'.Country::getName($d["country"]).'" />';
			$user .= $d["nickname"] == "" ? " Player #".$id_user : " " . $d["nickname"];

			if($d["total"] == $nbr_prediction["total_biggest_nbr_prediction"])
				$nbr_prediction["total_biggest_nbr_prediction_user"] = $user;
				
			if($d["TREND"] == $nbr_prediction["trend_biggest_nbr_prediction"])
				$nbr_prediction["trend_biggest_nbr_prediction_user"] = $user;
				
			if($d["VALUE"] == $nbr_prediction["value_biggest_nbr_prediction"])
				$nbr_prediction["value_biggest_nbr_prediction_user"] = $user;
				
			if($d["PM"] == $nbr_prediction["pm_biggest_nbr_prediction"])
				$nbr_prediction["pm_biggest_nbr_prediction_user"] = $user;
		}
				
		$this->assign("nbr_prediction", $nbr_prediction);
		
		$this->setTemplate($this->path_html."stat_game.html");
	}
}