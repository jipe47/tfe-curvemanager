<?php
function smarty_modifier_toPng($string)
{
	$n = explode(".", $string);
	array_pop($n);
	return implode(".", $n).".png";
}

?>