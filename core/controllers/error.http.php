<?php
/**
 * Displays a HTTP error.
 *
 * @author Jean-Philippe Collette
 * @package Controllers
 */
class HttpError extends Error
{
	private $array_error = array("404" => "");
	private $default_message = "Undefined HTTP error.";
	public function constructArg($arg)
	{
		$this->addAccess(self::ALL);
		$this->setTitle("HTTP Error");
		$this->code = $arg[0];
		if(array_key_exists($this->code, $this->array_error))
			$this->setTemplate(PATH_TPL_COMMON."html/error/".$this->code.".html");
		else
			$this->desc = $this->default_message;
	}
}
?>