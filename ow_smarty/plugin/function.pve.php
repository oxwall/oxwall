<?php
function smarty_function_pve($params, $smarty)
{
	$isEcho = ((isset($params['echo'])) && $params['echo'] === true); 
	printVar( $params['v'], $isEcho ); exit;
}
