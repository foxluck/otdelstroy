<?php
/* @var $smarty Smarty */
$smarty = &Core::getSmarty();

//specify that this is a popup window
$printable_version = true;
$smarty->assign('printable_version', 1);

$PrintableDivision = new Division(isset($_GET['pdid'])?$_GET['pdid']:0);

$InheritableInterfaces = $PrintableDivision->getInheritableInterfaces();
$Interfaces = $PrintableDivision->getInterfaces();
$ConnectedModules = &Core::getConnectedModules();
foreach ($InheritableInterfaces as $_Interface){
	
	if(!isset($ConnectedModules[$_Interface['ModConfigID']]))$ConnectedModules[$_Interface['ModConfigID']] = ModulesFabric::getModuleObj($_Interface['ModConfigID']);
	if(!isset($ConnectedModules[$_Interface['ModConfigID']]))continue;
	$ConnectedModules[$_Interface['ModConfigID']]->getInterface($_Interface['key']);
}

foreach ($Interfaces as $_Interface){
	
	if(!isset($ConnectedModules[$_Interface['ModConfigID']]))$ConnectedModules[$_Interface['ModConfigID']] = ModulesFabric::getModuleObj($_Interface['ModConfigID']);
	if(!isset($ConnectedModules[$_Interface['ModConfigID']]))continue;
	$ConnectedModules[$_Interface['ModConfigID']]->getInterface($_Interface['key']);
}
?>