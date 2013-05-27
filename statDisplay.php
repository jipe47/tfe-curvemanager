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

////////////////////////////
$plot = new PHPlot();
$type = Get::string("type");
$data = Get::raw("data");
$data = explode(";", $data);

if(Get::exists("labels"))
	$labels = unserialize(rawurldecode(Get::raw("labels")));
else
	$labels = array();
switch($type)
{
	case "pie":
		$plot->setPlotType('pie');
		
		$plot->SetDataType('text-data-single');
		foreach($data as $k => $d)
			$data[$k] = array('', $d);
		$plot->SetLegendPixels(5, 5);
		break;
}

foreach($labels as $l)
	$plot->SetLegend($l);


$plot->SetDataValues($data);

//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
?>