<?php
class UserModel extends Model
{
	public function getUser($id)
	{
		$user = $this->request->firstQuery("	SELECT u.* 
										FROM " . TABLE_USER . " u
										WHERE u.id='" . $id . "'");
		return $user;
	}
	
	public function searchUser()
	{
		return $this->request->fetchQuery("	SELECT u.*
											FROM " . TABLE_USER . " u
											ORDER BY lastname, firstname");
	}
	
	public function makeLinkById($id)
	{
		return $this->makeLinkByInfo(user_getUser($id));
	}
	
	public function makeLinkByInfo($info)
	{
		return '<a href="?Member/'.$info['id'] . '">'.$info['firstname']. ' ' .$info['lastname'].'</a>';
	}
}