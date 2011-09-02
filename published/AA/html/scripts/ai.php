<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );
	require_once( WBS_DIR."/published/hostagent/holderinfo.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$invalidField = null;

	switch (true) {
		case true :
			$holderInfo = new HolderInfo();
			$holderInfo = $holderInfo->Get( $DB_KEY );

			if ( PEAR::isError($holderInfo) )
			{
				$errorStr = $holderInfo->getMessage();
				$fatalError = true;
				break;
			}
			
			$holderInfo['regDate'] = convertToDisplayDate( $holderInfo['regDate'] );

			$link = sprintf( URL_REGISTER, base64_encode($DB_KEY), base64_encode($currentUser), base64_encode($language) );

			include WBS_DIR."kernel/hosting_plans.php";

			// Each application limits
			//
			$eachAppLimits = array();
			foreach ( $mt_commerce_applications as $appId )
			{
				if ( array_key_exists($appId, $global_applications) )
				{
					if ( in_array($appId, array(AA_APP_ID, MW_APP_ID)) || !array_key_exists($appId, $mt_hosting_plan_limitstats_data) )
						continue;

					$used = 0;
					$limit = getApplicationResourceLimits($appId);
					
					if ( isset($mt_hosting_plan_limitstats_data[$appId]) )
						$used = db_query_result($mt_hosting_plan_limitstats_data[$appId]['query'], DB_FIRST);

					$ratio = strlen($limit) ? round($used/$limit*100, 2) : null;
					$limit = strlen($limit) ? $limit : $kernelStrings['al_unlimited_label'];

					$eachAppLimits[$appId] = array(
						'appName'=>$global_applications[$appId][APP_NAME][$language],
						'status' => 1,
						'comment'=>$kernelStrings[$mt_hosting_plan_limitstats_data[$appId]['comment']], 
						'used'=>$used,
						'limit'=>$limit,
						'ratio'=>$ratio );
				} else
				{
					$appName = aa_getAppName( $appId );

					$eachAppLimits[$appId] = array(
						'appName'=>$appName[$language],
						'status' => 0 );
				}
			}

			// All applications limits
			//
			$allAppLimits = array();

			$QM = new DiskQuotaManager();
			$used = round($QM->GetUsedSpaceTotal($kernelStrings)/MEGABYTE_SIZE, 2);

			$limit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );
			$ratio = strlen($limit) ? round($used/$limit*100, 2) : null;
			$limit = strlen($limit) ? $limit.' MB' : $kernelStrings['al_unlimited_label'];

			$allAppLimits['SPACE'] = array( 'name'=> $kernelStrings['ai_limit_space_label'], 
				'limit'=>$limit, 
				'used'=>$used.' MB', 
				'ratio'=>$ratio );

			$limit = getApplicationResourceLimits( AA_APP_ID, 'USERS' );
			$used = db_query_result( $qr_selectGlobalUserCount, DB_FIRST, array( 'U_STATUS'=>RS_DELETED ) );
			$ratio = strlen($limit) ? round($used/$limit*100, 2) : null;
			$limit = strlen($limit) ? $limit : $kernelStrings['al_unlimited_label'];

			$allAppLimits['USERS'] = array( 'name'=> $kernelStrings['ai_limit_users_label'], 
				'limit'=>$limit, 
				'used'=>$used, 
				'ratio'=>$ratio );

			$limit = getSMSBalance( SMS_SYSTEM_USER );
			$limit = $limit[SMS_BALANCE];
			$limit = strlen($limit) ? '$'.$limit : $kernelStrings['al_unlimited_label'];

			$allAppLimits['SMS'] = array( 'name'=> $kernelStrings['ai_limit_sms_label'], 
				'limit'=>$limit, 
				'used'=>null, 
				'ratio'=>null );

			$ugpradeLink = getUpgradeLink( $kernelStrings, true );
	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, $kernelStrings['ai_page_title'] );
	$preproc->assign( FORM_LINK, PAGE_ACCOUNT_INFO );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		
		if(ClassManager::includeClass('AccountName', 'kernel') !== false){

			$AccountName = new AccountName();
			$res = $AccountName->loadByDBKEY($DB_KEY);
			if($res){
				
				$preproc->assign('account_name', $AccountName->account_name);
			}else{
				
				$preproc->assign('give_accountname_link', getCCLinkWithAuth( $DB_KEY, URL_MYWEBASYST.'?ukey=give_name&hidem=1&DBKEY='.base64_encode($DB_KEY)));
			}
		}
		
		$preproc->assign( "holderInfo", $holderInfo );
		$preproc->assign( "thisDbKey", $DB_KEY );
		$preproc->assign( "registerLink", $link );
		$preproc->assign( "eachAppLimits", $eachAppLimits );
		$preproc->assign( "allAppLimits", $allAppLimits );
		$preproc->assign( "displayBillingInfo", isHostingAccount() );
		$preproc->assign( "displayExtendLink", showBillingAlert() );
		$preproc->assign( "extendLink", getExtendLink() );

		$preproc->assign( "cancelLink", getCCLinkWithAuth($DB_KEY, sprintf(URL_CANCEL, base64_encode($DB_KEY))) );
		$preproc->assign( "upgradeLink", $ugpradeLink );

		$preproc->assign( "upgradeComment", sprintf($kernelStrings['ai_upgrade_comment'], $ugpradeLink ) );
		$preproc->assign( "cancelComment", sprintf($kernelStrings['ai_cancel_comment'], getCCLinkWithAuth($DB_KEY, sprintf(URL_CANCEL, base64_encode($DB_KEY))) ) );

		$days_before_suspend = ceil(getDaysBeforeSuspend());
		$preproc->assign( "days_before_suspend", $days_before_suspend);
		$preproc->assign( "daysLeft", sprintf( $days_before_suspend>=0?$kernelStrings['ai_days_left']:$kernelStrings['ai_days_after_suspend'], abs(ceil(getDaysBeforeSuspend()) )) );

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]) )
			$preproc->assign( "billingDate", convertToDisplayDate($databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE]) );
	}
	$preproc->display( "ai.htm" );
?>