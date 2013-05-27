<?php
class Category
{
	private static $cat = null;
	private static $loaded = false;
	public static function load()
	{
		$request = new SqlRequest();
		
		$array_sql = $request->fetchQuery("SELECT * FROM " . TABLE_CATEGORY);
		self::$cat = array();
		foreach($array_sql as $s)
			self::$cat[$s['id']] = $s;
	} 
	
	public static function checkLoad()
	{
		if(self::$loaded)
			return;
		self::$loaded = true;
		self::load();
	}
	
	public static function getAncestors($id, $includeId = false)
	{
		self::checkLoad();
		$array_ancestor = array();
		$c = $id;
		while(array_key_exists($c, self::$cat))
		{
			$array_ancestor[] = self::$cat[$c];
			$c = self::$cat[$c]['id_parent'];
		}
		if(!$includeId)
			array_shift($array_ancestor);
		return array_reverse($array_ancestor);
	}
}