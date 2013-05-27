<?php
function smarty_modifier_jphp_translate($string)
{
	 if(class_exists("Translator"))
    	return Translator::translate($string);
    else 
    	return "Undefined Translator";
}

?>