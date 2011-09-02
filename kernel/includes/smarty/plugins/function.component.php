<?php
function smarty_function_component($params, &$smarty){

	global $__WRAP_COMPONENT;

	return ModulesFabric::callModuleInterface('cptmanager', 'cpt_callComponent', $params['cpt_id'], $params, isset($__WRAP_COMPONENT)&&$__WRAP_COMPONENT);
}
?>