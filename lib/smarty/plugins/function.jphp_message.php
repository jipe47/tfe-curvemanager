<?php
function smarty_function_jphp_message($params, &$smarty)
{
	if (empty($params['text'])) {
        $smarty->trigger_error("message: missing 'text' parameter.");
        return;
    }
    $text = $params['text'];
	$type = (!empty($params['type'])) ? $params['type'] : Message::SUCCESS;
	
	$class = new Message($text, $type);
	return $class->render();
}