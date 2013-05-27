<?php
class CurveScriptM3CUpdateZone extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Update M3C Zones Parameters");
		$this->setAccessName("UpdateM3C");
		
		
		$this->addScriptArg("zone_length", "Zone length (percentage of curve's length)", 10);
		
				
		$this->addScriptArg("value_gain", "Value: gain", 750);
		$this->addScriptArg("value_loss", "Value: loss", 150);
		
		$this->addScriptArg("value_allowederror", "Value: allowed error (percentage of curve height)", 0.2);
		$this->addScriptArg("value_maxerror", "Value: max error (percentage of curve height)", 0.4);
				
		$this->addScriptArg("pm_gain", "PM: gain", 750);
		$this->addScriptArg("pm_loss", "PM: loss", 150);
		$this->addScriptArg("pm_mindiff", "Minimal difference for a PM prediction (percentage of curve's height)", 12);
		
				
		$this->addScriptArg("trend_gain", "Trend: gain", 1500);
		$this->addScriptArg("trend_loss", "Trend: loss", 150);
		
		$this->addScriptArg("trend_allowederror", "Trend: allowed error (percentage of curve's height)", 0.1);
		$this->addScriptArg("trend_maxerror", "Trend: max error (percentage of curve's height)", 0.2);
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		session_write_close();
		// Get parameters
		$param_zone_length			= Post::float("zone_length")/100;
		$param_value_maxerror 		= Post::float("value_maxerror");
		$param_value_allowederror 	= Post::float("value_allowederror");
		$param_value_gain 			= Post::float("value_gain");
		$param_value_loss 			= Post::float("value_loss");
		
		$param_pm_gain 				= Post::float("pm_gain");
		$param_pm_loss 				= Post::float("pm_loss");
		$param_pm_mindiff 			= Post::float("pm_mindiff")/100;
		
		$param_trend_maxerror 		= Post::float("trend_maxerror");
		$param_trend_allowederror 	= Post::float("trend_allowederror");
		$param_trend_gain 			= Post::float("trend_gain");
		$param_trend_loss 			= Post::float("trend_loss");
		
		SqlRequest::setStoreRequest(false); // Otherwise memory's max capacity is quickly reached
		set_time_limit(0);
		
		
		$array_zone = $this->request->fetchQuery("SELECT z.id, z.id_curve, z.start, z.end, c.name, c.points, c.display_pm
				FROM " . TABLE_CURVE_ZONE . " z
				LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
				ORDER BY c.id DESC");
		$nbr_hidden_pm = 0;
		$cnt = 0;
		foreach($array_zone as $k => $z)
		{
			$cnt++;
			$points = explode(";", $z['points']);
			$ys = array();
			foreach($points as $k => $v)
				$ys[] = floatval($v);
			
			$yMin 			= min($ys);
			$yMax 			= max($ys);
			$curveHeight	= $yMax - $yMin;
			
			$nbr_point = count($points);
			
			$value_maxerror 		= $param_value_maxerror * $curveHeight;
			$value_allowederror 	= $param_value_allowederror * $curveHeight;
			$value_gain 			= $param_value_gain;
			$value_loss 			= $param_value_loss;
			
			$pm_gain 			= $param_pm_gain;
			$pm_loss 			= $param_pm_loss;
			
			$trend_maxerror 	= $param_trend_maxerror * $curveHeight;
			$trend_allowederror = $param_trend_allowederror * $curveHeight;
			$trend_gain 		= $param_trend_gain;
			$trend_loss 		= $param_trend_loss;
			
			$array_zone = array(
					"value_gain" => $value_gain,
					"value_loss" => $value_loss,
					"value_maxerror" => $value_maxerror,
					"value_allowederror" => $value_allowederror,
					"pm_gain" => $pm_gain,
					"pm_loss" => $pm_loss,
					"trend_maxerror" => $trend_maxerror,
					"trend_allowederror" => $trend_allowederror,
					"trend_gain" => $trend_gain,
					"trend_loss" => $trend_loss);
		
			// Correct zone length
			$current_zonelength = $z['end'] - $z['start'];
			$required_zonelength = floor($nbr_point * $param_zone_length);
			
			if($required_zonelength != $current_zonelength)
			{
				$newStart = $z['end'] - $required_zonelength;
				//Log::add("Curve " . $z['name'] . " new start: " . $newStart . " instead of " . $current_zonelength);
				$array_zone['start'] = $newStart;
				$this->request->query("DELETE FROM " . TABLE_PREDICTION . " WHERE id_zone='" . $z['id'] . "'");
			}
			
			// Correct the hidden_pm flag of the curve
			$zoneYDiff = abs($points[$z['start']] - $points[$z['end']]);
			$minDiff = $param_pm_mindiff * $curveHeight;
			
			$display_pm = $zoneYDiff < $minDiff ? "0" : "1";
			
			if($display_pm == "0")
			{
				$nbr_hidden_pm++;
				//Log::add("Curve " . $z['name'] . " hidden from PM: " . $zoneYDiff . " &lt; " . $minDiff . " ; current count = " . $nbr_hidden_pm . " / " . $cnt);
			}
			if($z['display_pm'] != $display_pm)
			{
				$this->request->update(TABLE_CURVE, 'id="'.$z['id_curve'].'"', array('display_pm' => $display_pm));
			}
			
			//Log::add("Curve " . $z['name'] . " : " . $zoneYDiff . " / " . $minDiff);
			$this->request->update(TABLE_CURVE_ZONE, 'id="'.$z['id'].'"', $array_zone);
		}
		
		echo "Done<br />";
		echo "Nbr hidden curves for PM: " . $nbr_hidden_pm;
		SqlRequest::setStoreRequest(true);
	}
}