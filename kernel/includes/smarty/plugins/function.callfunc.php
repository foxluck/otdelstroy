<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {callfunc} plugin
 *
 * Type:     function<br>
 * Name:     callfunc<br>
 * Purpose:  call core functions
 * @param array
 * @param Smarty
 * @return mixed result of work calling function
 */
function smarty_function_callfunc($params, &$smarty)
{
	static $AllowedFunctions;
	if(!is_array($AllowedFunctions))
		$AllowedFunctions = array(
			'convertToDisplayDateTime',
			);
	if(!isset($params['func']))return 'Not specified function name';
	if(!in_array($params['func'], $AllowedFunctions))return 'Disallowed function';
	$func = $params['func'];
	unset($params['func']);
	return call_user_func_array($func, $params);
}

?>