<?php
// Iso 2-alpha to country flag
function smarty_modifier_jphp_countryFlag($string)
{
	$url = Country::getFlag($string, false);
	if($url == '')
		return "";
	$name = Country::getName($string);
	return '<img src="'.$url.'" title="'.$name.'" alt="'.$name.'" />';
}
?>