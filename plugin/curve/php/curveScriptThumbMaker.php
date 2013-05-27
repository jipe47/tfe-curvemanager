<?php
class CurveScriptThumbMaker extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Curve Thumbs Maker");
		$this->setAccessName("ThumbMaker");
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		SqlRequest::setStoreRequest(false); // Otherwise memory's max capacity is quickly reached
		set_time_limit(0);
		
		$array_curve = $this->request->fetchQuery("SELECT id FROM " . TABLE_CURVE);
		
		foreach($array_curve as $c)
		{
			if(file_exists("thumbs/th_curve_".$c['id']."_350x150.png"))
				continue;
			file_get_contents(URL_SITE."curveThumb.php?id_curve=".$c['id']."&width=350&height=150");
		}
		
		echo count($array_curve) . " thumbs processed.<br />";
	}
}