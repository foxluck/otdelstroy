<?php
define('FILE_DOESNT_EXISTS', '���� �� ����������');

$this->assignSubTemplate('module_install.html');

$this->ActionsHandler();

$ClassName = isset($_GET['class'])?$_GET['class']:'';
$ClassFile = isset($_GET['file'])?$_GET['file']:'';

if(preg_match('|([\/\\a-z]+\.php)|msi', $ClassFile,$ClassFile))$ClassFile = $ClassFile[1];
else $ClassFile = null;

if (!file_exists(DIR_MODULES.'/'.$ClassFile)){
	$ClassFile = null;
}

if(is_null($ClassFile)){
	
	$this->assign2template('Message',array('type'=>'error','text'=>FILE_DOESNT_EXISTS));
	return;
}
include_once(DIR_MODULES.'/'.$ClassFile);

if($ClassName&&!class_exists($ClassName,false)){

	$this->assign2template('Message',array('type'=>'error','text'=>CLASS_DOESNT_EXISTS));
	return;
}

$ConnectorInfo = ModulesFabric::getModuleConnectorInfo(DIR_MODULES.'/'.preg_replace('|class\.([a-z_]+)\.php$|i','connector.$1.xml',$ClassFile));
if($ConnectorInfo['single_installation']=='true'||$ConnectorInfo['single_installation']==true){
	
	$_POST['fACTION'] = 'INSTALL_CONFIG';
	$_POST['DATA']['ModuleClass'] = $ClassName;
	$_POST['DATA']['ModuleFile'] = $ClassFile;
	$_POST['DATA']['ConfigTitle'] = $ConnectorInfo['title'];
	$_POST['DATA']['ConfigDescr'] = $ConnectorInfo['description'];
	$this->ActionsHandler();
}
$this->assign2template('ModuleFile', $ClassFile);
$this->assign2template('ModuleClass', $ClassName);
$this->assign2template('Connector', $ConnectorInfo);
?>