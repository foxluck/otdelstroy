<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     scrName
 * Purpose:  returns WBS screen descriptions
 * -------------------------------------------------------------
 */

function smarty_function_scrDesc( $params, &$this )
{
    extract($params);

	return getScreenDescription( $APP_ID, $SCR_ID, $language );
}

?>
