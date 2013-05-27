<?php
class CCategoryScriptPositionFixer extends ScriptPage
{
	public function construct()
	{
		$this->setScriptName("Fix subcategories' positions");
		$this->setAccessName("PosFixer");
	}
	
	public function exec()
	{
		if(!CM_ENABLE_REQUEST)
		{
			echo "Script disabled.";
			return;
		}
		
		$array_all = $this->request->fetchQuery("SELECT * FROM " .TABLE_CATEGORY . " WHERE id_parent IS NOT NULL  ORDER BY name");
		
		$array_counts = array();
		
		foreach($array_all as $a)
		{
			$p = $a['id_parent'];
			if(!array_key_exists($p, $array_counts))
				$array_counts[$a['id_parent']] = 0;
			
			echo "New position of " . $a['name'] . " = " . $array_counts[$p] . "<br />";
			$this->request->update(TABLE_CATEGORY, "id='".$a['id']."'", array("position" => $array_counts[$p]));
			
			$array_counts[$a['id_parent']]++;
		}
	}
}