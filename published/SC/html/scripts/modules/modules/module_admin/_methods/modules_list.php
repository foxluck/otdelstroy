<?php	
$this->ActionsHandler();

/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

$ModulesInfo = ModulesFabric::getModulesInfo();

$TC = count($ModulesInfo);
$ModuleClasses = array();
for ($j=0;$j<$TC;$j++){
	
	$ModuleClasses[strtolower($ModulesInfo[$j]['class_name'])] = $j;
}

$ModulesConfigs = ModulesFabric::getConfigsInfo();

if(count($ModuleClasses)){
	$sql = 'SELECT ModuleID,ModuleClassName FROM ?#TBL_MODULES WHERE ModuleClassName IN (?@)';
	$Result = db_phquery($sql, array_keys($ModuleClasses));
	while ($_Row = db_fetch_assoc($Result)){
		
		$ModulesInfo[$ModuleClasses[strtolower($_Row['ModuleClassName'])]]['id'] = $_Row['ModuleID'];
		if(isset($ModulesConfigs[$_Row['ModuleID']])){
			
			$ModulesInfo[$ModuleClasses[strtolower($_Row['ModuleClassName'])]]['Configs'] = $ModulesConfigs[$_Row['ModuleID']];
			$ModulesInfo[$ModuleClasses[strtolower($_Row['ModuleClassName'])]]['configs_number'] = count($ModulesConfigs[$_Row['ModuleID']]);
		}
	}
}

function sortModulesTTT($Elem1,$Elem2){
	return strcmp($Elem1['title'],$Elem2['title']);
}
usort($ModulesInfo, 'sortModulesTTT');

$smarty->assign('Modules', $ModulesInfo);
$smarty->assign('sub_template',$this->getTemplatePath('backend/modules_list.html'));
?>