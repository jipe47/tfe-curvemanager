<?php
class jphpRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("jphp");
	}
	
	public function handler()
	{
		switch($this->arg->string(0))
		{
			case "flushcache":
				unset($_SESSION["jphp_cache"]);
				FileProcessing::deleteFile(PATH_CACHE.Cache::$filename);
				break;
				
			case "login":
				$this->setLocation("Timesheet");
				$login = mysql_real_escape_string(Post::string("login"));
				$password = mysql_real_escape_string(Post::string("password"));
				$remember = Post::boolValue("remember", "on", false);
				
				$info_login = $this->request->firstQuery("SELECT * FROM " . TABLE_USER . " WHERE login='" . $login . "'");
				
				if(count($info_login) == 0)
				{
					out::message("Undefined login.", Message::ERROR);
					break;
				}
				
				if(md5($password) != $info_login['password'])
				{
					out::message("Wrong password.", Message::ERROR);
					break;
				}
				
				$_SESSION['isAdmin'] = $info_login['id'];
				
				if($remember)
				{
					setcookie(STRUCTURE_NAME."_login", $login, time() + 3600*24*365);
					setcookie(STRUCTURE_NAME."_password", md5($password), time() + 3600*24*365);
				}
				$this->setLocation("AdminPanel");
				break;
				
			case "login_cookie":
				$back = explode(array_slice($this->arg->getArray(), 1));
				$this->setLocation($back);
				
				$login = mysql_real_escape_string(Cookie::string(STRUCTURE_NAME."_login"));
				$password = mysql_real_escape_string(Cookie::string(STRUCTURE_NAME."_password"));
				$info_login = $this->request->firstQuery("SELECT * FROM " . TABLE_USER . " WHERE login='" . $login . "'");
				
				if(count($info_login) == 0 || $password != $info_login['password'])
				{
					setcookie(STRUCTURE_NAME."_login", '', 0);
					setcookie(STRUCTURE_NAME."_password", '', 0);
					break;
				}
				$_SESSION['isAdmin'] = true;
				break;
				
			case "logout":
				$back = implode(JPHP::ARG_SEPARATOR, array_slice($this->arg->getArray(), 1));
				$this->setLocation($back);
				$_SESSION['isAdmin'] = false;
				setcookie(STRUCTURE_NAME."_login", '', 0);
				setcookie(STRUCTURE_NAME."_password", '', 0);
				break;
		}
		
	}
}