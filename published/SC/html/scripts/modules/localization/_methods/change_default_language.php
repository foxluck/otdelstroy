<?php
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */

	$PostVars = &$Register->get(VAR_POST);
	$GetVars = &$Register->get(VAR_GET);
	
	if(isset($PostVars['cancel']))RedirectSQ('?ukey=languages');
	
	if(isset($PostVars['change_default'])){
		
		$languageEntry = new Language();
		$res = $languageEntry->loadById($PostVars['new_language_id']);
		if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
		
		$languageEntry->enabled(true);
		$res = $languageEntry->save();
		if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
		
		_setSettingOptionValue('CONF_DEFAULT_LANG', $PostVars['new_language_id']);
		$smarty->assign('refreshInterfaceReq', true);
//		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '?ukey=languages', 'msg_information_saved');

	}
	
	$defaultLanguage = &ClassManager::getInstance('Language');
	/*@var $defaultLanguage Language*/
	$res = $defaultLanguage->loadById(CONF_DEFAULT_LANG);
	if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '?ukey=languages');
	
	$a_languages = LanguagesManager::getLanguages();
	$r_languages = array();
	foreach ($a_languages as $Language){
		/* @var $Language Language */
		if($Language->id == CONF_DEFAULT_LANG){
			continue;
		}
		$r_languages[] = array(
			'id' => $Language->id,
			'iso2' => $Language->iso2,
			'enabled' => $Language->enabled(),
			'name' => $Language->getName(),
			'thumbnail_url' => $Language->getThumbnailURL(),
		);
	}
	
	$smarty->assign_by_ref('defaultLanguage', $defaultLanguage);
	$smarty->assign_by_ref('languages', $r_languages);
	$smarty->assign('admin_sub_dpt', 'change_default_language.tpl.html');
?>