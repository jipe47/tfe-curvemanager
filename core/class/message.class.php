<?php
/**
 * Represents a status message.
 * 
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage IO
 */
class Message extends Page
{
	/**
	 * Message content.
	 * @var string
	 */
	private $message;
	
	/**
	 * Message type (see constants).
	 * @var string
	 */
	private $type;
	
	/**
	 * Info message.
	 * @var string
	 */
	const INFO = "info";
	/**
	 * Error message.
	 * @var string
	 */
	const ERROR = "error";
	
	/**
	 * Success message.
	 * @var string
	 */
	const SUCCESS = "success";
	
	/**
	 * Warning message.
	 * @var string
	 */
	const WARNING = "warning";
	
	/**
	 * Validation message.
	 * @var string
	 */
	const VALIDATION = "validation";
	
	public function constructArg($arg)
	{
		$this->addAccess(self::ALL);
		$this->setTemplate(PATH_TPL_COMMON."html/message.html");
		$this->assign("type", $arg[1]);
		$this->assign("message", $arg[0]);
		$this->setFullRender(false);
		$this->showHeaders(false);
	}
}
?>