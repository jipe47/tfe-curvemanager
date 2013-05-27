<?php
class BugtrackerRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("Bugtracker");
	}
	
	public function handler()
	{
		$query = $this->arg->string(0);
		
		$this->setLocation("Bugtracker");
		switch($query)
		{
			case "add":
				$this->setLocation("Bugtracker");
				$isHuman = Post::int("captcha_answer") == Session::int("captcha_code");

				if(!$isHuman)
				{
					out::message("Wrong captcha code.", Message::ERROR);
					return;
				}
				$info = array('summary' => Post::string("summary"),
				'version' => Post::string("version"),
				'description' => Post::string("description"),
				'reproduction' => Post::string("reproduction"),
				'device' => Post::string("device"),
				'ip' => $_SERVER['REMOTE_ADDR'],
				'status' => 'open');
				
				if($info["summary"] == "")
				{
					out::message("You must fill mandatory fields.", Message::ERROR);
					return;
				}
				
				$this->request->insert(TABLE_BUGTRACKER, $info);
				out::message("The issue has been recorded in the database. Thank you for reporting it!");
				break;
				
			case "edit":
			
				if(!CM_ENABLE_REQUEST)
				{
					curvemanagerShowLockMessage();
					break;
				}
				
				if(!isAdmin())
				{
					$this->setLocation("Home");
					return;
				}
				$id = Post::int("id");
				$info = array('summary' => Post::string("summary"),
						'version' => Post::string("version"),
						'device' => Post::string("device"),
						'description' => Post::string("description"),
						'reproduction' => Post::string("reproduction"),
						'status' => Post::string("status"));
				
				$this->request->UPDATE(TABLE_BUGTRACKER, "id='".$id."'", $info);
				out::message("The issue has been updated.");
				break;
				
				
			case "delete":
				if(!CM_ENABLE_REQUEST)
				{
					curvemanagerShowLockMessage();
					break;
				}
				if(!isAdmin())
				{
					$this->setLocation("Home");
					return;
				}
				$id = $this->arg->int(1);
				$this->request->query("DELETE FROM " . TABLE_BUGTRACKER . " WHERE id='" . $id . "'");
				out::message("Issue deleted.");
				
				break;
		}
	}
}