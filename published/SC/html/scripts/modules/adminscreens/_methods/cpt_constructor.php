<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	if(!wbs_auth()){

		RedirectSQ('?ukey=TitltePage');
	}
	global $__WRAP_COMPONENT;
	$__WRAP_COMPONENT = true;
	
	$Register = &Register::getInstance();
	/* @var $Register Register */

	$PostVars = &$Register->get(VAR_POST);
	$GetVars = &$Register->get(VAR_GET);
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$smarty->assign('CPT_CONSTRUCTOR_MODE', 1);

	include_once DIR_FUNC.'/func.component.php';

	$theme_id = isset($GetVars['theme_id'])?$GetVars['theme_id']:'';
	$tpl_id = isset($GetVars['tpl_id'])?$GetVars['tpl_id']:'';
	
	$themeEntry = new Theme();
	$res = $themeEntry->load($theme_id);
	if(PEAR::isError($res))throwMessage($res);
	
	$Register->set('CURRENT_THEME_ENTRY', $themeEntry);
	$Register->set('__CPT_TPL_ID', $tpl_id);
	
	define('__CPT_TPL_N', '__CPT_TPL_N_'.$tpl_id);
	
	if(isset($GetVars['caller'])){
		$JsHttpRequest =& new JsHttpRequest(translate("str_default_charset"));
		$PostVars = array_merge($PostVars,xStripSlashesGPC($_REQUEST));
	}
		
	$PostVars['action'] = isset($PostVars['action'])?$PostVars['action']:'';
	switch ($PostVars['action']){
		case 'CPT_MOD_LSETTINGS':do{
			$errorEntry = null;
			
			$cpt_tpl_id = $PostVars['cpt_tpl_id'];
			$lsettings = loadWData(__CPT_PARAMS.$cpt_tpl_id);
			
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $lsettings['cpt_id']);
			
			$form_params = cpt_getParamsFromForm($component_info, $PostVars);

			$cpt_smarty = cpt_getSmarty($component_info, $form_params);
			if(PEAR::isError($cpt_smarty)){
				$errorEntry = $cpt_smarty;break;
			}

			$ser_data = cpt_serializeData(cpt_getParamsFromFormExt($component_info, $PostVars));
			if(PEAR::isError($ser_data)){
				$errorEntry = $ser_data;break;
			}
			
			storeWData(__CPT_SMARTY.$cpt_tpl_id, $cpt_smarty);
			storeWData(__CPT_PARAMS.$cpt_tpl_id, array_merge($lsettings, $ser_data));

			define('__CPT_TPL_ID_CONST', $cpt_tpl_id);
			
			$r_id = md5($PostVars['component_id'].time());
			smarty_resource_register_register('__custom_code-'.$r_id, $cpt_smarty, time());

			$response_params = array('cpt_tpl_id' => $cpt_tpl_id, 'cptHTML' => $smarty->fetch('register:__custom_code-'.$r_id));
			$response_params['cptHTML'] = preg_replace('@^\<div id\="cpt\-tpl\-id\-[^\>]+\>(.*)<\/div>$@msi', '$1', $response_params['cptHTML']);
		}while (0);
		
			if(PEAR::isError($errorEntry)){
				
				Message::raiseAjaxMessage(MSG_ERROR, 0, $errorEntry->getMessage());
			}else {
				
				Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'saved', $response_params );
			}
			die;
		case 'CPT_GET_LSETTINGS_FORM':
			
			$lsettings = loadWData(__CPT_PARAMS.$PostVars['cpt_tpl_id']);
		
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $lsettings['cpt_id']);
	
			global $_RESULT;
			$_RESULT['lsettings_form_html'] = cpt_getForm('local_settings', $component_info, $themeEntry, $lsettings);
			die;
		case 'CPT_SAVE_SLIP':

			$ready_template = loadWData(__CPT_RAW_TPL. $tpl_id);

			$template_path = $themeEntry->getTemplatePath($tpl_id);
			foreach ($PostVars['slip'] as $container_id => $cpts){
				
				$container_id = str_replace('cpt-container-id-', '', $container_id);
				$cpts_str = '';
				foreach ($cpts as $cpt_tpl_id){
					$cpt_tpl_id = str_replace('cpt-tpl-id-', '', $cpt_tpl_id);
					$cpts_str .= loadWData(__CPT_SMARTY.$cpt_tpl_id);
				}
				
				$ready_template = preg_replace('@(<\!-- cpt_container_start)\[id\='.$container_id.'\]( -->).*?(<\!-- cpt_container_end -->)@msi','$1$2'.$cpts_str.'$3', $ready_template);
			}
			
			$overrideStyles = &CptOverrideStyles::instance();
			$overrideStyles->save_cache();
			$overrideStyles->erase_cache();
			
			$themeEntry->saveTemplate($tpl_id, $ready_template, isset($PostVars['temp_saving']) && $PostVars['temp_saving'], isset($PostVars['contentChanged']) && $PostVars['contentChanged']);

			Message::raiseAjaxMessage(MSG_SUCCESS, 0, 'thm_template_saved_msg');
			die;
		case 'CPT_PREPARE_HTMLCODE':do{
		
			$errorEntry = null;
			
			$component_info = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponentInfo', $PostVars['component_id']);
			
			$cpt_smarty = cpt_getSmarty($component_info, cpt_getParamsFromForm($component_info, $PostVars));
			if(PEAR::isError($cpt_smarty)){
				$errorEntry = $cpt_smarty;break;
			}
			
			global $_RESULT;
			$r_id = md5($PostVars['component_id'].time());
			smarty_resource_register_register('__custom_code-'.$r_id, $cpt_smarty, time());
	
			$_RESULT['cptHTML'] = $smarty->fetch('register:__custom_code-'.$r_id);
		}while (0);
		
			if(PEAR::isError($errorEntry)){
				
				Message::raiseAjaxMessage(MSG_ERROR, 0, $errorEntry->getMessage());
			}
			die;
	}
	
	storeWData(__CPT_TPL_N, 0);

	$components = ModulesFabric::callModuleInterface('cptmanager', 'cpt_getComponents', $tpl_id);

	for($i=0, $max_i = count($components); $i<$max_i; $i++){
		
		$components[$i]['__lsettings_form'] = cpt_getForm('local_settings', $components[$i], $themeEntry);
	}
	
	$smarty->assign('components', $components);
	
	$template_content = $themeEntry->getTemplateContent($tpl_id, true);

	$replaces = 0;
	function cpt_repl($matches){
		
		static $cnt;
		if(is_null($cnt))$cnt = 0;
		$cnt++;
		return '<!-- cpt_container_start[id='.$cnt.'] -->';
	}
	$template_content = preg_replace_callback('@<\!-- cpt_container_start -->@msi', 'cpt_repl', $template_content);

	$overrideStyles = &CptOverrideStyles::instance();
	$overrideStyles->erase_cache();
	
	storeWData(__CPT_RAW_TPL.$tpl_id, $template_content);
	
	$template_content = preg_replace('@<\!-- cpt_container_start\[id\=(\d+)\] -->@msi','<table cellpadding="0" frame="none" border="0" rules="none" cellspacing="0" style="width:100%;height: 100%;"><tr><td class="cpt_container" id="cpt-container-id-$1">', $template_content);
	$template_content = preg_replace('@<\!-- cpt_container_end -->@msi','</td></tr></table>', $template_content);
	
	smarty_resource_register_register('cpt_tpl_content', $template_content, time());
	
	$smarty->force_compile = true;
	
	$smarty->assign('theme_id', $theme_id);
	$smarty->assign('tpl_content', $smarty->fetch('register:cpt_tpl_content'));	
	$smarty->assign('tpl_head', $smarty->fetch($themeEntry->getTemplatePath('head')));	
	$smarty->assign('tpl_css', $themeEntry->getTemplateContent('css'));
	$smarty->assign('templated_changed', $Register->get('__THMTPL_CONTENTCHANGED'));
	
	$divisionEntry = &$Register->get(VAR_CURRENTDIVISION);
	$divisionEntry->MainTemplate = 'cpt_constructor.html';
?>