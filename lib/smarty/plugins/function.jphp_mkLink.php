<?php
function smarty_function_jphp_mkLink($params, &$smarty)
{
	if (empty($params['arg'])) {
        $smarty->trigger_error("translate : missing 'key' parameter.");
        return;
    }
    return "?".str_replace(",", JPHP::ARG_SEPARATOR, $params['arg']);
}