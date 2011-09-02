<?php
/**
 * @package Modules
 * @subpackage AdministratorScreens
 */
	$Register = &Register::getInstance();
	/*@var $Register Register*/
	$smarty = &$Register->get(VAR_SMARTY);
	/*@var $smarty Smarty*/
	$settings_groupID = 7;
	if ( isset($_POST) && count($_POST)>0 ){
		
		safeMode(true);
	}
	
	$settings = settingGetSettings( $settings_groupID );
	$smarty->assign('settings', $settings );
	
	$smarty->assign('controls', settingCallHtmlFunctions($settings_groupID) );
	
	$Message = &$Register->get(VAR_MESSAGE);
	if($_POST['save'] && (!Message::isMessage($Message) || !$Message->is_set())){
		
		Message::raiseMessageRedirectSQ(MSG_SUCCESS, '', 'msg_information_saved');
	}
	$smarty->assign('admin_sub_dpt', 'googleanalytics.html');
?>