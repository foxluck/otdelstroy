<?php
/*
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );

	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );

	$templateName = "classic";
//	$language = LANG_ENG;
*/
	if ( !isset( $lang_id ) )
		$lang_id = $language;

	define( "ALLTYPES", "ALLTYPES" );

	// Load application strings
	//
/*	$kernelStrings = $loc_str[$language];
	$LocalizationStrings = $db_loc_str[$language];
	$fatalError = false;
	$errorStr = null;*/
	$updated = false;

	$btnIndex = getButtonIndex( array("cancelbtn", "languagesbtn", "applybtn", "undobtn"), $_POST );

	switch ( $btnIndex ) {
		case 0 :
				redirectBrowser( PAGE_DB_LANGUAGES, array( 'lang_id'=>$lang_id, 'app_id'=>$app_id ) );
		case 1 :
				redirectBrowser( PAGE_DB_LANGUAGES, array( 'lang_id'=>$lang_id, 'app_id'=>$app_id, ) );
		case 2 :
				$updated = true;
				$res = wbs_saveFullLocalizationStrings( $lang_id, $app_id, $userIds, $userGroups, $userStrings, $userDescr, $kernelStrings, $LocalizationStrings, true );

				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();

					break;
				}
				$messageStr .= $LocalizationStrings['wbs_settings_update_success'];

				break;
		case 3 :
				redirectBrowser( PAGE_DB_LOCALIZATION, array( 'lang_id'=>$lang_id, 'app_id'=>$app_id ) );
	}

	switch ( true ) {
		case true :
					// Load languages
					//
					$sys_languages = wbs_listSysLanguages();
					if ( PEAR::isError($sys_languages) )
					{
						$errorStr = $sys_languages->getMessage();
						$fatalError = true;

						break;
					}

					$lang_ids = array();
					$lang_names = array();
					$lang_vals = array();

					foreach ( $sys_languages as $key=>$value )
					{
						$lang_ids[] = $key;
						$lang_names[] = $value[WBS_LANGUAGE_NAME];
						$lang_vals[] = $value;

						if ( $key == $lang_id )
							$cur_lang = $value;
					}

					$show_import = 1;
					if ( $cur_lang[WBS_LANGUAGE_ID] == LANG_ENG )
						$show_import = 0;

					// Load applications
					//
					$appList = listPublishedApplications( $language, true );
					if ( !is_array( $appList ) ) {
						$fatalError = true;
						$errorStr = $LocalizationStrings[1];

						break;
					}

					$appList = sortPublishedApplications( $appList );
					if ( !is_array( $appList ) )
						return PEAR::raiseError( $LocalizationStrings[1] );
					$app_ids = array( AA_APP_ID,MW_APP_ID,WBSADMIN_APP_ID );
					$app_names = array( $LocalizationStrings[25], $LocalizationStrings[64], $LocalizationStrings[6] );
					
					//HACK for DD/2.0
					if(isset($appList['DD'])){
						$app_dd = 'DD';
						$appList_ = array();
						$extra_dds = array('%s 2.0'=>'%s ','%s 2.0 backend'=>'%s_backend_folders','%s 2.0 public'=>'%s_public','%s 2.0 template'=>'%s_template_backend');
						foreach($extra_dds as $alter_name=>$alter_path){
							$alter_app_id = sprintf($alter_path, $app_dd);
							$appList_[$alter_app_id] = $appList[$app_dd];
							
							$appList_[$alter_app_id][APP_REG_APPLICATION][APP_REG_LOCAL_NAME] = sprintf($alter_name,$appList_[$alter_app_id][APP_REG_APPLICATION][APP_REG_LOCAL_NAME]);
						}
						$appList = array_merge($appList,$appList_);
					}

					foreach( $appList as $key=>$value ) {
						$app_ids[] = $key;
						$app_names[] = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];
					}

					// Load string groups
					//
					$groups = wbs_listApplicationStringGroups( $app_id, $lang_id );

					foreach( $groups as $key=>$value )
					{
						$type_ids[] = base64_encode($value);
						$type_names[] = $value;
					}

					$appList = listPublishedApplications( LANG_ENG, true );
					if ( !is_array( $appList ) )
						return PEAR::raiseError( $LocalizationStrings[1] );

					$appList = array_merge( array(
											AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$LocalizationStrings[25]) ),
											MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$LocalizationStrings[64]) ),
											WBSADMIN_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$LocalizationStrings[6]) ),
											 ), $appList );
											 

					
					// Page encoding
					//
					$langData = $sys_languages[$lang_id];
					$html_encoding = $langData[WBS_ENCODING];

					if ( !isset($lang_id) /* || !in_array($lang_id, $lang_ids) */ )
						$lang_id = $lang_ids[0];

					if ( !isset($app_id) )
						$app_id = $app_ids[0];
						
					$appList = array($app_id=>$appList[$app_id]);
						
					$curLocalization = wbs_loadCheckedLocalizationStrings( $lang_id, $appList );

	}

	/*extract(wbs_getSystemStatistics());

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, "wbsadmin" );

	$preproc->assign( 'systemConfiguration', $systemConfiguration );
	$preproc->assign( 'companyInfo', $companyInfo );
	$preproc->assign( 'systemInfo', $systemInfo );
	
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );*/
	$preproc->assign( FORM_LINK, PAGE_DB_LOCALIZATION );

	if ( !$fatalError ) {

		$preproc->assign( "cur_lang", $cur_lang );
		$preproc->assign( "show_import", $show_import );

		$preproc->assign( "errorStr", $errorStr );

		$preproc->assign( "lang_ids", $lang_ids );
		$preproc->assign( "lang_names", $lang_names );
		$preproc->assign( "lang_id", $lang_id );

		$preproc->assign( "app_ids", $app_ids );
		$preproc->assign( "app_names", $app_names );
		$preproc->assign( "app_id", $app_id );

		if ( isset($type_ids) )
			$preproc->assign( "type_ids", $type_ids );

		if ( isset($type_names) )
			$preproc->assign( "type_names", $type_names );

		if ( isset($type_id) )
			$preproc->assign( "type_id", $type_id );

		$preproc->assign( "curLocalization", $curLocalization );

		$preproc->assign( "updated", $updated );
	}
	
	$preproc->assign( PAGE_TITLE, sprintf('%s &mdash; %s',translate('lll_page_title'),isset($cur_lang['NAME'])?$cur_lang['NAME']:translate('lll_page_name')));
	$preproc->assign( 'pdfGuideSize', sprintf( "%02.0fK", filesize( GUIDE_FILE )/1024 ) );
	$preproc->assign( 'pdfAdminFile', GUIDE_FILE );
	$preproc->assign( 'returnLink', PAGE_DB_WBSADMIN );
	$preproc->assign ( 'waStrings', $LocalizationStrings);

	//$preproc->display( "localization.htm" );
	$preproc->assign( "mainTemplate","localization.htm" );

?>
