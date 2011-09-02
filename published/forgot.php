<?php
header('Content-Type: text/html; charset=UTF-8;');
	$wsdl_param = "wsdl";

	$loginScript = true;

	if ( isset($_GET[$wsdl_param]) || isset($_GET[strtoupper($wsdl_param)]) ) {
		require "common/soap/scripts/authorize.php";
		die();
	}

	$init_required = false;
	$AA_APP_ID = "AA";
	$messageStr = null;


	require_once( "common/html/includes/httpinit.php" );
	$localizationPath = "../published/AA/localization";
	$appStrings = loadLocalizationStrings( $localizationPath, strtolower($AA_APP_ID) );
	$isSSL = isset($_SERVER['HTTPS']); 

	if (ClassManager::includeClass('accountname', 'kernel')!==false) {
		// Dbkey aliases mechanism

		$account_name = AccountName::getDomainName();
		$dbkey = AccountName::getHostDBKEY();
		if(!$dbkey)$account_name = '';
	}elseif(file_exists(WBS_DIR."/kernel/wbs.xml")) {
		$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
		$dbkey=(string)$xml->FRONTEND['dbkey'];
		$account_name = 'OS version';
	} else {
		$dbkey='';
		$account_name = '';
	}

	$language = isset($_GET['lang'])&&$_GET['lang']==LANG_RUS?LANG_RUS:'';
	if($dbkey){
		
		$__host_data = loadHostDataFile($dbkey, $appStrings[LANG_ENG]);
		
		define( "WBS_ATTACHMENTS_DIR", sprintf( "%s/%s/attachments", WBS_DATA_DIR, $dbkey ) );
		$logoPath = getKernelAttachmentsDir();
		$logoPath .= "/logo.gif";
		$logoExists = file_exists($logoPath);
		
		$advSettings = $__host_data[HOST_ADVSETTINGS];
		$companyName = htmlspecialchars(stripslashes($advSettings["company_name"]));
		if (!$companyName)
			$companyName = getenv("HTTP_HOST");
		$showLogo = ($advSettings["show_company_top"] == "yes");
		$theme = $advSettings["theme"];
		
		if(!$language && !PEAR::isError($__host_data) && isset($__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]) && $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE]){
			
			$language = $__host_data[HOST_ADMINISTRATOR][HOST_LANGUAGE];
		}
	}
	
	if(!$language)$language = LANG_ENG;
	
	$charset = $language == LANG_RUS?'UTF-8':'iso-8859-1';
	$locStrings = $appStrings[$language];

	if ( isset($_GET['error']) && !isset($edited) )
		$errorStr = base64_decode( $_GET['error'] );

	
	if ( isset( $edited ) && $edited ) {
		switch ( true ) {
			case true:
				$userdata = trimArrayData( $userdata );

				$requiredFields = array( "U_ID", "account_name" );
				if($account_name)
					$userdata['account_name'] = $account_name;
				
				if ( PEAR::isError( $res = findEmptyField($userdata, $requiredFields) ) ) {
					$errorStr = $locStrings['app_allfields_message'];

					break;
				}
				
				if(onWebAsystServer()){
					$userdata['DB_KEY'] = AccountName::getHostDBKEY($userdata['account_name']);
					if(!$userdata['DB_KEY']){
						
						$errorStr = $locStrings['app_wrong_accname'];
						break;
					}
				}else{
					$userdata['DB_KEY'] = $dbkey;
				}

				$DB_NAME = null;
				$HOST_ID = null;

				$loginData = $userdata;
				
				$loginPageURL = null;
				if ( isset($thisPageURL) && strlen($thisPageURL) )
					$loginPageURL = $thisPageURL;

				if($iframe){
					$loginData['IFRAME'] = true;
				}
				
				$res = host_forgot($loginData, $locStrings, $_SERVER["REMOTE_ADDR"], "web");
				
				if ( PEAR::isError($res) ) {
					$errorStr = $res->getMessage();
					break;
				}
				
				if ($res == ST_OK) {
					$messageStr = sprintf($locStrings["app_forgotpassword_sended"], $loginData["EMAIL"]);
					$sended = true;
				} else {
					$errorStr = $locStrings["app_forgotpassword_error"];
				}
		}
	} else {
		$userdata = array();

		if (strlen($dbkey))
			$userdata['DB_KEY'] = $dbkey;
		else
			if ( isset( $_GET["DB_KEY"] ) )
				$userdata["DB_KEY"] = $_GET["DB_KEY"];
		if ( isset( $_GET["LOGIN"] ) )
			$userdata["U_ID"] = $_GET["LOGIN"];

		if ( !isset($userdata["DB_KEY"]) || !strlen($userdata["DB_KEY"]) )
			if ( isset($_COOKIE["wbs_login_host"]) )
				$userdata["DB_KEY"] = $_COOKIE["wbs_login_host"];

		if ( !isset($userdata["DB_KEY"]) || !strlen($userdata["DB_KEY"]) )
			$userdata["DB_KEY"] = null;

		if ( !isset($userdata["U_ID"]) || !strlen($userdata["U_ID"]) )
			$userdata["U_ID"] = null;
	}

	if ( isset($thisPageURL) && strlen($thisPageURL) )
		redirectBrowser( $thisPageURL, array('errorMessage'=>urlencode($errorStr)) );
	if(onWebAsystServer())
		include 'forgot.phtml';	
	else 
		include 'forgot_os.phtml';
?>