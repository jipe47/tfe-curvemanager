<?php
require_once "config/init.php";
require_once "lib/phplot/phplot.php";

//////////////////////////////////////////////////////////////////////////////////////////
//   _____          _____             __  __  ______  _______  ______  _____    _____   //
//  |  __ \  /\    |  __ \     /\    |  \/  ||  ____||__   __||  ____||  __ \  / ____|  //
//  | |__) |/  \   | |__) |   /  \   | \  / || |__      | |   | |__   | |__) || (___    //
//  |  ___// /\ \  |  _  /   / /\ \  | |\/| ||  __|     | |   |  __|  |  _  /  \___ \   //
//  | |   / ____ \ | | \ \  / ____ \ | |  | || |____    | |   | |____ | | \ \  ____) |  //
//  |_|  /_/    \_\|_|  \_\/_/    \_\|_|  |_||______|   |_|   |______||_|  \_\|_____/   //
//                                                                                      //
//////////////////////////////////////////////////////////////////////////////////////////

$html = 0;

$p_point_radius = 3;
$p_point_diameter = 2* $p_point_radius;

$p_endpoint_marginright = 20;
$p_endpoint_color_pred = 'red_80';
$p_endpoint_color_mean = 'red';

$p_colors = array(  'red' => array(255, 0, 0),
					//'red_80' => array(255, 0, 0, 80),
					'red_80' => array(255, 160, 160),
                    'black' => array(0, 0, 0),
                    'grey' => array(200, 200, 200),
					'grey_70' => array(200, 200, 200, 70),
					'grey_40' => array(200, 200, 200, 40));

////////////////////////////

$width = Get::int("width", 600);
$height = Get::int("height", 400);
$plot = new PHPlot($width, $height);

$mode = Get::string("mode");
$array_labels = array();

$data_point = array();

switch($mode)
{
	case "function":
		$x_start = Get::float("gen_x_start", 0);
		$x_end = Get::float("gen_x_end", 0);
		$x_step = Get::float("gen_x_step", 1);
		$noise = Get::float("gen_noise", 0);
		
		$f = $_GET["gen_f"];
		$f = str_replace("ppp", "+", $f);
		
		$data = Plugins::getPlugin("Curve")->getDefaultModel()->generatePoints($f, $x_start, $x_end, $x_step, $noise);
		
		break;
		
	case "points":
		$points = Get::string("points", "");
		$data = explode(";", $points);
		foreach($data as $k => $d)
			$data[$k] = explode(",", $d);
		break;
		
	case "id_curve":
		$id = Get::int("id_curve");
		$curveModel = new CurveModel();
		$infos = $curveModel->getCurveById($id);
		
		$data = $infos['points'];
		
		if($infos['hidden_x_end'] != "")
		{
			
			$x_end = floatval($infos['hidden_x_end']);
			for($i = count($data) ; $i <= $x_end ; $i++)
				$data[] = array($i, null);
		}
		
		break;
		
	case "partial":
		$id = Get::int("id_curve");
		$x = Get::float("x");
		
		$curveModel = new CurveModel();
		$infos = $curveModel->getCurveById($id);
		
		// Extend the plot to the unknown part
		$data = $infos['points'];
		$nbr_point = count($data);
		for($i = $nbr_point ; $i <= $x ; $i++)
			$data[] = array($i, null);
		
		// Curves' label
		$array_labels[] = "Known curve";
		
		// Fetch and insert additional curves
		$array_additional_curves 	= array();
		$ys 						= Get::string("ys");
		$user 						= Get::string("user");
		$prediction 				= Get::string("prediction");
		
		if($prediction != "")
			$array_additional_curves["SLA's Prediction"] = explode(";", $prediction);
		if($ys != "")
			$array_additional_curves["Real Curve"] = explode(";", $ys);
		if($user != "")
			$array_additional_curves["Users' Prediction"] = explode(";", $user);
		
		foreach($array_additional_curves as $label => $points)
		{
			$array_labels[] = $label;
				
			// Fill $data with null to remove the known part but the last point
			$i = 0;
			for(; $i < $nbr_point - 2 ; $i++)
				$data[$i][] = null;
			
			$i++;
			$data[$i][] = $data[$i][1];
			$i++;
			// Insert the prediction
			foreach($points as $y)
			{
				$data[$i++][] = floatval($y);
			}
		}
		
		break;
}

$array_labels[] = "Original Curve";

if(Get::exists("zones_to_predict") && Get::string("zones_to_predict") != "")
{
	$zones_to_predict = explode(";", Get::string("zones_to_predict"));
	$zone_start = intval($zones_to_predict[0]);
	$zone_end = intval($zones_to_predict[1]);
	$i = 0;
	
	for(; $i < $zone_start ; $i++)
		$data[$i][] = null;
	$data[$i][] = $data[$i][1];
	$i++;
	
	for(; $i <= $zone_end ; $i++)
	{
		$data[$i][] = $data[$i][1];
	}
	
	$array_labels[] = "Zone 1";
}


$predictions_type = Get::string("predictions_type");
if($predictions_type != "")
{
	if(Get::exists("zones_to_predict"))
	{
		$zones_to_predict = explode(";", Get::string("zones_to_predict"));
		$zone_start = intval($zones_to_predict[0]);
		$zone_end = intval($zones_to_predict[1]);
	}
	else if(Get::exists("id_zone"))
	{
		$id_zone = Get::int("id_zone");
		$request = new SqlRequest();
		$infos = $request->firstQuery("SELECT start, end FROM " . TABLE_CURVE_ZONE . " WHERE id='".$id_zone."'");
		$zone_start = intval($infos['start']);
		$zone_end = intval($infos['end']);
		
		//echo $zone_start.';'.$zone_end.'<br />';
	}
	
	switch($predictions_type)
	{
		
		case "algorithm":
			$algos = Get::exists("algorithms") ? " AND a.id IN ('".implode("','", explode(";", Get::string("algorithms")))."')" : "";
			$request = new SqlRequest();
			$id_zone = Get::int("id_zone");
			$infos_algo = $request->fetchQuery("SELECT a.name, p.* , z.start, z.end
					FROM " . TABLE_PREDICTION_ALGORITHM . " p 
					LEFT JOIN " . TABLE_CURVE_ZONE . " z ON z.id = p.id_zone
					LEFT JOIN " . TABLE_ALGORITHM . " a ON a.id = p.id_algorithm 
					WHERE p.id_zone='" . $id_zone . "' " . $algos);
			
			foreach($infos_algo as $a)
			{
				$prediction_data = explode(";", $a['prediction']);
				$i = 0;
				$zone_start = intval($a['start']);
				for(; $i < $zone_start ; $i++)
					$data[$i][] = null;
				$data[$i][] = $data[$i][1];
				$i++;
				foreach($prediction_data as $p)
					$data[$i++][] = floatval($p);
						
				$array_labels[] = $a['name'];
			}
			
			$displayUser = Get::string("display_user") != "";
			
			if($displayUser)
			{
				$pred_user = explode(";", Get::string("pred_user"));
				$i = 0;
				for(; $i < $zone_start ; $i++)
					$data[$i][] = null;
				$data[$i][] = $data[$i][1];
				$i++;
				foreach($pred_user as $p)
					$data[$i++][] = floatval($p);
					
				$array_labels[] = 'Users';
			}
			
			break;
		
		case "trend":
				
			$prediction_data = explode(";", Get::string("prediction_data"));
			
			$i = 0;
			for(; $i < $zone_start ; $i++)
				$data[$i][] = null;
			$data[$i][] = $data[$i][1];
			$i++;
			foreach($prediction_data as $p)
				$data[$i++][] = floatval($p);
			
			$array_labels[] = "Mean Predicted Trend";
			break;
			
		case "value":
			
			$mean = Get::float("prediction_data");
			
			$i = 0;
			for(; $i < $zone_start ; $i++)
				$data[$i][] = null;
			$data[$i][] = $data[$i][1];
			$i++;
			for(; $i < $zone_end ; $i++)
				$data[$i][] = null;
			$data[$i][] = $mean;
			
			$array_labels[] = "Mean Predicted Value";
			break;
	}

}

// Get x range
$xRange = array();
$yRange = array();
foreach($data as $d)
{
	$xRange[] = $d[0];
	$ys = array_filter(array_slice($d, 1));
	if(!empty($ys))
		$yRange = array_merge($ys ,$yRange);
}
$xMin = min($xRange);
$yMin = min($yRange);
$xMax = max($xRange);
$yMax = max($yRange);

foreach($data_point as $dp)
{
	foreach($dp as $coord)
	{
		$xMin = min($xMin, $coord[0]);
		$xMax = max($xMax, $coord[0]);
		
		$yMin = min($yMin, $coord[1]);
		$yMax = max($yMax, $coord[1]);
	}
}

$xLength = $xMax - $xMin;
$yLength = $yMax - $yMin;

$xMinPlot = $xMin;
//$xMaxPlot = $xMax + 1;
$xMaxPlot = count($data) + 1;
$yMinPlot = floor($yMin) - 0.1*$yLength;
$yMaxPlot = ceil($yMax) + 0.1*$yLength;

$xLengthPlot = $xMaxPlot - $xMinPlot;
$yLengthPlot = $yMaxPlot - $yMinPlot;

$plot->SetDataValues($data);
//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
/*$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');*/

if(count($array_labels) > 0)
{
	$plot->SetLegend($array_labels);
	$plot->SetLegendPosition(.5, 0, 'plot', 0.1, 0.05);
}
//Draw it
$output_name = "tmp/tmp-".microtime().".png";

$array_style = array();
for($i = 1 ; $i < count($data[0]) ; $i++)
	$array_style[] = 'solid';

$plot->SetLineStyles($array_style);
$plot->SetOutputFile($output_name);
$plot->SetIsInline(true);
$plot->SetPlotAreaWorld($xMinPlot, $yMinPlot, $xMaxPlot, $yMaxPlot);

$plot->DrawGraph();

header("Location: " . $output_name);
//echo '<img src="'.$output_name.'" />';