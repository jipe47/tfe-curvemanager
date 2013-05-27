<?php
class BugtrackerPlugin extends Plugin
{
	private $array_status = array("open" => "Opened", "closed" => "Closed", "wontfix" => "Will not be fixed");
	private $array_android = array(
			
			array("version" => "1.0", "name" => ""),
			array("version" => "1.1", "name" => ""),
			array("version" => "1.5", "name" => "Cupcake"),
			array("version" => "1.6", "name" => "Donut"),
			array("version" => "2.0 ", "name" => "Eclair"),
			array("version" => "2.0.1", "name" => "Eclair"),
			array("version" => "2.1", "name" => "Eclair"),
			array("version" => "2.2-2.2.3", "name" => "Froyo"),
			array("version" => "2.3-2.3.2", "name" => "Gingerbread"),
			array("version" => "2.3.3", "name" => "Gingerbread"),
			array("version" => "2.3.4", "name" => "Gingerbread"),
			array("version" => "2.3.5", "name" => "Gingerbread"),
			array("version" => "2.3.6", "name" => "Gingerbread"),
			array("version" => "2.3.7", "name" => "Gingerbread"),
			array("version" => "3.0", "name" => "Honeycomb"),
			array("version" => "3.1", "name" => "Honeycomb"),
			array("version" => "3.2", "name" => "Honeycomb"),
			array("version" => "4.0-4.0.2", "name" => "Ice Cream Sandwich"),
			array("version" => "4.0.3-4.0.4", "name" => "Ice Cream Sandwich"),
			array("version" => "4.1", "name" => "Jelly Bean"),
			array("version" => "4.2", "name" => "Jelly Bean")			
			);
	public function __construct()
	{
		$this->setPluginName("Bugtracker");
		$this->addSqlTable("bugtracker");
	}
	
	public function getStatus()
	{
		return $this->array_status;
	}
	
	public function getAndroidVersion()
	{
		return $this->array_android;
	}
}