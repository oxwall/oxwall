<?php
/**
 * Smarty printVar function for debugging purposes
 * 
 * @param mixed var
 * @package OW_Smarty $smarty
 */

function smarty_function_pv($params, $smarty)
{
	$isEcho = ((isset($params['echo'])) && $params['echo'] === true); 
	printVar( $params['v'], $isEcho );
}
?>