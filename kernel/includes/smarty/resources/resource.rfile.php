<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File:     resource.rfile.php
* Type:   resource
* Name:  rfile  
* Purpose:  Get template by relative path
* -------------------------------------------------------------
*/
function smarty_resource_rfile_source($tpl_name, &$tpl_source, &$smarty)
{
    $path = getcwd().'/'.$tpl_name;
    if(file_exists($path)){
		
    		$tpl_source = file_get_contents($path);
    		return true;
    }else return false;
}

function smarty_resource_rfile_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{
    $path = getcwd().'/'.$tpl_name;
    if(file_exists($path)){
		
    		$tpl_timestamp = filectime($path);
    		return true;
    }else return false;
}

function smarty_resource_rfile_secure($tpl_name, &$smarty)
{
    return true;
}

function smarty_resource_rfile_trusted($tpl_name, &$smarty)
{
	return true;
}
?>