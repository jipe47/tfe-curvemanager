<?php
class CurveScriptPositionFixer extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Fix Curve positions");
		$this->setAccessName("PositionFixer");
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		SqlRequest::setStoreRequest(false);
		$all = $this->request->fetchQuery("SELECT id, id_category, position FROM " . TABLE_CURVE . " ORDER BY position");
		
		$array_pos = array();
		
		foreach($all as $a)
		{
			if(!array_key_exists($a['id_category'], $array_pos))
				$array_pos[$a['id_category']] = 0;
			$this->request->update(TABLE_CURVE, "id='".$a['id']."'", array('position' => $array_pos[$a['id_category']]));
			$array_pos[$a['id_category']]++;
		}
		SqlRequest::setStoreRequest(true);
	}
}