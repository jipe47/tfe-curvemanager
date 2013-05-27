<?php
function smarty_function_jphp_widget($params, &$smarty)
{
	if (empty($params['p'])) {
        $smarty->trigger_error("translate : missing 'p' parameter.");
        return;
    }
    $p = $params['p'];
    $w = empty($params['w']) ? "" : $params['w'];
	unset($params['w']);
	unset($params['p']);
	
	$class = Plugins::getPlugin($p);
	if($w == "")
		$widget = $class->getDefaultWidget();
	else
		$widget = $class->getWidget($w);
	$widget->setArg($params);
	return $widget->render();
  //  return serialize($params);
}