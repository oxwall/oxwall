<?php
/**
 * Smarty function
 * 
 * @param mixed var
 * @package OW_Smarty $smarty
 */

function smarty_function_url_for($params, $smarty)
{
	$arr = array();
	
	$tmp = explode(':',$params['for']);
			
	$controller = trim($tmp[0]);
	
	$action = trim($tmp[1]);
	
	if( !empty($tmp[2]) && preg_match("/^\\[(.*)\\]$/", $tmp[2], $m) )
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
	
	return OW::getRouter()->urlFor( $controller, (!empty($action)?$action: null), $arr );
}
?>