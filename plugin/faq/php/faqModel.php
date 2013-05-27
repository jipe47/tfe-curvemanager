<?php
class FaqModel extends Model
{
public function getFaq()
	{
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_FAQ . " ORDER BY position");
	}
	
	public function getFaqById($id)
	{
		return $this->request->firstQuery("SELECT * FROM " . TABLE_FAQ . " WHERE id='" .$id."'");
	}
	
	public function getNbrFaq()
	{
		return $this->request->count(TABLE_FAQ);
	}
}