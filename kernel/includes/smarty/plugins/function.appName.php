<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     appName
 * Purpose:  returns WBS application name
 * -------------------------------------------------------------
 */

function smarty_function_appName( $params, &$this )
{
    extract($params);

	return getAppName( $APP_ID, $language );
}

?>
