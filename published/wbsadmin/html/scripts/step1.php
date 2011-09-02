<?php
//old unused code see firststep.php

	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$templateName =	"classic";
//	$language =	LANG_ENG;

	// Load	application	strings
	//
	$locStrings = $loc_str[$language];
	$db_locStrings = $db_loc_str[$language];
	$fatalError = false;
	$invalidField = null;
	$errorStr = null;
	$infoStr = null;
	
	function showStepProgress($step)
	{
		$step_=array(0,0,1,2,2,3,3,4);
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

	if ( !isset( $action ) )
		$action	= ACTION_NEW;

	switch (true) {
		case (true) :
			if ( $action == ACTION_NEW && (!isset($edited) || !$edited) ) 
			{
				$languages = array( DEF_LANG_ID=>DEF_LANG_NAME );
				$serverData = array( WBS_HOST=>'localhost', WBS_ENCODING=>'iso-8859-1', WBS_LANGUAGES=>$languages,	WBS_WEBASYSTHOST=>'localhost' );

				if ( count( $wbs_sqlServers ) != 0 )
					foreach ( $wbs_sqlServers as $key=>$data )
					{
						$serverData = $data;
						$serverData['SERVER_NAME'] = $key;
					 }
				}
	}

	$btnIndex =	getButtonIndex( array( "savebtn", "cancelbtn" ), $_POST );
	switch( $btnIndex )
	{
		case 0 : {
				if ( isset(  $serverData[WBS_ADMINRIGHTS] ) &&  $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL )
					$adminID = '1';
				else
					$adminID = '0';

				$u=base64_encode($serverData[WBS_ADMIN_USERNAME]);
				$p=base64_encode($serverData[WBS_ADMIN_PASSWORD]);

				if ( $adminID == 0 )
					$serverData[WBS_ADMIN_USERNAME] = $serverData[WBS_ADMIN_PASSWORD] = "";

				$serverData = prepareArrayToStore( $serverData );

				$serverData[WBS_LANGUAGES] = array( DEF_LANG_ID=>DEF_LANG_NAME );

				$res = wbsadmin_addmodSQLServer( count( $wbs_sqlServers	) != 0 ? ACTION_EDIT : ACTION_NEW, $serverData, $locStrings, $db_locStrings );
				if ( PEAR::isError( $res ) ) {
					$errorStr = $res->getMessage();

					$invalidField = $res->getUserInfo();

					$serverData[WBS_ADMIN_USERNAME] = base64_decode( $u );
					$serverData[WBS_ADMIN_PASSWORD] = base64_decode( $p );

					break;
				}

			redirectBrowser( PAGE_DB_WBSINSTALL_STEP2, array( 'u'=>$u, 'p'=>$p,	'isadm'=>$adminID )	);
		}

		case 1 :
				redirectBrowser( '../../../../install.php', array(	'cancel'=>1	) );

		default:
				break;

	}

	$pageTitle = ($action == ACTION_NEW) ? $db_locStrings[8] : $db_locStrings[9];

	extract(wbs_getSystemStatistics());

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	$preproc->assign( 'systemConfiguration', $systemConfiguration['info'] );
	//$preproc->assign( 'companyInfo', $companyInfo );
	//$preproc->assign( 'systemInfo', $systemInfo );
	
	$preproc->assign( PAGE_TITLE, $db_locStrings['install_title'] );
	$preproc->assign ( 'waStrings', $db_locStrings);
		
	$preproc->assign( FORM_LINK, PAGE_DB_WBSINSTALL_STEP1 );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ACTION, $action );
	$preproc->assign( 'GUIDE_FILE', GUIDE_FILE );
	$preproc->assign( "infoStr", $infoStr );
	$preproc->assign( "step", showStepProgress(5) );

	$preproc->assign( "disableChange", ( count( $wbs_sqlServers ) != 0 ) ? 1 : 0 );

	$preproc->assign( 'buttonCaption', $db_locStrings['install_continue']);

	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( INSTALL_GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfGuideFile', INSTALL_GUIDE_FILE );

	if ( !$fatalError )
	{
		$preproc->assign( 'serverData',	prepareArrayToDisplay($serverData) );
	}

	$preproc->display( "setupservers.htm" );

?>
