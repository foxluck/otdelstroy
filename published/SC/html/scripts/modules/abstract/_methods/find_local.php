<?php
	$Register = &Register::getInstance();
	$PostVars = &$Register->get(VAR_POST);
	$GetVars = &$Register->get(VAR_GET);
	
	$searchstr = isset($PostVars['searchstr'])?$PostVars['searchstr']:(isset($GetVars['searchstr'])?$GetVars['searchstr']:null);
	
	renderURL('searchstr='.$searchstr, '', true);
	
	$lang_id = isset($GetVars['lang_id'])?$GetVars['lang_id']:'';
	if(!$lang_id)RedirectSQ('?ukey=languages');
	
	$locals_type = isset($GetVars['locals_type'])?$GetVars['locals_type']:LOCALTYPE_BACKEND;
	if(!in_array($locals_type, array(LOCALTYPE_FRONTEND, LOCALTYPE_BACKEND, LOCALTYPE_GENERAL)))$locals_type = LOCALTYPE_BACKEND;
	
	$locals = array();
	
	$LanguageEntry = &ClassManager::getInstance('Language');
	/* @var $LanguageEntry Language */
	$res = $LanguageEntry->loadById($lang_id);
	if(PEAR::isError($res))throwMessage($res);
	
	$DefLanguageEntry = &ClassManager::getInstance('Language');
	/* @var $DefLanguageEntry Language */
	$res = $DefLanguageEntry->loadById(CONF_DEFAULT_LANG);
	if(PEAR::isError($res))throwMessage($res);
	
	if(isset($PostVars['save_locals'])){

		if(isset($PostVars['locals']))foreach ($PostVars['locals'] as $local_key=>$local_value){
			
			$LanguageEntry->updateLocal($local_key, $local_value);
			if(PEAR::isError($res))throwMessage($res);
		}

		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_save');
	}
	if(isset($GetVars['act']) && $GetVars['act'] == 'delloc' && isset($GetVars['local_id'])){
		
		$res = $LanguageEntry->deleteLocal($GetVars['local_id']);
		if (PEAR::isError($res))throwMessage($res);
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'local_id=&act=', 'loc_local_was_deleted');
	}
	
	$search_in_groups = (!SystemSettings::is_hosted()||defined('SCCONF_PROGRAMMER_MODE')&&constant('SCCONF_PROGRAMMER_MODE'))?array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_BACKEND, LOCALTYPE_HIDDEN):array(LOCALTYPE_FRONTEND, LOCALTYPE_GENERAL, LOCALTYPE_BACKEND);
	
	if(!is_null($searchstr) && $searchstr){
		
		$_searchstr = str_replace('%', '\\%', xEscapeSQLstring(trim($searchstr)));
		$DBHandler = &$Register->get(VAR_DBHANDLER);
		/* @var $DBHandler DataBase */
		
		if( $lang_id == CONF_DEFAULT_LANG){
			
			$dbq = '
				SELECT loctbl_s.`id`, loctbl_s.`value`, loctbl_s.`lang_id`, loctbl_s.`value` AS alt_value FROM ?#LOCAL_TABLE loctbl_s
				WHERE loctbl_s.`group` IN ("'.implode('","',xEscapeSQLstring($search_in_groups)).'") AND loctbl_s.`lang_id`=? AND (loctbl_s.`id` LIKE "%'.$_searchstr.'%" OR loctbl_s.`value` LIKE "%'.$_searchstr.'%")
				GROUP BY `id`,`value` ORDER BY `id` ASC
			';
		}else{
			
			$dbq = '
				SELECT ssl1.id,ssl1.lang_id, ssl2.value,ssl1.value AS alt_value FROM ?#LOCAL_TABLE ssl1 
				LEFT JOIN ?#LOCAL_TABLE ssl2 
				ON ssl1.id=ssl2.id AND ssl2.`lang_id`=? AND ssl2.`group` IN ("'.implode('","',xEscapeSQLstring($search_in_groups)).'")
				WHERE ssl1.lang_id=? AND (ssl1.id LIKE "%'.$_searchstr.'%" OR ssl2.id LIKE "%'.$_searchstr.'%" OR ssl1.`value` LIKE "%'.$_searchstr.'%" OR ssl2.`value` LIKE "%'.$_searchstr.'%") AND ssl1.`group` IN ("'.implode('","',xEscapeSQLstring($search_in_groups)).'") 
				GROUP BY `id`,`value` ORDER BY `id` ASC
			';
		}

		$DBRes = $DBHandler->ph_query($dbq, $lang_id, CONF_DEFAULT_LANG, $lang_id, CONF_DEFAULT_LANG);
		if(PEAR::isError($DBRes))throwMessage($DBRes);
		
		$pattern = '';
		$strlen = mb_strlen($_searchstr,"UTF-8");
		for($i=0;$i<$strlen;$i++){
			$char = mb_substr($_searchstr,$i,1,"UTF-8");
			$charlower = mb_strtolower($char,"UTF-8");
			$charupper = mb_strtoupper($char,"UTF-8");
			$pattern .= "[{$charlower}{$charupper}]";
		}
		mb_regex_encoding("UTF-8");
		while ($row = $DBRes->fetchAssoc()){
			$locals[$row['id']] = array(
				'id' => $row['id'],
				'id_search' => mb_ereg_replace("({$pattern})","<span style=\"color:orange;font-weight: bolder;\">\\1</span>",$row['id']),
				'value' => $row['value'],
				'defvalue' => mb_ereg_replace("({$pattern})","<span style=\"color:orange;font-weight: bolder;\">\\1</span>",htmlspecialchars($row['alt_value'], ENT_QUOTES)),
			);
		}
	}
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	
	$smarty->assign('admin_sub_dpt', 'find_local.htm');
	$smarty->assign_by_ref('LocalStrings', $locals);
	$smarty->assign('searchstr', $searchstr);
	$smarty->assign('records_num', count($locals));
	
	$smarty->assign_by_ref('Language', $LanguageEntry);
	$smarty->assign_by_ref('DefLanguage', $DefLanguageEntry);
?>