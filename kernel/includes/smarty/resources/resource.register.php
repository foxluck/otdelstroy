<?php
/*
* Smarty plugin
* -------------------------------------------------------------
* File:     resource.register.php
* Type:   resource
* Name:  register  
* Purpose:  Get template from register
* -------------------------------------------------------------
*/
define('SMARTY_RESOURCE_REGISTER', '__SMARTY_RESOURCE_REGISTER_');

function smarty_resource_register_source($tpl_name, &$tpl_source, &$smarty)
{

	$Register = &Register::getInstance();
    if($Register->is_set(SMARTY_RESOURCE_REGISTER.$tpl_name)){
		
    		$resource = &$Register->get(SMARTY_RESOURCE_REGISTER.$tpl_name);
    		$tpl_source = $resource['source'];
    		return true;
    }else return false;
}

function smarty_resource_register_timestamp($tpl_name, &$tpl_timestamp, &$smarty)
{

	$Register = &Register::getInstance();
    if($Register->is_set(SMARTY_RESOURCE_REGISTER.$tpl_name)){
		
    		$resource = &$Register->get(SMARTY_RESOURCE_REGISTER.$tpl_name);
    		$tpl_source = $resource['timestamp'];
    		return true;
    }else return false;
}

function smarty_resource_register_secure($tpl_name, &$smarty)
{
    return true;
}

function smarty_resource_register_trusted($tpl_name, &$smarty)
{
	return true;
}

function smarty_resource_register_register($tpl_name, $tpl_source, $tpl_timestamp){
	
	$Register = &Register::getInstance();
	$resource_data = array('source' => $tpl_source, 'timestamp' => $tpl_timestamp);
	$Register->set(SMARTY_RESOURCE_REGISTER.$tpl_name, $resource_data);
}
?>