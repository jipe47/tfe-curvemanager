<?php
class CategoryModel extends Model
{
	public function insertCategory($name, $id_parent = "", $icon = "")
	{
		
		$array_sql = array('name' => $name, 'position' => $this->getNbrCategory($id_parent));
		
		if($id_parent != "")
			$array_sql['id_parent'] = $id_parent;
			
		if($icon != "")
			$array_sql['icon'] = $icon;
			
		$this->request->insert(TABLE_CATEGORY, $array_sql);
		return $this->request->getLastId();
	}
	public function getCategoryById($id)
	{
		return $this->request->firstQuery("SELECT * FROM " . TABLE_CATEGORY . " WHERE id='" .$id . "'");
	}
	
	public function getRawCategories()
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_CATEGORY . " ORDER BY name");
	}
	public function getCategories($id_skip = -1)
	{
		$all = $this->request->fetchQuery("SELECT * FROM " . TABLE_CATEGORY . " ORDER BY id_parent, position");
		$tree = new Tree();
		$a = array();
		foreach($all as $c)
			$a[] = array('id' => $c['id'], 'value' => $c, 'id_parent' => $c['id_parent']);
	
		$tree->addAll($a);
		return $tree->getArray($id_skip);
	}
	public function getCategoryArborescenceByIdParent($id_parent)
	{
		if($id_parent == -1)
			$id_parent = "";
		
		$all = $this->getCategories(-1);
		$array_cat = CategoryAdmin::static_traverse($all);
		$array_cat_byid = array();
		
		foreach($array_cat as $c)
			$array_cat_byid[$c['id']] = $c;

		// Generating path from any category to root
		$array_pathtoroot = array();
		
		while($id_parent != "")
		{
			$array_pathtoroot[] = $array_cat_byid[$id_parent];
			$id_parent = $array_cat_byid[$id_parent]['id_parent'];
		}
		return array_reverse($array_pathtoroot);
	}
	public function getSubCategories($id_parent)
	{
		$where = $id_parent == "" || $id_parent == -1 ? "IS NULL" : "='" .$id_parent."'";
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_CATEGORY . " WHERE id_parent " .$where);
	}
	public function getNbrCategory($id_parent)
	{
		$where = $id_parent == "NULL" ? "id_parent IS NULL" : "id_parent='" . $id_parent."'";
		out::message("where = " . $where);
		return $this->request->count(TABLE_CATEGORY, $where);
	}
	
	public function getCategoriesInTreeStructure()
	{
		$all_cat = $this->request->fetchQuery("SELECT c.*, cn.nbr_curve FROM " . TABLE_CATEGORY . " c
										LEFT JOIN (SELECT COUNT(*) as nbr_curve, id_category FROM " . TABLE_CURVE . " 
										GROUP BY id_category ) cn ON cn.id_category = c.id
									ORDER BY c.name");
		
		$items = array();
		
		// From http://stackoverflow.com/questions/8840319/build-a-tree-from-a-flat-array-in-php
		foreach($all_cat as $c)
			$items[] = (object) array('id' => $c['id'], 'name' => $c['name'], 'id_parent' => intval($c['id_parent']), 'nbr_curve' => intval($c['nbr_curve']));
		
		$childs = array();
		
		foreach($items as $item)
		    $childs[$item->id_parent][] = $item;
		
		foreach($items as $k => $item) 
			if (isset($childs[$item->id]))
		    	$item->childs = $childs[$item->id];
			else
				$item->childs = array();
		return $childs[0];
	}
}