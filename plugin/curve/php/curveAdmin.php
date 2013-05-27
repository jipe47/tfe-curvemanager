<?php
class CurveAdmin extends AdminPage{
	public function construct()
	{
		$this->setAccessName("Curve");
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		$f = $query;
		switch($query)
		{
			case "add":
			case "edit":
				$info = array(
				"id" => -1,
				"name" => "",
				"tag" => "",
				"id_category" => -1,
				"priority" => false,
				"priority_threshold" => -1,
				"display" => true,
				"description" => "",
				"x_start" => "",
				"x_end" => "",
				"x_step" => "",
				"x_scale" => 1,
				"f" => "",
				"noise" => 0
				);
				
				$title = "New";
				$submit = "Add";
				
				if($query == "add")
				{
					if($this->arg->keyExists(1))
						$info["id_category"] = $this->arg->int(1);
				}
				else if($query == "edit")
				{
					$id = $this->arg->int(1);
					$info = $this->model->getCurveById($id);
					$title = "Editing";
					$submit = "Edit";
				}
				
				$this->assign("info", $info);
				$this->assign("submit", $submit);
				$this->assign("title", $title);
				
				$all = Plugins::getPlugin("Category")->getDefaultModel()->getCategories();
				$array_cat = $this->traverse($all);
				$this->tpl->assign("array_cat", $array_cat);
				
				$f = "addedit";
				JPHP::addOnloadFunction("initTabs()");
				break;
				
			case "list":
				$id_cat = $this->arg->int(1);
				$page = $this->arg->int(2, 1);
				
				$back = "Admin/Curve/list/".$id_cat;
				
				$total_curve = $this->model->getNbrCurvesByCategory($id_cat);
				$nbr_page = ceil($total_curve / $this->plugin->nbr_curve_per_page);
				$limit = ($page - 1) * $this->plugin->nbr_curve_per_page;
				
				$this->assign("array_curve", $this->model->getCurvesByCategory($id_cat, $limit, $this->plugin->nbr_curve_per_page));
				$this->assignArray(array("nbr_page" => $nbr_page, "page" => $page, "back" => $back));
				$this->assign("id_cat", $id_cat);
				
				// Get categories used in a group
				$array_group = $this->model->getGroups();
				$array_cat_ids = array();
				
				foreach($array_group as $g)
					$array_cat_ids[] = $g['id_category'];
				$this->assign("array_cat_ids", $array_cat_ids);
				break;
				
			case "view":
				$id 	= $this->arg->int(1);
				$info 	= $this->model->getCurveById($id);
				$zones 	= $this->model->getZonesByIdCurve($id);
				
				if(count($zones) > 0)
				{
					$array_zone = array();
					foreach($zones as $z)
						$array_zone[] = $z['start'].";".$z['end'];
					$zones_to_predict = implode(",", $array_zone);
				}
				else
					$zones_to_predict = "";
				
				$predictions = $this->model->getPredictionsById($id);

				$this->assign("info", $info);
				$this->assign("predictions", $predictions);
				$this->assign("array_zone", $zones);
				$this->assign("zones_to_predict", $zones_to_predict);
				
				// Retreive curve's specs
				$points = $info['points'];
				
				$minX = $points[0][0];
				$maxX = $points[count($points) - 1][0];
				if($info['hidden_id_zone'] != "")
					$maxX = intval($info['hidden_x_end']);
				$step = $points[1][0] - $points[0][0];
				
				$ys = array();
				foreach($points as $p)
					$ys[] = $p[0];
				$yMin = min($ys);
				$yMax = max($ys);
				$this->assignArray(array("minX" => $minX, "maxX" => $maxX, "step" => $step, "minY" => $yMin, "maxY" => $yMax, "curveHeight" => ($yMax - $yMin)));
				
				// Compute the mean trend for the hidden zone, if it exists
				$meanHiddenTrend = "";
				
				if($info['hidden_id_zone'] != "")
				{
					$meanHiddenTrend = $predictions[$info['hidden_id_zone']]["TREND"]["mean_prediction"];
				}
				$this->assign("meanHiddenTrend", $meanHiddenTrend);
				
				JPHP::addOnloadFunction("initTabs()");
				
				// For the option tab
				$all = Plugins::getPlugin("Category")->getDefaultModel()->getCategories();
				$array_cat = $this->traverse($all);
				$this->tpl->assign("array_cat", $array_cat);
				
				$this->assign("array_algorithm", $this->model->getAlgorithms());
				break;
		
			case "delete":
				$id = intval($arg[1]);
		
				$info = $this->model->getCurveById($id);
				$this->tpl->assign("info", $info);
				break;
				
				
			case "group":
				$array_group = $this->model->getGroups();
				$array_cat_ids = array();
				
				$all = Plugins::getPlugin("Category")->getDefaultModel()->getCategories();
				$array_cat = $this->traverse($all);
				
				
				$this->assign("array_group", $array_group);
				$this->assign("array_cat", $array_cat);
				
				$total_curve = 0;
				foreach($array_group as $g)
				{
					$total_curve += $g['nbr_curve'];
					$array_cat_ids[] = $g['id_category'];
				}
				$this->assign("total_curve", $total_curve);
				$this->assign("array_cat_ids", $array_cat_ids);
				break;
			case "editgroup":
				$this->assign("info", $this->model->getGroupById($this->arg->int(1)));
				break;
				
			case "monitor":
				$this->assign("array_monitored", $this->model->getMonitoredCurves());
				break;
				
			case "partial":
				$this->assign("array_curve", $this->model->getPartialCurves());
				break;
				
			case "makepartial":
				$id_curve = $this->arg->int(1);
				$this->assign("info", $this->model->getCurveById($id_curve));
				break;
				
			case "benchmark":
				$array_algorithm = $this->model->getAlgorithms();
				$array_algorithm[] = array("id" => "user", "name" => "Utilisateurs (agr)");
				$array_algorithm[] = array("id" => "user2", "name" => "Utilisateurs (ind)");
				
				$array_algorithm_namebyid = array();
				foreach($array_algorithm as $a)
					$array_algorithm_namebyid[$a['id']] = $a['name'];
				$this->assign("array_algorithm_namebyid", $array_algorithm_namebyid);
				
				$array_input = array("TREND", "VALUE", "PM");
				$this->assign("array_algorithm", $array_algorithm);
				$this->assign("array_input", $array_input);
				
				$array_group = $this->model->getGroupsBenchmark();
				$this->assign("array_group", $array_group);
				
				$id_group = $this->arg->int(1);
				$this->assign("id_group", $id_group);
				if($id_group != -1)
					$this->assign("array_curve", $this->model->getCurveBenchmark($id_group));
					
				// Find best algorithms for each category
				$best_algorithms = array();
				$best_algorithms_only = array();
				$best_algorithms_exp = array();
				
				$array_cat_name = array("DEMOGRAPHIC", "FINANCE", "FINANCE - noisy", "INDUSTRY", "INDUSTRY - noisy", "MACRO", "MICRO");
				$array_cat_parent = array("Month", "Year", "Quart");
				
				foreach($array_input as $input)
				{
					$best_algorithms[$input] = array();
					foreach($array_cat_parent as $cat_parent_name)
					{
						$best_algorithms[$input][$cat_parent_name] = array();
						$best_algorithms_only[$input][$cat_parent_name] = array();
						$best_algorithms_exp[$input][$cat_parent_name] = array();
						
							foreach($array_cat_name as $cat_name)
							{
								$best_algorithms[$input][$cat_parent_name][$cat_name] = array('first' => -1, 'second' => -1);
								$best_algorithms_only[$input][$cat_parent_name][$cat_name] = -1;
								$best_algorithms_exp[$input][$cat_parent_name][$cat_name] = -1;
							}
					}
				}
				
				
				foreach($array_group as $g)
				{
					foreach($array_input as $input)
					{
						$parentcategory_name = trim($g['parentcategory_name']);
						$category_name = trim($g['category_name']);
						
						$keys = array_keys($g['benchmark'][$input]);
						$keys[-1] = -1;
						$values = array_values($g['benchmark'][$input]);
						
						// Get first algo only
						
						$best_algorithms_only[$input][$parentcategory_name][$category_name] = -1;
						$best_algorithms_exp[$input][$parentcategory_name][$category_name] = -1;
						
						if(count($values) > 0)
						{
							$values_copy = array_values($g['benchmark'][$input]);
							$id_min = -1;
							
							//out::message("Looking in " . implode(",", $values_copy));
							//out::message("-> " . $keys[$id_min]);
							while(($id_min == -1 || $keys[$id_min] == "user" || $keys[$id_min] == "user2" || strstr($array_algorithm_namebyid[$keys[$id_min]], "*") !== false) 
							&& count($values_copy) > 0)
							{
								if($id_min != -1)
								{
									//out::message("Skipping " . $id_min . " - ".$keys[$id_min] . " - " . $array_algorithm_namebyid[$keys[$id_min]]);
									unset($values_copy[$id_min]);
								}
								$id_min = array_search(min($values_copy), $values_copy);
							}
							//out::message("BEST = " . $keys[$id_min] . " - " . $array_algorithm_namebyid[$keys[$id_min]]);
							$best_algorithms_only[$input][$parentcategory_name][$category_name] = $keys[$id_min];
						}
						
						// Get first algo only + exp
												
						if(count($values) > 0)
						{
							$values_copy = array_values($g['benchmark'][$input]);
							$id_min = -1;
							
							//out::message("Looking in " . implode(",", $values_copy));
							//out::message("-> " . $keys[$id_min]);
							while(($id_min == -1 || $keys[$id_min] == "user" || $keys[$id_min] == "user2" || strstr($array_algorithm_namebyid[$keys[$id_min]], "*") === false) 
							&& count($values_copy) > 0)
							{
								if($id_min != -1)
								{
									//out::message("Skipping " . $id_min . " - ".$keys[$id_min] . " - " . $array_algorithm_namebyid[$keys[$id_min]]);
									unset($values_copy[$id_min]);
								}
								$id_min = array_search(min($values_copy), $values_copy);
							}
							//out::message("BEST = " . $keys[$id_min] . " - " . $array_algorithm_namebyid[$keys[$id_min]]);
							$best_algorithms_exp[$input][$parentcategory_name][$category_name] = $keys[$id_min];
						}
						
						// First the best algorithm	
						$index_min = count($values) > 0 ? array_search(min($values), $values) : -1;
						$best_algorithms[$input][$parentcategory_name][$category_name]['first'] = $keys[$index_min];
						
						// First the second best algorithm
						unset($values[$index_min]);
						$index_min = count($values) > 0 ? array_search(min($values), $values) : -1;
						$best_algorithms[$input][$parentcategory_name][$category_name]['second'] = $keys[$index_min];
					}
				}
				
				// Find best algorithms for each row and columns
				
				$best_algorithms_percat = array();
				$best_algorithms_perparentcat = array();
				$best_algorithms_percat_only = array();
				$best_algorithms_perparentcat_only = array();
				$best_algorithms_percat_exp = array();
				$best_algorithms_perparentcat_exp = array();
				
				foreach($array_input as $input)
				{
					$best_algorithms_percat[$input] = array();
					
					foreach($array_cat_name as $cat)
					{
						// Algo only
						$firsts = array();
						foreach($best_algorithms_only[$input] as $parentcat => $id_algo)
							if($id_algo[$cat] != -1)
								$firsts[] = $id_algo[$cat];
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_percat_only[$input][$cat] = $id_best;
						
						
						// Algo only + exp
						$firsts = array();
						foreach($best_algorithms_exp[$input] as $parentcat => $id_algo)
							if($id_algo[$cat] != -1)
								$firsts[] = $id_algo[$cat];
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_percat_exp[$input][$cat] = $id_best;
					
						// All algos
						$firsts = array();
						foreach($best_algorithms[$input] as $parentcat => $id_algo)
							if($id_algo[$cat]['first'] != -1)
								$firsts[] = $id_algo[$cat]['first'];
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_percat[$input][$cat] = $id_best;
					}
					
					foreach($array_cat_parent as $parentcat)
					{
						// Algo only
						$firsts = array();
						foreach($best_algorithms_only[$input][$parentcat] as $cat => $id_algo)
							if($id_algo != -1)
								$firsts[] = $id_algo;
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_perparentcat_only[$input][$parentcat] = $id_best;
						
						// Algo only + exp
						$firsts = array();
						foreach($best_algorithms_exp[$input][$parentcat] as $cat => $id_algo)
							if($id_algo != -1)
								$firsts[] = $id_algo;
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_perparentcat_exp[$input][$parentcat] = $id_best;
						
						
						// All algos
						$firsts = array();
						foreach($best_algorithms[$input][$parentcat] as $cat => $id_algo)
							if($id_algo['first'] != -1)
								$firsts[] = $id_algo['first'];
						$count = array_count_values($firsts);
						$id_best = array_search(max(array_values($count)), $count);
						$best_algorithms_perparentcat[$input][$parentcat] = $id_best;
					}
				}
				
				
				$this->assign("array_cat", $array_cat_name);
				$this->assign("array_cat_parent", $array_cat_parent);
				$this->assign("array_best_algorithms", $best_algorithms);
				$this->assign("array_best_algorithms_percat", $best_algorithms_percat);
				$this->assign("array_best_algorithms_perparentcat", $best_algorithms_perparentcat);
				
				// Making latex arrays

				$array_latex1 = array();
				$array_latex2 = array();
				$array_latex3 = array();
				foreach($array_input as $input)
				{
					$array_latex1[$input] = array();
					$array_latex2[$input] = array();
					$array_latex3[$input] = array();
					
					$array_latex1[$input][] = array_merge(array_merge(array(""), $array_cat_parent), array("Meilleurs algorithmes"));
					$array_latex2[$input][] = array_merge(array_merge(array(""), $array_cat_parent), array("Meilleurs algorithmes"));
					$array_latex3[$input][] = array_merge(array_merge(array(""), $array_cat_parent), array("Meilleurs algorithmes"));
					
					foreach($array_cat_name as $c)
					{
						
						$a1 = array();
						$a2 = array();
						$a3 = array();
						$a1[] = $c;
						$a2[] = $c;
						$a3[] = $c;
						foreach($array_cat_parent as $cp)
						{
							// Array 1 : best algos only
							$id_best = $best_algorithms_only[$input][$cp][$c];
							if($id_best == -1)
								$a1[] = "";
							else if($id_best == "user")
								$a1[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
							else if($id_best == "user2")
								$a1[] = "\specialcell{Utilisateurs\\\\(individuels)}";
							else
								$a1[] = $array_algorithm_namebyid[$id_best];
						
						
							// Array 2 : best algos only + exp
							$id_best = $best_algorithms_exp[$input][$cp][$c];
							if($id_best == -1)
								$a2[] = "";
							else if($id_best == "user")
								$a2[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
							else if($id_best == "user2")
								$a2[] = "\specialcell{Utilisateurs\\\\(individuels)}";
							else
								$a2[] = $array_algorithm_namebyid[$id_best];
								
							// Array 3 : best of all
							$id_best = $best_algorithms[$input][$cp][$c]['first'];
							
							if($id_best == -1)
								$a3[] = "";
							else if($id_best == "user")
								$a3[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
							else if($id_best == "user2")
								$a3[] = "\specialcell{Utilisateurs\\\\(individuels)}";
							else
								$a3[] = $array_algorithm_namebyid[$id_best];
						}
						
						$id_best = $best_algorithms_percat_only[$input][$c];
						
						if($id_best == "user")
							$a1[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a1[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a1[] = $array_algorithm_namebyid[$id_best];
						
						$id_best = $best_algorithms_percat_exp[$input][$c];
						
						if($id_best == "user")
							$a2[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a2[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a2[] = $array_algorithm_namebyid[$id_best];
						
						$id_best = $best_algorithms_percat[$input][$c];
						
						if($id_best == "user")
							$a3[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a3[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a3[] = $array_algorithm_namebyid[$id_best];
						
						$array_latex1[$input][] = $a1;
						$array_latex2[$input][] = $a2;
						$array_latex3[$input][] = $a3;
					}
					
					$a1 = array();
					$a2 = array();
					$a3 = array();
					$a1[] = "Meilleurs algorithmes";
					$a2[] = "Meilleurs algorithmes";
					$a3[] = "Meilleurs algorithmes";
					
					foreach($array_cat_parent as $cp)
					{						
						// Array 1 : algo only
						$id_best = $best_algorithms_perparentcat_only[$input][$cp];
						
						if($id_best == "user")
							$a1[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a1[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a1[] = $array_algorithm_namebyid[$id_best];
						
						
						
						// Array 1 : algo only + exp
						$id_best = $best_algorithms_perparentcat_exp[$input][$cp];
						
						if($id_best == "user")
							$a2[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a2[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a2[] = $array_algorithm_namebyid[$id_best];
						
						
						// Array 3 : best of all
						$id_best = $best_algorithms_perparentcat[$input][$cp];
						if($id_best == "user")
							$a3[] = "\specialcell{Utilisateurs\\\\ (agrégés)}";
						else if($id_best == "user2")
							$a3[] = "\specialcell{Utilisateurs\\\\(individuels)}";
						else
							$a3[] = $array_algorithm_namebyid[$id_best];
						
					}
					$a1[] = "";
					$a2[] = "";
					$a3[] = "";
					$array_latex1[$input][] = $a1;
					$array_latex2[$input][] = $a2;
					$array_latex3[$input][] = $a3;
				}
				break;
		}
		$this->assign("latex_arg", "|c|".str_repeat("c|c|", count($array_cat_parent))."c|c|");
		$this->assign("array_latex1", $array_latex1);
		$this->assign("array_latex2", $array_latex2);
		$this->assign("array_latex3", $array_latex3);
		
		// Transposition of $array_group's benchmark for total output
		
		$array_group_t = array();
		foreach($array_group as $g)
		{
			$a = array();
			$common = array("parentcategory_name", "category_name");
			foreach($common as $c)
				$a[$c] = $g[$c];
			
			$a["benchmark"] = array();
			$nbr_algo = max(count($g["benchmark"]["TREND"]), max(count($g["benchmark"]["PM"]), count($g["benchmark"]["VALUE"])));
			
			$algos = array();
			foreach($g["benchmark"] as $input => $stuffs)
				$algos[$input] = array_keys($stuffs);
			
			for($j = 0 ; $j < $nbr_algo ; $j++)
			{
				$b = array();
				foreach($array_input as $input)
				{
					if(count($algos[$input]) < $j)
					$b[] = array("score" => "", "name" => "");
					else
					{
						$id_algo = $algos[$input][$j];
						$b[] = array("score" => $g["benchmark"][$input][$id_algo], "name" => $array_algorithm_namebyid[$id_algo]);
					}
				}
				
				$a["benchmark"][] = $b;
			}
			
			$array_group_t[] = $a;
		}
		$this->assign("array_group_t", $array_group_t);
		
		///////////////////////////////////
		/////// Comparison table V2 ///////
		///////////////////////////////////
		
		$array_cat_name 		= array("DEMOGRAPHIC", "FINANCE", "FINANCE - noisy", "INDUSTRY", "INDUSTRY - noisy", "MACRO", "MICRO");
		$array_cat_parentname 	= array("Month", "Year", "Quart");
		
		$array_latex1_v2 = array();
		$array_latex2_v2 = array();
		$array_latex3_v2 = array();
			
		$array_error_algo_percat = array();
		$array_error_algo_perparentcat = array();
		$array_error_algo_overall = array();		
		
		$template_error_algo = array();
			foreach($array_algorithm as $a)
				$template_error_algo[$a['id']] = array("error" => 0, "nbr_error" => 0);
				
		function computeMeanAlgo(&$array_error) {
			foreach($array_error as $k => $v)
			{
				if($v["nbr_error"] == 0)
					unset($array_error[$k]);
				else
					$array_error[$k] = $v["error"] / $v["nbr_error"];
			}
		}
		
		foreach($array_input as $input) {
			$array_latex1_v2[$input] = array();
			$array_latex2_v2[$input] = array();
			$array_latex3_v2[$input] = array();
			
			// First line: parentcategories
			$line = array("");
			foreach($array_cat_parentname as $p)
				$line[] = "\multicolumn{2}{|c|}{".$p."}";;
			$line[] = "\multicolumn{2}{|c|}{Meilleurs algorithmes}";
			
			$array_latex1_v2[$input][] = $line;
			$array_latex2_v2[$input][] = $line;
			$array_latex3_v2[$input][] = $line;
			
			///////////////////////////////////
			//// PER CATEGORY ERROR COMPUTATION
			///////////////////////////////////
			
			// Get the best algorithms for each cat x parent_cat
			$table_best_algo1 = array();
			$table_best_algo2 = array();
			$table_best_algo3 = array();
			
			foreach($array_cat_name as $c1)
			{
				$table_best_algo1[$c1] = array();
				$table_best_algo2[$c1] = array();
				$table_best_algo3[$c1] = array();
				foreach($array_cat_parentname as $c2)
				{
					$table_best_algo1[$c1][$c2] = " & ";
					$table_best_algo2[$c1][$c2] = " & ";
					$table_best_algo3[$c1][$c2] = " & ";
				}
			}
			
			// Compute best algorithms for each category
			$error_algo_percat = array();
			$error_algo_perparentcat = array();
			$error_algo_overall = array();
			
			foreach($array_cat_name as $c)
				$error_algo_percat[$c] = array_copy($template_error_algo);
			foreach($array_cat_parentname as $c)
				$error_algo_perparentcat[$c] = array_copy($template_error_algo);
			
			$error_algo_overall = array_copy($template_error_algo);
			
			// Foreach algorithm, sum its error for each category and parent category
			foreach($array_group as $g)
			{
				$cat = $g["category_name"];
				$parentcat = $g["parentcategory_name"];
				
				$k = array_keys($g["benchmark"][$input]);
				$table_best_algo3[$cat][$parentcat] = $array_algorithm_namebyid[$k[0]] . " & " . round($g["benchmark"][$input][$k[0]], 2);
				
				// Find the best classical algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "*") !== false || strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$table_best_algo1[$cat][$parentcat] = $array_algorithm_namebyid[$k[$i]]. " & " . round($g["benchmark"][$input][$k[$i]], 2);
				
				// Find the best classical algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$table_best_algo2[$cat][$parentcat] = $array_algorithm_namebyid[$k[$i]]. " & " . round($g["benchmark"][$input][$k[$i]], 2);
				
				foreach($g["benchmark"][$input] as $id_algo => $error)
				{
					$error_algo_percat[$cat][$id_algo]["error"] += $error;
					$error_algo_percat[$cat][$id_algo]["nbr_error"]++;
					$error_algo_perparentcat[$parentcat][$id_algo]["error"] += $error;
					$error_algo_perparentcat[$parentcat][$id_algo]["nbr_error"]++;
					$error_algo_overall[$id_algo]["error"] += $error;
					$error_algo_overall[$id_algo]["nbr_error"]++;
				}
			}
			
			$array_error_algo_percat[$input] = array();
			$array_error_algo_perparentcat[$input] = array();
			$array_error_algo_overall[$input] = array();

			foreach($array_cat_name as $c)
			{
				computeMeanAlgo($error_algo_percat[$c]);
				asort($error_algo_percat[$c]);
				$array_error_algo_percat[$input][$c] = $error_algo_percat[$c];
				$error_algo_percat[$c] = array_keys($error_algo_percat[$c]);
			}
			foreach($array_cat_parentname as $c)
			{
				computeMeanAlgo($error_algo_perparentcat[$c]);
				asort($error_algo_perparentcat[$c]);
				$array_error_algo_perparentcat[$input][$c] = $error_algo_perparentcat[$c];
				$error_algo_perparentcat[$c] = array_keys($error_algo_perparentcat[$c]);
			}
			computeMeanAlgo($error_algo_overall);
			asort($error_algo_overall);
			$array_error_algo_overall[$input] = $error_algo_overall;
			$error_algo_overall = array_keys($error_algo_overall);;
			
			// Filling the arrays
			foreach($array_cat_name as $c1)
			{
				$line1 = array();
				$line2 = array();
				$line3 = array();
				
				$line1[] = $c1;
				$line2[] = $c1;
				$line3[] = $c1;
				
				foreach($array_cat_parentname as $c2)
				{
					$e = explode(" & ", $table_best_algo1[$c1][$c2]);
					$line1[] = $e[0];
					$line1[] = $e[1];
					
					$e = explode(" & ", $table_best_algo2[$c1][$c2]);
					$line2[] = $e[0];
					$line2[] = $e[1];
					
					$e = explode(" & ", $table_best_algo3[$c1][$c2]);
					$line3[] = $e[0];
					$line3[] = $e[1];
				}
				$k = $error_algo_percat[$c1];
				
				// Find the first best classical algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "*") !== false || strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$line1[] = $array_algorithm_namebyid[$error_algo_percat[$c1][$i]];
				$line1[] = round($array_error_algo_percat[$input][$c1][$i], 2);
				//$line1[] = $array_error_algo_percat[$input][$c];
				
				// First the first best algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$line2[] = $array_algorithm_namebyid[$error_algo_percat[$c1][$i]];
				$line2[] = round($array_error_algo_percat[$input][$c1][$i], 2);
				
				// Find the best predictor
				$line3[] = $array_algorithm_namebyid[$error_algo_percat[$c1][0]];
				$line3[] = round($array_error_algo_percat[$input][$c1][0], 2);
				
				$array_latex1_v2[$input][] = $line1;
				$array_latex2_v2[$input][] = $line2;
				$array_latex3_v2[$input][] = $line3;
			}
			
			// Best algos per parent cat
			
			$line1 = array("Meilleurs algorithmes");
			$line2 = array("Meilleurs algorithmes");
			$line3 = array("Meilleurs algorithmes");
			
			foreach($array_cat_parentname as $c2)
			{
				$k = $error_algo_perparentcat[$c2];
				// Find the first best classical algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "*") !== false || strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$line1[] = $array_algorithm_namebyid[$error_algo_perparentcat[$c2][$i]];
				$line1[] = round($array_error_algo_perparentcat[$input][$c2][$i], 2);
				
				// Find the first best classical algorithm
				$i = 0;
				while(strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
					$i++;
				$line2[] = $array_algorithm_namebyid[$error_algo_perparentcat[$c2][$i]];
				$line2[] = round($array_error_algo_perparentcat[$input][$c2][$i], 2);
				
				$line3[] = $array_algorithm_namebyid[$error_algo_perparentcat[$c2][0]];
				$line3[] = round($array_error_algo_perparentcat[$input][$c2][0], 2);
			}

			$k = $error_algo_overall;
			// Find the first best classical algorithm
			$i = 0;
			while(strstr($array_algorithm_namebyid[$k[$i]], "*") !== false || strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
				$i++;
			$line1[] = $array_algorithm_namebyid[$error_algo_overall[$i]];
			$line1[] = round($array_error_algo_overall[$input][$i], 2);

			// Find the first best classical algorithm
			$i = 0;
			while(strstr($array_algorithm_namebyid[$k[$i]], "Utilisateur") !== false)
				$i++;
			$line2[] = round($array_algorithm_namebyid[$error_algo_overall[$i]], 2);
			$line2[] = $array_error_algo_overall[$input][$i];
			
			// Find the best overall predicator
			$line3[] = round($array_algorithm_namebyid[$error_algo_overall[0]], 2);
			$line3[] = $array_error_algo_overall[$input][0];
			
			$array_latex1_v2[$input][] = $line1;
			$array_latex2_v2[$input][] = $line2;
			$array_latex3_v2[$input][] = $line3;
		}
		
		$this->assign("array_latex1_v2", $array_latex1_v2);
		$this->assign("array_latex2_v2", $array_latex2_v2);
		$this->assign("array_latex3_v2", $array_latex3_v2);
		
		$this->assign("array_error_algo_percat", 		$array_error_algo_percat);
		$this->assign("array_error_algo_perparentcat", 	$array_error_algo_perparentcat);
		$this->assign("array_error_algo_overall", 		$array_error_algo_overall);
		
		// Make latex array of best predictors
		$array_latex4_v2 = array();
		$a = array();
		foreach($array_input as $k => $input)
			$a[] = "\multicolumn{2}{|c|}{".$input."}";
			
		$array_latex4_v2[] = $a;
		
		$array_allkeys = array();
		
		foreach($array_input as $input)
			$array_allkeys[$input] = array_keys($array_error_algo_overall[$input]);
			
		$nbr_algorithm = count($array_algorithm_namebyid);
		
		for($i = 0 ; $i < $nbr_algorithm ; $i++)
		{
			$a = array();
			foreach($array_input as $k => $input)
			{
				$key = $array_allkeys[$input][$i];
				$a[] = $array_algorithm_namebyid[$key];
				$a[] = round($array_error_algo_overall[$input][$key], 4);
			}
			$array_latex4_v2[] = $a;
		}
		
		$this->assign("array_latex4_v2", $array_latex4_v2);
		
		// Compute the gain of agregating users
		$array_gain_user = array();
		$array_latex_usergain = array();
		
		foreach($array_input as $input)
		{
			$array_gain_user[$input] = array();
			$array_latex_usergain[$input] = array();
			foreach($array_cat_name as $c1)
			{
				$array_gain_user[$input][$c1] = array();
				foreach($array_cat_parentname as $c2)
					$array_gain_user[$input][$c1][$c2] = null;
			}
		}
		
		foreach($array_group as $id_group => $info)
		{
			foreach($array_input as $input)
			{
				$array_gain_user[$input][$info["category_name"]][$info["parentcategory_name"]] = $info["benchmark"][$input]["user"] == 0 ? null : $info["benchmark"][$input]["user2"] / $info["benchmark"][$input]["user"];
			}
		}
		
		foreach($array_input as $input)
		{
			$a = array("");
			foreach($array_cat_parentname as $c2)
				$a[] = $c2;
				
			$array_latex_usergain[$input][] = $a;
			
			foreach($array_cat_name as $c1)
			{
				$a = array();
				$a[] = $c1;
				foreach($array_cat_parentname as $c2)
					$a[] = is_null($array_gain_user[$input][$c1][$c2]) ? "" : round($array_gain_user[$input][$c1][$c2], 2);
				$array_latex_usergain[$input][] = $a;
			}
		}
		
		$cols = "|c|" . str_repeat("c|", count($array_cat_parentname));
		$this->assign("array_gain_user", $array_gain_user);
		$this->assign("array_latex_usergain_cols", $cols);
		$this->assign("array_latex_usergain", $array_latex_usergain);
		
		// Compute the gain of using the corrected algorithms
			
		$array_gain_correction = array();
		foreach($array_input as $input)
		{
			$array_gain_correction[$input] = array();
			
			foreach($array_algorithm_namebyid as $id => $name)
				if(strstr($name, "*") === false && strstr($name, "Utilisateur") === false)
					$array_gain_correction[$input][$name] = array("ratio" => 0, "nbr_ratio" => 0);
		}
		
		
		foreach($array_group as $id_group => $info)
		{
			foreach($info["benchmark"] as $input => $algos)
			{
				foreach($algos as $id_algo => $error)
				{
					$name = $array_algorithm_namebyid[$id_algo];
					
					if(strstr($name, "Utilisateur") || strstr($name, "*"))
						continue;
					$error_normal = $error;
					/*
					if($name == "KNN")
						out::message("error knn = " . $error_normal);
					*/
					// Search corrected algorithm's error
					
					foreach($algos as $id_algo2 => $error)
						if($id_algo != $id_algo2 && str_replace("*", "", $array_algorithm_namebyid[$id_algo2]) == $name)
						{
							$error_corrected = $error;
							break;
						}
					/*
					if($name == "KNN")
					{
						out::message("error knn* = ".$error_corrected);
						out::message("Ratio = " . $error_normal/$error_corrected);
					}
					*/
					$array_gain_correction[$input][$name]["ratio"] += $error_normal/$error_corrected;
					$array_gain_correction[$input][$name]["nbr_ratio"]++;
				}
			}
		}
		
		// Compute means
		
		foreach($array_gain_correction as $input => $algos)
		{
			foreach($algos as $name => $infos)
			{
				if($infos["nbr_ratio"] == 0)
					unset($array_gain_correction[$input][$name]);
				$array_gain_correction[$input][$name] = $infos["ratio"] / $infos["nbr_ratio"];
			}
			arsort($array_gain_correction[$input]);
		}
		
		
		
		$array_latex_correctiongain = array();
		
		$nbr_algo_normal = max(count($array_gain_correction["TREND"]), max(count($array_gain_correction["VALUE"]), count($array_gain_correction["PM"])));
		
		$a = array();
		foreach($array_input as $k => $input)
			$a[] = "\multicolumn{2}{|c|}{".$input."}";
		
		$array_latex_correctiongain_arg = "|".str_repeat("c|", count($array_input)*2);
		
		$array_latex_correctiongain[] = $a;
		
		for($i = 0 ; $i < $nbr_algo_normal ; $i++)
		{
			$a = array();
			foreach($array_input as $input)
			{
				$keys = array_keys($array_gain_correction[$input]);
				$a[] = $keys[$i];
				$a[] = round($array_gain_correction[$input][$keys[$i]], 2);
			}
			$array_latex_correctiongain[] = $a;
		}
		
		$this->assign("array_latex_correctiongain", $array_latex_correctiongain);
		$this->assign("array_latex_correctiongain_arg", $array_latex_correctiongain_arg);
		
		// Crappy code that does not work very well
		
		
		$array_gain_correction_percat = array();
		foreach($array_input as $input)
			$array_gain_correction_percat[$input] = array();
		
		foreach($array_error_algo_percat as $input => $cats)
		{
			foreach($cats as $name_cat => $scores)
			{
				$array_gain_correction_percat[$input][$name_cat] = array();
				
				foreach($scores as $id_algo => $score)
				{
					$name = $array_algorithm_namebyid[$id_algo];
					if(strstr($name, "Utilisateur"))
						continue;
					$isCorrection = strstr($name, "*", $name) !== false;
					
					$name_key = str_replace("*", "", $name);
					
					if(!array_key_exists($name_key, $array_gain_correction_percat[$input][$name_cat]))
						$array_gain_correction_percat[$input][$name_cat][$name_key] = $score;
					else if($isCorrection)
						$array_gain_correction_percat[$input][$name_cat][$name_key] = $score / $array_gain_correction_percat[$input][$name_cat][$name_key];
					else
						$array_gain_correction_percat[$input][$name_cat][$name_key] = $array_gain_correction_percat[$input][$name_cat][$name_key] / $score;
				}
				
				arsort($array_gain_correction_percat[$input][$name_cat]);
			}
		}
		
		
		$array_gain_correction_perparentcat = array();
		foreach($array_input as $input)
			$array_gain_correction_perparentcat[$input] = array();
		
		foreach($array_error_algo_perparentcat as $input => $cats)
		{
			foreach($cats as $name_cat => $scores)
			{
				$array_gain_correction_perparentcat[$input][$name_cat] = array();
				
				foreach($scores as $id_algo => $score)
				{
					$name = $array_algorithm_namebyid[$id_algo];
					if(strstr($name, "Utilisateur"))
						continue;
					$isCorrection = strstr($name, "*", $name) !== false;
					
					$name_key = str_replace("*", "", $name);
					
					if(!array_key_exists($name_key, $array_gain_correction_perparentcat[$input][$name_cat]))
						$array_gain_correction_perparentcat[$input][$name_cat][$name_key] = $score;
					else if($isCorrection)
						$array_gain_correction_perparentcat[$input][$name_cat][$name_key] = $score / $array_gain_correction_perparentcat[$input][$name_cat][$name_key];
					else
						$array_gain_correction_perparentcat[$input][$name_cat][$name_key] = $array_gain_correction_perparentcat[$input][$name_cat][$name_key] / $score;
				}
				
				arsort($array_gain_correction_perparentcat[$input][$name_cat]);
			}
		}
		
		$this->assign("array_gain_correction", $array_gain_correction);
		$this->assign("array_gain_correction_percat", $array_gain_correction_percat);
		$this->assign("array_gain_correction_perparentcat", $array_gain_correction_perparentcat);
		
		$this->assign("array_cat_name", $array_cat_name);
		$this->assign("array_cat_parentname", $array_cat_parentname);
		
		return $this->renderFile($this->path_html."admin/curve_".$f.".html");
	}
	
	public function traverse($n)
	{
		return self::static_traverse($n);
	}
	
	public static function static_traverse($n)
	{
		$a = array();
	
		if($n['id'] != 0)
			$a[] = array('id' => $n['id'], 'value' => $n['value'], 'level' => $n['level'], 'id_parent' => $n['id_parent']);
	
		foreach($n['children'] as $c)
			$a = array_merge($a, self::static_traverse($c));
		return $a;
	}
}