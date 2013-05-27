<?php
class CurveRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("Curve");
		$this->registerHandler("curve", "handler_curve");
		$this->registerHandler("group", "handler_group");
		$this->registerHandler("zone", "handler_zone");
		$this->addAccess(Page::ADMIN);
	}
	
	public function handler_group()
	{
		$back = "Admin/Curve/group";
		$this->setLocation($back);
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		switch($this->arg->string(0))
		{
			case "add":
				$name 			= Post::string("name");
				$id_category 	= Post::int("id_category");
				$after 			= Post::string("after");
				$nbr_curve 		= Post::int("nbr_curve");
				
				if($id_category == 0)
				{
					out::message("You must select a category.", Message::WARNING);
					break;
				}
				
				if($after == "top")
				{
					$position = 0;
					$this->request->query("UPDATE " . TABLE_CURVE_GROUP . " SET position = position + 1");
				}
				else if($after == "bottom")
					$position = $this->request->count(TABLE_CURVE_GROUP);
				else
				{
					$position = intval($after) + 1; 
					$this->request->query("UPDATE " . TABLE_CURVE_GROUP . " SET position = position + 1 WHERE position >= " .$position);
				}
				
				$this->request->insert(TABLE_CURVE_GROUP, array("id_category" => $id_category, "position" => $position, "name" => $name, "nbr_curve" => $nbr_curve));
				out::message("Level inserted");
				break;
				
			case "edit":
				$id = Post::int("id");
				$name = Post::string("name");
				$nbr_curve = Post::int("nbr_curve");
				$this->request->update(TABLE_CURVE_GROUP, 'id="'.$id.'"', array('name' => $name, 'nbr_curve' => $nbr_curve));
				out::message("Level edited");
				break;
				
			case "delete":
				$id = $this->arg->int(1);
				$info = $this->model->getGroupById($id);
				$this->request->query("UPDATE " . TABLE_CURVE_GROUP . " SET position = position - 1 WHERE position > " . $info['position']);
				$this->request->query("DELETE FROM " . TABLE_CURVE_GROUP . " WHERE id='" . $id . "'");
				out::message("Group deleted");
				break;
				
			case "move":
				$id = $this->arg->int(1);
				$direction = $this->arg->string(2);
				$inc = $direction == "up" ? -1 : 1;
				$info = $this->model->getGroupById($id);
				$newposition = $info['position'] + $inc;
				$this->request->query("UPDATE " . TABLE_CURVE_GROUP . " SET position = " . $info['position'] . " WHERE position='" . $newposition . "'");
				$this->request->query("UPDATE " . TABLE_CURVE_GROUP . " SET position = " . $newposition . " WHERE id='" .$id."'");
                out::message("Level moved.");
				break;
		
		}
		$this->setLocation($back);
	}
	
	public function handler_zone()
	{
		$back = "Admin/Curve/curve/list";
		$this->setLocation($back);
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		switch($this->arg->string(0))
		{
			case "add":
			case "edit":
				$id_curve = Post::int("id_curve");
				$from = Post::double("start");
				$to = Post::double("end");
		
				$info_curve = $this->model->getCurveById($id_curve);
				$points = explode(";", $info['points']);
				$points[count($points) - 1] = explode(",", $points[count($points) - 1]);
		
				$maxX = $points[count($points) - 1][0];
				$endzone = $to == $maxX;
		
				$id = Post::int("id");
		
				$info = array("id_curve" => $id_curve, "start" => $from);
		
				// Parameters
				$doubles = array("value_loss", "value_maxerror", "value_allowederror", "value_gain", 
						"pm_gain", "pm_loss", 
						"trend_maxerror", "trend_gain", "trend_allowederror", "trend_loss");
				foreach($doubles as $d)
					$info[$d] = Post::double($d);
		
				if(!$endzone)
					$info['end'] = $to;
					
				if($id == -1)
				{
					// TODO Vérifier qu'il n'y a pas de recouvrement
					$this->request->insert(TABLE_CURVE_ZONE, $info);
					$m = "Zone added";
				}
				else
				{
					$this->request->update(TABLE_CURVE_ZONE, "id", $id, $info);
					$m = "Zone updated";
				}
				out::message($m);
				$back = "Admin/Curve/view/".$id_curve;
				break;
		
			case "delete":
				$id = $this->arg->int(1);
				$info = $this->model->getZoneById($id);
				$back = "Admin/Curve/view/".$info['id_curve'];
				$this->request->query("DELETE FROM " . TABLE_CURVE_ZONE . " WHERE id='" . $id . "'");
				out::message("Zone deleted");
				break;
		}
		$this->setLocation($back);
	}
	public function handler_curve()
	{
		$back = "Admin/Curve/list";
		$this->setLocation($back);
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		switch($this->arg->string(0))
		{
			case "editcategory":
				$id_curve = Post::int("id");
				$id_cat = Post::int("id_category");
				
				$info_curve = $this->model->getCurveById($id_curve);
				
				$nbr_curve_newcat = $this->request->count(TABLE_CURVE, 'id_category="'.$id_cat.'"');
				$this->request->update(TABLE_CURVE, 'id="'.$id_curve.'"', array('id_category' => $id_cat, 'position' => $nbr_curve_newcat));
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = position - 1 WHERE position > " . $info_curve['position'] . " AND id_category='". $info_curve['id_category']."'");
				out::message("Curve's category updated");
				$back = "Admin/Curve/view/".$id_curve;
				break;
				
			case "move":
				$id = $this->arg->int(1);
				$direction = $this->arg->string(2);
				$inc = $direction == "up" ? -1 : 1;
				$info = $this->model->getCurveById($id);
				$id_category = $info['id_category'];
				$newposition = $info['position'] + $inc;
				$where = "id_category='" . $id_category . "'";
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = " . $info['position'] . " WHERE position='" . $newposition . "' AND ".$where);
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = " . $newposition . " WHERE id='" .$id."'");
				$back = "Admin/Curve/list/".$id_category;
				out::message("The curve has been moved");
				break;
				
			case "movetotop":
				$id = $this->arg->int(1);
				$info = $this->model->getCurveById($id);
				$id_category = $info['id_category'];
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = position + 1 WHERE position < ".$info['position']." AND id_category='" . $id_category . "'");
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = 0 WHERE id='" .$id."'");
				$back = "Admin/Curve/list/".$id_category;
				out::message("The curve has been moved to top");
				break;
				
			case "toggledisplay":
				$id = $this->arg->int(1);
				$info = $this->model->getCurveById($id);
				$this->request->query("UPDATE " . TABLE_CURVE . " SET display = (display + 1)%2 WHERE id='" . $id . "'");
				
				if($info['display'])
					out::message("The curve has been hidden");
				else
					out::message("The curve is visible");
				$page = ceil($info['position'] / $this->plugin->nbr_curve_per_page);
				$back = "Admin/Curve/list/".$info['id_category']."/".$page."#curve".$id;
				break;
			case "addedit":
				
				// Grab global informations
				$id = Post::int("id", -1);
				$a = array(	'name' 					=> Post::string("name"),
							'tag' 					=> Post::string("tag"),
							'description' 			=> Post::string("description"),
							'id_category' 			=> Post::string("id_category"),
							"display" 				=> Post::boolValue("display", "on", false),
						);
				
				
				// Generate/fetch curve's points, depending on the user's choice 
				$newcurve_choice = Post::string("newcurve_choice");
				
				switch($newcurve_choice)
				{
					case "generate":
						$a["gen_x_start"] 	= Post::float("gen_x_start", 0);
						$a["gen_x_end"] 	= Post::float("gen_x_end", 0);
						$a["gen_x_step"] 	= Post::float("gen_x_step", 1);
						$a["gen_noise"] 	= Post::float("gen_noise", 0);
						$a["gen_f"] 		= Post::string("gen_f");
						
						$data = $this->model->generatePoints($a['gen_f'], $a['gen_x_start'], $a['gen_x_end'], $a['gen_x_step'], $a['gen_noise']);
						
						break;
						
					case "import-file":
						$y 					= Post::int("import_file_y", 0);
						$skip 				= Post::int("import_file_skip", 0);
						
						$text_delimiter 	= Post::string("import_text_delimiter", "");
						$column_separator 	= Post::string("import_column_separator", "");
						$organisation 		= Post::string("import_file_organisation");
						$data = array();
						
						//out::message("text delimiter = $text_delimiter");
						//out::message("col sep = $column_separator");
						//out::message("org = $organisation");
						
						// Retreive the file contents and transform it into an array
						$import_file 	= FileUpload::moveFile("import_file", "curve");
						$handler 		= fopen(PATH_UPLOAD."curve/".$import_file, "r");
						$content 		= array();
						

						function digitStringSanitizer(&$str, $key)
						{
							$str = str_replace(array('\t', '\r', '\n', ' '), "", $str);
							//return preg_replace("#[^0-9\.]#", "", $str);
						}
						function getLine($handle, $column_separator, $text_delimiter)
						{
							if($text_delimiter == '')
							{
								$line = fgets($handle);
								
								if($line === false)
									return false;
								$a = explode($column_separator, $line);
								//array_walk($a, "digitStringSanitizer");	// TODO Does not work ?!
								return $a;
							}
							else
								return fgetcsv($handle, 0, $column_separator, $text_delimiter);
						}
						
						while(($line = getLine($handler, $column_separator, $text_delimiter)) !== false)
							$content[] = $line;
						
						fclose($handler);
						
						//out::message("Data = " . serialize($content));
						
						$nbr_item = Post::int("import_file_nbritem", -1);
						
						$x = 0;
						if($nbr_item == -1)
							$nbr_item = count($content);
						// Extract the data
						switch($organisation)
						{
							case "line":
								foreach($content as $c)
								{
									if($skip != 0)
									{
										$skip--;
										continue;
									}
									$data[] = array($x, $c[$y]);
									$x++;
									if($x == $nbr_item)
										break;
								}
								break;
						
							case "column":
								$length = count($content[$x]);
						
								for($i = 0 ; $i < $length ; $i++)
								{
									if($skip != 0)
									{
										$skip--;
										continue;
									}
									$data[] = array($x, $content[$y][$i]);
									$nbr_item++;
									if($x == $nbr_item)
										break;
								}
								break;
						}
						
						FileProcessing::deleteFile(PATH_UPLOAD."curve/".$import_file);
						//out::message(serialize($data));
						
						break;
						
					case "import-url":
						$import_choice = Post::string("import_choice");
						
						$url = Post::string("import_url");
						if(preg_match("#quandl#", $url))
						{
							// TODO
						}
						break;
				}
				
				// Create the data field (array to string)
				$ys = array();
				foreach($data as $d)
					$ys[] = str_replace(",", ".", $d[1]);
				
				$a['points'] = implode(";", $ys);
				//out::message("points = " .$a['points']);
				
				// Sql insertion (or update)
				if($id == -1)
				{
					$this->request->insert(TABLE_CURVE, $a);
					$id = $this->request->getLastId();
					$m = "The curve has been added.";
				}
				else
				{
					$this->request->update(TABLE_CURVE, "id='".$id."'", $a);
					$m = "The curve has been edited.";
				}
				// Is this a partial curve? If yes, configure it
				$partial = Post::boolValue("partial", "on", false);
				
				$back = $partial ? "Admin/Curve/makepartial/".$id : "Admin/Curve/view/".$id;
				
				Messages::add($m, Message::SUCCESS);
				break;
		
			case "delete":
				$id = $this->arg->int(1);
				$info = $this->model->getCurveById($id);
				$this->request->query("UPDATE " . TABLE_CURVE . " SET position = position - 1 WHERE position >  ".  $info['position']);
				$back .= "/".$info['id_category'];
				$this->request->query("DELETE FROM " . TABLE_CURVE . " WHERE id='" . $id . "'");
				Messages::add("The curve has been deleted.", Message::SUCCESS);
				break;
				
			case "makepartial":
				$id_curve 	= Post::int("id_curve");
				$info 		= $this->model->getCurveById($id_curve);
				
				$x_end 			= $info['points'][count($info['points']) - 1][0];
				$missing_choice = Post::string("missing_choice");
				$hidden_x 		= $missing_choice == "nbr" ? $x_end + Post::float("missing_nbr") : Post::float("missing_x");
				
				$array_sql 	= array('hidden_x_end' => $hidden_x);
				
				$hidden_ys 	= Post::string("missing_ys");
				if($hidden_ys != "")
					$array_sql["hidden_ys"] = $hidden_ys;
				
				$hidden_prediction = Post::string("prediction");
				if($hidden_prediction != "")
					$array_sql["hidden_prediction"] = $hidden_prediction;
				
				if($info['hidden_id_zone'] == "")
				{
					// TODO Compute zone's parameters
					$this->request->insert(TABLE_CURVE_ZONE, array(
							'id_curve' => $id_curve, 
							'start' => $x_end, 
							'end' => $hidden_x));
					$array_sql['hidden_id_zone'] = $this->request->getLastId();
				}
				
				
				$cnt = $this->request->firstQuery("SELECT COUNT(*) as cnt FROM " . TABLE_CURVE . " WHERE priority IS NOT NULL");
				$array_sql['priority'] = $cnt['cnt'];
				
				$this->request->update(TABLE_CURVE, "id='".$id_curve."'", $array_sql);
				out::message("Curve is now partial.");
				$back = "Admin/Curve/view/".$id_curve;
				break;
				
			case "movepartial":
				$id = $this->arg->int(1);
				$direction = $this->arg->string(2);
				$inc = $direction == "up" ? -1 : 1;
				$info = $this->model->getCurveById($id);
				$newposition = $info['position'] + $inc;
				$this->request->query("UPDATE " . TABLE_CURVE . " SET priority = " . $info['priority'] . " WHERE priority='" . $newposition . "'");
				$this->request->query("UPDATE " . TABLE_CURVE . " SET priority = " . $newposition . " WHERE id='" .$id."'");
				$back = "Admin/Curve/partial";
				break;
			
			default:
				Messages::add("Undefined operation.", Message::ERROR);
		}
		$this->setLocation($back);
	}
}
