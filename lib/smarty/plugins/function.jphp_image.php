<?php
function smarty_function_jphp_image($params, &$smarty)
{
	if (empty($params['src'])) {
        $smarty->trigger_error("jphp_image : missing 'src' parameter.");
        return;
    }
    
    $src = $params['src'];
    unset($params['src']);
    
    if(!file_exists($src) && !empty($params['default']))
    {
    	$src = $params['default'];
    	unset($params['default']);
    }
    
  //  $html = '<img src="'.$src.'"';
  	$html = '<img ';
    $html_prefix = "";
    $html_suffix = "";
    $arg = new ArrayProcessor($params);
    
    $array_ignore = array("transform_width", "transform_height", "default");
    $array_attr = array("style" => "", "src" => $src);
    
    foreach($params as $key => $value)
    {
    	if(in_array($key, $array_ignore))
    		continue;
    		
    	switch($key)
    	{
    		case "description":
    			$array_attr["alt"] = $value;
    			$array_attr["title"] = $value;
    			break;
    		case "template":
    			$template_img = $arg->string("template_img");
    			$array_template = array(
    				"icon" => PATH_TPL_COMMON."images/icon/".$template_img.".png",
    				"button" => PATH_TPL_COMMON."images/buttons/".$template_img.".png");
    			$array_attr["src"] = $array_template[$value];
    			break;
    		case "link":
    			$html_prefix .= '<a href="'.$value.'">';
    			$html_suffix .= '</a>';
    			break;
    		case "transform":
    			if(!$arg->keyExists("transform_width"))
    			{
					$smarty->trigger_error("jphp_image: missing 'transform_width' parameter for image transformation.");
					return;
    			}
    	
    			if(!$arg->keyExists("transform_height"))
    			{
					$smarty->trigger_error("jphp_image: missing 'transform_height' parameter for image transformation.");
					return;
    			}
    			
    			$width = $arg->int("transform_width");
    			$height = $arg->int("transform_height");
    			
    			switch($value)
    			{
    				case "crop":
    					$new_dimension = ImageProcessing::computeOffset($src, $width, $height);
    					
    					$html_prefix .= '<div style="overflow: hidden; width: ' . $width . 'px, height: ' . $height . 'px">';
    					$html_suffix .= '</div>'; 
    					
    					$width = $new_dimension["width"];
    					$height = $new_dimension["height"];
    					
    					$array_attr["style"] .= 'margin-top: -'.$new_dimension['offsetY'].'px; margin-left: -'.$new_dimension['offsetX'].'px;';
    					break;
    					
    				case "resize":
    					$dimensions = getimagesize($src);
    					$new_dimension = ImageProcessing::computeDim($dimensions[0], $dimensions[1], $width, $height);
    					$width = $new_dimension["width"];
    					$height = $new_dimension["height"];
    					$array_attr["style"] .= 'width: '.$width . 'px; height: ' . $height.'px;';
    					break;	
    				
    				default:
    					$smarty->trigger_error("jphp_image: undefined transformation operation '".$value."'");
    					return;
    			}
    			
    			$array_attr["width"] = $width;
    			$array_attr["height"] = $height;
    			
    			unset($params["transform_width"]);
    			unset($params["transform_height"]);
    			break;
    		default:
    			$array_attr[$key] = $value;
    	}
    }
    
    foreach($array_attr as $key => $value)
    	if($value != "")
    		$html .= ' '.$key.'="'.$value.'"'; 
    $html .= '/>';
    return $html_prefix.$html.$html_suffix;
}