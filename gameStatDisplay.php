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

$p_colors = array(  'red' => array(255, 0, 0),
                    'black' => array(0, 0, 0),
                    'grey' => array(200, 200, 200));

$data = array();

$nbr_day = Get::int("nbr_day", 10);
////////////////////////////

$type = Get::string("type");
$full = Get::string("full", "false") == "true";

if($full)
	$plot = new PHPlot(1000, 450);
else
	$plot = new PHPlot();

switch($type)
{
	case "user":
		$usermodel = Plugins::getPlugin("UserAndroid")->getDefaultModel();
		$array_data = $usermodel->getNewUserMeasure($nbr_day);
		
		if(!$full)
		{
			$plot->SetTitle("Number of new users (last " . $nbr_day . " days)");
			$plot->SetXLabel("Day");
			$plot->setYLabel("New users");
		}
		break;
	case "prediction":
		$curvemodel = Plugins::getPlugin("Curve")->getDefaultModel();
		$array_data = $curvemodel->getPredictionMeasure($nbr_day);
				
		if(!$full)
		{
			$plot->SetTitle("Number of sent predictions (last " . $nbr_day . " days)");
			$plot->SetXLabel("Day");
			$plot->setYLabel("Sent predictions");
		}
		else
		{
			$plot->SetXLabel("Jour");
			$plot->setYLabel("CDF");
		}
}

// Fill in the gaps
for($i = $nbr_day - 1 ; $i >= 0  ; $i--)
{
	$day = date("j M", time() - $i * 60 * 60 * 24);
	$data[$day] = array($day, 0);
}

// Insert data
$nbr_pred = 0;
foreach($array_data as $u)
{
	$day = date("j M", strtotime($u['timestamp_add']));
	$data[$day] = array($day, $u['nbr']);
	$nbr_pred += intval($u['nbr']);
}

$data = array_values($data);

if($full) // Plot a CDF
{
	$cnt = 0;
	
	foreach($data as $k => $d)
	{
		$cnt += $d[1];
		$data[$k][1] = floatval($cnt)/$nbr_pred;
	}
	/*
	echo "<pre>";
	print_r($data);
	echo "</pre>";
	*/
	$plot->SetPlotAreaWorld(0, 0, count($data), 1);
}


/*$plot->SetDataType('text-data-single');
foreach($data as $k => $d)
	$data[$k] = array('', $d);
	*/
$plot->SetDataValues($data);
$plot->SetYLabelType("data", 0, "", "");
//$plot->SetYLabelType('printf', '%d');
//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

if($full)
{
	//$plot->SetYLabelType('printf', '%8.1');
	$plot->SetPrecisionY(1);
	$plot->SetXDataLabelAngle(90);
	$plot->SetFontTTF('x_label', '/home/jipe/public_html/curvemanager/font/arial.ttf', 10);
	$plot->SetFontTTF('y_label', '/home/jipe/public_html/curvemanager/font/arial.ttf', 10);
	$plot->SetFontTTF('y_title', '/home/jipe/public_html/curvemanager/font/arial.ttf', 14);
	$plot->SetFontTTF('x_title', '/home/jipe/public_html/curvemanager/font/arial.ttf', 14);
}
$plot->DrawGraph();
?>