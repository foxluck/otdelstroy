<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$PostVars = &$Register->get(VAR_POST);
	
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
		$aLanguage->setName($PostVars['lang_name']);
		$aLanguage->setISO2($PostVars['lang_iso2']);
		$aLanguage->enabled(isset($PostVars['lang_enabled']));
		$aLanguage->direction = isset($PostVars['direction'])&&$PostVars['direction']?1:0;
		$res = $aLanguage->save();
		if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage(), '', array('lang_data' => $PostVars, 'error_field'=>'lang_iso2'));
		
		$FilesVars = &$Register->get(VAR_FILES);
		if(isset($FilesVars['upload_thumbnail']) && $FilesVars['upload_thumbnail']['name'] ){
			
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
			
			$res = copy($new_thumbnail['tmp_name'], DIR_FLAGS.'/'.$aLanguage->id.'.'.$extension);
			if(!$res){
				Message::raiseMessageRedirectSQ(MSG_ERROR, '', 'loc_error_file_upload');
			}
			$aLanguage->setThumbnail($aLanguage->id.'.'.$extension);
			$aLanguage->save();
		}
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS,'?ukey=languages', 'loc_languages_added');
	}
	
	$Message = &$Register->get(VAR_MESSAGE);
	/*@var $Message Message*/
	if(Message::isMessage($Message) && $Message->is_set() && is_array($Message->lang_data)){
		
		$language = array();
		foreach ($Message->lang_data as $key=>$val){
			
			$language[str_replace('lang_', '', $key)] = $val;
		}
		$smarty->assign('language', $language);
	}
	
	$smarty->assign('admin_sub_dpt', 'addmod_language.tpl.html');
?>