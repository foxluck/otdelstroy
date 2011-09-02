<?php
//old unused code see firststep.php
	$init_required = false;

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	define( 'COMPLETE_FLAG', WBS_DIR."kernel/complete" );
	

	$templateName = "classic";
//	$language = LANG_ENG;

	$locStrings = $loc_str[$language];
	$db_locStrings = $db_loc_str[$language];

	$fatalError = false;
	$errorStr = null;
	$invalidField = null;
	$profileCreated = false;

	if ( !isset( $action ) || $action == "" )
		$action = ACTION_NEW;

	if ( $action == ACTION_NEW )
		$DB_KEY = null;


	$u = isset( $_POST["u"] ) ? $_POST["u"] : $_GET["u"];
	$p = isset( $_POST["p"] ) ? $_POST["p"] : $_GET["p"];
	$isadm = isset( $_POST["isadm"] ) ? $_POST["isadm"] : $_GET["isadm"];

	define( "DATE_DISPLAY_FORMAT", DB_DEF_DATE_FORMAT );

	function showStepProgress($step)
	{
		$step_=array(0,0,1,2,2,3,3,4,);
		if(isset($step_[$step])){
			$step=$step_[$step];
		}else{
			$step=-1;
		}
		$names=array('License','System','Extract','MySQL','Done');
		$step_string='';
		foreach ($names as $stepNum=>$stepName){
			if($stepNum<$step){
				$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="color:black">'.$stepName.'</span>';
			}elseif ($stepNum==$step){
				$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="font-size:130%;font-weight:bolder;color:black">'.$stepName.'</span>';		
			}else{
				$step_string.=(strlen($step_string)?'&nbsp;&raquo;&nbsp;':'').'<span style="color:grey">'.$stepName.'</span>';		
			}
		}
		return $step_string;		
	}
	$btnIndex = getButtonIndex( array("savebtn", "cancelbtn"), $_POST );

	switch ( $btnIndex ) {
		case 1 : redirectBrowser( '../../../../install.php', array( 'cancel'=>1 ) );
	}

	switch (true) {
		case (true) : {
			// Load application list
			//
			$appData = listPublishedApplications( $language, true );
			if ( !is_array( $appData ) ) {
				$errorStr = $db_locStrings[1];
				$fatalError = true;

				break;
			}

			$appData = sortPublishedApplications( $appData );

			foreach( $appData as $APP_ID=>$cur_data ) {
				$parents = $cur_data[APP_REG_PARENTS];
				if ( false !== ($index = array_search ( AA_APP_ID, $parents ) ) ) {
					unset($parents[$index]);

					$pub = array();
					foreach ( $parents as $cur_parent )
						$pub[] = $cur_parent;

					$cur_data[APP_REG_PARENTS] = $pub;
					$appData[$APP_ID] = $cur_data;
				}
			}

			if ( !isset($app_list) )
				$app_list = null;

			$appData = html_extendApplicationData( $appData, $app_list, "Dependent on", $language );
			$appData = fixApplicationDependences( $appData );

			if ( $action == ACTION_NEW && !isset($edited) )
			{
				$hostData = array();
				$hostData[HOST_DBSETTINGS][HOST_DATE_FORMAT] = HOST_DEF_DATE_FORMAT;
				$hostData[HOST_DBSETTINGS][HOST_MAXUSERCOUNT] = HOST_DEF_MAXUSERCOUNT;
			}

			$serverNames = array_keys($wbs_sqlServers);

			foreach( $dateFormats as $dateFormat ) {
				$dateFormat_ids[] = $dateFormat;
				$dateFormat_names[] = $dateFormat;
			}

			$hostData[HOST_DBSETTINGS][HOST_SQLSERVER] = $serverNames[0];

			if ( $action == ACTION_NEW ) {
				if ( (!isset($edited) || !$edited) ) {
					$serverData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
					$hasAdminRights = $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL;

					if ( !$hasAdminRights )
						$hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] = "use";
				}

				$prevServerName = $hostData[HOST_DBSETTINGS][HOST_SQLSERVER];

			}

			$language_names = $language_ids = $language_names_indexed = array();
			$curServerData = $wbs_sqlServers[$hostData[HOST_DBSETTINGS][HOST_SQLSERVER]];
			foreach ( $curServerData[WBS_LANGUAGES] as $lang_id => $lang_name ) {
				$language_names[] = $lang_name;
				$language_ids[] = $lang_id;
				$language_names_indexed[$lang_id] = $lang_name;
			}

		}

		$hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER] = base64_decode( $u );
		$hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD] = base64_decode( $p );

		// Account operations log
		//
		$dom = null;
		$logRecords = listAccountLogRecords( $DB_KEY, $locStrings, $dom );
		if ( PEAR::isError($logRecords) || !count($logRecords) )
			$logRecords = null;

		if ( $logRecords ) {
			$logData = array();
			foreach( $logRecords as $index=>$logRecord ) {
				$opType = $logRecord->get_attribute(AOPR_TYPE);

				$opTypeName = $locStrings[$accountOperationNames[$opType]];

				$recordData = array( AOPR_DATETIME => convertToDisplayDateTime( $logRecord->get_attribute(AOPR_DATETIME) ),
									AOPR_TYPE => $opTypeName,
									AOPR_IP => $logRecord->get_attribute(AOPR_IP) );

				if ( $opType == aop_modify )
					$recordData['ROW_URL'] = prepareURLStr( PAGE_DB_LOGDATA, array( 'DB_KEY'=>$DB_KEY,
										'index'=>$index ) );

				$logData[] = $recordData;
			}

			$logRecords = array_reverse($logData);
		}
	}

	switch ( $btnIndex ) {
		case 0 : {
			
			$hostData[HOST_DBSETTINGS][HOST_DBSIZE_LIMIT] = "";
			$hostData[HOST_DBSETTINGS][HOST_EXPIRE_DATE] = "";
			$hostData[HOST_ADMINISTRATOR]['PASSWORD2'] = $hostData[HOST_ADMINISTRATOR]['PASSWORD1'] = $hostData[HOST_FIRSTLOGIN]['PASSWORD2'] = $hostData[HOST_FIRSTLOGIN]['PASSWORD1'];
			$hostData[HOST_DBSETTINGS][HOST_DATE_FORMAT] = HOST_DEF_DATE_FORMAT;
			$hostData[HOST_DBSETTINGS][HOST_MAXUSERCOUNT] = null;
			$hostData[HOST_ADMINISTRATOR][HOST_LANGUAGE] = LANG_ENG;

			$dbSettingsData = prepareArrayToStore( $hostData[HOST_DBSETTINGS] );
			$accountData = prepareArrayToStore( $hostData[HOST_FIRSTLOGIN] );
			$adminData = prepareArrayToStore( $hostData[HOST_ADMINISTRATOR] );

			$dbCreateData = ($action == ACTION_NEW) ? prepareArrayToStore( $hostData[HOST_DB_CREATE_OPTIONS] ) : array();
			$invalidArr = 0;

			if ( !isset($dbSettingsData[HOST_READONLY]) )
				$dbSettingsData[HOST_READONLY] = 0;

			$appData = listPublishedApplications( $language, true );
			if ( !is_array( $appData ) ) {
				$errorStr = $db_locStrings[1];
				$fatalError = true;

				break;
			}

			$appData = sortPublishedApplications( $appData );

			foreach( $appData as $APP_ID=>$cur_data ) {
				$parents = $cur_data[APP_REG_PARENTS];
				if ( false !== ($index = array_search ( AA_APP_ID, $parents ) ) ) {
					unset($parents[$index]);

					$pub = array();
					foreach ( $parents as $cur_parent )
						$pub[] = $cur_parent;

					$cur_data[APP_REG_PARENTS] = $pub;
					$appData[$APP_ID] = $cur_data;
				}
			}


			$appList = array();

			foreach( $appData as $APP_ID => $APP_DATA )
				$appList[] = $APP_ID;

			if ( !strlen( $dbSettingsData[HOST_DB_KEY] ) ) {
				if ( isset($hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION]) ) {
					if ( $hostData[HOST_DB_CREATE_OPTIONS][HOST_CREATE_OPTION] == "new" )
						$newDBKey = substr( $dbCreateData[HOST_DATABASE_NEW], 0, DB_MAXDBKEYLEN );
					else
						$newDBKey = substr( $dbCreateData[HOST_DATABASE_EXISTING], 0, DB_MAXDBKEYLEN );
				} else
					$newDBKey = null;

				$dbSettingsData[HOST_DB_KEY] = $newDBKey;
				$hostData[HOST_DBSETTINGS][HOST_DB_KEY] = $newDBKey;
			}

			$dbSettingsData[HOST_RECIPIENTSLIMIT] = null;

			if ( !isset( $hostData ) )
				$hostData = array();

			$hostData[HOST_BALANCE][MODULE_CLASS_SMS][HOST_VALUE] = "UNLIMITED";
			$hostData[HOST_MODULES][MODULE_CLASS_SMS]["ID"] = "";
			$hostData[HOST_MODULES][MODULE_CLASS_SMS]["DISABLED"] = 1;

			$dbSettingsData[HOST_MODULES] = $hostData[HOST_MODULES];
			$dbSettingsData[HOST_BALANCE] = $hostData[HOST_BALANCE];

			$res = addModDBProfile( $action, $DB_KEY, $dbSettingsData, $accountData, $adminData, $dbCreateData, $locStrings, $invalidArr, $appList, null, null, true );

			if ( PEAR::isError( $res ) ) {

				if ( strlen( $res->getUserInfo()) ) {

					$invalidField = $res->getUserInfo();

					if ( $invalidArr == 0 )
						$invalidField = sprintf( "hostData[DBSETTINGS][%s]", $invalidField );
					elseif ( $invalidArr == 1 )
						$invalidField = sprintf( "hostData[FIRSTLOGIN][%s]", $invalidField );
					elseif ( $invalidArr == 2 )
						$invalidField = sprintf( "hostData[ADMINISTRATOR][%s]", $invalidField );
					elseif ( $invalidArr == 3 )
						$invalidField = sprintf( "hostData[DB_CREATE_OPTIONS][%s]", $invalidField );
				}

				$errorStr = $res->getMessage();

				if ( $invalidField == "hostData[ADMINISTRATOR][PASSWORD1]" )
					$invalidField = "hostData[FIRSTLOGIN][PASSWORD1]";

				break;
			}

			$profileCreated = true;
			$DB_KEY = $res;


			$complete = fopen( COMPLETE_FLAG, "w+" );
			fputs( $complete, "DO NOT REMOVE THIS FILE!" );
			fclose( $complete );

			if ( $sendmail_enabled )
			{

				$scriptName = $_SERVER['SCRIPT_NAME'];

				$scriptPath = substr( $scriptName, 0, strlen($scriptName)-strlen(basename($scriptName)) );
				$scriptPath = substr( $scriptPath, 0, strpos( $scriptPath, "/published/wbsadmin/html/scripts/" ) );

				$serverName = $_SERVER['SERVER_NAME'];

				$dbAdminURL = sprintf( "http://%s%s/published/admin.php", $serverName, $scriptPath );
				$loginURL = sprintf( "http://%s%s/published/login.php", $serverName, $scriptPath );

				$loginPageURL = prepareURLStr( $loginURL, array('DB_KEY'=>$DB_KEY) );

				$adminPageURL = $dbAdminURL;

				$message  = $db_locStrings['install_maillog_title'];
				$message .= $db_locStrings['install_maillog_congr'];
				$message .= $db_locStrings['install_maillog_wba'];
				$message .= sprintf( "%s\n", $adminPageURL  );
				$message .= "\n\n";
				$message .= $db_locStrings['install_maillog_wbd'];
				$message .= sprintf( "%s", $loginPageURL  );
				$message .= "\n\n";
				$message .= sprintf( $db_locStrings['install_maillog_dbkey'],  $DB_KEY );
				$message .= sprintf( $db_locStrings['install_maillog_login_as'], $hostData[HOST_FIRSTLOGIN]['FIRSTNAME'], $hostData[HOST_FIRSTLOGIN]['LASTNAME'] );
				$message .= sprintf( $db_locStrings['install_maillog_login'],  $hostData[HOST_FIRSTLOGIN]['LOGINNAME'] );
				$message .= sprintf( $db_locStrings['install_maillog_password'],  $hostData[HOST_FIRSTLOGIN]['PASSWORD1'] );
				$message .= $db_locStrings['install_maillog_login_as_adm']."ADMINISTRATOR\n";
				$message .= sprintf( $db_locStrings['install_maillog_password'],  $hostData[HOST_ADMINISTRATOR]['PASSWORD1'] );;

				mail( $hostData[HOST_FIRSTLOGIN]['EMAIL'], "WebAsyst Installation Wizard Notification", $message, "From: \"WebAsyst Installation Wizard\"<$wbs_robotemailaddress>\nContent-Type: text/plain; charset=\"windows-1251\"");
			}

			break;
		}
	}

	$pageTitle = ($action == ACTION_NEW) ? $db_locStrings[4] : $db_locStrings[5];
	extract(wbs_getSystemStatistics());

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	$preproc->assign( 'systemConfiguration', $systemConfiguration['info'] );
	//$preproc->assign( 'companyInfo', $companyInfo );
	//$preproc->assign( 'systemInfo', $systemInfo );
	
	$preproc->assign( PAGE_TITLE, $db_locStrings['install_title'] );
	$preproc->assign ( 'waStrings', $db_locStrings);
	
	$preproc->assign( FORM_LINK, PAGE_DB_WBSINSTALL_STEP2 );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ACTION, $action );
	$preproc->assign( "buttonCaption", ($action == ACTION_NEW) ? $db_locStrings['btn_add_db']:$db_locStrings['btn_save']);

	$preproc->assign( "isadm", $isadm );
	$preproc->assign( "u", $u );
	$preproc->assign( "p", $p );

	$preproc->assign( 'installGuideFile', INSTALL_GUIDE_FILE );

	if ( $action == ACTION_EDIT || $profileCreated )
	{
		$preproc->assign( "DB_KEY", $DB_KEY );
		$preproc->assign( "LOGINNAME", $hostData[HOST_FIRSTLOGIN]['LOGINNAME'] );
		$preproc->assign( "LPASSWORD", $hostData[HOST_FIRSTLOGIN]['PASSWORD1'] );

	}

	if ( !$fatalError ) {
		$preproc->assign( "hostData", $hostData );

		$preproc->assign( "language_names", $language_names );
		$preproc->assign( "language_ids", $language_ids );
		$preproc->assign( "language_names_indexed", $language_names_indexed );

		$preproc->assign( "dateFormat_ids", $dateFormat_ids );
		$preproc->assign( "dateFormat_names", $dateFormat_names );

		$preproc->assign( "app_data", $appData );
		$preproc->assign( "profileCreated", $profileCreated );

		$preproc->assign( "serverNames", $serverNames );

		$preproc->assign( "logRecords", $logRecords );
		$preproc->assign( "step", showStepProgress(6) );

		if ( isset($hasAdminRights) )
			$preproc->assign( "hasAdminRights", $hasAdminRights );

		if ( $profileCreated )
		{
			$preproc->assign( "loginURL", sprintf( "login.php?DB_KEY=%s", $DB_KEY ) );
			$preproc->assign( "adminURL", sprintf( "admin.php" ) );
		}

		if ( $action == ACTION_NEW )
			$preproc->assign( "prevServerName", $prevServerName );
	}

	$preproc->display( "setupdbprofile.htm" );
?>
