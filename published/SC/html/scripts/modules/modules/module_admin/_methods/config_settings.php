<?php
$this->assignSubTemplate('backend/module_config.html');
$configID = isset($_GET['configID'])?$_GET['configID']:0;
/* @var $ModuleObj Module */
$ModuleObj = ModulesFabric::getModuleObj($configID);
if(!$ModuleObj->getConfigID())return;
$ModuleSettings = $ModuleObj->getSettings();
foreach ($ModuleSettings as $_Key=>$_ModuleSetting){
	
	if(isset($_ModuleSetting['edit_html']))continue;
	if(isset($_POST['fACTION'])&&$_POST['fACTION']=='SAVE_CONFIG_SETTINGS'){
		
		ModulesFabric::saveSetting($_ModuleSetting, $_POST['configID']);
	}
	$ModuleSettings[$_Key]['edit_html'] = ModulesFabric::getSettingHTML($_ModuleSetting);
}
if(isset($_POST['fACTION'])&&$_POST['fACTION']=='SAVE_CONFIG_SETTINGS')RedirectSQ();
$this->assign2template('ConfigID', $ModuleObj->getConfigID());
$this->assign2template('ConfigSettings', $ModuleSettings);
$this->assign2template('ConfigTitle', $ModuleObj->getConfigTitle());
?>