<?php
session_start();
require_once "config/init.php";
require_once "lib/phplot/phplot.php";

define("FORCE_THUMB_REBUILD", true);

$id_curve 	= Get::int("id_curve");
$width 		= Get::int("width");
$height 	= Get::int("height");
$url 		= "thumbs/th_curve_".$id_curve."_".$width."x".$height.".png";


if(!file_exists($url) || FORCE_THUMB_REBUILD)
{
	// Fetching the curve points
	$info = Plugins::getPlugin("Curve")->getDefaultModel()->getCurveById($id_curve);
	$data = array();
		
	$data = $info['points'];

	// Genere the thumb
	$plot = new PHPlot($width, $height);
	$plot->SetDataValues($data);
	$plot->SetIsInline(true);
	$plot->SetOutputFile($url);
	
	//$plot->SetXTickLabelPos('none');
	$plot->SetXDataLabelPos('none');
	$plot->SetYDataLabelPos('none');
	
	$plot->SetXTickLabelPos('none');
	$plot->SetXTickPos('none');
	$plot->SetYTickLabelPos('none');
	$plot->SetYTickPos('none');
	
	
	
	//$plot->SetPlotAreaWorld(NULL, $yMinPlot, NULL, $yMaxPlot);
	$plot->DrawGraph();
}

//echo '<img src="'.$url.'" />';

header('Location: ' . $url);
?>
