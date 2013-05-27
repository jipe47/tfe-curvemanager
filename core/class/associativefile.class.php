<?php
class AssociativeFile
{
	private $content = array();
	private $filename = null;
	
	private $updateFile = false;
	
	public static function getContent($file)
	{
		$handle = fopen($file, 'r+');
		$array = array();
		while(($line = fgets($handle)) !== false)
		{
			if($line == "." || $line == "..")
				continue;
			
			$pos = strpos($line, ":");
			$varname = trim(substr($line, 0, $pos));
			$varvalue = trim(substr($line, $pos + 1));
			$array[$varname] = $varvalue;
		}
		fclose($handle);
		return $array;
	}
	
	public static function saveContent($file, $content)
	{
		out::message("Saving " . $file . " : " . serialize($content));
		if(file_exists($file))
			unlink($file);
		$handle = fopen($file, 'a');
		if($handle === false)
			out::message("ERROR", Message::ERROR);
		foreach($content as $k => $v)
			fputs($handle, $k.":".$v.PHP_EOL);
		fclose($handle);
		out::message("Done");
	}
	
	public function __construct($filename)
	{
		$this->filename = $filename;
		if(file_exists($filename))
			$this->content = self::getContent($filename);
	}
	
	public function dump()
	{
		return $this->content;
	}
	
	public function set($name, $value)
	{
		if(isset($this->content[$name]) && $this->content[$name] == $value)
			return;
		$this->updateFile = true;
		$this->content[$name] = $value;
	}
	
	public function get($name)
	{
		return $this->content[$name];
	}
	
	public function __destruct()
	{
		if($this->updateFile && count($this->content) > 0)
			self::saveContent($this->filename, $this->content);
	}
}