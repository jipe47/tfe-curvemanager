<?php
class CurveAjax extends AjaxPage
{
	public function construct()
	{
		$this->setAccessName("Curve");
		$this->registerHandler("android", "handler_android");
		$this->registerHandler("admin", "handler_admin");
	}
	
	
	public function handler_admin()
	{
		$query = $this->arg->string(0);
		error_reporting(E_ALL);
		switch($query)
		{
			case "monitor":
				$this->setTemplate($this->path_html."ajax/curve_monitor.html");
				$id_group = Post::int("id_group");
				$array_curve = $this->model->getMonitoredCurves($id_group);
				$this->assign("array_curve", $array_curve);
				break;
		}
		
		return $this->renderTemplate();
	}
	
	public function handler_android()
	{
		$query = $this->arg->string(0);
		error_reporting(E_ALL);
		switch($query)
		{
			case "getCurve":
				$id_user 			= $this->arg->int(1);
				$predictionInput 	= $this->arg->string(2); // TREND, PM or VALUE
				$training 			= $this->arg->string(3) == "training"; // else == "normal"
				$predictionType 	= $this->arg->string(4); // PROGRESSIVE or ATTHEEND
				$gameMode 			= $this->arg->string(5); // AGAINSTCLOCK or NORMAL
				$random				= $this->arg->string(6, "level") == "random";
				$level 				= $this->arg->int(7, 0);
				
				if($random || $gameMode == "AGAINSTCLOCK")
				{
					$nbr_random = $random ? 10 : 150;
					$array_curve = $this->model->getRandomCurves($id_user, $predictionInput, $training, $nbr_random);
					$finish = count($array_curve) == 0;
					$position = -1;
				}
				else
				{
					/*
					 * First, get the unpredicted curves from the current (or next) group in the user's progression
					 */
					$groups 				= $this->model->getGroupByUser($id_user);
					$position 				= $groups[$predictionInput]['position'];
					
					if($position != $level)
					{
						$info_group 			= $this->model->getGroupByPosition($level);
						$id_group 				= $info_group['id'];
						$nbr_curve_predicted 	= 0;
						$nbr_curve 				= $info_group['nbr_curve'];
						$nbr_curve_tofetch 		= $nbr_curve;
					}
					else
					{					
						$id_group 				= $groups[$predictionInput]['id'];
						$nbr_curve_predicted 	= $groups[$predictionInput]['nbr_curve_predicted'];
						$nbr_curve				= $groups[$predictionInput]['nbr_curve'];
						
						$nbr_curve_tofetch		= $gameMode == "AGAINSTCLOCK" || $groups[$predictionInput]['nbr_curve_predicted'] == $groups[$predictionInput]['nbr_curve']
													? $groups[$predictionInput]['nbr_curve']
													: $groups[$predictionInput]['nbr_curve'] - $nbr_curve_predicted;
					}												
					
					$finish = false;
					
					
					$array_curve 	= $this->model->getGroupCurvesByPositionUser($level, $id_user, $predictionInput, $nbr_curve_tofetch);
					
					if($array_curve['nbr_curve'] == 0)
						$finish = true;
					$array_curve = $array_curve['curves'];				
				}

				/*
				 * Second, find the first prioritary curves the user did not predicted
				 */
				$max_prior = max(ceil(0.1 * count($array_curve)), 1);
				$array_prior = $this->model->getPrioritizedCurvesByUser($id_user, $max_prior, $predictionInput);
				
				foreach($array_prior as $k => $p)
				{
					// TODO Decide if use ys if available or not
					if($p['hidden_ys'] != "")
						$array_prior[$k]['points'] .= ';'.$p['hidden_ys'];
					else
						$array_prior[$k]['points'] .= ';'.$p['hidden_prediction'];
				}
				
				/*
				 * Third, determine if there is at least one curve to predict
				 */
				$finish = $finish && count($array_prior) == 0;
				
				if($finish)
					return "FINISH";
				/*
				 * Finally, fetch the zones and return the json serialization
				 */
				// TODO array_slice sur $array_curve pour arriver au total à $nbr_curve courbes
				$array_curve = array_merge($array_curve, $array_prior);
				
				// Fetch curves' id
				$array_ids = array();
				foreach($array_curve as $s)
					$array_ids[] = $s['id'];
				
				// Fetch zones
				$array_zone = array();
				
				$sql_zone = $this->request->fetchQuery("SELECT z.* 
				FROM " . TABLE_CURVE_ZONE . " z
				LEFT JOIN " . TABLE_CURVE . " c ON c.id = z.id_curve
				WHERE z.id_curve IN (".implode(",", $array_ids).")
				ORDER BY c.position DESC, z.start");
				foreach($sql_zone as $z)
					$array_zone[$z['id_curve']][] = $z;
			
				// Build the json array
				$zone_floats = array("value_loss", "value_maxerror", "value_allowederror", "value_gain", "pm_gain", "pm_loss", "pm_equalheight", "trend_maxerror", "trend_gain", "trend_loss", "trend_allowederror");
				$array_json = array();
				foreach($array_curve as $a)
				{
					$zones = array();
					if(array_key_exists($a['id'], $array_zone))
						foreach($array_zone[$a['id']] as $z)
						{
							$zz = array("start" => $z['start'], "end" => $z['end'], "id" => $z['id'], "endzone" => $z['end'] == "");
							foreach($zone_floats as $zf)
								$zz[$zf] = floatval($z[$zf]);
							$zones[] = $zz;
						}
							
						$array_json[] = array(
								'id' => $a['id'],
								'name' => $a['name'],
								'description' => $a['description'],
								'id_category' => $a['id_category'],
								'points' => $a['points'],
								"zones" => $zones);
				}
				
				$nbr_group_after = $this->request->count(TABLE_CURVE_GROUP, "position > " . $level);
				
				$array_json = array('curves' => $array_json, "nbr_group_after" => $nbr_group_after, 'groupPosition' => $position);
				return json_encode($array_json);
				
			case "sendPrediction":
				$json = json_decode(Post::string("data"));
                $indexes = array("confidence", "id_zone", "predictionType", "predictionInput", "training", "prediction", "id_user", "score", "level", "id_serie", "gameMode", "hasEnteredConfidence");
                
                $data = array();
                foreach($indexes as $k => $i)
                    $data[$i] = $json[$k];
                
                // Process and secure prediction data
                $confidence 		= intval($data["confidence"]);
                $id_zone 			= intval($data["id_zone"]);
                $predictionType 	= $data["predictionType"];
                $predictionInput 	= $data["predictionInput"];
                $training 			= $data["training"] == "true";
                $prediction 		= $data["prediction"];
				$score 				= $training ? 0 
												: floatval($data["score"]);
				$id_user 			= intval($data["id_user"]);
				$level				= intval($data["level"]);
				$id_serie			= $data["id_serie"];
				$gameMode			= $data["gameMode"];
				$hasEnteredConfidence = $data["hasEnteredConfidence"] == "true";
                
				// Check if the input data type are coherent
                if(!in_array($predictionType, array("ATTHEEND", "PROGRESSIVE")))
                    return;
                if(!in_array($predictionInput, array("VALUE", "PM", "TREND")))
                    return;
                  
                // TODO Handle time_spent
                $array_sql = array(	"id_user" => $id_user, 
									"training" => ($training ? "1" : "0"), 
									"confidence" => $confidence, 
									"id_zone" => $id_zone, 
									"type" => $predictionType, 
									"input" => $predictionInput, 
									"data" => $prediction, 
									"time_spent" => -1,
									"id_serie" => $id_serie,
									"gamemode" => $gameMode,
									"hasEnteredConfidence" => $hasEnteredConfidence);
                $this->request->insert(TABLE_PREDICTION, $array_sql);
				
				// TODO Instead of the following instructions, compute the prediction score and update the user's score
				if($score > 0)
					$this->request->query("UPDATE " . TABLE_USER_ANDROID . " SET score = score + " . $score . " WHERE id='" . $id_user . "'");
					
				if(!$training && $gameMode != "AGAINSTCLOCK")
				{
					$info_group = $this->model->getGroupByUser($id_user); 
					
					if($info_group[$predictionInput]["position"] == $level)
					{
						$inc = true;
						$nbr_curve_predicted = $info_group[$predictionInput]["nbr_curve_predicted"] + 1;
						
						// Check if the user completed a level. If yes, position++
						if($nbr_curve_predicted == $info_group[$predictionInput]['nbr_curve'])
						{
							$newposition = $info_group[$predictionInput]['position'] + 1;
							
							$info = $this->model->getGroupByPosition($newposition);
							
							if(!empty($info))
							{
								$this->request->update(TABLE_USER_GROUP, "id_user='" .$id_user."' AND predictionInput='".$predictionInput."'", array('id_group' => $info['id'], 'nbr_curve_predicted' => 0));
								$inc = false;
							}
							else
								echo "No next group.";
						}
						else
						{
							echo "Not complete : " . $info_group[$predictionInput]['nbr_curve_predicted'] . " != " . $info_group[$predictionInput]['nbr_curve'];
						}
						
						if($inc && $info_group[$predictionInput]["nbr_curve_predicted"] != $info_group[$predictionInput]['nbr_curve'])
							$this->request->update(TABLE_USER_GROUP, "id_user='" .$id_user."' AND predictionInput='".$predictionInput."'", array('nbr_curve_predicted' => $nbr_curve_predicted));
					}
                }
                
                
				return "1";
		}
	}
}
