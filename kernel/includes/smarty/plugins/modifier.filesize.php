<?php



function smarty_modifier_filesize($size)
{
	if (!strlen($size)) {
		return "";
	}
	if ($size >= 1024 * 1024 * 1024) {
		return sprintf(_('%s GB'), round(ceil($size) >> 30, 2));
	} elseif($size >= 1024 * 1024) {
		return sprintf(_('%s MB'), round(ceil($size) >> 20, 2));
	} elseif($size >= 1024) {			
		return sprintf(_('%s KB'), round(ceil($size) >> 10, 2));
	} else {
		return sprintf(_('%s B'), round(ceil($size), 2));
	}	
}