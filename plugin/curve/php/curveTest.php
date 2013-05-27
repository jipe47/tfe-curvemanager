<?php
class CurveTest extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Curve Statistics");
		$this->setAccessName("Test");
	}
	
	public function exec()
	{
		$curves = $this->model->getCurves();
		$max_length = -1;
		$mean = 0;
		$vals = array();
		foreach($curves as $c)
		{
			$p = count(explode(";", $c["points"]));
			$mean += $p;
			$vals[] = $p;
			$max_length = max($p, $max_length);
		}
		sort($vals);
		$nbr_curve = count($curves);
		$i = floor($nbr_curve/2);
		$median = count($curves) % 2 == 0 ? ($vals[$i] + $vals[$i + 1])/2 : $vals[$i+1];
		$mean /= count($curves);
		echo "Max length = " . $max_length."<br />Mean length = " . $mean . "<br />Median length = " . $median;
	}
}