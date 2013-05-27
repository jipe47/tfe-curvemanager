<?php
class CurveScriptM3CInsert extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Import M3C");
		$this->setAccessName("ImportM3C");
		$this->addScriptArg("zone_nbr", "Number of zone", 1);
		$this->addScriptArg("zone_length", "Zone length (in percent of curve's length)", 10);
				
		$this->addScriptArg("value_gain", "Value: gain", 750);
		$this->addScriptArg("value_loss", "Value: loss", 150);
		
		$this->addScriptArg("value_allowederror", "Value: allowed error (percentage of curve height)", 0.2);
		$this->addScriptArg("value_maxerror", "Value: max error (percentage of curve height)", 0.4);
				
		$this->addScriptArg("pm_gain", "PM: gain", 750);
		$this->addScriptArg("pm_loss", "PM: loss", 150);
		
		$this->addScriptArg("pm_equalheight", "PM: equal zone height (percentage of curve height)", 0.2);
		$this->addScriptArg("pm_maxerror", "PM: max error (percentage of curve height)", 0.4);
				
		$this->addScriptArg("trend_gain", "Trend: gain", 1500);
		$this->addScriptArg("trend_loss", "Trend: loss", 150);
		
		$this->addScriptArg("trend_allowederror", "Trend: allowed error (percentage of curve height)", 0.1);
		$this->addScriptArg("trend_maxerror", "Trend: max error (percentage of curve height)", 0.2);
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		$files 		= array("month", "other", "quart", "year");
		//$files 		= array("other");
		$cat 		= array();
		
		// Get parameters
		$param_value_maxerror 		= Post::float("value_maxerror");
		$param_value_allowederror 	= Post::float("value_allowederror");
		$param_value_gain 			= Post::float("value_gain");
		$param_value_loss 			= Post::float("value_loss");
		
		$param_pm_gain 				= Post::float("pm_gain");
		$param_pm_loss 				= Post::float("pm_loss");
		$param_pm_equalheight 		= Post::float("pm_equalheight");
		//echo "curveheight = $curveHeight<br />pm_equalheight = $pm_equalheight<br />";
		
		//Log::add("$name : yMin = $yMin, yMax = $yMax, curveheight = $curveHeight, pm_equalheight = $pm_equalheight");
		
		$param_trend_maxerror 		= Post::float("trend_maxerror");
		$param_trend_allowederror 	= Post::float("trend_allowederror");
		$param_trend_gain 			= Post::float("trend_gain");
		$param_trend_loss 			= Post::float("trend_loss");
		
		// Create the root category for the M3C
		$this->request->insert(TABLE_CATEGORY, array("name" => "M3C Challenge"));
		$id_root = $this->request->getLastId();
		
		// Create main categories for each file
		foreach($files as $f)
		{
			$this->request->insert(TABLE_CATEGORY, array("name" => ucfirst($f), "id_parent" => $id_root));
			$cat[] = $this->request->getLastId();
		}
		
		$zone_nbr 		= Post::int("zone_nbr");
		$zone_percentage= Post::float("zone_length")/100;
		
		SqlRequest::setStoreRequest(false); // Otherwise memory's max capacity is quickly reached
		set_time_limit(0);
		
		foreach($files as $n => $f)
		{
			$handle = fopen("data/M3C_".$f.".csv", "r");
		
			$array_subcat = array(); // type of curve => id of subcat
			$array_count = array(); // id of subcat => nbr_curve
			
			// Skip first line
			fgetcsv($handle);
		
			$n_curve = 0;
			while(($line = fgetcsv($handle, 0, ";")) !== false)
			{
				$name 			= str_replace(" ", "", $line[0]);
				$type 			= $line[3];
				
				//if($name != "N2024")
				//	continue;
				
				$tag 			= "M3C,".$type;
				$start_year 	= intval($line[4]);
				$start_month 	= intval($line[5]);
		
				$extra 		= $start_year."-".$start_month;
				$points 	= array_slice($line, 6);
		
				$ys = array();
				foreach($points as $k => $v)
				{
					$v = str_replace(",", ".", $v);
					
					if(!is_numeric($v))
						break;
					$ys[] = floatval($v);
				}
				
				$nbr_point = count($ys);
				$x_start = 0;
				$x_end = $nbr_point - 1;
		
				// Curve insertion
				if(!array_key_exists($type, $array_subcat))
				{
					$array_count[$type] = 0;
					$this->request->insert(TABLE_CATEGORY, array('name' => $type, 'id_parent' => $cat[$n]));
					$array_subcat[$type] = $this->request->getLastId();
				}
				
				$array_sql = array("name" => $name, "tag" => $tag, 
						"id_category" => $array_subcat[$type],
						"points" => implode(";", $ys), 
						"extra" => $extra,
						"position" => $array_count[$type],
						"display" => 1);
				
				$this->request->insert(TABLE_CURVE, $array_sql);
				$id_curve = $this->request->getLastId();
				$array_count[$type]++;
				
				// Compute y extrema
				//Log::add("points = " . implode(";", $points), Message::WARNING);
				$yMin = min($ys);
				$yMax = max($ys);				
				
				$curveHeight = $yMax - $yMin;
				$value_maxerror 		= $param_value_maxerror * $curveHeight;
				$value_allowederror 	= $param_value_allowederror * $curveHeight;
				$value_gain 			= $param_value_gain;
				$value_loss 			= $param_value_loss;
				
				$pm_gain 			= $param_pm_gain;
				$pm_loss 			= $param_pm_loss;
				$pm_equalheight 	= $param_pm_equalheight * $curveHeight;
				//echo "curveheight = $curveHeight<br />pm_equalheight = $pm_equalheight<br />";
				
				//Log::add("$name : yMin = $yMin, yMax = $yMax, curveheight = $curveHeight, pm_equalheight = $pm_equalheight");
				
				$trend_maxerror 	= $param_trend_maxerror * $curveHeight;
				$trend_allowederror = $param_trend_allowederror * $curveHeight;
				$trend_gain 		= $param_trend_gain;
				$trend_loss 		= $param_trend_loss;
				
				// Insert zones
				$zone_length = floor($zone_percentage * $nbr_point);
				$end 	= $nbr_point - 1;
				$start 	= $end - $zone_length;
				
				for($i = 0 ; $i < $zone_nbr ; $i++)
				{
					$array_zone = array(
							"start" => $start, 
							"end" => $end, 
							"id_curve" => $id_curve,
							"value_gain" => $value_gain, 
							"value_loss" => $value_loss, 
							"value_maxerror" => $value_maxerror, 
							"value_allowederror" => $value_allowederror,
							"pm_gain" => $pm_gain, 
							"pm_loss" => $pm_loss, 
							"pm_equalheight" => $pm_equalheight, 
							"trend_maxerror" => $trend_maxerror, 
							"trend_allowederror" => $trend_allowederror, 
							"trend_gain" => $trend_gain, 
							"trend_loss" => $trend_loss);
					$this->request->insert(TABLE_CURVE_ZONE, $array_zone);
					$start -= $zone_length;
					$end -= $zone_length;
				}
				
				$n_curve++;
				//break;
			}
			fclose($handle);
		
			echo $f.": " . $n_curve . " curve(s) imported<br />";
		}
		
		SqlRequest::setStoreRequest(true);
	}
}