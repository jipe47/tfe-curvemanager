<?php
/**
 * Displays an amazing debug page.
 *
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class Debugger extends Page
{
	public function construct()
	{
		$this->setFullRender(false);
		$this->showHeaders(false);
		$this->setTemplate(PATH_TPL_COMMON."html/debug.html");
		//out::message("Aaaaa");
		$this->addAccess(self::ALL);
	}
	public function prerender()
	{
		//out::message("Bbbbbb");
		if(!JPHP::debug())
			return;
		//JPHP::addOnloadFunction("debug_init()");
		$this->assign('nbrRequest', $this->request->getNbrRequest());
		
		$array_chrono = $this->chrono->getTimes();
		foreach($array_chrono as $n => $v)
			$array_chrono[$n] = round($v * 1000);
		
		$this->assign('array_chrono', $array_chrono);
		arsort($array_chrono);
		$this->assign('array_chrono_slow', array_slice($array_chrono, 0, 10));
		
		
		$this->assign('array_request', $this->request->getRequests());
		$this->assign('array_log', Log::getEntries());
		$this->assign('array_session', $_SESSION);
		$this->assign('array_plugin', Plugins::getPlugins());
		$this->assign('array_cookie', ((count($_COOKIE) > 0) ? $_COOKIE : array()));
		$this->assign("array_right", User::getRight());
		$this->assign("array_group", implode(",", User::getGroup()));
		$this->assign("array_ini", JPHP::getAllIni());
		$this->assign("array_server", $_SERVER);
		$this->assign("array_all_right", JPHP::getRight());
		$this->assign("array_pageRegister", PageRegister::getRegister());
		$this->assign("array_config", class_exists("Config") ? Config::dump() : array());
		$this->assign("array_notify", Cache::getTimes());
		
		$array_cache = array();
		if(file_exists(PATH_CACHE.Cache::$filename))
			$array_cache = unserialize(file_get_contents(PATH_CACHE.Cache::$filename));
		$this->assign("array_cache", $array_cache);
		
		
		$array_panel = array("cache_notification", "cache", "config", "chrono", "user", "sql", "right", "plugin", "cookie", "session", "log", "pageRegister");
		$this->assign("array_panel", $array_panel);
	}
}


?>