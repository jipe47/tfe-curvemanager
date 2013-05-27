<?php
class File
{
	private $file;
	private $file_res;
	private $opened = false;
	
	public function __construct($file, $open = true)
	{
		$this->file = $file;
		
		if($open)
			$this->openFile($file);
	}
	
	private function openFile()
	{
		if($this->opened)
			return;
		$file = func_num_args() == 0 ? $this->file : func_get_arg(0);
		$this->file_res = fopen($file, "w+");
		$this->opened = true;
	}
	
	public function write($data)
	{
		$this->openFile();
		fputs($this->file_res, $data);
	}
	
	public function getContents()
	{
		return file_get_contents($this->file);
	}

	public function __destruct()
	{
		if($this->opened)
			fclose($this->file_res);
	}
}