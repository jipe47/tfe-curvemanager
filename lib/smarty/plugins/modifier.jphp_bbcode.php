<?php
function smarty_modifier_jphp_bbcode($string)
{
	return BBCode::parse($string);
}

?>