<?php
class BugtrackerModel extends Model
{
	public function getBugs()
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_BUGTRACKER . " ORDER BY timestamp DESC");
	}
	
	public function getBug($id)
	{
		return $this->request->firstQuery("SELECT * FROM " . TABLE_BUGTRACKER . " WHERE id='".$id."'");
	}
}