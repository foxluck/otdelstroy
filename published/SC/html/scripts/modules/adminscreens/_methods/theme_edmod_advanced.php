<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	include_once DIR_FUNC.'/func.component.php';

	$Register->set('CURRENT_THEME_ENTRY', $themeEntry);
	
	if(isset($GetVars['caller'])){
		$JsHttpRequest =& new JsHttpRequest(translate("str_default_charset"));
		$PostVars = $_REQUEST;
		$PostVars = xStripSlashesGPC($PostVars);
	}
	
	switch (@$PostVars['action']){
		case 'save_template':
			$res = $themeEntry->saveTemplate($tpl_id, $PostVars['template'], isset($GetVars['temp_saving']) && intval($GetVars['temp_saving']), isset($GetVars['contentChanged']) && intval($GetVars['contentChanged']));
			if(PEAR::isError($res))Message::raiseMessageRedirectSQ(MSG_ERROR, '', $res->getMessage());
			
			Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'thm_template_saved_msg');
			die;
			break;
		
		case 'SAVE_GSETTINGS':
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $PostVars['component_id']);
			$component_gsettings_descr = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getSettingsVDescr', $component_info['id'], 'global_settings');

			$errorEntry = null;
			/*@var $errorEntry PEAR_Error*/
			foreach ($component_gsettings_descr as $name=>$vdescr){
				
				if(!function_exists('cptsettingserializer_'.$vdescr['type'])){
					print 'Uknown setting handler - cptsettingserializer_'.$vdescr['type'];die;
					continue;
				}
				
				$post = array();
				
				foreach ($PostVars as $key=>$val){
					
					if(!preg_match("@^cpts_{$name}_(.*)$@", $key, $sp))continue;
					
					$post[$sp[1]] = $val;
				}
				$ser_val = call_user_func_array('cptsettingserializer_'.$vdescr['type'], array($vdescr['params'], $post));
				if (PEAR::isError($ser_val)) {
					$errorEntry = &$ser_val;break;
				}

				$themeEntry->setComponentSettingValue($component_info['id'], $name, $ser_val);
			}
			
			if(!PEAR::isError($errorEntry)){
				
				$themeEntry->saveTheme();
				isset($GetVars['caller'])?
					Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'thm_msg_cpt_settings_saved', $PostVars):
					Message::raiseMessageRedirectSQ(MSG_SUCCESS, 0, 'thm_msg_cpt_settings_saved');
			}else{
				
				isset($GetVars['caller'])?
					Message::raiseAjaxMessage(MSG_ERROR, 0, $errorEntry->getMessage(), $PostVars):
					Message::raiseMessageRedirectSQ(MSG_ERROR, 0, $errorEntry->getMessage());
			}
			if(isset($GetVars['caller']))die;
			break;
			
		case 'CPT_PREPARE_SMARTYCODE':
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $PostVars['component_id']);

			global $_RESULT;
			$_RESULT['cptSmarty'] = cpt_getSmarty($component_info, cpt_getParamsFromForm($component_info, $PostVars));;
		
			if(PEAR::isError($_RESULT['cptSmarty'])){
				
				Message::raiseAjaxMessage(MSG_ERROR, 0, $_RESULT['cptSmarty']->getMessage());
				unset($_RESULT['cptSmarty']);
			}
			die;
		case 'GET_HTMLCODE_INFO':
			global $_RESULT;
			$_RESULT = htmlCodesManager::getCodeInfo($PostVars['code_key']);
			die;break;
	}
	
	if(isset($GetVars['caller']))die;
	
	$components = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponents', $tpl_id);

	for($i=0, $max_i = count($components); $i<$max_i; $i++){
		
		$components[$i]['__gsettings_form'] = cpt_getForm('global_settings', $components[$i], $themeEntry);
		$components[$i]['__lsettings_form'] = cpt_getForm('local_settings', $components[$i], $themeEntry);
	}

	$smarty->force_compile = true;
	
	$smarty->assign('components', $components);
	
	$smarty->assign('template', $themeEntry->getTemplateContent($tpl_id, true));
	
	$smarty->assign('templated_changed', $Register->get('__THMTPL_CONTENTCHANGED'));
?>