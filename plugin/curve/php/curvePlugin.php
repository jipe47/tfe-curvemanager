<?php
class CurvePlugin extends Plugin
{	
	public $nbr_curve_per_page = 10;
	public function __construct()
	{
		parent::__construct();
		$this->setPluginName("Curve");
		$this->addAdminLink("Add a curve", "add");
		$this->addAdminLink("Manage curves", "list");
		$this->addAdminLink("Manage levels", "group");
		$this->addAdminLink("Manage partial curves", "partial");
		$this->addAdminLink("View monitored curves", "monitor");
		$this->addAdminLink("Benchmark", "benchmark");
		$this->addSqlTable("curve");
		$this->addSqlTable("curve_zone");
		$this->addSqlTable("curve_group");
		$this->addSqlTable("user_group");
		$this->addSqlTable("prediction");
		$this->addSqlTable("prediction_algorithm");
		$this->addSqlTable("algorithm");
	}
}