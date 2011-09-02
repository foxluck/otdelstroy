<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     thumbnail
 * Purpose:  WebAsyst thumbnail support
 * -------------------------------------------------------------
 */

function smarty_function_thumbnail( $params, &$smarty )
{
	extract($params);

	$fileNameData = pathinfo( $fileName );
	$ext = strtolower($fileNameData['extension']);
	
	if (empty($baseSrc))
		$baseSrc = "../../../";

	$baseDir = "{$baseSrc}common/html/thumbnails";
	$thumbDir = "../../../common/html/thumbnails";
	
	$fileSrc = "$baseDir/$ext.$os.$size.gif";
	$filePath = "$thumbDir/$ext.$os.$size.gif";
	if ( !file_exists($filePath) )
		$fileSrc = "$baseDir/common.$os.$size.gif";

	return $fileSrc;
}

?>