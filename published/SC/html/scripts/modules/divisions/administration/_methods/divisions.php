<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$PostVars = &$Register->get(VAR_POST);
		
	if(isset($PostVars['fACTION'])&& $PostVars['fACTION']=='DELETEDIV'){

		$DelDivision = new Division($PostVars['fDATA']['xID']);
		$DelDivision->delete();
		RedirectSQ('?ukey=div_tree');
	}

	DivisionModule::ActionsHandler();
	
	$CurrDivision = &DivisionModule::getDivision(isset($_GET['edid'])?$_GET['edid']:0);
	/* @var $CurrDivision Division */
	$CurrDivision->loadCustomSettings();
	
	$smarty->assign('CurrDivision', $CurrDivision);
	
	$Sub = isset($_GET['sub'])?$_GET['sub']:'list';
	
	switch ($Sub){
		
		case 'list':
			$BranchDivs = &DivisionModule::getBranchDivisions($CurrDivision->getID());
			$_TC = count($BranchDivs)-1;
			for (;$_TC>=0;$_TC--){
				
				$BranchDivs[$_TC]->getChildDivisionsNumber();
			}
			$InstalledMods = &ModulesFabric::getModuleObjs();
			$Interfaces = array();
			/*@var $CurrDivision DivisionModule*/
			$CurrDivInterfaces = $CurrDivision->getInterfaces();
	
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
			$smarty->assign('BranchDivs', $BranchDivs);
			$smarty->assign('sub_template', $this->getTemplatePath('backend/division_list.html'));
			break;
		case 'add_div':
			$CurrDivision = &$Register->get(VAR_CURRENTDIVISION);
			
			$CurrDivision->MainTemplate = 'backend/index.tpl.html';
			$smarty->assign('sub_template', $this->getTemplatePath('backend/division_new.html'));
			$smarty->assign('','');
			break;
	}
?>