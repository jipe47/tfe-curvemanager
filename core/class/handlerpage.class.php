<?php
/**
 * Makes a class able to define handlers.
 *
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Page
 */
abstract class HandlerPage extends Page
{
	private $handlers = array();
	private $defaultHandler = null;
	
	public function selfRender()
	{
		$arg = $this->arg;
		return $this->handler();
	}
	
	/**
	 * Registers a handler.
	 * @param string $handler Handler name.
	 * @param string $methodname Method name associated to the handler.
	 */
	protected function registerHandler($handler, $methodname)
	{
		$this->handlers[$handler] = $methodname;
	}
	
	/**
	 * Specify a default handler.
	 * @param string $methodname Method name.
	 */
	protected function registerDefaultHandler($methodname)
	{
		$this->defaultHandler = $methodname;
	}
	// Executed if not overloaded by subclass
	
	/**
	 * Executes a handler based on the page arguments.
	 * @param array $arg arguments
	 * @throws Exception Throwned if the handler does not exist and 
	 * 					 if no default handler is specified.
	 */
	public function handler()
	{
		$argc = $this->arg->argc();
		if($argc == 0)
		{
			if($this->defaultHandler == null)
				throw new Exception("Undefined (default) handle name (1).");
			else
				$handlername = $this->defaultHandler;
		}
		else 
		{
			if(array_key_exists($this->arg->string(0), $this->handlers))
				$handlername = $this->arg->string(0);
			else if($this->defaultHandler != null)
				$handlername = $this->defaultHandler;
			else
				throw new Exception("Undefined (default) handle name (2).");
		}
		
		$fromDefault = ($argc == 0 && $this->defaultHandler != null) || ($argc > 0 && !array_key_exists($this->arg->string(0), $this->handlers) && $this->defaultHandler != null);
		
		if(!$fromDefault)
		{
			$this->arg->unsetValue(0);
			$this->arg->normalize();
		}
		
		$name = $this->arg->string(0);
		//$classname = self::getPageClass($name, $this->getClassType($name));
		//JPHP::getPageInstance($name, $page)
		$methodname = $fromDefault ? $this->defaultHandler : $this->handlers[$handlername];
		
		return $this->$methodname();
	}
}