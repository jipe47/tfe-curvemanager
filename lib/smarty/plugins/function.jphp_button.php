<?php
function smarty_function_jphp_button($params, &$smarty)
{
	if (empty($params['template'])) {
        $smarty->trigger_error("jphp_button: missing 'template' parameter.");
        return;
    }
    
    $arg = new ArrayProcessor($params);
    $tpl = $arg->string("template");
    $description = $arg->string("description");
    $name = $arg->string("name");

    $arg->unsetValue("description");
    $arg->unsetValue("template");
    $arg->unsetValue("name");
    
    $html_attr = "";
    foreach($arg->getArray() as $k => $v)
    	$html_attr .= $k.'="'.$v.'" ';
    	
    if($description != "")
    {
    	$html_attr .= 'alt="' . $description . '"';
    	$html_attr .= 'title="'.$description.'"';
    }
    
    switch($tpl)
    {
    	case "icon":
    		$html = '<img src="'.PATH_TPL_COMMON.'images/buttons/'.$name.'.png" '.$html_attr.' /';
    		return $html;
    	default:
    		$smarty->trigger_error("jphp_button: undefined template '".$tpl."'");
    		return;
    }
}