<?php
	class LanguagesListController extends ActionsController {
		
		function save_order(){
					
			$scan_result = scanArrayKeysForID($_POST, 'priority');
			$uLanguage = new Language();
			foreach ($scan_result as $lang_id=>$scan_info){
				
				$uLanguage->loadById($lang_id);
				$uLanguage->setPriority($scan_info['priority']);
				$uLanguage->save();
			}
			
			Message::raiseAjaxMessage(MSG_SUCCESS, '', 'loc_langorder_saved');
			die;
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$PostVars = &$Register->get(VAR_POST);
			$GetVars = &$Register->get(VAR_GET);
			
			if(isset($GetVars['act']) && $GetVars['act'] == 'dellang' && isset($GetVars['lang_id'])){
				
				$del_lang_id = trim($GetVars['lang_id']);
				renderURL('act=&lang_id=', '', true);
				if(CONF_DEFAULT_LANG == $del_lang_id){
					
					throwMessage('loc_couldnt_delete_deflang');
				}
				
				$DelLang = &ClassManager::getInstance('Language');
				/*@var $DelLang Language*/
				$DelLang->loadById($del_lang_id);
				$res = $DelLang->delete();
				if(PEAR::isError($res))throwMessage($res);
				
				throwMessage('loc_msg_lang_removed', MSG_SUCCESS);
			}
					
			$a_languages = LanguagesManager::getLanguages();
			$r_languages = array();
			foreach ($a_languages as $Language){
				/* @var $Language Language */
				$r_languages[] = array(
					'id' => $Language->id,
					'iso2' => $Language->iso2,
					'enabled' => $Language->enabled(),
					'priority' => $Language->getPriority(),
					'name' => $Language->getName(),
					'thumbnail_url' => $Language->getThumbnailURL(),
				);
			}
			
			$smarty->assign_by_ref('languages', $r_languages);
			$smarty->assign('languages_num', count($r_languages));
			$smarty->assign('admin_sub_dpt', 'languages_list.tpl.html');
		}
	}
	
	ActionsController::exec('LanguagesListController');
?>