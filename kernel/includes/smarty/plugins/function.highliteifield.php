<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {highliteifield} plugin
 *
 * Type:     function<br>
 * Name:     highliteifield<br>
 * Purpose:  highlite field
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_highliteifield($params, &$smarty){
	
	extract($params);
	if(!isset($iFieldName)){
		
		$iFieldName = $smarty->get_template_vars ('errorField');
	}
	if ( $iFieldName == $FieldName )
		return "<font color=red>";
	else
		return '';
}
?>