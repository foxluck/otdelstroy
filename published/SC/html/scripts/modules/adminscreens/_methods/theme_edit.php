<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	$Register = &Register::getInstance();
	$smarty = &$Register->get(VAR_SMARTY);
	/* @var $smarty Smarty */
	$GetVars = &$Register->get(VAR_GET);
	$PostVars = &$Register->get(VAR_POST);
	
	$theme_id = isset($GetVars['theme_id'])?$GetVars['theme_id']:'';
	
	$themeEntry = new Theme();
	$res = $themeEntry->load($theme_id);
	if(PEAR::isError($res))throwMessage($res);

	if(isset($GetVars['reset'])){
		
		$themeEntry->reset();
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'reset=', 'thm_msg_successful_reset');
	}
	
	$templates_info = $themeEntry->getTemplatesInfo();
	$tpl_id = isset($GetVars['tpl_id'])&& array_key_exists($GetVars['tpl_id'], $templates_info)?$GetVars['tpl_id']:'';

	if(!strlen($tpl_id)){
		foreach ($templates_info as $tpl_id => $info)break;
	}
	
	$edmod = isset($GetVars['edmod']) && in_array($GetVars['edmod'], array('simple', 'advanced'))?$GetVars['edmod']:'simple';
	if(!$templates_info[$tpl_id][$edmod.'_editor'])$edmod = 'advanced';
	
	$smarty->assign(array(
		'edmod' => $edmod,
		'comment_str' => $edmod=='simple'?'thm_designeditor_descr_simple':($tpl_id!=TPLID_CSS&&$tpl_id!=TPLID_HEAD?'thm_designeditor_descr_advanced':($tpl_id==TPLID_CSS?'thm_designeditor_descr_css':($tpl_id==TPLID_HEAD?'thm_designeditor_descr_head':''))),
		'edmod_file' => "backend/theme_edmod_{$edmod}.htm"
	));
	
	$smarty->assign('templates_info', $templates_info);
	$smarty->assign('tpl_id', $tpl_id);

	include_once dirname(__FILE__)."/theme_edmod_{$edmod}.php";
	
	$smarty->assign('theme', array('id' => $theme_id, 'title' => $themeEntry->getTitle()));
	$smarty->assign('theme_id', $theme_id);
	$smarty->assign('theme_is_local', $themeEntry->isLocal());
	
	$smarty->assign('admin_sub_dpt', 'theme_edit.htm');
?>