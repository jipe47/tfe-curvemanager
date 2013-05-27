<?php
class CurveScriptM3CDelete extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Flush M3C");
		$this->setAccessName("FlushM3C");
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		$this->request->query("DELETE FROM " . TABLE_CURVE . " WHERE tag LIKE '%M3C%'");
		$this->request->query("DELETE FROM " . TABLE_CATEGORY . " WHERE name LIKE '%M3C%'");
	}
}