<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}




$params=array();

switch (true) {
	case (true) :

		$timeZones = array();
		foreach( $GLOBALS['_DATE_TIMEZONE_DATA']  as $key=>$val )
		$timeZones[] = array(
		'ID' => $key,
		'NAME' => sprintf("%s %s", $val['shortname'], $val['longname'] ),
		'DST' => $val['hasdst']
		);

		if ( !isset($edited) || !$edited )
		{
			$commondata = array();

			$installInfo=wbs_getInstallInformation();
			$commondata['COMPANY'] = $installInfo['COMPANY'];
			$commondata['LICENSE'] = $installInfo['LICENSE'] ;

			$commondata['EMAIL'] = $sendmail_enabled;
			$commondata['DATA_PATH'] = fixPathSlashes($wbs_dataPath);
			$commondata['WBS_INSTALL_PATH'] = WBS_INSTALL_PATH;
			$commondata['PORT'] = HTTPS_PORT;
			$commondata['TIMEOUT'] = SESSION_TIMEOUT/60;
			$commondata['ROBOTEMAIL'] = $wbs_robotemailaddress;
			$commondata[WBS_MEMORYLIMIT] = $wbs_settingsLimit;

			$commondata['SERVER_TZ'] = SERVER_TZ;

			$commondata['SERVER_TIME_ZONE_ID'] = SERVER_TIME_ZONE_ID;
			$commondata['SERVER_TIME_ZONE_DST'] = SERVER_TIME_ZONE_DST;
			$commondata = array_merge($commondata,wbs_getProxySettings(),wbs_getSmtpSettings());
		}
}

$btnIndex = getButtonIndex( array("savebtn", "cancelbtn","returnbtn"), $_POST );

switch( $btnIndex ) {
	case 0 :
		$commondata = rescueElement( $commondata, "EMAIL", 0 );

		$res = wbs_saveCommonSettings( $commondata, $kernelStrings, $db_locStrings );

		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();
			if ( $res->getCode() == ERRCODE_INVALIDFIELD )
			$invalidField = $res->getUserInfo();

			break;

		}
		$params['msg']=base64_encode('wbs_settings_update_success');
	case 1 :
	case 2 :
		redirectBrowser( PAGE_SECTION_SETUP, $params,"",false,false,true  );

}
//
$preproc->assign( FORM_LINK, PAGE_DB_COMMON );

$preproc->assign( PAGE_TITLE, 'cmn_set_page_title');

if ( !$fatalError ) {
	$preproc->assign( 'commondata', prepareArrayToDisplay($commondata, null, true) );
	$preproc->assign( "timeZones", $timeZones );
}

$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
$preproc->assign( 'defavalmem', WBS_DEFMEMORYAVAILABLE );

if($invalidField){
	$preproc->assign ( 'JavaScriptOnLoad','focusControl("commondata['.$invalidField.']")');
}

$preproc->assign( "mainTemplate","commonsettings.htm" );
?>