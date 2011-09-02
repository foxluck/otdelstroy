<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     scrName
 * Purpose:  returns WBS mail notification name
 * -------------------------------------------------------------
 */

function smarty_function_mnName( $params, &$this )
{
    extract($params);

	return getNotificationName( $APP_ID, $mnID, $language );
}

?>
