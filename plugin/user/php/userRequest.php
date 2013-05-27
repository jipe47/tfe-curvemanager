<?php
class UserRequest extends RequestPage
{
	public function construct()
	{
		$this->setAccessName("User");
		$this->registerHandler("connection", "handler_connection");
		$this->registerHandler("admin", "handler_admin");
		/*if(JPHP::getArg(1) == "Request" && JPHP::getArg(2) == "User" && JPHP::getArg(3) == "connection")
		{
			out::message("Setting all access");*/
			$this->addAccess(self::ALL);
		//}
	}
	
	public function handler_admin()
	{
		$this->setLocation("Home");
		if(!CM_ENABLE_REQUEST)
		{
			curvemanagerShowLockMessage();
			return;
		}
		
		if(!isAdmin())
		{
			out::message("You must be identified to access to this page.", Message::ERROR);
			$this->setLocation(JPHP::get("default_page"));
			return "";
		}
		$query = $this->arg->string(0);
		switch($query)
		{
			case "addedit":
				$id 		= Post::int("id");
				$pass1 		= Post::value("pass1");
				$pass2 		= Post::value("pass2");
				$email 		= Post::value("email");
				$login 		= Post::value("login");
				$firstname 	= Post::value("firstname");
				$lastname 	= Post::value("lastname");
			
				$cnt_login = $this->request->count(TABLE_USER, "login", $login);
				
				if($id == -1)
				{
					if($cnt_login == 0)
					{
						$cnt_email = $this->request->count(TABLE_USER, "email", $email);
							
						if($cnt_email == 0)
						{
							if($pass1 == $pass2 && $pass1 != "")
							{
								$this->request->insert(TABLE_USER, array('password' => md5($pass1), 'login' => $login, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email));
								$id_user = $this->request->getLastId();
								out::message("You successfully created a user.", Message::SUCCESS);
									
							}
							else
								Messages::add("Passwords are different.", Message::ERROR);
						}
						else
							Messages::add("E-mail already registered.", Message::ERROR);
					}
					else
						Messages::add("Login already exists.", Message::ERROR);
				}
				else
				{
					$a = array('login' => $login, 'firstname' => $firstname, 'lastname' => $lastname, 'email' => $email);
					
					if($pass1 != "")
					{
						if($pass1 == $pass2)
							$a['password'] = md5($pass1);
						else
							out::message("Password not updated since you entered different passwords.", Message::ERROR);
					}
					
					$this->request->update(TABLE_USER, "id='".$id."'" , $a);
					out::message("User updated.");
				}
				break;
			case "delete":
				$id = $this->arg->int(1);
				$this->request->query("DELETE FROM " . TABLE_USER . " WHERE id='".$id."'");
				out::message("User deleted.");
		}
		$this->setLocation("Admin/User/list");
		return;
	}

	public function handler_connection()
	{
		$query = $this->arg->string(0);
		switch($query)
		{
			case "login_cookie":
			case "login":
				$fromCookie = ($query == "login_cookie");
				if($fromCookie)
				{
					$login = $_COOKIE[STRUCTURE_NAME.'_login'];
					$pass = $_COOKIE[STRUCTURE_NAME.'_password'];
					$remember = "on";
					if(count($this->arg->argc()) == 0)
						$back = JPHP::get("default_page");
					else
						$back = implode(JPHP::ARG_SEPARATOR, $this->arg->getArray());
				}
				else
				{
					$login = Post::value("login_login");
					$pass = Post::value("login_password");
					$remember = Post::value("login_remember", "");
					$back = Post::boolValue("back", "on");
						
					$pass = md5($pass);
				}

				$info = $this->request->firstQuery("SELECT * FROM " . TABLE_USER . " WHERE login='" . $login . "'");

				$fail = true;
				$m = "";
				if($login != $pass && $pass != "")
				{
					if($this->request->getNbrResponse() == 1)
					{
						if($info['password'] == $pass)
						{
							User::loadId($info['id']);
							$fail = false;
							if($remember)
							{
								setcookie(STRUCTURE_NAME."_login", $login, time() + 3600*24*365);
								setcookie(STRUCTURE_NAME."_password", $pass, time() + 360*24*365);
							}
							$back = Post::string("back");
							if($back == "")
								$back = "Home";
							if(!$fromCookie)
								$m = "You are now connected.";
						}
						else if(!$fromCookie)
							$m = "Bad password.";
					}
					else if(!$fromCookie)
						$m = "Bad login.";
				}
				else if(!$fromCookie)
					$m = "Form not completed.";

				if($fromCookie && $fail)
				{
					setcookie(STRUCTURE_NAME."_login", "", 0);
					setcookie(STRUCTURE_NAME."_password", "", 0);
				}
				
				if($m == "")
					break;
					
				$m_type = $fail ? Message::ERROR : Message::SUCCESS;
				out::message($m, $m_type);
				
				if($fail)
					$back = "Login";
				break;


			case "logoff":
				$_SESSION = array();
				session_destroy();
				setcookie(STRUCTURE_NAME."_login", 0, 0);
				setcookie(STRUCTURE_NAME."_password", 0, 0);
				Messages::add("You have been disconnected.", Message::SUCCESS);
				$back = implode(JPHP::ARG_SEPARATOR, $arg);

				if($back == "")
				$back = JPHP::get("default_page");
				break;

			
			case "retreiveaccount":
					
				$data = Post::value("email");
					
				$count = $this->request->count(TABLE_USER, "email='".$data."'");
					
				if($count == 1)
				{
					$code = jphp_generateId(50);
					$pass = Post::value("pass1");
					$pass2 = Post::value("pass2");
						
					if($pass != "" && $pass == $pass2)
					{
						$info = $this->request->firstQuery("SELECT email FROM " . TABLE_USER . " WHERE email='" . $data . "'");
							
						$link = "?Request/".STRUCTURE_NAME."/retreiveaccountconfirm/".$code;
							
						$mail = new Mail($email, JPHP::get("title_prefix", STRUCTURE_NAME." - ")."New Password", "Your password has been reseted. <a href=\"".URL_SITE.$link."\">Please clic on this link to confirm your new password</a>.");
						$mail->addHeader("From", EMAIL);
						$mail->send();
						$this->request->update(TABLE_USER, "email='".$data."'", array('confirmation_code' => $code, 'confirmation_new_password' => md5($pass)));
						Messages::add("An e-mail has been sent to confirm your new password.", Message::SUCCESS);
					}
					else
					Messages::add("Passwords are different.", Message::ERROR);
				}
				else
				Messages::add("Email does not exist in database.", Message::ERROR);
					
				$back = "Login";
					
				break;
					
			case "retreiveaccountconfirm":
				if(!empty($arg[1]))
				{
					$code = mysql_real_escape_string($arg[1]);
						
					$info = $this->request->firstQuery("SELECT id, confirmation_new_password FROM " . TABLE_USER . " WHERE confirmation_code='".$code."'");
						
					if($this->request->getNbrResponse() == 1)
					{
						$this->request->update(TABLE_USER, "id='" . $info['id']."'", array('password' => $info['confirmation_new_password'], 'confirmation_new_password' => 'NULL', 'confirmation_code' => 'NULL'));
						Messages::add("Your password has been updated.", Message::SUCCESS);
					}
					else
					Messages::add("Unknown code.", Message::ERROR);
				}
				else
				Messages::add("Code not specified.", Message::ERROR);
					
				$back = "Login";
				break;
		}
		$this->setLocation($back);
	}
}