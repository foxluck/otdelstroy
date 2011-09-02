<?php
	$smarty = &Core::getSmarty();
	/* @var $Smarty Smarty */
	
	DivisionModule::ActionsHandler();
	
	$smarty->assign( 'sub_template', $this->getTemplatePath('backend/division_add_interface.html'));
	
	$InstalledMods = &ModulesFabric::getModuleObjs(INIT_LOCAL);
	$Interfaces = array();

	$ConnectedModules = array();
	
	foreach ($CurrDivInterfaces as $_Ind=>$_Interface){
		
		if(!isset($ConnectedModules[$_Interface['ModConfigID']]))$ConnectedModules[$_Interface['ModConfigID']] = ModulesFabric::getModuleObj($_Interface['ModConfigID']);
		if(!isset($ConnectedModules[$_Interface['ModConfigID']]))continue;
		$_T = $ConnectedModules[$_Interface['ModConfigID']]->getInterfaceParams($_Interface['key']);
		$CurrDivInterfaces[$_Ind]['name'] = $_T['name'];
	}
	
	unset($ConnectedModules);
	
	if(is_array($InstalledMods)){
		foreach ($InstalledMods as $_InstMod){
			
			$_Ints = $_InstMod->getInterfacesParams(INTDIVAVAILABLE);
			if(count($_Ints))
				$Interfaces[] = array(
					'key' => $_InstMod->getConfigKey(),
					'configID' => $_InstMod->getConfigID(),
					'interfaces' => $_Ints,
				);
		}
	}

	$smarty->assign('CurrDivInterfaces', $CurrDivInterfaces);
	$smarty->assign('Interfaces', $Interfaces);
?>