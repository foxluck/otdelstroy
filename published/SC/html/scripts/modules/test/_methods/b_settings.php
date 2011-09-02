<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();

// payment types list
$setting_groups = settingGetAllSettingGroup();
$smarty->assign('setting_groups', $setting_groups );

if ( isset($_POST) && count($_POST)>0 )
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		if ( isset($_GET['settings_groupID']) )
			Redirect(set_query('&settings_groupID='.$_GET['settings_groupID'].'&safemode=yes'));
		else
			Redirect(set_query('&safemode=yes'));
	}
}

if ( isset($_GET['settings_groupID']) ){
	
	$settings = settingGetSettings( $_GET['settings_groupID'] );
	$smarty->assign('settings', $settings );

	$smarty->assign('controls', settingCallHtmlFunctions($_GET['settings_groupID']) );
	$smarty->assign('settings_groupID', $_GET['settings_groupID'] );
}

if ( !isset($_GET['settings_groupID']) && count($setting_groups) > 0 )
	Redirect(set_query('&settings_groupID='.	$setting_groups[0]['settings_groupID']));

// set sub-department template
$smarty->assign('admin_sub_dpt', 'conf_setting.tpl.html');
?>