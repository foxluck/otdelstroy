<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	$defaultLanguage = &LanguagesManager::getDefaultLanguage();
	
	if(isset($PostVars['addmodlang'])){
		
		$aLanguage = &ClassManager::getInstance('Language');
		/* @var $aLanguage Language */
		
		
		$required_fields = array( 'lang_name', 'lang_iso2' );
		
		foreach ($required_fields as $field_key){
			
			$PostVars[$field_key] = trim($PostVars[$field_key]);
			if(!strlen($PostVars[$field_key]))Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'msg_fill_required_fields', '', array('lang_data' => $PostVars, 'error_field'=>$field_key));
		}
		
		$PostVars['lang_iso2'] = strtolower($PostVars['lang_iso2']);
		if(!preg_match('/^[a-z]{2}$/', $PostVars['lang_iso2'])){
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'loc_iso2_should_be', '', array('lang_data' => $PostVars, 'error_field'=>'lang_iso2'));
		}
		
		$aLanguage->loadById($PostVars['lang_id']);
		$aLanguage->setName($PostVars['lang_name']);
		$aLanguage->setISO2($PostVars['lang_iso2']);
		$aLanguage->enabled($defaultLanguage->id == $aLanguage->id?true:isset($PostVars['lang_enabled']));
		$aLanguage->direction = isset($PostVars['direction'])&&$PostVars['direction']?1:0;
		$res = $aLanguage->save();
		if(PEAR::isError($res))
			Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('lang_data' => $PostVars));
		
		$FilesVars = &$Register->get(VAR_FILES);
		if(isset($FilesVars['upload_thumbnail']) && $FilesVars['upload_thumbnail']['name']){
			
			$new_thumbnail = $FilesVars['upload_thumbnail'];
			if($new_thumbnail['error']){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', translate('loc_error_file_upload').' ('.$new_thumbnail['error'].')');
			}
			if(!preg_match('/\.(\w{3})$/', $new_thumbnail['name'], $subpatterns)){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'loc_notsupported_filetype');
			}
			$extension = $subpatterns[1];
			if(!in_array($extension, array('gif','jpeg','jpg','png'))){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'loc_notsupported_filetype');
			}
			
			$res = Functions::exec('file_move_uploaded', array($new_thumbnail['tmp_name'], DIR_FLAGS.'/'.$aLanguage->id.'.'.$extension));

			if(PEAR::isError($res)){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
			}
			$aLanguage->setThumbnail($aLanguage->id.'.'.$extension);
			$aLanguage->save();
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS,'','msg_information_save');
	}
	
	$lang_id = isset($GetVars['lang_id'])?$GetVars['lang_id']:0;
	
	$aLanguage = &ClassManager::getInstance('Language');
	/* @var $aLanguage Language */
	$aLanguage->loadById($lang_id);

	$r_language = array(
		'id' => $aLanguage->id,
		'iso2' => $aLanguage->iso2,
		'enabled' => $aLanguage->enabled(),
		'name' => $aLanguage->getName(),
		'thumbnail_url' => file_exists(DIR_FLAGS.'/'.$aLanguage->getThumbnail())&&$aLanguage->getThumbnail()?(URL_FLAGS.'/'.$aLanguage->getThumbnail()):'',
		'is_default' => $aLanguage->id == $defaultLanguage->id,
		'direction' => $aLanguage->direction, 
	);
	
	$smarty->assign('language', $r_language);
	
	$Message = &$Register->get(VAR_MESSAGE);
	
	/*@var $Message Message*/
	if(Message::isMessage($Message) && $Message->is_set() && is_array($Message->lang_data)){
		
		$r_language = array();
		foreach ($Message->lang_data as $key=>$val){
			
			$r_language[str_replace('lang_', '', $key)] = $val;
		}
		$smarty->assign('language', $r_language);
	}

	$smarty->assign('admin_sub_dpt', 'addmod_language.tpl.html');
?>