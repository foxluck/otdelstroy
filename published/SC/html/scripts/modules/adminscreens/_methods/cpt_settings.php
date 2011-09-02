<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	include_once DIR_FUNC.'/func.component.php';

	class CptSettingsController extends ActionsController {
		
		function save_settings(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$currLanguage = &LanguagesManager::getCurrentLanguage();
			global $lang_list;
			$smarty->template_dir = DIR_FTPLS.'/'.$lang_list[$currLanguage->id]->template_path;

			$errorEntry = null;
			$themeEntry = new Theme();
			$res = $themeEntry->load($this->getData('theme_id'));
			if(PEAR::isError($res))throwMessage($res);
			
			$Register->set('CURRENT_THEME_ENTRY', $themeEntry);
			
		do{
				
			$cpt_tpl_id = $this->getData('cpt_tpl_id');
			$lsettings = loadWData(__CPT_PARAMS.$cpt_tpl_id);
			
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $lsettings['cpt_id']);
			
			$form_params = cpt_getParamsFromForm($component_info, $this->getData());

			$cpt_smarty = cpt_getSmarty($component_info, $form_params);
			
			if(PEAR::isError($cpt_smarty)){
				$errorEntry = $cpt_smarty;break;
			}
			$ser_data = cpt_serializeData(cpt_getParamsFromFormExt($component_info, $this->getData()));
			if(PEAR::isError($ser_data)){
				$errorEntry = $ser_data;break;
			}
						
			$lsettings = array_merge($lsettings, $ser_data);

			storeWData(__CPT_SMARTY.$cpt_tpl_id, $cpt_smarty);
			storeWData(__CPT_PARAMS.$cpt_tpl_id, $lsettings);

			define('__CPT_TPL_ID_CONST', $cpt_tpl_id);
		
			$r_id = md5($component_info['component_id'].time());
			
			smarty_resource_register_register('__custom_code-'.$r_id, $cpt_smarty, time());

			$smarty->assign('CPT_CONSTRUCTOR_MODE',1);
			
			$response_params = array(
				'cpt_tpl_id' => $cpt_tpl_id, 
				'cptHTML' => $smarty->fetch('register:__custom_code-'.$r_id),
				'cpt_tpl_settings' => cpt_getForm('local_settings', $component_info, $themeEntry, $lsettings)
			);
			$response_params['cptHTML'] = preg_replace('@^\<div id\="cpt\-tpl\-id\-[^\>]+\>(.*)<\/div>$@msi', '$1', $response_params['cptHTML']);
			if($Register->is_set('__OVERRIDESTYLES_CACHE_CSSFILE__')){
				$response_params['overridecache_cssfile'] = $themeEntry->getURLOffset().'/'.$Register->get('__OVERRIDESTYLES_CACHE_CSSFILE__');
			}else{
				$response_params['overridecache_cssfile'] = '';		
			}
		}while (0);
		
			if(PEAR::isError($errorEntry)){
				
				Message::raiseAjaxMessage(MSG_ERROR, 0, $errorEntry->getMessage());
			}else {
				
				Message::raiseAjaxMessage(MSG_SUCCESS, 0, '', $response_params );
			}
			die;
		}
		
		function main(){
			
			$Register = &Register::getInstance();
			$smarty = &$Register->get(VAR_SMARTY);
			/* @var $smarty Smarty */
			$divisionEntry = &$Register->get(VAR_CURRENTDIVISION);
			/*@var $divisionEntry Division*/
			$divisionEntry->MainTemplate = 'backend/cpt_settings.html';
			
			$themeEntry = new Theme();
			$res = $themeEntry->load($this->getData('theme_id'));
			if(PEAR::isError($res))throwMessage($res);
			
			$Register->set('CURRENT_THEME_ENTRY', $themeEntry);
			
			$lsettings = loadWData(__CPT_PARAMS.$this->getData('cpt_tpl_id'));
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $lsettings['cpt_id']);
			
			$smarty->assign('cpt_tpl_id', $this->getData('cpt_tpl_id'));
			$smarty->assign('theme_id', $this->getData('theme_id'));
			$smarty->assign('cpt_tpl_settings', cpt_getForm('local_settings', $component_info, $themeEntry, $lsettings));
		}
	}
	
	ActionsController::exec('CptSettingsController');
?>