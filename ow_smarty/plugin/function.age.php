<?php
/**
 * Smarty printVar function for debugging purposes
 * 
 * @param mixed var
 * @package OW_Smarty $smarty
 */

function smarty_function_age($params, $smarty)
{ 
	$date = UTIL_DateTime::parseDate( $params['dateTime'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT );
        return UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
}
?>