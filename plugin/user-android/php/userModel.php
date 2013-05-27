<?php
class UserAndroidModel extends Model
{
	public $HIGHSCORE_MAX_PLAYER = 20;
	
	public function getNbrUser()
	{
		return $this->request->count(TABLE_USER_ANDROID);
	}
	public function getNewUserMeasure($nbr_day_before)
	{
		$array_user = $this->request->fetchQuery("
				SELECT COUNT(*) as nbr, timestamp_add
				FROM " . TABLE_USER_ANDROID . "
				WHERE timestamp_add > DATE_SUB(now(), INTERVAL ".$nbr_day_before." DAY)
				GROUP BY DAY(timestamp_add)
				");
		
		return $array_user;
	}
	public function getUserById($id_user)
	{
		$info = $this->request->firstQuery("	SELECT u.*
											FROM " . TABLE_USER_ANDROID . " u
											WHERE u.id='" . $id_user . "'");
		
		return empty($info) ? false : $info;
	}
	public function getUserByAccount($account)
	{
		$info = $this->request->firstQuery("	SELECT u.*
											FROM " . TABLE_USER_ANDROID . " u
											WHERE u.account='" . $account . "'");
		
		return empty($info) ? false : $info;
	}
	public function getUserStat($id)
	{
		$array = $this->request->firstQuery("   
			SELECT u.nickname, u.score, u.id,
				(SELECT COUNT(*) FROM " . TABLE_USER_ANDROID . " WHERE u.score >= score) as rank
			
			FROM " . TABLE_USER_ANDROID . " u
			WHERE u.id='" . $id . "'");

		return SqlRequest::removeIntKeys($array);
	}
	
	public function getGlobalStat()
	{
		$global = array();
		$count_pred = $this->request->fetchQuery("
			SELECT type, COUNT(*) as cnt
			FROM " . TABLE_PREDICTION . "
			GROUP BY input
			");
		$count_player = $this->request->count(TABLE_USER_ANDROID);
		$global['general'] = $count_pred;
		$global['player'] = array('nbr_player' => $count_player);
		
		return SqlRequest::removeIntKeys($global);
	}
	
	public function getHighScore($maxplayer = -1)
	{
		if($maxplayer < 0)
			$maxplayer = $this->HIGHSCORE_MAX_PLAYER;
		return $this->request->fetchQuery("SELECT * FROM " . TABLE_USER_ANDROID . " WHERE score > 0 ORDER BY score DESC LIMIT " . $maxplayer);
	}
	// /!\ Not complet !!!
	public function getHighScoreByGamemode($gm)
	{
		return $this->request->fetchQuery("SELECT u.nickname, u.country, 
		FROM " . TABLE_PREDICTION . " p
		LEFT JOIN " . TABLE_USER_ANDROID . " u
		ON p.id_user = u.id");
	}
}