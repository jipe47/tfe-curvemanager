<?php
class UserAndroidAjax extends AjaxPage
{
	public function construct()
	{
		$this->setAccessName("UserAndroid");
		$this->registerHandler("android", "handler_android");
	}
	
	public function handler_android()
	{
		$query = $this->arg->string(0);
		
		switch($query)
		{
			case "resetStat":
				if(!CM_ENABLE_ACCOUNTRESET)
					break;
				$id_user = Post::int("id_user");
				if($id_user == -1)
					return "";
				$this->request->update(TABLE_PREDICTION, "id_user='". $id_user . "'", array('id_user' => 'NULL'));
				$this->request->query("DELETE FROM " . TABLE_USER_GROUP . " WHERE id_user='" . $id_user . "'");
				break;
			case "updateNickname":
				if(!CM_ENABLE_ACCOUNTRESET)
					break;
				$nickname 	= Post::string("nickname");
				$id_user 	= Post::int("id_user");
				if($id_user == -1 || $nickname == "")
					break;
					
				$this->request->update(TABLE_USER_ANDROID, "id='".$id_user."'", array('nickname' => $nickname));
				break;
			case "getInfo":
				$id 		= Post::int("id_user");
				$country 	= Post::string("country");
				$account 	= Post::string("account");
				$account_res = $account; // Used to not loose the account name in case of a account reset.
				$reset		= Post::boolValue("reset", "yes", false);
				
				// If the user asked for an account reset, set id to -1 and remove the existing account.
				
				if($reset && CM_ENABLE_ACCOUNTRESET)
				{
					$this->request->update(TABLE_USER_ANDROID, 'id="'.$id.'"', array('account' => ""));
					$account_res = "";
					$id = -1;
					$info = false;
				}
				else
					$info = $id == -1 ? false : $this->model->getUserById($id);
				
				// If the user started the application for the first time...
				if($info === false)
				{
					// Check if a user with a similar Google account exists. If yes, it is the current user
					if($account_res != "")
					{
						$info = $this->model->getUserByAccount($account_res);
						if($info !== false)
						{
							$id = $info['id'];
							$nickname = $info['nickname'];
							$score = $info['score'];
						}
					}
					
					if($id == -1)
					{
						$array_info = array('ip' => $_SERVER['REMOTE_ADDR'], 'country' => $country);
						
						if($account != "")
							$array_info['account']= $account;
							
						$this->request->insert(TABLE_USER_ANDROID, $array_info);
						$id = $this->request->getLastId();
						$score = 0;
						$nickname = "";
					}
				}
				else
				{
					$score 		= $info['score'];
					$nickname 	= $info['nickname'];
				}
				$nbr_user = $this->request->count(TABLE_USER_ANDROID);
				$rank = $this->request->count(TABLE_USER_ANDROID, "score >= " . intval($score));
				$nbr_group = $this->request->count(TABLE_CURVE_GROUP);
				$groups = Plugins::getPlugin("Curve")->getDefaultModel()->getGroupByUser($id);
				return json_encode(array('id' => $id, 'score' => intval($score), 
				'rank' => intval($rank), "nickname" => $nickname, "nbr_user" => $nbr_user,
				'position_value' => intval($groups["VALUE"]["position"]), 'position_pm' => intval($groups["PM"]["position"]), 'position_trend' => intval($groups["TREND"]["position"]),
				'nbr_group' => $nbr_group
				));
				
			case "getStat":
                //$id = Post::int("id");
                $id = $this->arg->int(1);
                
                if($id == -1)
                {
                    $this->request->insert(TABLE_USER_ANDROID, array('ip' => $_SERVER['REMOTE_ADDR']));
                    $id = $this->request->getLastId();
                }
                $user_stat = $this->model->getUserStat($id);
				$global_stat = $this->model->getGlobalStat();
				
				$stat = array(	'global' => $global_stat, 
								'user' => $user_stat);
								
                return json_encode($stat);
		}
	}
}