<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */

class ThemesManager extends ActionsController
{
	function apply_theme()
	{
		
		$theme_id = $this->getData('theme_id');
		if($theme_id){
			_setSettingOptionValue('CONF_CURRENT_THEME', $theme_id);
			Theme::cleanUpCache();
			Message::raiseMessageRedirectSQ(MSG_SUCCESS, 'apply_theme', 'thm_msg_theme_applied');
		}
			
	}
	
	function export_theme()
	{
		
	}
	
	function install_theme()
	{
		
	}

	function main()
	{
		$smarty = &Core::getSmarty();
		/*@var $smarty Smarty */
		$themes = ns_themes::getThemes();

		$smarty->assign('current_theme', $themes[CONF_CURRENT_THEME]);
		unset($themes[CONF_CURRENT_THEME]);

		$smarty->assign('themes', $themes);
		$smarty->assign('admin_sub_dpt', 'themes_list.htm');
			
	}
}


ActionsController::exec('ThemesManager');
?>