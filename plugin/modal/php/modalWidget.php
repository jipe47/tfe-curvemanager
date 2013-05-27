<?php
class ModalWidget extends Widget
{
	private static $lightLoaded = false;
	
	public function construct()
	{
		$this->setAccessName("basic");
		$this->addAccess(self::ALL);
	}
	
	public function render()
	{
		//$bufferize = $this->arg->bool("bufferize", false);
		$bufferize = false;
		if($bufferize)
			$id = "window_light";
		else
			$id = jphp_generateId(15);
		$this->assign("id", $id);
		$uid = jphp_generateId(10);
		// Creates the button
		if($this->arg->keyExists("button_image"))
		{
			$title = $this->arg->string("button_image_title");
			$alt = $this->arg->string("button_image_alt");
			$src = $this->arg->string("button_image");
			
			if($bufferize)
				$button = '<a href="#leanModal_window_light" onclick="leanModal_loadWindow(\''.$uid.'\')" class="leanModal_button"><img src="'. $src.'" title="'.$title.'" alt="'.$alt.'" /></a>';
			else
				$button = '<a href="#modal_'.$id.'" class="leanModal_button"><img src="'. $src.'" title="'.$title.'" alt="'.$alt.'" /></a>';
		}
		else
		{
			if($bufferize)
				$button = '<a href="#leanModal_window_light" onclick="leanModal_loadWindow(\''.$uid.'\')" class="leanModal_button">'.$this->arg->string("button_text").'</a>';
			else
				$button = '<a href="#modal_'.$id.'" class="leanModal_button">'.$this->arg->string("button_text").'</a>';
		}
		
		// Generate modal window's HTML
		$array_arg = $this->arg->getArray();
		$array_arg["title"] = $this->arg->string("title");
		foreach($array_arg as $k => $v)
			$this->assign($k, $v);
		
		// Add classes to window
		$this->assign("class", array_key_exists("class", $array_arg) ? $array_arg["class"] : "");
		
		// Stylize the window
		$array_style_attribute = array("width", "height");
		
		if(count(array_intersect($array_style_attribute, array_keys($array_arg))) > 0)
		{
			$style = ' style="';
			
			foreach($array_style_attribute as $a)
				if(array_key_exists($a, $array_arg))
					$style .= $a.': '.$array_arg[$a].';';
			
			$style .= '"';
		}
		else
			$style = "";
		$this->assign("style", $style);
		
		if($this->arg->keyExists("content_template"))
		{
			$content = $this->renderFile($this->arg->string("content_template"));
			$this->assign("content", $content);
		}
		else
			$content = "";
		
		
		try
		{
			JPHP::addOnLoadFunction("initJQueryLeanModal()");
		}
		catch(Exception $e)
		{
		}
		
		
		if($bufferize)
		{
			$title = $array_arg['title'];
			JPHP::addToBuffer('<div id="leanModal_buffer_title_'.$uid.'">'.$title.'</div>');
			JPHP::addToBuffer('<div id="leanModal_buffer_content_'.$uid.'">'.$content.'</div>');
			
			if(!self::$lightLoaded)
			{
				self::$lightLoaded = true;
				$this->setTemplate($this->path_html."window_light.html");
				JPHP::addToBuffer($this->renderTemplate());
			}
		}
		else
		{
			$this->setTemplate($this->path_html."window.html");
			JPHP::addToBuffer($this->renderTemplate());
		}
		
		
		
		// Create the button
		//return '<a href="#'.$id.'" class="leanModal_button"><input type="button" value="Display" /></a>';
		return $button;
	}
}