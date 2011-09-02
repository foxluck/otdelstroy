<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */	
	if(!wbs_auth()){

		RedirectSQ('?ukey=TitlePage');
	}
	$Register = &Register::getInstance();

	$PostVars = &$Register->get(VAR_POST);
	$GetVars = &$Register->get(VAR_GET);
	$smarty = &$Register->get(VAR_SMARTY);

	include_once DIR_FUNC.'/func.component.php';

	$theme_id = isset($GetVars['theme_id'])?$GetVars['theme_id']:'';
	$tpl_id = TPLID_GENERAL_LAYOUT;
	
	$themeEntry = new Theme();
	$res = $themeEntry->load($theme_id);
	if(PEAR::isError($res)){

		RedirectSQ('?ukey=TitltePage');
	}
	
	$Register->set('CURRENT_THEME_ENTRY', $themeEntry);
	
	$template_content = $themeEntry->getTemplateContent($tpl_id);

	smarty_resource_register_register('cpt_tpl_content', $template_content, time());
	$smarty->force_compile = true;
	
	$smarty->assign('tpl_content', $smarty->fetch('register:cpt_tpl_content'));	
	$smarty->assign('tpl_head', $smarty->fetch($themeEntry->getTemplatePath('head')));	
	$smarty->assign('tpl_css',$themeEntry->getTemplateContent('css'));	
	$smarty->assign('tpl_css_path',$themeEntry->getPath());	

	$divisionEntry = &$Register->get(VAR_CURRENTDIVISION);
	$divisionEntry->MainTemplate = 'theme_preview.html';
	?>