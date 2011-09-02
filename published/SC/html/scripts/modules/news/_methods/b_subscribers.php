<?php
/* @vars $smarty Smarty */
$smarty = &Core::getSmarty();
set_query('safe_mode=');

if(isset($_POST['fACTION'])){
	
	$xREQUEST_URI = set_query('&QWERTY=');
	/**
	 * this action is forbidden when SAFE MODE is ON
	 */
	if (CONF_BACKEND_SAFEMODE)RedirectSQ('&safemode=yes');
	
	if(!session_is_registered('SUBSCRIBE_MESSAGE')){
		
		session_register('SUBSCRIBE_MESSAGE');
	}
	switch ($_POST['fACTION']){
		case 'fLoadSubscribersListFile':
			
			$UploadError = false;
			do{
				if(SystemSettings::is_hosted()){
					$UploadError=true;
					break;
				}
				if (!isset($_FILES['fSubscribersListFile']['tmp_name'])){
					$UploadError=true;
					break;
				}
				if (!$_FILES['fSubscribersListFile']['tmp_name']){
					$UploadError=true;
					break;
				}
				if (!$_FILES['fSubscribersListFile']['size']){
					$UploadError=true;
					break;
				}
				if (!file_exists($_FILES['fSubscribersListFile']['tmp_name'])){
					$UploadError=true;
					break;
				}
			}while (0);
			if($UploadError){
				
				$_SESSION['SUBSCRIBE_MESSAGE'] = array(
					'Message' => translate("sbscrbrs_err_uploading_file"),
					'MessageCode' => 2,
				);
				break;
			}
			$FileContents = file ($_FILES['fSubscribersListFile']['tmp_name']);
			$emailCounter = 0;
			foreach ($FileContents as $_email){
				
				$_email = trim($_email);
				if(subscrVerifyEmailAddress($_email) == ''){
					
					subscrAddUnRegisteredCustomerEmail($_email);
					$emailCounter++;
				}
			}
			if(!$emailCounter){
				
				$_SESSION['SUBSCRIBE_MESSAGE'] = array(
					'Message' => translate("sbscrbrs_err_empty_file"),
					'MessageCode' => 2,
				);
				break;
			}else {
				
				$_SESSION['SUBSCRIBE_MESSAGE'] = array(
					'Message' => str_replace('{*EMAILS_NUMBER*}', $emailCounter, translate("sbscrbrs_msg_import_successful")),
					'MessageCode' => 1,
				);
			}
			break;
		case 'fEraseSubscribersList':
			$CountRow = 0;
			$Subscriptions = subscrGetAllSubscriber('', $CountRow);
			
			foreach ($Subscriptions as $_Subscription){
				
				subscrUnsubscribeSubscriberByEmail(base64_encode($_Subscription['Email']));
			}
			if(!count($Subscriptions))break;
			$_SESSION['SUBSCRIBE_MESSAGE'] = array(
				'Message' => str_replace('{*EMAILS_NUMBER*}', count($Subscriptions),translate("sbscrbrs_msg_deleted_all_records")),
				'MessageCode' => 1,
			);
			break;
		case 'fExportSubscribersList':
			$CountRow = 0;
			$Subscriptions = subscrGetAllSubscriber('', $CountRow);
			$ExportBuffer = '';
			if(!count($Subscriptions))break;
			$fp = @fopen(DIR_TEMP.'/subscribers.txt', 'w');
			if(!$fp){
				
				$_SESSION['SUBSCRIBE_MESSAGE'] = array(
					'Message' => translate("sbscrbrs_err_creating_file"),
					'MessageCode' => 2,
				);
				break;
			}

			foreach ($Subscriptions as $_Subscription){
				
				fwrite($fp, $_Subscription['Email']."\r\n");
			}
			
			$getFileParam = Crypt::FileParamCrypt( "GetSubscriptionsList", null );
			$smarty->assign( "getFileParam", $getFileParam );

			$_SESSION['SUBSCRIBE_MESSAGE'] = array(
				'Message' => str_replace('{*URL*}', 'get_file.php?getFileParam='.$getFileParam, translate("sbscrbrs_msg_export_successful")),
				'MessageCode' => 1,
			);
			
			fclose($fp);
			break;
	}
	Redirect($xREQUEST_URI);
}

if (isset($_GET['unsub'])) // unsubscribe registered user
{
	if (CONF_BACKEND_SAFEMODE) //this action is forbidden when SAFE MODE is ON
	{
		RedirectSQ('&safemode=yes&unsub=');
	}

	subscrUnsubscribeSubscriberByEmail( ($_GET["unsub"]) );
	
	if(!session_is_registered('SUBSCRIBE_MESSAGE')){
		
		session_register('SUBSCRIBE_MESSAGE');
	}
	$_SESSION['SUBSCRIBE_MESSAGE'] = array(
		'Message' => str_replace('{*EMAIL*}',base64_decode($_GET["unsub"]), translate("sbscrbrs_msg_email_deleted")),
		'MessageCode' => 1,
	);
	RedirectSQ('unsub=');
}

$callBackParam = array();
$subscribers = array();

$count = 0;
$htmlNavigator = GetNavigatorHtml( set_query('__tt='), 10, 
	'subscrGetAllSubscriber', $callBackParam, 
	$subscribers, $offset, $count );
if(!count($subscribers)&&$offset){
	
	Redirect(set_query('offset='));
}

$smarty->assign( 'urlToSubscibe', set_query('__tt=') );

foreach($subscribers as &$subscriber)
{
	$subscriber["Email64"] = base64_encode( $subscriber["Email" ]);
}

/**
 * Messages handler
 */
if(isset($_SESSION['SUBSCRIBE_MESSAGE'])){
	
	if(isset($_SESSION['SUBSCRIBE_MESSAGE']['Message']) && isset($_SESSION['SUBSCRIBE_MESSAGE']['MessageCode'])){
		
		$smarty->assign('Message', $_SESSION['SUBSCRIBE_MESSAGE']['Message']);
		$smarty->assign('MessageCode', $_SESSION['SUBSCRIBE_MESSAGE']['MessageCode']);
		unset($_SESSION['SUBSCRIBE_MESSAGE']['Message']);
	}
}

$smarty->assign( 'navigator', $htmlNavigator );
$smarty->assign( 'allow_upload', !SystemSettings::is_hosted() );
$smarty->assign( 'subscribers', $subscribers );
$smarty->assign( 'subscribers_count', $count );

$smarty->assign('sub_template', $this->getTemplatePath('backend/subscribers.html'));
?>