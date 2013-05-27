<?php
class Help extends AndroidPage
{
	private $nbr_page = 8;
	
	public function construct()
	{
	}
	public function prerender()
	{
		parent::prerender();
		
		if(!$this->androidView)
		{
			$this->setTemplate(TPL."html/help.html");
			return;
		}
		
		$this->setContainer(TPL."html/help-container.html");
		
		// Get the current page
		$page = max(1, min($this->arg->int(0, 1), $this->nbr_page));
		
		// Compute the navigation vars
		$hasPrevious = $page > 1;
		$hasNext = $page < $this->nbr_page;
		
		$previous = $page - 1;
		$next = $page + 1;
		
		$this->assign("nav", array(
								"hasPrevious" => $hasPrevious, 
								"hasNext" => $hasNext, 
								"next" => $next, 
								"previous" => $previous,
								"current" => $page,
								"total" => $this->nbr_page));
		
		$this->setTemplate(TPL."html/help-".$page.".html");
	}
}