<?php
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/AA/aa.php" );
	if(onWebAsystServer())require_once( WBS_DIR."/published/hostagent/holderinfo.php" );

	//
	// Authorization
	//
	
	$errorStr = null;
	$fatalError = false;
	$SCR_ID = "CP";

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$currencyList = array();
	$fileCount = 0;
	$totalSize = 0;
	
	$smses = getSMSBalance( SMS_SYSTEM_USER );  
	$databaseInfo[HOST_BALANCE]['sms'][HOST_VALUE] = $smses['SMS_BALANCE'];

	

			
	$screens = array("CI" => "company.php", "CL" => "currencylist.php", "SYS" => "system.php", "SMS" => "sms.php");
	$sRights = array ();
	foreach ($screens as $cScreen => $cFile) {
		//if (checkUserFunctionsRights( $currentUser, AA_APP_ID, $cScreen, $kernelStrings ))
	 	$sRights[$cScreen] = $cFile;
	}
	
	if(onWebAsystServer()){do {
		$billing_date = $databaseInfo[HOST_DBSETTINGS][HOST_BILLINGDATE];
		
		$current_plan = getApplicationHostingPlan();
 		$current_rate = getPlanRate($current_plan);
 	
		$holderInfo = new HolderInfo();
		$holderInfo = $holderInfo->Get( $DB_KEY ); 
		if ( PEAR::isError($holderInfo) )
		{
			$errorStr = $holderInfo->getMessage();
			$fatalError = true;
			break;
		}
  
		if (empty($holderInfo['regDate'])) {
			$holderInfo = Array ( 
				'company'   => $databaseInfo[HOST_FIRSTLOGIN][HOST_COMPANYNAME],
				'firstName' => $databaseInfo[HOST_FIRSTLOGIN][HOST_FIRSTNAME],
				'lastName'  => $databaseInfo[HOST_FIRSTLOGIN][HOST_LASTNAME],
				'email'     => $databaseInfo[HOST_FIRSTLOGIN][HOST_EMAIL],
				'regDate'   => $databaseInfo[HOST_DBSETTINGS][HOST_CREATEDATE]);
			}

		$link = sprintf( URL_REGISTER, base64_encode($DB_KEY), base64_encode($currentUser), base64_encode($language) );
		$upgradeLink = getUpgradeLink( $kernelStrings, false );
		
		$holderInfo['regDate'] = convertToDisplayDate( $holderInfo['regDate'] );
		
		// Load information about customer
		//
		$customer = aa_customerByMail($DB_KEY, true);
		
		if ( PEAR::isError($customer) ){
			
			$errorStr = $customer->getMessage();
			$fatalError = true;
			
		}
	
		 
	} while (false);}

	$availableExtraMail = 0;
	if(!empty($customer)) {
		foreach ($customer->ExtraEmails as $neednt) {
			$availableExtraMail++;
		}
	}
	$availableExtraMail = AA_EXTRA_MAIL_LIMIT - $availableExtraMail;
	
	$email_errors = array();

	//
	// Form handling
	//
	$btnIndex = getButtonIndex( array( "fm_extra_emails", 'Delele_mail'), $_REQUEST );

	switch ($btnIndex) {
		case 0 :
			{
			if(empty($customer->MTC_ID)) {
				break;
			}
				
			// Add another addon E-mails
			//
			
			$success = false;
			$sql = null;
			$new_mails = array();
			
			foreach ($_REQUEST['fm_extra_emails'] as $mail) {

				if ($availableExtraMail <= 0){
					
					$errorStr = sprintf($kernelStrings['bill_extra_mails_limit'], AA_EXTRA_MAIL_LIMIT);
					break(2);
					
				}
			
				$mail = trim($mail);
				
				if (empty($mail)){
					continue;
				}
				
				// Check mail for valid
				//
				if (!valid_email($mail)){
					$errorStr .= sprintf($kernelStrings['bill_mail_invalid'], $mail);
					continue;
				}

				// Check if email is busy
				//
				$contact = aa_checkContactByMail($mail);
				
				if ( PEAR::isError($contact) ){
					$errorStr = $contact->getMessage();
					$fatalError = true;
					break;
				} elseif ($contact) {
					$errorStr = $contact;
					$email_errors[$mail] = $contact;
					break;
				}

/*				
				if (array_key_exists($mail, $customer->ExtraEmails) || $customer->MTC_EMAIL == $mail){
					$errorStr .= sprintf($kernelStrings['bill_mail_exists'], $mail);
					continue;
				}
*/
				if (in_array($new_mails, $new_mails)){
					continue;
				}

				// Collent additional mails
				//
				$new_mails[] = $mail;
				$success = true;
				
				$availableExtraMail--;
				
			}
			
			if (!$success && empty($errorStr)){
				$errorStr = $kernelStrings['bill_empty_mails'];
				break;
			}

			if (!empty($errorStr)){
				break;
			}
			
			$res = aa_addCustomerExtraEmails($new_mails, $customer->MTC_ID, 'ssdsd');
			
			if ( PEAR::isError($res) ){
				
				$errorStr = $res->getMessage();;
				$fatalError = true;
				break;
			}
			
			redirectBrowser( PAGE_AADMIN, array() );
			break;		
		}
		case 1 :
			{

			$mailForDelete = trim($_REQUEST['Delele_mail']);
			
			if (!array_key_exists($mailForDelete, $customer->ExtraEmails)){
				
				$errorStr .= sprintf($kernelStrings['bill_mail_owner_error'], $mailForDelete);
				$fatalError = true;
			
				break;
			}

			$res = aa_deleteCustomerExtraEmail($customer->MTC_ID, $mailForDelete);
			
			if ( PEAR::isError($res) ){
				
				$errorStr = $res->getMessage();;
				$fatalError = true;
				break;
			}
			
			redirectBrowser( PAGE_AADMIN, array() );
			break;
		}
	}	

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	if(ClassManager::includeClass('AccountName', 'kernel') !== false){
		$AccountName = new AccountName();
		$res = $AccountName->loadByDBKEY($DB_KEY);
		if($res){
			$preproc->assign('account_name', $AccountName->account_name);
		}
	}
	
	$preproc->assign( "billing_date", convertToDisplayDate($billing_date) );
	$preproc->assign( "WBSHost", getWBSHost() );
	$preproc->assign( "price_for_current", $current_rate);
	$preproc->assign( "current_plan", $current_plan );
	$preproc->assign( "customer", $customer );
	$preproc->assign( "availableExtraMail", $availableExtraMail );

	$preproc->assign( "article", $mt_currency_style[$databaseInfo[HOST_FIRSTLOGIN][HOST_LANGUAGE]]);
	
	$preproc->assign( "holderInfo", $holderInfo );
	$preproc->assign( "thisDbKey", $DB_KEY );
	$preproc->assign( "registerLink", $link );
	$preproc->assign( "upgradeLink", $upgradeLink );
	$preproc->assign ("IS_HOSTED_ACCOUNT", onWebAsystServer() && ((isset($databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE])&&$databaseInfo[HOST_DBSETTINGS][HOST_TRIALDATASOURCE]) || $holderInfo['email']) );
	

	$preproc->assign( PAGE_TITLE, $kernelStrings['aa_screen_long_name'] );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	
	$preproc->assign( "sRights", $sRights);
	
	$preproc->assign( 'fm_extra_emails', @$_REQUEST['fm_extra_emails']);
	$preproc->assign( 'email_errors', $email_errors);
	
	$preproc->display( "aadmin.htm" );
?>