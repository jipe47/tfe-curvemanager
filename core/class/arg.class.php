<?php
/**
 * Helps the processing of arrays
 * 
 * @author Jean-Philippe Collette
 * @package Core
 * @subpackage Misc
 */
class Arg extends ArrayProcessor
{
	public function __construct()
	{
		$query_string = func_num_args() == 0 ? $_SERVER['QUERY_STRING'] : func_get_arg(0);
		
		$array_arg = !is_array($query_string) ? explode(JPHP::ARG_SEPARATOR, $query_string) : $query_string;
		parent::__construct($array_arg);
	}
	
	public static function getArg()
	{
		return self::$arg;
	}
	
	public function getAll()
	{
		return $this->arg;
	}
	
	public function makeLink($i = 0)
	{
		$stuff = array();
		
		if($i != 0)
			foreach($this->array as $k => $v)
			{
				if($i != 0)
				{
					$i--;
					continue;
				}
				$stuff[] = urlencode($v);
			}
		else
			foreach($this->array as $k => $v)
				if(!is_numeric($k))
					$stuff[] = urlencode($k)."/".urlencode($v);
				else 
					$stuff[] = urlencode($v);
		
			
		return implode("/", $stuff);
	}
	
	
	public function normalize()
	{
		$this->array = array_values($this->array);
	}
	
	public static function stringStatic($array, $field, $default = self::DEFAULT_STRING)
	{
		if(array_key_exists($array, $this->array))
			return !get_magic_quotes_gpc() ? $array[$key] : stripslashes($array[$key]);
		else
			return $default;
	}
	
	public function argc()
	{
		return $this->getNbrEntry();
	}
	
	public function after($i)
	{
		return array_slice($this->array, $i + 1);
	}
}
?>