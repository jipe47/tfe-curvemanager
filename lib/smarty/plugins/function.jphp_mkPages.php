<?php
function smarty_function_jphp_mkPages($params, &$smarty)
{
	if (empty($params['count']) && empty($params['current']) && empty($params['back'])) {
        $smarty->trigger_error("mkPages: missing parameters.");
        return;
    }
	return generatePages($params['count'], $params['current'], $params['back']);
}