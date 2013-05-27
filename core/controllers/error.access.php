<?php
/**
 * Displays an access error.
 *
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class AccessError extends Error
{
	protected $accessError;
	// Arguments : array(code, desc, access)
	public function constructArg($arg)
	{
		$this->addAccess(self::ALL);
		$this->setTitle("Access Error");
		$a = $arg[2];
		$this->code = "44";
		
		if($a['admin_only'])
			$this->desc = "Only administrators can access to this page.";
		else
		{
			$right = count($a['right']) > 0;
			$group = count($a['group']) > 0;
			
			$this->desc = "<p>Vous devez possÃ©der les attributs suivants:</p><ul>";
			if(!$a['anonymous'])
				$this->desc .= "<li><a href='?Login'>Etre identifié</a></li>";
			
			if($right)
			{
				foreach($a['right'] as $r)
					$this->desc .= "<li>Appartenir au groupe <strong>".$r."</strong></li>";
			}
			if($group)
			{
				$info_group = $this->request->fetchQuery("SELECT id, name FROM " . TABLE_GROUP . " WHERE id IN (".implode(",", $a['group']).")");
				
				foreach($info_group as $g)
					$this->desc .= "<li>Posséder le droit <strong>".$g['name']."</strong></li>";
				
			}
			$this->desc .= "</ul>";
		}
	}
}
?>