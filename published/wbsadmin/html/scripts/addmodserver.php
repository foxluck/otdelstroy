<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}
$infoStr = null;
$invalidField = null;
if(!isset($server)&&!wbs_multiDbkeyEnabled()){
	$server_list = array_keys($wbs_sqlServers);
	if(count($server_list)){
		$action = ACTION_EDIT;
		$server = base64_encode($server_list[0]);
	}else{
		$action = ACTION_NEW;
	}
}

switch (true) {
	case (true) :
		if ( $action == ACTION_EDIT && (!isset($edited) || !$edited) ) {
			$server = base64_decode($server);

			$serverData = $wbs_sqlServers[$server];
			$serverData['SERVER_NAME'] = $server;
		}

		if ( $action == ACTION_NEW && (!isset($edited) || !$edited)&&(wbs_multiDbkeyEnabled()||!count($wbs_sqlServers)) ) {
			$serverData = array( WBS_HOST=>'localhost',WBS_DBCHARSET=>'utf8', WBS_ENCODING=>'utf8', WBS_LANGUAGES=>array(), WBS_WEBASYSTHOST=>'localhost' );
		}

		$sys_languages = wbs_listSysLanguages();
		if ( PEAR::isError($sys_languages) ) {
			$errorStr = $sys_languages->getMessage();
			$fatalError = true;
			break;
		}
}

$btnIndex = getButtonIndex( array( "savebtn", "cancelbtn", "deletebtn", "testbtn" ), $_POST );

switch( $btnIndex ) {
	case 0 :  {
		$serverData = prepareArrayToStore( $serverData );

		if ( !isset($serverData[WBS_LANGUAGES]) )
		$serverData[WBS_LANGUAGES] = array();
		$res = wbsadmin_addmodSQLServer( $action, $serverData, $kernelStrings, $LocalizationStrings );

		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();
			$invalidField = $res->getUserInfo();
			break;
		}
		redirectBrowser( PAGE_DB_SQLSERVERS, array('msg'=>base64_encode($LocalizationStrings['wbs_settings_update_success'])),"",false,false,true);
	}
	case 1 : redirectBrowser( PAGE_DB_SQLSERVERS, array() );
	case 2 : {
		$res = wbsadmin_deleteSQLServer( $serverData, $kernelStrings, $LocalizationStrings );
		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();
			break;
		}
		redirectBrowser( PAGE_DB_SQLSERVERS, array() );
	}
	case 3 : {
		$host = (strlen($serverData[WBS_PORT])) ? $serverData[WBS_HOST].":".$serverData[WBS_PORT] : $serverData[WBS_HOST];
		if ( $res = @mysql_connect( $host, $serverData[WBS_ADMIN_USERNAME], $serverData[WBS_ADMIN_PASSWORD] )) {
			$infoStr = "Connection successful";
			mysql_close($res);
		} else{
			$errorStr = mysql_error();
		}
		break;
	}
}

$pageTitle = ($action == ACTION_NEW) ? $LocalizationStrings[8] : $LocalizationStrings[9];

$preproc->assign( PAGE_TITLE, $pageTitle);
$preproc->assign( 'pageHeader', $pageTitle );
$preproc->assign( FORM_LINK, PAGE_DB_ADDMODSERVER );
$preproc->assign( INVALID_FIELD, $invalidField );
$preproc->assign( ACTION, $action );
$preproc->assign( "infoStr", $infoStr );
$preproc->assign( 'buttonCaption', ($action == ACTION_NEW) ? $LocalizationStrings[11] : $LocalizationStrings[10] );

if ( !$fatalError ) {
	$preproc->assign( 'serverData', prepareArrayToDisplay($serverData) );
	$preproc->assign( 'sys_languages', $sys_languages );
}
$preproc->assign('multiDBKEY',wbs_multiDbkeyEnabled());

$preproc->assign( "mainTemplate","addmodserver.htm" );
?>