<?php
function smarty_function_display_name($params, $smarty)
{
	$service = BOL_UserService::getInstance();

	return $service->getDisplayName($params['id']);
}
?>