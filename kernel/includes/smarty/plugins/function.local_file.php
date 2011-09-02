<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     local_file
 * Purpose:  return path to a localized file
 * -------------------------------------------------------------
 */

function smarty_function_local_file( $params, &$this )
{
    extract($params);

	echo sprintf( "../../../%s/html/%s/localization/%s/%s", $APP_ID, $template, $lang, $file );
}

?>
