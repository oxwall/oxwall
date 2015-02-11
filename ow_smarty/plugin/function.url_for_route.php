<?php
/**
 * Smarty function
 * 
 * @param mixed var
 * @package OW_Smarty $smarty
 */
/*

{url_for_route for="route-name:[param=>val, param2=>val2]"}

 *
 */
function smarty_function_url_for_route($params, $smarty)
{
	$arr = array();
	
	$tmp = explode(':', $params['for']);
			
	$route = trim($tmp[0]); 
	
	if( !empty($tmp[1]) && preg_match("/^\\[(.*)\\]$/", $tmp[1], $m) )
	{
		
		if(!empty($m[1]) && trim($m[1]) != '' )
		{
			
			foreach ( explode(',', $m[1]) as $val )
			{
				$ff = explode('=>', $val);
				$k = trim( strval( $ff[0] ) );
				$v = trim( strval( $ff[1] ) );
				$arr[$k] = $v;
			}
		}

	}

	return OW::getRouter()->urlForRoute($route, $arr);
}
?>