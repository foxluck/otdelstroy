<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     scrName
 * Purpose:  returns WBS screen name
 * -------------------------------------------------------------
 */

function smarty_function_scrName( $params, &$this )
{
    extract($params);

	return getScreenName( $APP_ID, $SCR_ID, $language );
}

?>
