<?php
// Purpose	gets class name in file
// Inputs   $fileName - full file name
// Remarks	this file must contains only one class syntax valid declaration
// Returns	class name
// SEE virtualModule::getClassName()

function GetClassName( $fileName )
{
	$strContent = file_get_contents($fileName);
	$_match = array();
	$strContent = substr($strContent, strpos($strContent, '@connect_module_class_name'), 100);
	if(preg_match("|\@connect_module_class_name[\t ]+([0-9a-z_]*)|i", $strContent, $_match)){
		return $_match[1];
	}else {
		return false;
	}
}

// Purpose	gets module object array
// Inputs     	module file array
function modGetModules( $moduleFiles )
{
	$modules	= array();
	foreach( $moduleFiles as $fileName )
	{
		$className	= GetClassName( $fileName );
		if(!$className) continue;
		$objectModule = new $className();
		if ( $objectModule->is_installed() )
		$modules[] = $objectModule;
	}
	return $modules;
}

function modGetModuleObjects( $moduleFiles, $exludeClasses = array() )
{
	$modules	= array();
	foreach( $moduleFiles as $fileName ){

		$className	= GetClassName( $fileName );
		if(!$className) continue;
		if(in_array($className,$exludeClasses))continue;
		if(!class_exists($className,false))include_once($fileName);
		$objectModule = new $className();
		$objectModule->className = get_class($objectModule);
		$modules[] = $objectModule;
	}
	return $modules;
}

function modGetModuleConfigs($_ModuleClassName){

	$ModuleConfigs = array();

	$sql = "SELECT * FROM `?#MODULES_TABLE` WHERE `ModuleClassName` LIKE ? ORDER BY module_name ASC";
	$Result = db_phquery($sql,$_ModuleClassName);
	while ($_Row = db_fetch_row($Result)) {

		$ModuleConfigs[] = array(
			'ConfigID' 		=> $_Row['module_id'],
			'ConfigName' 	=> $_Row['module_name'],
			'ConfigClass' 	=> $_ModuleClassName,
		);
	}

	return $ModuleConfigs;
}

function modGetModuleConfig($_ConfigID){
	$sql = "
		SELECT * FROM ".MODULES_TABLE." WHERE module_id=".intval($_ConfigID)."
	";
	return db_fetch_row(db_query($sql));
}

function modUninstallModuleConfig($_ConfigID){

	$ModuleConfig = modGetModuleConfig($_ConfigID);
	$_tClass = new $ModuleConfig['ModuleClassName']();
	$_tClass->uninstall($ModuleConfig['module_id']);
}

function modGetAllInstalledModuleObjs($_ModuleType = 0,$strongCheck = true){
	$ModuleObjs = array();
	$sql = 'SELECT `module_id` FROM `?#MODULES_TABLE`';
	if($_ModuleType){
		$sql .= ' WHERE (`module_type`=?) OR (`module_type` IS NULL)' ;
	}
	$sql .=	' ORDER BY `module_name` ASC, `module_id` ASC';
	$Result = db_phquery($sql,$_ModuleType);
	while ($_Row = db_fetch_assoc($Result)) {

		$_TObj = modGetModuleObj($_Row['module_id'], $_ModuleType);

		if($_TObj && $_TObj->get_id() && (!$strongCheck||$_TObj->is_installed()))	$ModuleObjs[] = $_TObj;
	}
	return $ModuleObjs;
}

/**
 * @param int $_ID: module ID
 * @param mixed $_ModuleType: SHIPPING_RATE_MODULE|PAYMENT_MODULE
 * @return virtualModule
 */
function modGetModuleObj($_ID, $_ModuleType = 0){

	$ModuleConfig = modGetModuleConfig($_ID);
	$objectModule = null;
	if(!$_ID) return $objectModule;

	if ($ModuleConfig['ModuleClassName']) {
		if(!class_exists($ModuleConfig['ModuleClassName'],false)){
			$moduleFiles = array();
			$IncludeDir = '';
			switch ($_ModuleType){

				case SHIPPING_RATE_MODULE:
					$IncludeDir = DIR_MODULES."/shipping";
					break;
				case PAYMENT_MODULE:
					$IncludeDir = DIR_MODULES."/payment";
					break;
				case SMSMAIL_MODULE:
					$IncludeDir = DIR_MODULES.'/ordering/smsmail';
					break;
			}
			$moduleFiles = GetFilesInDirectory( $IncludeDir, "php" );


			foreach( $moduleFiles as $fileName ){
				$className = GetClassName( $fileName );
				if(strtolower($className) == strtolower($ModuleConfig['ModuleClassName'])){
					require_once($fileName);
					break;
				}

			}
		}
		if(class_exists($ModuleConfig['ModuleClassName'],false)){
			$objectModule = new $ModuleConfig['ModuleClassName']($_ID);

			if($_ModuleType && $objectModule->getModuleType()!=$_ModuleType){
				$objectModule = null;
			}elseif($objectModule&&($objectModule->getModuleType()!=$ModuleConfig['module_type'])){
				$sql = 'UPDATE `?#MODULES_TABLE` SET `module_type`=? WHERE `module_id`=?';
				db_phquery($sql,$objectModule->getModuleType(),$_ID);
			}
		}
	}else {

		$moduleFiles = array();
		switch ($_ModuleType){

			case SHIPPING_RATE_MODULE:
				$moduleFiles = GetFilesInDirectory(  DIR_MODULES."/shipping", "php" );
				break;
			case PAYMENT_MODULE:
				$moduleFiles = GetFilesInDirectory(  DIR_MODULES."/payment", "php" );
				break;
			case SMSMAIL_MODULE:
				$IncludeDir =  DIR_MODULES."/ordering/smsmail";
				break;
		}

		foreach( $moduleFiles as $fileName )
		{
			$className	= GetClassName( $fileName );
			if(!$className) continue;
			if(!class_exists($className,false))require_once($fileName);
			$objectModule = new $className();

			if ( $objectModule->get_id() == $_ID && $objectModule->title==$ModuleConfig['module_name'])
			return $objectModule;
			else $objectModule = null;
		}
	}
	return $objectModule;
}
?>