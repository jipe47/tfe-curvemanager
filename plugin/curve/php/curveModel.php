<?php
class CurveModel extends Model
{
	
	/***********************/
	/*** ERROR FUNCTIONS ***/
	/***********************/
	
	public function quadraticError($ys_true, $ys_pred)
	{
		$sum = 0;
		foreach($ys_true as $i => $y_true)
		{
			$y_pred = $ys_pred[$i];
			$sum += pow($y_pred - $y_true, 2);
		}
		return $sum / count($ys_true);
	}
	
	public function SMAPE($ys_true, $ys_pred)
	{
		return $this->SMAPE1($ys_true, $ys_pred);
	}
	
	public function SMAPE1($ys_true, $ys_pred)
	{
		$sum = 0;

		foreach($ys_true as $i => $y_true)
		{
			$y_pred = $ys_pred[$i];
			$sum += abs($y_pred - $y_true) / ((abs($y_true) + abs($y_pred))/2);
		}
		return $sum / count($ys_true);
	}
	
	public function SMAPE2($ys_true, $ys_pred)
	{
		$sum = 0;

		foreach($ys_true as $i => $y_true)
		{
			$y_pred = $ys_pred[$i];
			$sum += abs($y_pred - $y_true) / (abs($y_true) + abs($y_pred));
		}
		return $sum / count($ys_true);
	}
	
	public function SMAPE3($ys_true, $ys_pred)
	{
		$num = 0;
		$den = 0;

		foreach($ys_true as $i => $y_true)
		{
			$y_pred = $ys_pred[$i];
			$num += abs($y_pred - $y_true);
			$den += abs($y_true) + abs($y_pred);
		}
		return $num / floatval($den);
	}
	public function computeError($ys_true, $ys_pred)
	{
		if(!is_array($ys_true))
		{
			$ys_true = array($ys_true);
			$ys_pred = array($ys_pred);
		}
		
		if(count($ys_true) != count($ys_pred))
		{
			out::message("Can't compute error on (" . implode(",", $ys_true) . ") and on (" . implode(",", $ys_pred) . ")", Message::ERROR);
			//echo "Can't compute error on (" . implode(",", $ys_true) . ") and on (" . implode(",", $ys_pred) . ")<br />";
			return 0;
		}
		return $this->SMAPE($ys_true, $ys_pred);
	}

	public function getUserMeanPredictions($id_group = -1)
	{
		$where = $id_group == -1 ? "" : " AND g.id='".$id_group."'";
		$array_pred_user = $this->request->fetchQuery("
				SELECT p.*, g.id as id_group, z.start, z.end, c.id as id_curve, c.points
				FROM " . TABLE_PREDICTION . " p
				LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
				LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				WHERE c.position < g.nbr_curve $where
				");

		$array_user_mean = array();

		// Sum all users' predictions
		foreach($array_pred_user as $k => $v)
		{
			$id_zone = $v["id_zone"];
			$input = $v["input"];
			$prediction = $v["data"];
			
			$points = explode(";", $v["points"]);		
			foreach($points as $k => $p)
				$points[$k] = floatval($p);
			
			$points_topredict = array_slice($points, $v["start"], $v["end"] - $v["start"]);
			
			if(!array_key_exists($id_zone, $array_user_mean))
			{
				$zone_length = $v["end"] - $v["start"];
				$array_trend_mean = array();
				
				for($i = 0 ; $i < $zone_length ; $i++)
					$array_trend_mean[] = 0;
				
				$array_user_mean[$id_zone] = array(
						
						"id_group" => $v["id_group"],
						"id_curve" => $v["id_curve"],
						"points" => $points,
						"points_topredict" => $points_topredict,
						"nbr_point" => count($points),
						"nbr_point_topredict" => count($points_topredict),
						
						"predictions" => array(
							"TREND" => array("nbr_prediction" => 0, "mean" => $array_trend_mean, "error" => 0),
							"VALUE" => array("nbr_prediction" => 0, "mean" => 0, "error" => 0),
							"PM" => array("nbr_prediction" => 0, "nbr_p" => 0, "nbr_m" => 0, "mean" => "", "error" => 0)
						)
				);
			}

			$array_user_mean[$id_zone]["predictions"][$input]["nbr_prediction"]++;

			switch($input)
			{
				case "VALUE":
					$array_user_mean[$id_zone]["predictions"][$input]["mean"] += floatval($prediction);
					break;
				case "TREND":
					$preds = explode(";", $prediction);
					foreach($preds as $i => $p)
						$array_user_mean[$id_zone]["predictions"][$input]["mean"][$i] += $p;
					break;

				case "PM":
					if($prediction == "PLUS")
						$array_user_mean[$id_zone]["predictions"][$input]["nbr_p"]++;
					else
						$array_user_mean[$id_zone]["predictions"][$input]["nbr_m"]++;
					break;
			}
		}

		// Compute global users predictions
		foreach($array_user_mean as $id_zone => $infos)
		{
			foreach($infos["predictions"] as $input => $array)
			{
				if($array["nbr_prediction"] == 0)
					continue;
				switch($input)
				{
					case "VALUE":
						$mean_value = $array["mean"] / floatval($array["nbr_prediction"]);
						$infos["predictions"][$input]["mean"] = $mean_value;
						$real_value = $infos["points_topredict"][$infos["nbr_point_topredict"] - 1];
						$infos["predictions"][$input]["error"] = $this->computeError($real_value, $mean_value);
						break;

					case "TREND":
						foreach($array["mean"] as $i => $m)
							$infos["predictions"][$input]["mean"][$i] = $m / floatval($array["nbr_prediction"]);
						
						
						$infos["predictions"][$input]["error"] = $this->computeError($infos["points_topredict"], $infos["predictions"][$input]["mean"]);
						break;

					case "PM":
						$mean_pred = $array["nbr_p"] > $array["nbr_m"] ? "PLUS" : "MINUS";
						$answer =  $infos["points_topredict"][$infos["nbr_point_topredict"] - 1] > $infos["points_topredict"][0] ? "PLUS" : "MINUS";
						$infos["predictions"][$input]["mean"] = $mean_pred;
						$infos["predictions"][$input]["error"] = $mean_pred == $answer ? 0 : 1;
						break;
				}
			}
			
			$array_user_mean[$id_zone] = array('predictions' => $infos['predictions'], 'id_group' => $infos['id_group'], 'id_curve' => $infos['id_curve']);
		}
		
		return $array_user_mean;
	}

	public function getGroupsBenchmark()
	{
		$output = array();
		$array_group = $this->request->fetchQuery("
				SELECT g.*, cat.name as category_name, cat2.name as parentcategory_name, cat2.id as id_category_parent
				FROM " . TABLE_CURVE_GROUP . " g
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = g.id_category
				LEFT JOIN " . TABLE_CATEGORY . " cat2 ON cat.id_parent = cat2.id
				ORDER BY g.position
				");
		
		
		// Pre-fill the output array
		$array_algo = $this->getAlgorithms();
		$array_input = array("TREND", "VALUE", "PM");
		
		$sum_error_per_group = array(); // id_group => sum
		foreach($array_group as $g)
		{
		
			$array = array(
					"id" => $g["id"], 
					"name" => $g["name"], 
					"id_category" => $g["id_category"], 
					"id_category_parent" => $g["id_category_parent"], 
					"parentcategory_name" => trim($g["parentcategory_name"]), 
					"category_name" => trim($g["category_name"]), 
					"position" => $g["position"],
					"benchmark" => array(
							"VALUE" => array(),
							"TREND" => array(),
							"PM" => array()
					));

			$output[$g["id"]] = $array;
		}
		
		/***********************************************/
		/**** AGREGATED USER PREDICTIONS PROCESSING ****/
		/***********************************************/

		$array_user_mean = $this->getUserMeanPredictions();
		
		// Sum errors by group and input
		$array_user_pred_bygroup = array();
		$array_user2_pred_bygroup = array();
		foreach($array_group as $g)
		{
			$array_user_pred_bygroup[$g["id"]] = array(
						"TREND" => array("nbr_error" => 0, "error" => 0),
						"VALUE" => array("nbr_error" => 0, "error" => 0),
						"PM" => array("nbr_error" => 0, "error" => 0)
					);
					
			$array_user2_pred_bygroup[$g["id"]] = array(
						"TREND" => array("nbr_error" => 0, "error" => 0),
						"VALUE" => array("nbr_error" => 0, "error" => 0),
						"PM" => array("nbr_error" => 0, "error" => 0)
					);
		}
		
		foreach($array_user_mean as $id_zone => $infos)
		{
			$id_group = $infos["id_group"];
			//out::message("line = <pre>" . print_r($infos, true)."</pre>");
			if($infos["predictions"]["TREND"]["nbr_prediction"] > 0)
			{
				//out::message("ok : " . $infos["predictions"]["TREND"]["error"] . " for $id_group");
				$array_user_pred_bygroup[$id_group]["TREND"]["nbr_error"]++;
				$array_user_pred_bygroup[$id_group]["TREND"]["error"] += $infos["predictions"]["TREND"]["error"];
			}
			
			if($infos["predictions"]["VALUE"]["nbr_prediction"] > 0)
			{
				$array_user_pred_bygroup[$id_group]["VALUE"]["nbr_error"]++;
				$array_user_pred_bygroup[$id_group]["VALUE"]["error"] += $infos["predictions"]["VALUE"]["error"];
			}
			
			if($infos["predictions"]["PM"]["nbr_prediction"] > 0)
			{
				$array_user_pred_bygroup[$id_group]["PM"]["nbr_error"]++;
				$array_user_pred_bygroup[$id_group]["PM"]["error"] += $infos["predictions"]["PM"]["error"];
			}
		}
		
		// Compute means
		foreach($array_user_pred_bygroup as $id_group => $infos)
		{
			if($infos["TREND"]["nbr_error"] > 0)
			{
				$mean_error = $infos["TREND"]["error"] / floatval($infos["TREND"]["nbr_error"]);
				$output[$id_group]["benchmark"]["TREND"]["user"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["TREND"]["error"] = $mean_error;
			}
			
			if($infos["VALUE"]["nbr_error"] > 0)
			{
				$mean_error = $infos["VALUE"]["error"] / floatval($infos["VALUE"]["nbr_error"]);
				$output[$id_group]["benchmark"]["VALUE"]["user"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["VALUE"]["error"] = $mean_error;
			}
			if($infos["PM"]["nbr_error"] > 0)
			{
				$mean_error = $infos["PM"]["error"] / floatval($infos["PM"]["nbr_error"]);
				$output[$id_group]["benchmark"]["PM"]["user"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["VALUE"]["error"] = $mean_error;
			}
		}
		
		/*************************************/
		/****    MEAN USER PROCESSING     ****/
		/*************************************/
		
		$array_pred = $this->request->fetchQuery("	SELECT p.*, g.id as id_group, c.points, z.start, z.end
													FROM " . TABLE_PREDICTION . " p
													LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
													LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
													LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
													LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
													WHERE c.position <= g.nbr_curve
													");
		
		foreach($array_pred as $k => $p)
		{
			$id_group = intval($p['id_group']);
			
			$points = explode(";", $p['points']);
			$start = intval($p['start']);
			foreach($points as $i => $d)
			{
				if($i > $start)
					$points[$i] = floatval($d);
				else
					unset($points[$i]);
			}
			$points = array_values($points);
			switch($p['input'])
			{
				case "TREND":
					$data = explode(";", $p['data']);
					foreach($data as $i => $d)
						$data[$i] = floatval($d);
					$error = $this->computeError($points, $data);
					break;
				case "VALUE":
					$data = floatval($p['data']);
					$error = $this->computeError(array($points[count($points) - 1]), array($data));
					break;
				case "PM":
					$answer = $points[0] < $points[count($points) - 1] ? "PLUS" : "MINUS";
					$error = $answer == $p['data'];
					break;
			}
			
			$array_user2_pred_bygroup[$id_group][$p['input']]["nbr_error"]++;
			$array_user2_pred_bygroup[$id_group][$p['input']]["error"] += $error;
		}
		
		foreach($array_user2_pred_bygroup as $id_group => $infos)
		{
			if($infos["TREND"]["nbr_error"] > 0)
			{
				$mean_error = $infos["TREND"]["error"] / floatval($infos["TREND"]["nbr_error"]);
				$output[$id_group]["benchmark"]["TREND"]["user2"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["TREND"]["error"] = $mean_error;
			}
			
			if($infos["VALUE"]["nbr_error"] > 0)
			{
				$mean_error = $infos["VALUE"]["error"] / floatval($infos["VALUE"]["nbr_error"]);
				$output[$id_group]["benchmark"]["VALUE"]["user2"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["VALUE"]["error"] = $mean_error;
			}
			if($infos["PM"]["nbr_error"] > 0)
			{
				$mean_error = $infos["PM"]["error"] / floatval($infos["PM"]["nbr_error"]);
				$output[$id_group]["benchmark"]["PM"]["user2"] = $mean_error;
				//$array_user_pred_bygroup[$id_group]["VALUE"]["error"] = $mean_error;
			}
		}
		
		/******************************************/
		/**** ALGORITHM PREDICTIONS PROCESSING ****/
		/******************************************/
		
		$array_pred_algo = $this->getAlgorithmPrediction();
		
		$array_count_algopergroup = array(); // [$id_group, $id_algo] => $nbr_pred

		$once = 5;

		foreach($array_pred_algo as $k => $v)
		{
			if(!array_key_exists($v["id_group"], $array_count_algopergroup))
				$array_count_algopergroup[$v["id_group"]] = array();
			if(!array_key_exists($v["id_algorithm"], $array_count_algopergroup[$v["id_group"]]))
				$array_count_algopergroup[$v["id_group"]][$v["id_algorithm"]] = 1;
			else
				$array_count_algopergroup[$v["id_group"]][$v["id_algorithm"]]++;
			
			$points = explode(";", $v["points"]);
			// Get points that must be predicted
			$y_true = array_slice($points, $v["start"]+1, $v["end"] - $v["start"]); // TODO Check if the point range is aligned to y_pred, should be OK
			foreach($y_true as $k => $y)
				$y_true[$k] = floatval($y);
				
			// Get prediction
			$y_pred = explode(";", $v["prediction"]);
			foreach($y_pred as $k => $y)
				$y_pred[$k] = floatval($y);
			
			if(!array_key_exists($v["id_algorithm"], $output[$v["id_group"]]["benchmark"]["TREND"]))
			{
				$output[$v["id_group"]]["benchmark"]["TREND"][$v["id_algorithm"]] = 0;
				$output[$v["id_group"]]["benchmark"]["VALUE"][$v["id_algorithm"]] = 0;
				$output[$v["id_group"]]["benchmark"]["PM"][$v["id_algorithm"]] = 0;
			}
			
			// Compute error for TREND
			if(count($y_true) != count($y_pred))
			{
				echo "Error";
				echo "<pre>";
				print_r($v);
				echo "</pre>";
				echo "<hr />";
			}
			$error = $this->computeError($y_true, $y_pred);
			//$array_pred[$k]["error"] = $error;

			$output[$v["id_group"]]["benchmark"]["TREND"][$v["id_algorithm"]] += $error;
			
			// Compute error for VALUE
			$last_y_true = $y_true[count($y_true) - 1];
			$last_y_pred = $y_pred[count($y_pred) - 1];
			$error = $this->computeError($last_y_true, $last_y_pred);
			$output[$v["id_group"]]["benchmark"]["VALUE"][$v["id_algorithm"]] += $error;
			
			// Compute error for PM
			$first_y_true = $y_true[0];
			$first_y_pred = $y_pred[0];
			
			$answer_true = $last_y_true > $first_y_true ? "PLUS" : "MINUS";
			$answer_pred = $last_y_pred > $first_y_pred ? "PLUS" : "MINUS";
			/*
			if($once == 0)
			{
				out::message("points = " . $v["points"]);
				out::message("zone_start = " . $v["start"] . " - zone_end = " . $v["end"]);
				out::message("y_true = " . implode(" ; " , $y_true));
				out::message("y_pred = " . implode(" ; " , $y_pred));
				out::message("first_y_true = " . $first_y_true);
				out::message("last_y_true = " . $last_y_true);
				out::message("first_y_pred = " . $first_y_pred);
				out::message("last_y_pred = " . $last_y_pred);
				out::message("answer_true = " . $answer_true);
				out::message("answer_pred = " . $answer_pred);
			}
			$once--;
			*/
			$output[$v["id_group"]]["benchmark"]["PM"][$v["id_algorithm"]] += ($answer_true == $answer_pred) ? 0 : 1;
		}

		foreach($array_group as $g)
		{
			foreach($array_algo as $a)
			{
				if(array_key_exists($a["id"], $output[$g["id"]]["benchmark"]["TREND"]))
				{
					$output[$g["id"]]["benchmark"]["TREND"][$a["id"]] = $output[$g["id"]]["benchmark"]["TREND"][$a["id"]] / $array_count_algopergroup[$g["id"]][$a["id"]];
					$output[$g["id"]]["benchmark"]["VALUE"][$a["id"]] = $output[$g["id"]]["benchmark"]["VALUE"][$a["id"]] / $array_count_algopergroup[$g["id"]][$a["id"]];
					$output[$g["id"]]["benchmark"]["PM"][$a["id"]] = $output[$g["id"]]["benchmark"]["PM"][$a["id"]] / $array_count_algopergroup[$g["id"]][$a["id"]];
				}
			}
			
			/******************************************/
			/****          SCORE SORTING           ****/
			/******************************************/
			
			foreach($array_input as $i)
				asort($output[$g["id"]]["benchmark"][$i]);
		}

		return $output;
	}
	
	public function getCurveBenchmark($id_group)
	{
		
		$array_curve = $this->request->fetchQuery("
				SELECT g.id as id_group, z.start, z.end, c.id as id_curve, c.points, c.name as curve_name
				FROM " . TABLE_CURVE_ZONE . " z
				LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				WHERE c.position < g.nbr_curve AND g.id='".$id_group."'
				ORDER BY c.position
				");
		
		$array_algo = $this->getAlgorithms();
		
		$output = array();
		
		
		foreach($array_curve as $c)
		{
			$array = array("id" => $c["id_curve"], "name" => $c["curve_name"],
					"benchmark" => array(
							"TREND" => array(), 
							"VALUE" => array(), 
							"PM" => array()));
			
			$output[$c["id_curve"]] = $array;
		}
		
		
		/**************************/
		/**** USER PREDICTIONS ****/
		/**************************/
		
		$user_mean_pred = $this->getUserMeanPredictions($id_group);
		
		foreach($user_mean_pred as $id_zone => $infos)
		{
			/*
			echo "<pre>";
			print_r($infos);
			echo "</pre>";
			echo "<hr />";
			*/
			//$output[$infos["id_curve"]]["benchmark"]["TREND"];
			
			if($infos["predictions"]["TREND"]["nbr_prediction"] > 0)
				$output[$infos["id_curve"]]["benchmark"]["TREND"]["user"] = $infos["predictions"]["TREND"]["error"];
			
			if($infos["predictions"]["VALUE"]["nbr_prediction"] > 0)
				$output[$infos["id_curve"]]["benchmark"]["VALUE"]["user"] = $infos["predictions"]["VALUE"]["error"];
			
			if($infos["predictions"]["PM"]["nbr_prediction"] > 0)
				$output[$infos["id_curve"]]["benchmark"]["PM"]["user"] = $infos["predictions"]["PM"]["error"];
		}
		
		/*******************************/
		/**** ALGORITHM PREDICTIONS ****/
		/*******************************/
		
		$array_pred_algo = $this->getAlgorithmPrediction($id_group);
		
		foreach($array_pred_algo as $k => $v)
		{
			$points = explode(";", $v["points"]);
			// Get points that must be predicted
			$y_true = array_slice($points, $v["start"]+1, $v["end"] - $v["start"]); // TODO Check if the point range is aligned to y_pred, should be OK
			foreach($y_true as $k => $y)
				$y_true[$k] = floatval($y);
		
			// Get prediction
			$y_pred = explode(";", $v["prediction"]);
			foreach($y_pred as $k => $y)
				$y_pred[$k] = floatval($y);
				
			// Compute error for TREND
			$error = $this->computeError($y_true, $y_pred);
			$output[$v["id_curve"]]["benchmark"]["TREND"][$v["id_algorithm"]] = $error;
				
			// Compute error for VALUE
			$last_y_true = $y_true[count($y_true) - 1];
			$last_y_pred = $y_pred[count($y_pred) - 1];
			$error = $this->computeError($last_y_true, $last_y_pred);
			$output[$v["id_curve"]]["benchmark"]["VALUE"][$v["id_algorithm"]] = $error;
				
			// Compute error for PM
			$first_y_true = $y_true[0];
			$first_y_pred = $y_pred[count($y_pred) - 1];
				
			$answer_true = $last_y_true > $first_y_true ? "PLUS" : "MINUS";
			$answer_pred = $last_y_pred > $first_y_pred ? "PLUS" : "MINUS";
				
			$output[$v["id_curve"]]["benchmark"]["PM"][$v["id_algorithm"]] = $answer_true == $answer_pred ? 0 : 1;
		}
		
		return $output;
	}

	public function getAlgorithms()
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_ALGORITHM . " ORDER BY name");
	}
	public function getAlgorithmPrediction($id_group = -1)
	{
		$where = $id_group == -1 ? "" : " AND g.id='".$id_group."'";
		return $this->request->fetchQuery("
				SELECT p.*, c.points, z.start, z.end, g.id as id_group, c.id as id_curve, a.name
				FROM " . TABLE_PREDICTION_ALGORITHM . " p
				LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
				LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				LEFT JOIN " . TABLE_ALGORITHM . " a ON p.id_algorithm = a.id
				WHERE c.position < g.nbr_curve AND p.prediction != '' $where
				");
	}
	public function getPredictionMeasure($nbr_day_before)
	{
		$array_user = $this->request->fetchQuery("
				SELECT COUNT(*) as nbr, timestamp_add
				FROM " . TABLE_PREDICTION . "
				WHERE timestamp_add > DATE_SUB(now(), INTERVAL ".$nbr_day_before." DAY)
				GROUP BY DAY(timestamp_add)
				");

		return $array_user;
	}

	public function getMonitoredCurves($id_group=-1)
	{
		if($id_group == -1)
		{
			// Fetch groups and make an id array
			$array_g = $this->getGroups();
			$array_group = array();
			foreach($array_g as $g)
			{
				$array_group[$g['id']] = $g;
				$array_group[$g['id']]['curves'] = array();
				$array_group[$g['id']]['nbr_pred_value'] = 0;
				$array_group[$g['id']]['nbr_pred_pm'] = 0;
				$array_group[$g['id']]['nbr_pred_trend'] = 0;
				$array_group[$g['id']]['nbr_pred_total'] = 0;
			}
				
			// Fetch monitored curves
			$array_curve = $this->request->fetchQuery("
					SELECT c.*, cnt1.cnt as cnt_value, cnt2.cnt as cnt_pm, cnt3.cnt as cnt_trend, g.id as id_group
					FROM " . TABLE_CURVE . " c
					LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
					RIGHT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
					LEFT JOIN (
					SELECT COUNT(*) as cnt, z.id_curve FROM
					" . TABLE_PREDICTION . " p
					LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
					WHERE p.input='VALUE'
					GROUP BY z.id_curve
			) cnt1 ON cnt1.id_curve = c.id
						
					LEFT JOIN (
					SELECT COUNT(*) as cnt, z.id_curve FROM
					" . TABLE_PREDICTION . " p
					LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
					WHERE p.input='PM'
					GROUP BY z.id_curve
			) cnt2 ON cnt2.id_curve = c.id
						
					LEFT JOIN (
					SELECT COUNT(*) as cnt, z.id_curve FROM
					" . TABLE_PREDICTION . " p
					LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
					WHERE p.input='TREND'
					GROUP BY z.id_curve
			) cnt3 ON cnt3.id_curve = c.id
					WHERE display='1'
					ORDER BY c.position
					");
				
			$processed_id = array();
			foreach($array_curve as $c)
			{
				$nbr_curve_ingroup = count($array_group[$c['id_group']]['curves']);
				//out::message("nbr_curveingroup = " . $nbr_curve_ingroup);
				if(		$nbr_curve_ingroup == $array_group[$c['id_group']]['nbr_curve']
						|| 	in_array($c['id'], $processed_id))
					continue;

				$processed_id[] = $c['id'];

				$array_group[$c['id_group']]['curves'][] = $c;
				$array_group[$c['id_group']]['nbr_pred_value'] += $c['cnt_value'];
				$array_group[$c['id_group']]['nbr_pred_pm'] += $c['cnt_pm'];
				$array_group[$c['id_group']]['nbr_pred_trend'] += $c['cnt_trend'];
				$array_group[$c['id_group']]['nbr_pred_total'] += $c['cnt_trend'] + $c['cnt_pm'] + $c['cnt_value'];
			}

				
			return $array_group;
		}
		$array_sql = $this->request->fetchQuery("
				SELECT c.*, cnt.cnt, cnt.input
				FROM " .TABLE_CURVE . " c
				LEFT JOIN " . TABLE_CATEGORY . " cat ON c.id_category = cat.id
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				LEFT JOIN (
				SELECT COUNT(*) as cnt, p.input, z.id_curve
				FROM " . TABLE_PREDICTION . " p
				LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
				GROUP BY z.id_curve, p.input) cnt ON cnt.id_curve = c.id
				WHERE g.id='" . $id_group . "' AND c.position < g.nbr_curve
				ORDER BY c.position");

		$array_cnt = array();

		foreach($array_sql as $s)
		{
			if(!array_key_exists($s['id'], $array_cnt))
				$array_cnt[$s['id']] = array('TREND' => 0, 'PM' => 0, 'VALUE' => 0);
			$array_cnt[$s['id']][$s['input']] = $s['cnt'];
		}

		$array_curve = array();

		foreach($array_sql as $s)
		{
			if(array_key_exists($s['id'], $array_curve))
				continue;
			$array_curve[$s['id']] = $s;
			$array_curve[$s['id']]['cnt_VALUE'] = $array_cnt[$s['id']]['VALUE'];
			$array_curve[$s['id']]['cnt_PM'] = $array_cnt[$s['id']]['PM'];
			$array_curve[$s['id']]['cnt_TREND'] = $array_cnt[$s['id']]['TREND'];
			$array_curve[$s['id']]['cnt_total'] = $array_cnt[$s['id']]['VALUE'] + $array_cnt[$s['id']]['PM'] + $array_cnt[$s['id']]['TREND'];
		}
		return $array_curve;
	}

	public function computeScore($prediction)
	{
		$zone = $this->getZoneById($prediction['id_zone']);

		switch($prediction['predictionInput'])
		{
			case "VALUE":
				$pred = floatval($prediction['prediction']);
				$answer = floatval($zone['']);
				/*
				 float diff = Math.abs(pred - answer);

				float gain = this.zone.value_gain;
				float loss = this.zone.value_loss;
				float allowederror = this.zone.value_allowederror;
				float maxerror = this.zone.value_maxerror;

				if(diff == 0 || diff < maxerror)
					return gain;
				else if(diff > this.zone.value_maxerror || allowederror == 0)
					return -loss;
				else
					return (-loss - gain)/allowederror * pred + gain;
				*/
			case "PM":

				// return this.prediction == this.pmGetAnswer() ? this.zone.pm_gain : this.zone.pm_loss;

			case "TREND":
				return 0;
		}

		return 0;
	}

	public function getCurveById($id)
	{
		$array_sql = $this->request->firstQuery("SELECT * FROM " . TABLE_CURVE . " WHERE id='" .$id . "'");
		$array_sql['points'] = $this->getPoints(explode(";", $array_sql['points']));
		return $array_sql;
	}

	private function getPoints($ys)
	{
		$full_point = array();
		$x = 0;
		foreach($ys as $y)
			$full_point[] = array($x++, floatval($y));
		return $full_point;
	}

	public function getZoneById($id)
	{
		return $this->request->firstQuery("SELECT * FROM " . TABLE_CURVE_ZONE . " WHERE id='" . $id . "'");
	}
	public function getZonesByIdCurve($id_curve)
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_CURVE_ZONE . " WHERE id_curve='" . $id_curve . "' ORDER BY start ASC");
	}

	public function getNbrCurvesByCategory($id_category)
	{
		return $this->request->count(TABLE_CURVE, "id_category='". $id_category."'");
	}
	
	public function getCurves()
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_CURVE);
	}
	public function getCurvesByCategory($id_category, $limit = null, $nbr = null)
	{
		if($limit == null && $nbr == null)
			return $this->request->fetchQuery("SELECT * FROM " . TABLE_CURVE . " WHERE id_category='" . $id_category . "' ORDER BY position");
		else
			return $this->request->fetchQuery("SELECT * FROM " . TABLE_CURVE . " WHERE id_category='" . $id_category . "' ORDER BY position LIMIT " .$limit.",".$nbr);
	}

	// From http://developerinstincts.blogspot.be/2010/09/random-float-in-php.html
	public function random_float ($min,$max) {
		return ($min+lcg_value()*(abs($max-$min)));
	}

	public function generatePoints($f, $x_start, $x_end, $x_step, $noise)
	{
		$data = array();

		for($x = $x_start ; $x <= $x_end ; $x += $x_step)
		{
			$y = -1;
			eval('$y = '.$f.';');

			// Add noise
			$y += $this->random_float(0, $noise);
			$y = round($y, 3);

			$data[] = array($x, $y);
		}
		return $data;
	}

	public function getTrendPredictionsByIdZone($id_zone)
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_PREDICTION . " WHERE id_zone='" . $id_zone . "' AND input='TREND'");
	}

	public function getPredictionsById($id_curve)
	{
		$info = $this->getCurveById($id_curve);
		$points = $info['points'];
		$all = $this->request->fetchQuery("	SELECT p.*, z.start, z.end
				FROM " . TABLE_PREDICTION . " p
				LEFT JOIN " . TABLE_CURVE_ZONE . " z
				ON z.id = p.id_zone
				WHERE z.id_curve='" . $id_curve . "'");

		$empty_pred = array("VALUE" => array(
				"nbr_prediction" => 0,
				"predictions" => array(),

				"mean_prediction" => 0,
				"variance_prediction" => 0,

				"confidences" => array(),
				"mean_confidence" => 0,
				"variance_confidence" => 0
		),
				"PM" => array(
						"nbr_prediction" => 0,
						"nbr_prediction_p" => 0,
						"nbr_prediction_m" => 0,
						"percentage_p" => 0,
						"percentage_m" => 0,
						"predictions" => array(),
							
						"confidences" => array(),
						"mean_confidence" => 0,
						"variance_confidence" => 0),
				"TREND" => array(
						"nbr_prediction" => 0,
						"predictions" => array(),
						"confidences" => array(),
						"mean_prediction" => "",
						"mean_confidence" => 0,
						"variance_confidence" => 0)
		);

		$pm_alias = array("PLUS" => "p", "MINUS" => "m");

		$array = array();
		$array_zone = array();

		// For each zone, there is no prediction
		$zones 	= $this->getZonesByIdCurve($id_curve);
		foreach($zones as $z)
		{
			$array[$z['id']] = $empty_pred;
			$array_zone[$z['id']] = $z;
		}

		// Store predictions
		foreach($all as $k => $a)
		{
			$array_zone[$a["id_zone"]] = array("start" => $a['start'], "end" => $a['end']);
			 
			$array[$a["id_zone"]][$a["input"]]["nbr_prediction"]++;
			$array[$a["id_zone"]][$a["input"]]["confidences"][] = intval($a['confidence']);
				
			if($a["input"] == "VALUE")
				$array[$a["id_zone"]][$a["input"]]["predictions"][] = floatval($a['data']);
			else if($a["input"] == "PM")
				$array[$a["id_zone"]][$a["input"]]["nbr_prediction_".$pm_alias[$a['data']]]++;
			else
				$array[$a["id_zone"]][$a["input"]]["predictions"][] = $a['data'];
		}
		// Process various numbers
		$inputs = array("VALUE", "PM", "TREND");

		foreach($array as $z => $a)
		{
			foreach($inputs as $in)
			{
				$array[$z][$in]["mean_confidence"]      = Stat::mean($a[$in]["confidences"]);
				$array[$z][$in]["variance_confidence"]  = Stat::variance($a[$in]["confidences"], $a[$in]["mean_confidence"]);
			}
				
			// VALUE processing
			$array[$z]["VALUE"]["mean_prediction"] = Stat::mean($a["VALUE"]["predictions"]);
			$array[$z]["VALUE"]["variance_prediction"] = Stat::variance($a["VALUE"]["predictions"], $array[$z]["VALUE"]["mean_prediction"]);
				
			// PM Processing
			if($array[$z]["PM"]["nbr_prediction"] > 0)
			{
				$array[$z]["PM"]["percentage_p"] = round(100*$array[$z]["PM"]["nbr_prediction_p"] / $array[$z]["PM"]["nbr_prediction"], 2);
				$array[$z]["PM"]["percentage_m"] = round(100*$array[$z]["PM"]["nbr_prediction_m"] / $array[$z]["PM"]["nbr_prediction"], 2);
			}
			// Trend Processing
			$mean_trend = array();
			$nbr_prediction = $array[$z]["TREND"]["nbr_prediction"];
				
			if($nbr_prediction > 0)
			{
				$nbr_point = count(explode(";", $array[$z]["TREND"]["predictions"][0]));

				for($i = 0 ; $i < $nbr_point ; $i++)
					$mean_trend[] = 0 ;

				foreach($array[$z]["TREND"]["predictions"] as $p)
				{
					$p = explode(";", $p);
					for($i = 0 ; $i < $nbr_point ; $i++)
					{
						$mean_trend[$i] += floatval($p[$i]);
					}
				}

				for($i = 0 ; $i < $nbr_point ; $i++)
					$mean_trend[$i] = $mean_trend[$i] / $nbr_prediction;
			}
			$mean_trend = implode(";", $mean_trend);
				
			$array[$z]["TREND"]["mean_prediction"] = $mean_trend;
		}

		return $array;
	}

	public function getGroups()
	{
		$array_sql = $this->request->fetchQuery("	SELECT c.name as category_name, cnt_curve.nbr_curve, cnt_user.nbr_user, cnt_user.predictionInput, g.*
				FROM " . TABLE_CURVE_GROUP . " g
				LEFT JOIN " . TABLE_CATEGORY . " c
				ON c.id = g.id_category
				LEFT JOIN (SELECT id_category, COUNT(*) as nbr_curve
				FROM " . TABLE_CURVE . " GROUP BY id_category) cnt_curve
				ON cnt_curve.id_category = g.id_category
				LEFT JOIN (SELECT COUNT(*) as nbr_user, id_group, predictionInput
				FROM " . TABLE_USER_GROUP . " GROUP BY id_group, predictionInput) cnt_user
				ON cnt_user.id_group = g.id
				ORDER BY g.position");

		$array_group = array();

		foreach($array_sql as $s)
		{
			if(!array_key_exists($s['id'], $array_group))
			{
				$array_group[$s['id']] = $s;
				$array_group[$s['id']]['nbr_user_value'] = 0;
				$array_group[$s['id']]['nbr_user_pm'] = 0;
				$array_group[$s['id']]['nbr_user_trend'] = 0;
				$array_group[$s['id']]['nbr_user'] = 0;
			}
				
				
			// If there is no user in a group, skip the next computations
			if($s['predictionInput'] == "")
				continue;
			$array_group[$s['id']]['nbr_user_'.strtolower($s['predictionInput'])] += $s['nbr_user'];
			$array_group[$s['id']]['nbr_user'] += $s['nbr_user'];
		}


		return $array_group;
	}

	public function getGroupById($id)
	{
		return $this->request->firstQuery("
				SELECT *
				FROM " . TABLE_CURVE_GROUP . "
				WHERE id='" . $id . "'");
	}


	public function getGroupByPosition($position)
	{
		return $this->request->firstQuery("
				SELECT *
				FROM " . TABLE_CURVE_GROUP . "
				WHERE position='" . $position . "'");
	}

	public function getGroupByUser($id_user)
	{
		$display = false;

		$array_sql = $this->request->fetchQuery("
				SELECT g.*, ug.predictionInput, ug.nbr_curve_predicted
				FROM " . TABLE_USER_GROUP . " ug
				LEFT JOIN " . TABLE_CURVE_GROUP . " g
				ON g.id = ug.id_group
				WHERE ug.id_user='" . $id_user . "'");

		$array_group = array("TREND" => null, "VALUE" => null, "PM" => null);
		$ids_cat = array();
		$array_conj = array();

		$nbr_null = count($array_group);
		foreach($array_sql as $s)
		{
			$array_group[$s["predictionInput"]] = $s;
		}

		// Filling the gap(s)
		if($nbr_null > 0)
		{
			$info_firstGroup = $this->request->firstQuery("SELECT * FROM " . TABLE_CURVE_GROUP . " WHERE position='0'");
				
			foreach($array_group as $k => $s)
			{
				if(!is_null($s))
					continue;
				$this->request->insert(TABLE_USER_GROUP, array('predictionInput' => $k, 'id_user' => $id_user, 'id_group' => $info_firstGroup['id']));
				$array_group[$k] = $info_firstGroup;
				$array_group[$k]['predictionInput'] = $k;
				$array_group[$k]['nbr_curve_predicted'] = 0;
			}
		}

		if($display)
		{
			echo "<pre>";
			print_r($array_group);
			echo "</pre>";
		}

		return $array_group;
	}

	public function getGroupCurvesByIdUser($id_group, $id_user, $predictionInput, $limit)
	{
		//echo "byIdUser : limit = " . $limit . "<br />";
		$array_sql = $this->request->fetchQuery("
				SELECT c.*, g.id as id_group, z.nbr_unpredicted
				FROM " . TABLE_CURVE . " c
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				LEFT OUTER JOIN (
				SELECT COUNT(*) as nbr_unpredicted, id_curve
				FROM " . TABLE_CURVE_ZONE . "
				WHERE id NOT IN (
				SELECT id_zone
				FROM " . TABLE_PREDICTION . "
				WHERE id_user='" . $id_user . "' AND input='" . $predictionInput . "' AND training='0')
				GROUP BY id_curve) z on z.id_curve = c.id
				WHERE
				g.id='" . $id_group . "'
				AND		z.nbr_unpredicted > 0
				LIMIT ".$limit."
				");

		$array_return = array();
		$array_return['nbr_curve'] = count($array_sql);
		$array_return['curves'] = $array_sql;
		return $array_return;
	}

	public function getGroupCurvesByPositionUser($position, $id_user, $predictionInput, $limit)
	{
		$where = $predictionInput == "PM" ? "AND c.display_pm = 1" : "";
		//Log::add("Asked position: " . $position);
		//echo "byPosition : limit = " . $limit . "<br />";
		$array_sql = $this->request->fetchQuery("
				SELECT c.*, g.id as id_group
				FROM " . TABLE_CURVE . " c
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN " . TABLE_CURVE_GROUP . " g ON g.id_category = cat.id
				LEFT JOIN (
				SELECT COUNT(*) as nbr_unpredicted, id_curve
				FROM " . TABLE_CURVE_ZONE . "
				WHERE id NOT IN (
				SELECT id_zone
				FROM " . TABLE_PREDICTION . "
				WHERE id_user='" . $id_user . "' AND input='".$predictionInput."' AND training='0')
				GROUP BY id_curve) z on z.id_curve = c.id
				WHERE
				g.position='" . $position . "'
				AND z.nbr_unpredicted > 0 AND c.display='1' " . $where . "
				ORDER BY c.position
				LIMIT " . $limit . "
				");

		$array_return = array();
		$array_return['nbr_curve'] = count($array_sql);
		$array_return['curves'] = $array_sql;
		$array_return['id_group'] = $array_return['nbr_curve'] > 0 ? $array_sql[0]['id_group'] : false;
		return $array_return;
	}

	public function getPartialCurves()
	{
		return $this->request->fetchQuery("
				SELECT c.*, cat.name as category_name, cnt.cnt as nbr_prediction
				FROM " . TABLE_CURVE . " c
				LEFT JOIN " . TABLE_CATEGORY . " cat ON cat.id = c.id_category
				LEFT JOIN (
				SELECT z.id_curve, COUNT(*) as cnt
				FROM " . TABLE_PREDICTION . "  p
				LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
				GROUP BY z.id_curve
		) cnt ON cnt.id_curve = c.id
				WHERE c.priority IS NOT NULL
				ORDER BY c.priority");
	}

	public function getPrioritizedCurvesByUser($id_user, $max, $predictionInput)
	{
		return $this->request->fetchQuery("
				SELECT c.*
				FROM " . TABLE_CURVE . " c
				INNER JOIN " . TABLE_CURVE_ZONE . " z ON c.hidden_id_zone = z.id
				LEFT OUTER JOIN (
				SELECT COUNT(*) as cnt, id_zone
				FROM " . TABLE_PREDICTION . "
				WHERE id_user='" . $id_user . "' AND input='" . $predictionInput . "' AND training='0'
				GROUP BY id_zone) cnt ON cnt.id_zone = z.id
				WHERE cnt.cnt IS NULL LIMIT " . $max
		);
	}

	public function getRandomCurves($id_user, $predictionInput, $training, $limit=10)
	{
		$where = $predictionInput == "PM" ? "AND c.display_pm = 1" : "";
		$training = $training ? "1" : "0";
		$array = $this->request->fetchQuery("
				SELECT c.*
				FROM " . TABLE_CURVE . " c
				LEFT JOIN (
				SELECT COUNT(*) as nbr_unpredicted, id_curve
				FROM " . TABLE_CURVE_ZONE . "
				WHERE id NOT IN (
				SELECT id_zone
				FROM " . TABLE_PREDICTION . "
				WHERE id_user='" . $id_user . "' AND input='".$predictionInput."' AND training='".$training."')
				GROUP BY id_curve) z on z.id_curve = c.id
				WHERE z.nbr_unpredicted > 0 " . $where . "
				ORDER BY position
				LIMIT " . $limit . "
				");
		shuffle($array);
		return $array;
	}
}
