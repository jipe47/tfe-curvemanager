<?php
function smarty_function_jphp_select_country($params, $template)
{
	$countries = array_key_exists('from', $params) ? $params['from'] : Country::getCountries();
	$format = array_key_exists('format', $params) && in_array($params['format'], array("select", "checkbox")) ? $params['format'] : "select";
	$none_option = array_key_exists('none_option', $params) && $params['none_option'];
	
	$field_name = array_key_exists('field_name', $params) ? $params['field_name'] : "country";
	$field_id = array_key_exists('field_name', $params) ? ' id="'.$params['field_name'].'"' : '' ;
	$field_value = array_key_exists("field_value", $params) && array_key_exists($params['field_value'], $countries) ? $params['field_value'] : "";

	$separator_start = array_key_exists('separator_start', $params) ? $params['separator_start'] : "";
	$separator_end = array_key_exists('separator_end', $params) ? $params['separator_end'] : "<br />";
	
	$array_selected = array_key_exists('array_selected', $params) ? $params['array_selected'] : array();
	
	$showflag = array_key_exists('showflag', $params) ? $params['showflag'] : false;
	
	$uid = jphp_generateId(10);
	
	$html = "";
	switch($format)
	{	
		case "select":
			$html = '<select name="'.$field_name.'"'.$field_id.'>';
			if($none_option)
				$html .= '<option value="none">&lt;None&gt;</option>';
			foreach($countries as $i => $c)
			{
				$selected = $field_value == $i ? ' selected="selected"' : '';
				$html .= '<option value="'.$i.'"'.$selected.'>'.$c.'</option>';
			}
			$html .= '</select>';
			break;
			
		case "checkbox";
			foreach($countries as $i => $c)
			{
				$checked = in_array($i, $array_selected) ? ' checked="checked"' : '';
				$html .= $separator_start;
				
				$html .= '<input type="checkbox" id="country_'.$i.'_'.$uid.'" name="'.$field_name.'[]" value="'.$i.'"'.$checked.' />&nbsp;';
				$html .= '<label for="country_'.$i.'_'.$uid.'">';
				if($showflag)
					$html .= '<img src="'.PATH_TPL_COMMON.'images/countries/'.strtolower($i).'.png" alt="" title="" />&nbsp;';
				$html .= $c.'</label>'.$separator_end;
			}
			break;
	}
	
	return $html;
}

?>