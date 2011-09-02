<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     imagesize
 * Purpose:  returns image size
 * -------------------------------------------------------------
 */
function smarty_function_imagesize($params, &$smarty)
{
    extract($params);

	getimagesize( $path, $imgInfo );

	$widhValue = $imgInfo[0];
	$heightValue = $imgInfo[1];

    $smarty->assign($width, $widhValue);
    $smarty->assign($height, $heightValue);
}

/* vim: set expandtab: */

?>