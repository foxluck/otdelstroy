<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	$Message = &$Register->get(VAR_MESSAGE);
	/*@var $Message Message*/
	
	$lang_id = isset($GetVars['lang_id'])?$GetVars['lang_id']:'';
	if(!$lang_id)RedirectSQ('?ukey=languages');
	
	$LanguageEntry = &ClassManager::getInstance('Language');
	/* @var $LanguageEntry Language */
	$res = $LanguageEntry->loadById($lang_id);
	if(PEAR::isError($res))throwMessage($res);
	/**
	 * Default language
	 */
	$DefLanguageEntry = &ClassManager::getInstance('Language');
	/* @var $DefLanguageEntry Language */
	$res = $DefLanguageEntry->loadById(CONF_DEFAULT_LANG);
	if(PEAR::isError($res))throwMessage($res);
	
	if(isset($GetVars['act']) && $GetVars['act'] == 'delloc' && isset($GetVars['local_id'])){
		
		$res = $LanguageEntry->deleteLocal($GetVars['local_id']);
		if (PEAR::isError($res))throwMessage($res);
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'local_id=&act=', 'loc_local_was_deleted');
	}
	
	if(isset($PostVars['save_locals'])){

		$error_str = '';
		$error_field = '';
		do{
		/**
		 * Update local strings
		 */
		if(isset($PostVars['locals']))foreach ($PostVars['locals'] as $local_key=>$local_value){
			
			if($LanguageEntry->id == $DefLanguageEntry->id && !strlen($local_value)){
				$error_str = 'loc_empty_loc_defvalue';
				$error_field = $local_key;
				break;
			}
			$LanguageEntry->updateLocal($local_key, $local_value);
			if(PEAR::isError($res))throwMessage($res);
		}
		if($error_str)break;
		
		if(isset($PostVars['newlocal_key'])){

			$highlight_empty_defstrings = false;
			/**
			 * Data validation
			 */
			foreach ($PostVars['newlocal_key'] as $sub_group=>$r_newlocal_key){
				
				$r_newlocal_key = $PostVars['newlocal_key'][$sub_group];
				$__keys = array();
				foreach ($r_newlocal_key as $i=>$newlocal_key){
					
					$newlocal_key = trim($PostVars['newlocal_key'][$sub_group][$i]);
					$newlocal_value = trim($PostVars['newlocal_value'][$sub_group][$i]);
					$newlocal_defvalue = trim($PostVars['newlocal_defvalue'][$sub_group][$i]);
					
					if(!$newlocal_key){

						if($newlocal_defvalue || $newlocal_value){
							$error_str = 'loc_empty_loc_id';
							$error_field = '_all_empty_loc_id';
							break;
						}else continue;
					}
					if(isset($__keys[$newlocal_key]) || $DefLanguageEntry->getLocal($newlocal_key)){
						
						$error_str = 'loc_reserved_loc_id';
						$error_field = $newlocal_key;
						break;
					}
					if($LanguageEntry->id != $DefLanguageEntry->id && !$newlocal_defvalue && !$error_str){
						
						$error_str = 'loc_empty_loc_defvalue';
						$error_field = $newlocal_key;
						$highlight_empty_defstrings = true;
					}
					if($LanguageEntry->id == $DefLanguageEntry->id && !$newlocal_value && !$error_str){
						
						$error_str = 'loc_empty_loc_defvalue';
						$error_field = $newlocal_key;
						$highlight_empty_defstrings = true;
					}
					
					$__keys[$newlocal_key] = 1;
				}				
			}
			if($error_str)break;
			
			foreach ($PostVars['newlocal_key'] as $sub_group=>$r_newlocal_key){
				
				$r_newlocal_key = &$PostVars['newlocal_key'][$sub_group];
				foreach ($r_newlocal_key as $i=>$newlocal_key){
					
					$newlocal_key = trim($r_newlocal_key[$i]);
					$newlocal_value = trim($PostVars['newlocal_value'][$sub_group][$i]);
					$newlocal_defvalue = trim($PostVars['newlocal_defvalue'][$sub_group][$i]);
					
					if(!$newlocal_key)continue;
					
					if($LanguageEntry->id != $DefLanguageEntry->id){
						
						$res = $DefLanguageEntry->addLocal($newlocal_key, $newlocal_defvalue, $PostVars['local_group'], $sub_group);
						if(PEAR::isError($res))throwMessage($res);
					}
					$res = $LanguageEntry->addLocal($newlocal_key, $newlocal_value, $PostVars['local_group'], $sub_group);
					if(PEAR::isError($res))throwMessage($res);
				}
			}
		}
		
		}while(0);
		if($error_str){
			
			$wdata_id = getUniqueWDataID();
			storeWData($wdata_id, $PostVars);
			Message::raiseMessageRedirectSQ(MSG_ERROR, 'wdata='.$wdata_id.($error_field?'#ef_'.$error_field:''), $error_str, '', array('Fields'=>$error_field, 'highlight_empty_defstrings' => $highlight_empty_defstrings));
		}
		
		if($add_local_flag){
			
			$DefLanguageEntry->addLocal($add_local_id, $add_local_defvalue, $PostVars['local_group'], $add_local_subgroup);
			$LanguageEntry->addLocal($add_local_id, $add_local_value, $PostVars['local_group'], $add_local_subgroup);
		}
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_save');
	}
	
	$sub_group = isset($GetVars['sub_group'])?$GetVars['sub_group']:'gen';
	$xnLocalGroups = new xmlNodeX();
	$xnLocalGroups->renderTreeFromFile(DIR_CFG.'/localgroup.xml');
	$r_xnLocalGroup = $xnLocalGroups->xPath('/LocalGroups/LocalGroup');
	$local_groups = array();
	foreach ($r_xnLocalGroup as $xnLocalGroup){
		/*@var $xnLocalGroup xmlNodeX*/
		if(SystemSettings::is_hosted()&&(!defined('SCCONF_PROGRAMMER_MODE')||!constant('SCCONF_PROGRAMMER_MODE')) && $xnLocalGroup->attribute('hidden'))continue;
		if(SystemSettings::is_hosted()&&(!defined('SCCONF_PROGRAMMER_MODE')||!constant('SCCONF_PROGRAMMER_MODE')) && $xnLocalGroup->attribute('id') == LOCALTYPE_HIDDEN)continue;
	//Allow edit backend strings
	//	if((!defined('SCCONF_PROGRAMMER_MODE'||!constant('SCCONF_PROGRAMMER_MODE'))) && $xnLocalGroup->attribute('id') == LOCALTYPE_BACKEND)continue;
		
		$localgroup_id = $xnLocalGroup->attribute('id');
		$local_groups[$localgroup_id] = array(
			'id' => $localgroup_id,
			'name' => translate($xnLocalGroup->attribute('name')),
			'sub_groups' => array()
			);
		
		$r_xnLocalSubGroup = $xnLocalGroup->getChildrenByName('LocalSubGroup');
		foreach ($r_xnLocalSubGroup as $xnLocalSubGroup){
			/*@var $xnLocalSubGroup xmlNodeX*/
			$local_groups[$localgroup_id]['sub_groups'][$xnLocalSubGroup->attribute('id')] = array(
				'id' => $xnLocalSubGroup->attribute('id'),
				'name' => translate($xnLocalSubGroup->attribute('name')),
			);
		}
	}
	
	$locals_type = isset($GetVars['locals_type'])?$GetVars['locals_type']:LOCALTYPE_GENERAL;
	if(!in_array($locals_type, (defined('SCCONF_PROGRAMMER_MODE')&&constant('SCCONF_PROGRAMMER_MODE')||!SystemSettings::is_hosted())?array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_BACKEND, LOCALTYPE_HIDDEN):array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_BACKEND)))$locals_type = LOCALTYPE_GENERAL;
	
	if(!array_key_exists($sub_group, $local_groups[$locals_type]['sub_groups']))$sub_group = 'gen';
	
	$smarty->assign(array(
		'sub_group' => $sub_group,
		'local_groups' => $local_groups,
		'locals_type' => $locals_type,
		'LocalStrings' => $LanguageEntry->getLocals($locals_type, false, false),
		'DefLocalStrings' => $DefLanguageEntry->getLocals($locals_type, true, true),
		'admin_sub_dpt' => 'locals.htm',
		'Language' => $LanguageEntry,
		'DefLanguage' => $DefLanguageEntry,
		'is_deflang' => $LanguageEntry->id == $DefLanguageEntry->id,
		'highlight_empty_defstrings' => Message::isMessage($Message) && $Message->is_set() && isset($Message->highlight_empty_defstrings) && $Message->highlight_empty_defstrings,
		'reserved_loc_id' => Message::isMessage($Message) && $Message->is_set() && isset($Message->Fields)?$Message->Fields:''
	));
	
	if(isset($GetVars['wdata'])){

		$smarty->assign(loadWData($GetVars['wdata']));
		unsetWData($GetVars['wdata']);
		renderURL('wdata=','',true);
	}
?>