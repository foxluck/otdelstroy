<?php

	/*
	 *
	 * Common HTTP routines
	 *
	 */

	if ( !(isset($allow_page_caching) && !$allow_page_caching) ) {
		header( "Pragma: no-cache" );
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
	}

	/*
	 * Common page variables
	 */

	define( "PAGE_TITLE", "pageTitle" );
	define( "SCRIPT_NAME", "scriptName" );
 	define( "SORTING_COL", "sorting" );

 	define( "INVALID_FIELD", "invalidField" );
 	define( "ERROR_STR", "errorStr" );
 	define( "FORM_LINK", "formLink" );

	define( "OPENER", "opener" );
	define( "OPENER_PARAMS", "openerParams" );
	define( "FATAL_ERROR", "fatalError" );

	define( "PAGE_ATTACHED_FILES", "PAGE_ATTACHED_FILES" );
	define( "RECORD_FILES", "RECORD_FILES" );
	define( "PAGE_DELETED_FILES", "PAGE_DELETED_FILES" );

	define( "HELP_TOPIC", "help_topic" );

	define( "INFO_STR", "infoStr" );

	/*
	 * Standard button names
	 */

	define( "BTN_SAVE", "savebtn" );
	define( "BTN_CANCEL", "cancelbtn" );
	define( "BTN_RETURN", "returnbtn" );
	define( "BTN_CLEAR", "clear" );
	define( "BTN_EDIT", "edit" );
	define( "BTN_ATTACH", "attachbtn" );
	define( "BTN_DELETEFILES", "deletefilesbtn" );
	define( "BTN_NEXT", "nextbtn" );

	$processButtonTemplate = "javascript:processTextButton('%s', 'form')";
	$processAjaxButtonTemplate = "javascript:processAjaxButton('%s')";

	/*
	 * Pages names
	 */

	define( "PAGE_COMPANY", "company.php" );
	define( "PAGE_LOGIN", "../../../login.php" );
	define( "PAGE_PREFERENCES", "preferences.php" );
	define( "PAGE_LOOKANDFEEL", "lookandfeel.php" );
	define( "PAGE_ACCESSLIST", "accesslist.php" );
	define( "PAGE_CHANGEPASSWORD", "changepassword.php" );
	define( "PAGE_SIMPLEREPORT", "../../../AA/html/scripts/simplereport.php" );
	define( "PAGE_CURRENCYLIST", "currencylist.php" );
	define( "PAGE_AADMIN", "aadmin.php" );
	define( "PAGE_CHANGE_PLAN", "change_plan.php" );
	define( "PAGE_PROCEED_PAY", "proceed_pay.php" );
	define( "PAGE_ADDMODCURRENCY", "addmodcurrency.php" );
	define( "PAGE_FRAMESET", "../../../common/html/scripts/frameset.php" );
	define( "PAGE_ADMINSTARTUP", "../../../AA/html/scripts/adminstartup.php" );
	define( "PAGE_USERSTARTUP", "../../../AA/html/scripts/userstartup.php" );
	define( "PAGE_UPGRADEACCOUNT", "../../../AA/html/scripts/change_plan.php" );
	define( "PAGE_SYSTEM", "system.php" );
	define( "PAGE_CHANGEEXPPERIOD", "changeexpperiod.php" );
	define( "PAGE_CUSTOMIZEVIEW", "customizeview.php" );
	define( "PAGE_GETFILETHUMB", "../../../common/html/scripts/getfilethumb.php" );
	define( "PAGE_IMPORTFORMATMANAGER", "../../../AA/html/scripts/importformatsmanager.php" );
	define( "PAGE_GETIMGFIELDFILE", "getimgfieldfile.php" );
	define( "PAGE_USERSANDGROUPS", "usersandgroups.php" );
	define( "PAGE_ADDMODUSER", "addmoduser.php" );
	define( "PAGE_ADDMODGROUP", "addmodgroup.php" );
	define( "PAGE_ADDTOGROUP", "addtogroup.php" );
	define( "PAGE_REVOKEUSERPRIVS", "revoleuserprivs.php" );
	define( "PAGE_CHANGEUSERSTATUS", "changeuserstatus.php" );
	define( "PAGE_IMPORTUSERS", "../../../UG/html/scripts/importusers.php" );
	define( "PAGE_EXPORTUSERS", "../../../UG/html/scripts/exportusers.php" );
	define( "PAGE_PRINT", "print.php" );
	define( "PAGE_GETULFILE", 'getulfile.php' );
	define( "PAGE_MANAGETEMPLATES", '../../../AA/html/scripts/managetemplates.php' );
	define( "PAGE_DELETEUSERS", 'deleteusers.php' );
	define( "PAGE_REPORTS", 'reports.php' );
	define( "PAGE_REGTRIAL", 'registertrial.php' );

	define( "PAGE_ACCESSDIAGRAMS", 'accessdiagrams.php' );
	define( "PAGE_ACCESSRIGHTS_USERS", 'rep_acessrights_users.php' );
	define( "PAGE_ACCESSRIGHTS_REP_USERS", 'rep_accessrightsusrrep.php' );
	define( "PAGE_ACCESSRIGHTS_GROUPS", 'rep_acessrights_groups.php' );
	define( "PAGE_ACCESSRIGHTS_REP_GROUPS", 'rep_accessrightsgrprep.php' );

	define( "PAGE_SMS", "sms.php" );
	define( "PAGE_SMS_UA", "sms_ua.php" );
	define( "PAGE_SMS_BH", "sms_bh.php" );
	define( "PAGE_SMS_CREDIT_HIST", "smscrhist.php" );
	define( "PAGE_SMS_HIST", "smshist.php" );
	define( "PAGE_SMS_TEXT", "smstext.php" );
	define( "PAGE_SMS_GETFILE", "smsgetfile.php" );
	define( "PAGE_SMS_HISTORY", "sms_history.php" );

	define( "PAGE_SMS_BALANCE", "smsbalance.php" );
	define( "PAGE_ACCOUNT_SUSPENDED", "../../../AA/html/scripts/suspended.php" );

	define( "PAGE_ACCOUNT_INFO", "ai.php" );

	/*
	 * Cookie timeouts
	 */

	define( "COOKIE_TO_LONG", 2592000 );
	define( "COOKIE_TO_SHORT", 28800 );

	/*
	 * HTML-related user settings
	 */

	define( "NEW_TEMPLATE", "new_template" );

	/*
	 * Pages support
	 */

	define( "PAGES_SHOW", "showPageSelector" );
	define( "PAGES_PAGELIST", "pages" );
	define( "PAGES_CURRENT", "currentPage" );
	define( "PAGES_NUM", "pagesNum" );
	define( "RECORDS_PER_PAGE", 30 );

	/*
	 * HTML-client files
	 */

	define( "TEMPLATELIST_PATH", fixPathSlashes(fixDirPath(WBS_DIR."published/common/html/includes/templates.xml")) );
	define( "TEMPLATES_HOMEDIR", fixPathSlashes(fixDirPath(WBS_DIR."published/common/html")) );

	/*
	 * HTML List XML tags
	 */

	define( "TEMPLATELIST_LIST", "TEMLATELIST" );
	define( "TEMPLATELIST_TEMPLATE", "TEMPLATE" );
	define( "TEMPLATELIST_ID", "ID" );
	define( "TEMPLATELIST_NAME", "NAME" );
	define( "TEMPLATELIST_FODER", "FODER" );
	define( "TEMPLATELIST_FRAMES", "FRAMES" );
	define( "TEMPLATELIST_STYLESETS", "STYLESETS" );

	define( "HTML_DEFAULT_TEMPLATE", "classic" );
	define( "HTML_DEFAULT_STYLESET", "darkblue" );
	define( "HTML_STYLESET", "styleSet" );

	/*
	 * First page consts
	 */

	define( "PAGE_BLANK", "../../../AA/html/scripts/blank.php" );
	define( "PAGE_TIPSANDTRICKS", "../../../AA/html/scripts/tipsandtricks.php" );
	define( "PAGE_LAST", "LAST_PAGE" );

	if ( !defined('START_PAGE') )
		define( "START_PAGE", "START_PAGE" );

	define( "LOGIN_PAGE_URL", "LOGIN_PAGE_URL" );

	/*
	 * Tabs support
	 */

	define( "PT_NAME", "NAME" );
	define( "PT_PAGE_ID", "PAGE_ID" );
	define( "PT_FILE", "FILE" );
	define( "PT_CONTROL", "CONTROL" );
	define( "PT_CUSTOM_ID", "CUSTOM_ID" );
	define( "PT_ON_OPEN", "ON_OPEN" );
	define( "PT_PATH", "PATH" );

	/*
	 * CSV files support
	 */

	define( "CSV_DBSCHEME", "csv_dbscheme" );

	define( "CSV_DBSCHEME_PACKED", "csv_dbscheme_packed" );
	define( "CSV_FILEHEADERS_PACKED", "csv_headers_packed" );
	define( "CSV_IMPORTSCHEME_PACKED", "csv_importscheme_packed" );

	define( "CSV_FILEHEADERS", "csv_headers" );
	define( "CSV_HEADERDBLINK", "csv_headerdblink" );
	define( "CSV_IMPORTFIRSTLINE", "csv_importfirstline" );

	define( "CSV_STEP", "csv_step" );
	define( "CSV_STEP_LOADFILE", "LOADFILE" );
	define( "CSV_STEP_SETSCHEME", "SETSCHEME" );
	define( "CSV_STEP_FINISHED_SAVESCHEMA", "FINISHED_SAVESCHEMA" );
	define( "CSV_STEP_FINISHED", "FINISHED" );

	define( "CSV_SEPARATOR", "csv_separator" );

	/*
	 * Tree/document constants
	 */

	define( "TREEDOC_MAXVIEWUSERS", 10 );

	/*
	 * Extracting variables
	 */

	extract( $_GET );
	extract( $_POST );
	extract( $_FILES );
	
	/*if (isset($get_key_from_url) && !empty($_GET["DB_KEY"]))
		$DB_KEY = base64_decode($DB_KEY);
	elseif (isset($get_key_from_url) && !empty($_POST["DB_KEY"]))
		$DB_KEY = base64_decode($DB_KEY);*/
		

	function redirect($url){

		$softwareInfo = getUserAgent();

		$winIIS = strstr(php_uname(), 'Windows') && ( $softwareInfo["Server"] == 'IIS' );

		if ( $winIIS )
			$str_redirect = "Refresh: 0;url=%s";
		else
			$str_redirect = "Location: %s";

		header(sprintf($str_redirect, escapeCRLF($url)));
		die;
	}

	function escapeCRLF($str){

		return str_replace(array("\r\n",'%0d%0a', "\n",'%0a', "\r",'%0d'),'',$str);
	}

	function redirectBrowser( $pageName, $parameters, $namedAnchor = "", $outputSpace = false, $skipSID = false, $post = false, $get = false, $target = "" )
	//
	// Redirect browser to the address specified
	//
	//		Parameters:
	//			$pageName - page URL
	//			$parameters - HTTP GET parameters (associative array)
	//			$namedAnchor - anchor on the page
	//			$outputSpace - output space after redirect (to save headers)
	//			$skipSID - do not add SID to the URL parameter list
	//			$post - use POST method
	//			$get - use form with GET method
	//
	//		Returns: nothing
	//
	{
		$softwareInfo = getUserAgent();

		$winIIS = strstr(php_uname(), 'Windows') && ( $softwareInfo["Server"] == 'IIS' );
		
		if ( $winIIS )
			$str_redirect = "Refresh: 0;url=%s".((strpos($pageName,'?')===false)?'?':'&')."%s";
		else
			$str_redirect = "Location: %s".((strpos($pageName,'?')===false)?'?':'&')."%s";

		$str_html_redirect = "%s".((strpos($pageName,'?')===false)?'?':'&')."?%s";
		

		if ( !is_array($parameters) )
			$parameters = array();
			
		$inplaceScreen = false;
		if (isset($_GET["inplaceScreen"]))
			$inplaceScreen = $_GET["inplaceScreen"];
		if (isset($_POST["inplaceScreen"]))
			$inplaceScreen = $_POST["inplaceScreen"];
		if ($inplaceScreen)
			$parameters["inplaceScreen"] = $inplaceScreen;
		
		$paramArray = array();

		if ( !$skipSID )
			if ( ini_get('session.use_trans_sid') )
				$paramArray[] = ini_get( 'session.name' ).'='.session_id();

		while ( list( $key, $val ) = each ( $parameters ) )
			$paramArray[] = $key."=".$val;

		$paramStr = implode( "&", $paramArray );

		$URI = sprintf( $str_redirect, $pageName, $paramStr );

		if ( !$post && !$get ) {
			if ($outputSpace || !strlen($namedAnchor))
			{
				if ( !$winIIS && ( $softwareInfo["Agent"]=="Firefox" || $softwareInfo["Agent"]=="Mozilla" || $softwareInfo["Agent"]=="Netscape" ) )
					header( 'HTTP/1.1 303 See Other' );

				header( $URI );
			}
			else {
				$URI = sprintf( $str_html_redirect, $pageName, $paramStr );

				if (strlen($namedAnchor))
					$URI = $URI.'#'.$namedAnchor;

				echo "<html><meta http-equiv=\"refresh\" content=\"0;url=$URI\"></html>";
			}

			if ($outputSpace) {
				echo "&nbsp";
				flush();
			}
		} else {
			$paramStr = "";

			foreach ( $parameters as $key=>$val )
				$paramStr .= "<input type=hidden name=$key value=\"$val\">";
				
			if ( !$get )
				$page = "<html><head><title>Redirecting...</title></head><body onLoad='document.forms[0].submit()'><form action='%s' method=post %s>%s</form></body></html>";
			else
				$page = "<html><head><title>Redirecting...</title></head><body onLoad='document.forms[0].submit()'><form action='%s' method=get %s>%s</form></body></html>";

			echo sprintf( $page, $pageName, ($target != '') ? "target='$target'" : '', $paramStr );
		}

		die();
	}

	function getUserAgent()
	{
		$curos=strtolower($_SERVER['HTTP_USER_AGENT']);
		$uip=$_SERVER['REMOTE_ADDR'];
		$ssoft=strtolower( $_SERVER["SERVER_SOFTWARE"] );

		if (strstr($curos,"mac")) {
			$uos="MacOS";
		} else
			if (strstr($curos,"linux")) {
				$uos="Linux";
			} else
				if (strstr($curos,"win")) {
					$uos="Windows";
				} else
					if (strstr($curos,"bsd")) {
						$uos="BSD";
					} else
						if (strstr($curos,"qnx")) {
							$uos="QNX";
						} else
							if (strstr($curos,"sun")) {
								$uos="SunOS";
							} else
								if (strstr($curos,"solaris")) {
									$uos="Solaris";
								} else
									if (strstr($curos,"irix")) {
										$uos="IRIX";
									} else
										if (strstr($curos,"aix")) {
											$uos="AIX";
										} else
											if (strstr($curos,"unix")) {
												$uos="Unix";
											} else
												if (strstr($curos,"amiga")) {
													$uos="Amiga";
												} else
													if (strstr($curos,"os/2")) {
														$uos="OS/2";
													} else
														if (strstr($curos,"beos")) {
															$uos="BeOS";
														} else {
															$uos="[?]EgzoticalOS";
														}

		if (strstr($curos,"gecko")) {
			if (strstr($curos,"safari")) {
				$bos="Safari";
			} else
				if (strstr($curos,"camino")) {
					$bos="Camino";
				} else
					if (strstr($curos,"firefox")) {
						$bos="Firefox";
					} else
						if (strstr($curos,"netscape")) {
							$bos="Netscape";
						} else {
							$bos="Mozilla";
						}
		} else
			if (strstr($curos,"opera")) {
				$bos="Opera";
			} else
				if (strstr($curos,"msie")) {
					$bos="Internet Exploder";
				} else
					if (strstr($curos,"voyager")) {
						$bos="Voyager";
					} else
						if (strstr($curos,"lynx")) {
							$bos="Lynx";
						} else {
							$bos="[?]EgzoticalBrowser";
						}

		if (strstr($ssoft,"apache"))
		{
				$sos="Apache";
		} else
			if (strstr($ssoft,"iis"))
			{
					$sos="IIS";
			}
			else
				$sos = "Apache";

		return array( "OS"=>$uos, "Agent"=>$bos, "Server"=>$sos );
	}

	function getCurrentAddress()
	//
	// Get the full URL of the current page
	//
	//		Returns: string
	//
	{
		$pagePath = $_SERVER['PHP_SELF'];
		$pageHost = $_SERVER['HTTP_HOST'];
		$pageProtocol = ( $_SERVER['SERVER_PORT'] != HTTPS_PORT ) ? 'http://' : 'https://';

		return sprintf( "%s%s%s", $pageProtocol, $pageHost, $pagePath );
	}

	function getLoginURL( $level = 3 )
	//
	// Get login page of the current system. Used only in the aoplication script pages.
	//
	//		Parameters:
	//			$level - level of current script, relative to WBS_DIR
	//
	//		Returns: string
	//
	{
		$URL = dirname( getCurrentAddress() );

		$pathData = explodePath( $URL );

		if ( !strlen($pathData[count($pathData)-1]) )
			array_pop($pathData);

		for ( $i = 1; $i <= $level; $i++ )
			array_pop( $pathData );

		return implode("/", $pathData).'/login.php';
	}

	function getButtonIndex( $values, $vars, $numeric = true )
	//
	// Get the index of button pressed on the form
	//
	//		Parameters:
	//			$values - button names (associative array)
	//			$vars - HTTP_POST_VARS or HTTP_GET_VARS (associative array)
	//
	//		Returns the index of the button pressed or -1 if buttons was not pressed.
	//
	{
		$result = -1;

		if ( is_array( $vars ) )
		{
			while ( list( $key, $val ) = each ( $vars ) )
				for ( $i = 0; $i < count( $values ); $i++ )
				{
					$pos = strpos ( $key, $values[$i] );

					if ( strlen($pos) && ($pos >= 0) ) {
						$result = $numeric ? $i : $key;
						break 2;
					}
				}
		}

		return $result;
	}

	function prepareArrayToStore( $array, $excludes = null )
	//
	// Apply prepareStrToStore function for each array item
	//
	//		Parameters:
	//			$array - array
	//
	//		Returns: associative array
	//
	{
		if ( !is_array( $array ) )
			return $array;

		if ( is_null($excludes) )
			$excludes = array();

		$resultArr = array();

		while ( list( $key, $val ) = each ( $array ) )
			if ( !in_array( $key, $excludes ) && !is_array($val) )
				$resultArr = array_merge( $resultArr, array( $key=>prepareStrToStore( $val ) ) );
			else
				$resultArr = array_merge( $resultArr, array( $key=>$val ) );

		return $resultArr;
	}

	function saveCookie( $cookieName, $cookieValue, $timeout = null )
	//
	// Save Cookie's
	//
	//		Parameters:
	//			$cookieName - name of the cookie to save
	//			$cookieValue - value of the cookie
	//			$timeout - live time of the cookie. If unassigned, cookie value will be destroyed after browser closed
	//
	//		Returns: nothing
	//
	{
		if ( !is_null( $timeout ) )
			header("Set-Cookie: $cookieName=$cookieValue; expires=" . gmdate("l, d-M-Y H:i:s", time() + $timeout) . " GMT; path=/;");
		else
			header("Set-Cookie: $cookieName=$cookieValue; path=/;");
	}

	//
	// Dynamic menu functions
	//

	function assignMenuLinks( $menuLinks, $language, $ui_names = false, $targets = false )
	//
	// Replace identifiers of the pages with the links from the menu definition array
	//
	//		Parameters:
	//			$menuLinks - an array, returned by listUserScreens() function
	//			$language - current user language
	//
	//		Returns an array of the following structure:
	//			array( APP_NAME1=>array(SCR_NAME=>SCR_LINK1...)... )
	//
	{
		global $global_screens;
		global $DB_KEY;
		global $databaseInfo;

		$result = array();

		$appList = array_keys($menuLinks);
		
		$oldTemplate = isset($databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']) && $databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE'];
		
		if ( !$oldTemplate )
		{
			for ( $i = 0; $i < count($appList); $i++ ) {
				$APP_ID = $appList[$i];
				$app_lang = $language;//getApplicationLanguage( $APP_ID, $language );
				
				$app_folder = $APP_ID;
				//$appData = $global_applications[$APP_ID];
				//$appName = trim(($ui_names) ? $appData[APP_UI_NAME][$app_lang] : $appData[APP_NAME][$app_lang]);

				$result[$APP_ID] = array( 'PAGES'=>array() );

				$appPages = $menuLinks[$APP_ID];
				$appName = "";
				foreach( $appPages as $SCR_ID ) {
					if ( isset($global_screens[$APP_ID][$SCR_ID]) ) {
						$SCR_DATA = $global_screens[$APP_ID][$SCR_ID];
						$scr_name = trim($SCR_DATA[SCR_NAME][$app_lang]);
						$scr_ui_name = $scr_name;
						if (empty($scr_name))
							$scr_name = trim($SCR_DATA[SCR_NAME][LANG_ENG]);
						if (!$appName)
							$appName = $scr_name;
						
						$scr_page = sprintf("../../../%s/html/scripts/%s", $app_folder, $SCR_DATA[SCR_PAGE] );

						$result[$APP_ID]['PAGES'][] = array( SCR_NAME=>$scr_name, SCR_UI_NAME=>$scr_ui_name, SCR_PAGE=>$scr_page, SCR_ID=>$SCR_ID );
					}
				}
				$result[$APP_ID]["APP_NAME"] = $appName;
			}
		} else {
			for ( $i = 0; $i < count($appList); $i++ ) {
				$APP_ID = $appList[$i];
				$app_lang = getApplicationLanguage( $APP_ID, $language );

				$app_folder = $APP_ID;
				$appData = $global_applications[$APP_ID];
				$appName = trim(($ui_names) ? $appData[APP_UI_NAME][$app_lang] : $appData[APP_NAME][$app_lang]);
				
				$appPages = $menuLinks[$APP_ID];
				foreach( $appPages as $SCR_ID ) {
					if ( isset($global_screens[$APP_ID][$SCR_ID]) ) {
						$SCR_DATA = $global_screens[$APP_ID][$SCR_ID];
						$scr_name = trim($SCR_DATA[SCR_NAME][$app_lang]);
						$scr_page = sprintf("../../../%s/html/scripts/%s", $app_folder, $SCR_DATA[SCR_PAGE] );

						if ( !$targets )
							$result[$appName][$scr_name] = $scr_page;
						else
							$result[$appName][$scr_name] = $SCR_DATA[SCR_TARGET];
					}
				}

			}
		}
		return $result;
	}

	function getScreenPage( $APP_ID, $SCR_ID )
	//
	// Get page URL from application screen identifier
	//
	//		Parameters:
	//			$APP_ID - application identifier
	//			$SCR_ID - screen identifier
	//
	//		Returns: url string
	//
	{
		global $global_screens;

		return $global_screens[$APP_ID][$SCR_ID][SCR_PAGE];
	}

	//
	// HTML-Frames support
	//

	function getTemplateFirstPage( $U_ID, $directAccess = false )
	//
	//	Check if user pages patterns used a frames.
	//		If frames used, returns url to frameset.htm.
	//		In other cases returns link to first page of the user.
	//
	//		Parameters:
	//			U_ID - user identifier
	//			$directAccess - return actual first page instead of frameset, in case of framed template
	//
	// 	Returns: string containing url to first user page
	//
	{
		global $DB_KEY;
		global $wbs_templates;
		global $databaseInfo;

		$oldTemplate = isset($databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE']) && $databaseInfo[HOST_DBSETTINGS]['OLD_TEMPLATE'];

		$template_IDs = array_keys( $wbs_templates );

		/*
		$isAdminAccount = isAdministratorID( $U_ID );
		if ( $isAdminAccount ) {
			$adminData = loadAdminInfo();
			$templateID = $adminData[TEMPLATE];
		} else
			$templateID = readUserCommonSetting( $U_ID, TEMPLATE );


		if ( !strlen($templateID) )
			$templateID = HTML_DEFAULT_TEMPLATE;
		*/
		if ( !$oldTemplate )
			$templateID = "cssbased";
		else
			$templateID = 'classic';

		if ( !in_array( $templateID, $template_IDs ) )
			return getUserFirstPage( $U_ID );

		$templateParams = $wbs_templates[$templateID];

		if ( $directAccess )
			return getUserStartUpPage( $U_ID, false );

		if ( $templateParams["FRAMES"] )
			return PAGE_FRAMESET;
		else
			return getUserFirstPage( $U_ID );
	}

	function loadTemplateStylesets( $templateName )
	//
	// Returns list of template stylesets
	//
	//		Parameters:
	//			$templateName - the name of template
	//
	//		Returns array
	//
	{
		$result = array();

		$stylesetsDir = sprintf( "%s/%s/stylesets", TEMPLATES_HOMEDIR, $templateName );

		if ( !file_exists($stylesetsDir) )
			return $result;

		if ( !($handle = opendir($stylesetsDir)) )
			return $result;

		while ( false !== ($name = readdir($handle)) ) {

			$dirname = $stylesetsDir.'/'.$name;

			if ( $name != "." && $name != ".." && is_dir($dirname) )
				$result[] = $name;
		}

		closedir( $handle );

		return $result;
	}

	function loadTemplateList()
	//
	// Load internal templates list of WBS HTML-client
	//
	//		Returns: boolean
	//
	{
		global $wbs_templates;
		$wbs_templates = array();

		if ( !file_exists(TEMPLATELIST_PATH) )
			return false;

		$dom = @domxml_open_file( realpath(TEMPLATELIST_PATH) );
		if ( !$dom )
			return false;

		$root = @$dom->root();
		if ( !$root )
			return false;

		$templates = $root->get_elements_by_tagname( TEMPLATELIST_TEMPLATE );
		if ( !is_array($templates) || !count($templates) )
			return true;

		for ( $i = 0; $i < count($templates); $i++ ) {
			$template = $templates[$i];

			$templateName = $template->get_attribute(TEMPLATELIST_NAME);

			$templateID = $template->get_attribute(TEMPLATELIST_ID);
			$templateData = array( TEMPLATELIST_NAME=>$templateName,
									TEMPLATELIST_FODER=>@$template->get_attribute(TEMPLATELIST_FODER),
									TEMPLATELIST_FRAMES=>@$template->get_attribute(TEMPLATELIST_FRAMES),
									TEMPLATELIST_STYLESETS=>loadTemplateStylesets($templateID) );

			$wbs_templates[$templateID] = $templateData;
		}

		return true;
	}

	function getUserStartUpPage( $U_ID, $checkRights = true )
	//
	// Returns startup page of the user depend on current user settings.
	//
	//		Parameters:
	//			$U_ID - user identifier
	///			$checkRights - check if user have rights to work with his startup page
	//
	//		Returns: string containing script identifier of the user page.
	//
	{
		$page = readUserCommonSetting( $U_ID, START_PAGE );
		$page = trim($page);

		if ( !strlen($page) )
			if ( SHOW_TIPSANDTRICKS )
				return PAGE_TIPSANDTRICKS;
			else
				return PAGE_BLANK;

		if ( $page == USE_BLANK )
			return PAGE_BLANK;

		if ( $page == USE_TIPSANDTRICKS )
			if ( SHOW_TIPSANDTRICKS )
				return PAGE_TIPSANDTRICKS;
			else
				return PAGE_BLANK;

		if ( $page == USE_LAST)
			$page = readUserCommonSetting( $U_ID, PAGE_LAST );

		$pageData = explode( "/", $page );
		$APP_ID = strtoupper( $pageData[0] );
		$SCR_ID = $pageData[1];

		if ( $checkRights ) {
			$userScreens = listUserScreens( $U_ID );

			if ( !array_key_exists( $APP_ID, $userScreens ) )
				return PAGE_BLANK;

			$app_screens = $userScreens[$APP_ID];
			if ( !in_array( $SCR_ID, $app_screens ) )
				return PAGE_BLANK;
		}

		return sprintf( "../../../%s/html/scripts/%s", $APP_ID, getScreenPage( $APP_ID, $SCR_ID ) );
	}

	function getUserFirstPage( $U_ID )
	//
	// Returns first page of the user depend on current user settings, taking into account some condition
	//
	//		Parameters:
	//			$U_ID - user identifier
	//
	//		Returns: string containing script identifier of the user page.
	//
	{
		global $loc_str;

		html_initRights( $_SESSION[WBS_DBKEY] );

		if ( isAdministratorID( $U_ID ) ) {
			$adminData = loadAdminInfo();
			$language = $adminData[LANGUAGE];

			return prepareURLStr( PAGE_ADMINSTARTUP, array() );
		} else
			$language = readUserCommonSetting( $U_ID, LANGUAGE );

		if ( !strlen($language) )
			$language = LANG_ENG;

		$kernelStrings = $loc_str[$language];

		if ( isSystemFirstLogin() && SHOW_TIPSANDTRICKS ) {
			return prepareURLStr( PAGE_TIPSANDTRICKS, array() );
		}

		if ( !( $loginMessage = loginMessageRequired($U_ID, $kernelStrings, $infoStr) ) ) {
			return getUserStartUpPage( $U_ID );
		} else
			return prepareURLStr( PAGE_SIMPLEREPORT, array(INFO_STR=>urlencode(base64_encode($infoStr))) );
	}

	//
	// Authorization functions
	//

	function checkUserLogin( $U_ID )
	//
	//	Check user logged in to system
	//
	//		Parameters:
	//			U_ID - user identifier
	//
	//		Returns true, if user logged in using login page.
	//
	{
		return strlen( $U_ID );
	}

	function initPage( $U_ID, $SCR_ID, $APP_ID, $public )
	//
	//  Checks if user logged in and hava an access to page specified.
	//		Initialization of variables $currentUser, $language, $templateName, $styleSet, $html_encoding
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$SCR_ID - page identifier
	//			$APP_ID - application identifier
	//			$public - common access page
	//
	//		Returns
	//			0, if user is not logged in
	//			-1 - if user doesn't have access to page
	//			1 - if user logged in and have an access to page
	//
	{
		global $currentUser;
		global $language;
		global $templateName;
		global $styleSet;
		global $html_encoding;
		
		global $inplaceScreen;
		global $global_screens;

		$U_ID = strtoupper( $U_ID );
		$currentUser = $U_ID;
		$html_encoding = 'utf-8';

		if ( !checkUserLogin( $U_ID ) )
			return 0;
		
		if ($inplaceScreen) {
			list ($service, $encFolder) = split("_", $inplaceScreen);
			$serviceScreens = array_keys($global_screens[$service]);
			$serviceScreenId = $serviceScreens[0];
			if ( !checkUserAccessRights( $U_ID, $serviceScreenId, $service, $public) )
				return -1;
		} else {
			if ( !checkUserAccessRights( $U_ID, $SCR_ID, $APP_ID, $public) )
				return -1;
		}

		if ( $U_ID == ADMIN_USERNAME ) {
			$adminInfo = loadAdminInfo();

			$language =	$adminInfo[LANGUAGE];
			$templateName = $adminInfo[TEMPLATE];
			$styleSet = HTML_DEFAULT_STYLESET;
		}
		else {
			$language = readUserCommonSetting( $U_ID, LANGUAGE );

			$templateName = readUserCommonSetting( $U_ID, TEMPLATE );
			if ( !strlen($templateName) )
				$templateName = HTML_DEFAULT_TEMPLATE;

			if ( $templateName == HTML_DEFAULT_TEMPLATE ) {
				$styleSet = readUserCommonSetting( $U_ID, HTML_STYLESET );
				if ( !strlen( $styleSet ) )
					$styleSet = HTML_DEFAULT_STYLESET;

				$templateStyleSets = loadTemplateStylesets( $templateName );
				if ( !in_array( $styleSet, $templateStyleSets ) ) {
						$templateName = HTML_DEFAULT_TEMPLATE;
						$styleSet = HTML_DEFAULT_STYLESET;
					}
			}
		}
		
		if (defined("SET_USER_LANGUAGE")) {
			$language = SET_USER_LANGUAGE;
		}
			
		$lang = getApplicationLanguage( $APP_ID, $language );
		
		if ($lang) {
		    $language = $lang;
		} 

		return 1;
	}

	function pageUserAuthorization( $SCR_ID, $APP_ID, $public, $doNotLogout = false )
	//
	// Perform authorization of the user in the page specified.
	//		Fill in $errorStr and $fatalError variables if access denied.
	//
	//		Parameters:
	//			$SCR_ID - page identifier
	//			$APP_ID - application identifier
	//			$public - common access page
	//			$topLevelPage - top level common access page
	//			$doNotLogout - do not logout in case of error
	//
	//		Returns: nothing
	//
	{
		global $language;
		global $templateName;
		global $errorStr;
		global $fatalError;
		global $loc_str;
		global $userGlobalSettings;

		// Authorize user
		//
		$code = initPage( $_SESSION[WBS_USERNAME], $SCR_ID, $APP_ID, $public );

		if ( $code != 1 ) {

			handleEvent($APP_ID, "onFailurePageUserAuthorization", array(), $language);
			if (!strlen($language))
				$language = LANG_ENG;

			if (!strlen($templateName))
				$templateName = HTML_DEFAULT_TEMPLATE;

			if ( !$doNotLogout )
				redirectBrowser( PAGE_LOGIN, array() );

			$errorStr = $loc_str[$language][ERR_GENERALACCESS];
			$fatalError = true;
		}

		// Prepare user session
		//
		global $currentUser;

		if ( strlen($SCR_ID) && strlen($APP_ID) )
			writeUserCommonSetting( $currentUser, PAGE_LAST, sprintf("%s/%s", $APP_ID, $SCR_ID), $loc_str[$language] );

		if ( !isAdministratorID( $currentUser ) && isSystemFirstLogin())
			writeHostDataFileParameter( "/".HOST_DATABASE."/".HOST_DBSETTINGS, HOST_FIRSTLOGIN, 1, $loc_str[$language] );

		$userGlobalSettings = array();
		$userGlobalSettings = readUserSummaryCommonSysSettings( $currentUser, $loc_str[$language] );

		// Check the billing suspend
		//

		if ( isHostingAccount() && $SCR_ID != PAGE_ACCOUNT_SUSPENDED && $SCR_ID != 'CP' )
		{
			handleEvent($APP_ID, "onFailurePageUserAuthorization", array(), $language);
			$days = getDaysBeforeSuspend();
			if ( $days < 0 )
			{
				redirectBrowser( PAGE_ACCOUNT_SUSPENDED, array() );
			}
		}
		global $databaseInfo;

		if( isset($databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) && $databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE])
		{
			$dbStamp = sqlTimestamp( $databaseInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE] );

			if ( $dbStamp <= time() )
			{
				handleEvent($APP_ID, "onFailurePageUserAuthorization", array(), $language);
				redirectBrowser( '../../../AA/html/scripts/logout.php', array() );
			}
		}

		handleEvent($APP_ID, "onSuccessPageUserAuthorization", array(), $language);
		return null;
	}

	function html_login( $userdata, $kernelStrings, $noexpire = false, $directAccess = false, $loginPageURL = null, $confirmTrialSubscription = false )
	//
	// Performs html-client system login
	//
	//		Parameters:
	//			$userdata - an array containing login form information
	//			$kernelStrings - an array with the localization strings
	//				for the current laguage (given from localization.php)
	//			$noexpire - do not expire cookie
	//			$directAccess - return actual first page instead of frameset, in case of framed template
	//			$loginPageURL - URL of the login page
	//
	//		Returns first user page address
	//
	{
		global $databaseInfo;
		global $userGlobalSettings;
		global $session_started_earlier;
		
		$userdata = trimArrayData( $userdata );

		$U_ID = strtoupper($userdata["U_ID"]);
		$DB_KEY = strtolower($userdata["DB_KEY"]);

		$session_started = ini_get( 'session.auto_start' );
		
		$exPeriod = ( isset($databaseInfo[HOST_DBSETTINGS][HOST_SESS_EXPIRE_PERIOD]) ) ?
			$databaseInfo[HOST_DBSETTINGS][HOST_SESS_EXPIRE_PERIOD] : SESSION_USE_SYSTEM_TO;

		if (defined("LOGIN_NOEXPIRE") && LOGIN_NOEXPIRE) {
			ini_set( 'session.cookie_lifetime', 2592000 );
			session_set_cookie_params( 2592000 );			
		} else {
			ini_set( 'session.cookie_lifetime', 0 );
			session_set_cookie_params( 0 );
			setcookie("onbrowsercloseexpire", true);
		}
		
		if ( !$session_started && !$session_started_earlier )
			session_start();
		
		$_SESSION[WBS_USERNAME] = $U_ID;
		$_SESSION[WBS_DBKEY] = $DB_KEY;
		$_SESSION['timestamp'] = time();
		$_SESSION[LOGIN_PAGE_URL] = $loginPageURL;
		
		$_SESSION[HOST_SESS_EXPIRE_PERIOD] = $exPeriod;
		if (defined("LOGIN_NOEXPIRE") && LOGIN_NOEXPIRE)
			$_SESSION["NOEXPIRE"] = 1;
		else
			unset($_SESSION["NOEXPIRE"]);
		
		if ( !isAdministratorID($U_ID) ) {
			$template = readUserCommonSetting( $U_ID, NEW_TEMPLATE );
			if ($template && strlen($template) ) {
				writeUserCommonSetting( $U_ID, TEMPLATE, $template, $kernelStrings );
				writeUserCommonSetting( $U_ID, NEW_TEMPLATE, "", $kernelStrings );
			}
		}

		if ( isset($databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY]) && $databaseInfo[HOST_DBSETTINGS][HOST_TEMPORARY] && $confirmTrialSubscription )
		{
			require_once( WBS_DIR."published/hostagent/hostagent.php" );
			require_once( WBS_DIR."published/hostagent/trialregistrator.php" );

			$Registrator = new TrialRegistrator( null, null );
			$Registrator->ConfirmTrial( $DB_KEY );
		}

		if ( $directAccess && strlen($userdata['PAGE']) ) {
			$pageData = explode( "/", $userdata['PAGE'] );
			$APP_ID = strtoupper( $pageData[0] );
			$SCR_ID = $pageData[1];
			
			$_SESSION['HIDENAVIGATION'] = 1;

			$userGlobalSettings = array();
			$userGlobalSettings = readUserSummaryCommonSysSettings( $U_ID, $kernelStrings );

			//
			// Check user rights
			//

			$userScreens = listUserScreens( $U_ID );
			if ( !array_key_exists( $APP_ID, $userScreens ) ) {
				redirectBrowser( 'login.php', array( 'error'=>base64_encode($kernelStrings['app_accessdenied_message']) ) );
				return null;
			}

			$app_screens = $userScreens[$APP_ID];
			if ( !in_array( $SCR_ID, $app_screens ) ) {
				redirectBrowser( 'login.php', array( 'error'=>base64_encode($kernelStrings['app_accessdenied_message']) ) );
				return null;
			}

			return sprintf( "%s/html/scripts/%s", $APP_ID, getScreenPage( $APP_ID, $SCR_ID ) );
		} else {
			return true;
			$_SESSION['HIDENAVIGATION'] = 0;
			return getTemplateFirstPage( $U_ID, $directAccess );
		}
	}

	function process_htmllogin( $kernelStrings, $loginData, $directAccess = false, $loginPageURL = null, $confirmTrialSubscription = false, $noEnter = false )
	//
	// Function used on login page to process form data
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//			$loginData - user login data
	//			$directAccess - user attempts to access his start page directly
	//			$loginPageURL - URL of the login page
	//
	{
		global $host_applications,$session_started_earlier;
		
		$res = host_login( $loginData, $kernelStrings, $_SERVER["REMOTE_ADDR"], "web", false, $directAccess);
		if ( PEAR::isError($res) )
			return $res;
			
	
		if ($noEnter) {
			$userdata = $loginData;
			$userdata = trimArrayData( $userdata );

			$U_ID = strtoupper($userdata["U_ID"]);
			$DB_KEY = strtolower($userdata["DB_KEY"]);

			$session_started = ini_get( 'session.auto_start' );

			ini_set( 'session.cookie_lifetime', 2592000 );
			session_set_cookie_params( 2592000 );

			if ( !$session_started && !$session_started_earlier )
				session_start();

			$_SESSION[WBS_USERNAME] = $U_ID;
			$_SESSION[WBS_DBKEY] = $DB_KEY;
			$_SESSION['timestamp'] = time();
			$_SESSION[LOGIN_PAGE_URL] = $loginPageURL;
		
			return true;
		}

		setcookie ( "wbs_login_host", $loginData["DB_KEY"], time()+COOKIE_TO_LONG, "/" );
		if (Env::Post('remember')) {
			setcookie ("wbs_username", $loginData["U_ID"], time()+30 * 86400, "/" );
		    setcookie('wbs_hash', md5($loginData['U_PASSWORD']), time() + 30 * 86400, "/");
		}		

		$host_applications = getHostApplications(false);
		
		if ( PEAR::isError($host_applications) )
			die( "Unable load host application list" );

		if ( $directAccess ) {
			$host_applications = array_merge( $host_applications, array( MYWEBASYST_APP_ID, AA_APP_ID, UG_APP_ID ) );

			foreach ( $host_applications as $APP_ID )
				if ( !performAppRegistration( $APP_ID ) )
					die( "Error registering applications" );
		}
		
		$first_page = html_login( $loginData, $kernelStrings, false, $directAccess, $loginPageURL, $confirmTrialSubscription );

		$pathData = explodePath($first_page);
		$path = array();
		for ( $i = 0; $i < count($pathData); $i++ )
			if ( $pathData[$i] != ".." )
				$path[] = $pathData[$i];

		$path = implode( "/", $path );
		
		if(isset($loginData['REDIRECT']))
			redirect($loginData['REDIRECT']);
		
		if (is_string($first_page))
			redirect($first_page);
		else {
			if (onWebAsystServer())
				redirect('./');
			else
				redirect('./index.php');
		}
		
		return;/*

		if(isset($loginData['IFRAME'])){

			redirect('iframe_redirect.php?url='.base64_encode($path).(isset($loginData['ssl'])?'&ssl=1':''));
		}

		redirectBrowser( $path, array(), "", true );*/
	}

	//
	// Attached files management functions
	//

	function add_moveAttachedFile( $fileInfo, $fileList, $destFolder, $kernelStrings, $temporary = false, $tmpPrefix = null  )
	//
	//  Move attached file from temprorary php directory into directory specified.
	//		and add this file into file list.
	//
	//		Parameters:
	//			$fileInfo - file info array with the following fields:
	//				name - file name
	//				type - mime-type
	//				size - size in bytes
	//				screenname - display file name
	//				comment - file comments
	//				tmpfilename - temprorary file name
	//			$fileList - A string, containing a list of files
	//			$destFolder - destination file path.
	//			$kernelStrings - localization strings for the current language (givent from localization.php)
	//			$temporary - True if the file is termporary
	//			$tmpPrefix - termprorary file prefix
	//
	//		Returns updated array or PEAR_Error
	//
	{
		$filename = $fileInfo['name'] = getFileBaseName( $fileInfo['name'] );

		if ( !strlen( $filename ) )
			return $fileList;

		$destFolder = fixDirPath($destFolder);

		$limit = getApplicationResourceLimits( AA_APP_ID, 'SPACE' );
 
		if ( $limit !== null || DATABASE_SIZE_LIMIT > 0 ) {
			$QuotaManager = new DiskQuotaManager();

			$TotalUsedSpace = $QuotaManager->GetUsedSpaceTotal( $kernelStrings );
			if ( PEAR::isError($TotalUsedSpace) )
				return $TotalUsedSpace;

			if ( $QuotaManager->SystemQuotaExceeded($TotalUsedSpace + $fileInfo['size']) )
				return $QuotaManager->ThrowNoSpaceError( $kernelStrings );
/*

			$spaceUsed = getSystemSpaceUsed();
			$spaceUsed += $fileInfo['size'] + getDatabaseSize();

			if ( strlen($filename) && spaceLimitExceeded($spaceUsed/MEGABYTE_SIZE) )
				return PEAR::raiseError( $kernelStrings['app_dbsizelimit_message'], ERRCODE_APPLICATION_ERR ); */
		}

		$res = @forceDirPath( $destFolder, $fdError );
		if ( !$res )
			return PEAR::raiseError( $kernelStrings[ERR_CREATEDIRECTORY] );

		$diskFileName = $fileInfo['diskfilename'] = translit( $fileInfo['name'] );
		$tmpFileName = $fileInfo['tmpfilename'] = uniqid( TMP_FILES_PREFIX.$tmpPrefix );

		$destFileName = ($temporary) ? $tmpFileName : $diskFileName ;

		// Move file
		//
		$filePath = $destFolder."/".$destFileName;

		if ( !@move_uploaded_file( $fileInfo['tmp_name'], $filePath ) )
			return PEAR::raiseError( $kernelStrings[ERR_ATTACHFILE] );

		// Add file to list
		//
		$fileList = addAttachedFile( $fileList, $fileInfo );
		if ( PEAR::isError( $fileList ) )
			return PEAR::raiseError( $kernelStrings[ERR_ATTACHFILE] );

		return $fileList;
	}

	function makeRecordAttachedFilesList( $RECORD_FILES, $PAGE_DELETED_FILES, $PAGE_ATTACHED_FILES, $kernelStrings )
	//
	// Create attached file list based on existing files, removed files and added files.
	//
	//		Parameters:
	//			$RECORD_FILES - A list of existing attached files
	//			$PAGE_DELETED_FILES - a list of removed files
	//			$PAGE_ATTACHED_FILES - a list of appended files
	//			$kernelStrings - an array with localization strings for the current language (given from localization.php)
	//
	//		Returns: string containing file list in the xml format or PEAR_Error
	//
	{
		$res = removeAttachedFileList( $RECORD_FILES, $PAGE_DELETED_FILES) ;
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_ATTACHFILE] );

		$RECORD_FILES = $res;

		$res = mergeAttachedFileLists( $RECORD_FILES, $PAGE_ATTACHED_FILES );
		if ( PEAR::isError($res) )
			return PEAR::raiseError( $kernelStrings[ERR_ATTACHFILE] );

		return $res;
	}

	function deleteAttachedFiles( $RECORD_FILES, &$PAGE_DELETED_FILES, &$PAGE_ATTACHED_FILES,
									$newDeletedFiles, $existingDeletedFiles, $kernelStrings )
	//
	// Apply changes based on list of removed files.
	//		Also removes files from temprorary directory if they was removed on the page.
	//
	//		Parameters:
	//			$RECORD_FILES - a list of existing attached files
	//			$PAGE_DELETED_FILES - a list of removed files
	//			$PAGE_ATTACHED_FILES - a list of appended files
	//			$newDeletedFiles - a list of files that are attached an then deleted.
	//			$existingDeletedFiles - a list of files, attached to record and deleted files.
	//			$kernelStrings - an array with localization strings for the current language (given from localization.php)
	//
	//		Returns: nothing or PEAR_Error
	//
	{
		$pathPattern = "%s/%s";
		$tmpDirPath = fixDirPath(WBS_TEMP_DIR);
		$pageFiles = $PAGE_ATTACHED_FILES;

		if ( is_array($newDeletedFiles) )
			foreach( $newDeletedFiles as $fileKey=>$fileName ) {
				$fileName = base64_decode( $fileName );

				$fileInfo = getAttachedFileInfo( $pageFiles, $fileName );
				if ( is_null($fileInfo) )
					continue;

				if ( !@unlink( sprintf($pathPattern, $tmpDirPath, $fileInfo["tmpfilename"]) ) )
					return PEAR::raiseError( $kernelStrings[ERR_DELETEFILE] );

				$res = removeAttachedFile( $pageFiles, $fileName );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( sprintf($kernelStrings[ERR_DELETEFILEFROMLIST], $fileName) );

				$pageFiles = $res;
			}

		$PAGE_ATTACHED_FILES = $pageFiles;

		$recFiles = $RECORD_FILES;
		$delFiles = $PAGE_DELETED_FILES;

		if ( is_array($existingDeletedFiles) )
			foreach( $existingDeletedFiles as $fileKey=>$fileName ) {
				$fileName = base64_decode( $fileName );

				$fileInfo = getAttachedFileInfo( $recFiles, $fileName );
				$res = addAttachedFile( $delFiles, $fileInfo );
				if ( PEAR::isError( $res ) )
					return PEAR::raiseError( sprintf($kernelStrings[ERR_DELETEFILEFROMLIST], $fileName) );

				$delFiles = $res;
			}

		$PAGE_DELETED_FILES = $delFiles;
	}

	function makeAttachedFileList( $RECORD_FILES, $PAGE_DELETED_FILES, $PAGE_ATTACHED_FILES,
									$cbDeleteNewFileName, $cbDeleteRecFileName )
	//
	// Returns list of attached files to send into html template.
	//
	//		Parameters:
	//			$RECORD_FILES - a list of existing attached files.
	//			$PAGE_DELETED_FILES - a list of deleted files
	//			$PAGE_ATTACHED_FILES - a list of attached files
	//			$cbDeleteNewFileName - checkbox name for delete files attached
	//			$cbDeleteRecFileName - checkbox name for delete files, attached to record
	//
	//		Returns: an array of files or nothing
	//
	{
		$newAttachedFiles = listAttachedFiles( $PAGE_ATTACHED_FILES );
		$newFilesNum = count($newAttachedFiles);

		$curRecordFiles = removeAttachedFileList( $RECORD_FILES, $PAGE_DELETED_FILES );
		$recordAttachedFiles = listAttachedFiles( $curRecordFiles );
		$recordFilesNum = count($recordAttachedFiles);

		if ( $newFilesNum || $recordFilesNum ) {
			$attachedFiles = array();

			for ( $i = 0; $i < $newFilesNum; $i++ ) {
				$curFile = $newAttachedFiles[$i];

				$attachedFiles[$curFile["screenname"]] = array( "type"=>AF_NEWFILE,
																"size"=>formatFileSizeStr($curFile["size"]),
																"diskfilename"=>sprintf("%s", base64_encode($curFile["diskfilename"]) ),
																"delcbName"=>$cbDeleteNewFileName,
																"delcbValue"=>sprintf("%s", base64_encode($curFile["name"]) ) );
			}

			for ( $i = 0; $i < $recordFilesNum; $i++ ) {
				$curFile = $recordAttachedFiles[$i];

				$attachedFiles[$curFile["screenname"]] = array( "type"=>AF_NEWFILE,
																"size"=>formatFileSizeStr($curFile["size"]),
																"diskfilename"=>sprintf("%s", base64_encode($curFile["diskfilename"]) ),
																"delcbName"=>$cbDeleteRecFileName,
																"delcbValue"=>sprintf("%s", base64_encode($curFile["name"]) ) );
			}

			return $attachedFiles;
		}

		return null;
	}

	function getUploadLimitInfoStr( $kernelStrings, $multipleFiles = false )
	//
	// Returns string containing information about restrictions of sizes of uploaded files
	//
	//		Parameters:
	//			$kernelStrings - an array containin strings stored within localization.php in specific language
	//			$multipleFiles - message for form with possibility to attach some files per one upload
	//
	//		Returns string
	//
	{
		$Manager = new DiskQuotaManager();
		$availableSpace = $Manager->GetAvailableSystemSpace($kernelStrings);

		if ( $availableSpace !== null ) {
			$availableSpace = formatFileSizeStr( $availableSpace );
			$result = sprintf( $kernelStrings['app_availablespace_text'], $availableSpace );
		} else
			$result = "";

		$resultMessages = array();

		$maxUploadSize = getMaxUploadSize();
		if ( $availableSpace !== null && $availableSpace > $maxUploadSize )
			$resultMessages[] = sprintf($kernelStrings['app_filesizelimit_text'], formatFileSizeStr( $maxUploadSize ) );

		if ( $multipleFiles ) {
			$maxPostSize = getMaxPostSize();
			if ( $availableSpace !== null && $availableSpace > $maxPostSize )
				$resultMessages[] = sprintf($kernelStrings['app_totalsizelimit_text'], formatFileSizeStr( $maxPostSize ) );
		}

		$cnt = count($resultMessages);
		if ( $cnt ) {
			if ( strlen($result) )
				$resultStr = $result."<br/>";
			else
				$resultStr = null;
			for ( $i = 1; $i <= $cnt; $i++ ) {
				$resultStr .= $resultMessages[$i-1];
				if ( $i < $cnt )
					$resultStr .= "<br/>";
			}

			return $resultStr;
		} else
			return $result;
	}

	function getFileBaseName( $filepath )
	//
	// Helper function for obtaining base file name for uploaded files
	//
	//		Parameters:
	//			$filepath - full file path
	//
	//		Returns string
	//
	{
		return ltrim(basename( ' ' . $filepath ));
	}

	//
	// Applications and screens functions
	//

	function html_extendApplicationData( $appData, $selectedIds, $commentStr, $language )
	//
	// Extends information about applications, given from listPublishedApplications() function.
	//
	//		Parameters:
	//			$appData - information about application
	//			$selectedIds - selected application identifiers
	//			$commentStr - string, containing relation between applications
	//			$language - user language
	//
	//		Returns an extended array $appData
	//
	{
		if ( !is_null($selectedIds) )
			$selectedIds = array_keys( $selectedIds );

		$app_ids = array_keys( $appData );

		for( $i = 0; $i < count( $app_ids ); $i++ ) {
			$APP_ID = $app_ids[$i];
			$curAppData = $appData[$APP_ID];

			$curAppData["PARENTS_JS"] = sprintf( "'%s'", implode("','", $curAppData[APP_REG_PARENTS]) );

			$curAppData["DEPENDENT_JS"] = sprintf( "'%s'", implode("','", $curAppData[APP_REG_DEPENDENCES]) );

			$curAppData[APP_CHECKED] = 0;
			if ( !is_null($selectedIds) )
				if ( in_array( $APP_ID, $selectedIds ) )
					$curAppData[APP_CHECKED] = 1;

			$appData[$APP_ID] = $curAppData;
		}

		return $appData;
	}

	function html_initRights( $DB_KEY )
	//
	// Initializes the user rights manager
	//
	//		Parameters:
	//			$DB_KEY - database key
	//
	//
	{
		global $appListToLoad;
		global $UR_Manager;

		$databaseInfo = loadHostDataFile( $DB_KEY );

		if ( !defined('DATABASE_SIZE_LIMIT') )
		{
			$dbLimit = $databaseInfo[HOST_DBSETTINGS][HOST_DBSIZE_LIMIT];
			if ( !strlen($dbLimit) )
				$dbLimit = 0;
			define( "DATABASE_SIZE_LIMIT", $dbLimit );
		}

		if ( is_null($appListToLoad) )
			$host_applications = getHostApplications();
		else
			$host_applications = $appListToLoad;

		if ( PEAR::isError($host_applications) )
			die( "Unable load host application list" );

		$host_applications = array_merge( $host_applications, array( MYWEBASYST_APP_ID, AA_APP_ID, UG_APP_ID ) );

		// System information registering
		//
		foreach ( $host_applications as $APP_ID )
			if ( !performAppRegistration( $APP_ID ) )
				die( "Error registering applications" );

		$ur_applications = array();
		foreach ( $host_applications as $application )
			$ur_applications[$application] = $application;

		foreach ( sortApplicationList( $ur_applications ) as $application )
			require_once( WBS_PUBLISHED_DIR . "/". strtoupper( $application ) . "/" .WBS_UR_APPCLASS_FILE );
	}

	//
	// Pages support
	//

	function addPagesSupport( $list, $recsPerPage, &$showPageSelector, &$currentPage, &$pages, &$pageCount )
	//
	// Automation of display large lists
	//
	//		Parameters:
	//			$list - source list
	//			$recsPerPage - count of records per page
	//			$showPageSelector - true if select page combobox required
	//			$currentPage - current page
	//			$pages - items of select page combobox (see showPageSelector) or as HTML links if
	//			$pageCount - maximum count of pages
	//
	//		Returns an array with records range between [$currentPage .. $currentPage + $recsPerPage]
	//
	{
		$itemCount = count($list);
		$showPageSelector = $itemCount > $recsPerPage;

		$pages = array();

		if ( $showPageSelector ) {
			$pageCount = ceil( $itemCount/$recsPerPage );
			if ( $currentPage > $pageCount )
				$currentPage --;

			$list = refinedSlice( $list, ($currentPage-1)*$recsPerPage, $recsPerPage );

			for ( $i = 0; $i < $pageCount; $i++ )
				$pages[] = $i+1;
		}

		return $list;
	}

	function getQueryLimitValues( $itemCount, $recsPerPage, &$showPageSelector, &$currentPage, &$pages, &$pageCount, &$startIndex, &$count )
	//
	// Returns limit values for mySQL query and prepares displaying page links for HTML page
	//
	//		Parameters:
	//			$itemCount - item count in the query result
	//			$recsPerPage - count of records per page
	//			$showPageSelector - true if select page combobox required
	//			$currentPage - current page. May be alteret by the function
	//			$pages - items of select page combobox (see showPageSelector) or as HTML links if
	//			$pageCount - maximum count of pages
	//			$startIndex - query start index
	//			$count - query count
	//
	//		Returns null
	//
	{
		$showPageSelector = $itemCount > $recsPerPage;

		$pages = array();

		if ( $showPageSelector ) {
			$pageCount = ceil( $itemCount/$recsPerPage );
			if ( $currentPage > $pageCount )
				$currentPage  = $pageCount;

			$startIndex = ($currentPage-1)*$recsPerPage;
			$count = $recsPerPage;

			for ( $i = 0; $i < $pageCount; $i++ )
				$pages[] = $i+1;
		} else {
			$startIndex = 0;
			$count = $recsPerPage;
		}

		return null;
	}

	//
	// Thumbnail support
	//

	function dumpFileThumbnail( $filePath, $os, $srcExt = null )
	//
	// Prints thumbnail file content
	//
	//		Parameters:
	//			$filePath - path to the original file
	//			$os - thumbnail operation system style
	//			$srcExt - file extension
	//
	//		Returns file content
	//
	{
		$thumbPath = findThumbnailFile( $filePath, $ext );

		if ( !is_null($thumbPath) ) {
			if ( $ext == 'gif' )
				header( 'Content-type: image/gif' );
			else
				header( 'Content-type: image/jpeg' );

			@readfile( $thumbPath );
		} else {
			$fileNameData = pathinfo( $filePath );

			$baseDir = "../../../common/html/thumbnails";
			$filePath = "$baseDir/$srcExt.$os.32.gif";

			if ( !file_exists($filePath) )
				$filePath = "$baseDir/common.$os.32.gif";

			header( 'Content-type: image/gif' );
			@readfile( $filePath );
		}
	}

	function getThumbnailModifyDate( $filePath, $os, $srcExt = null )
	//
	// Returns thumbnail modification date
	//
	//		Parameters:
	//			$filePath - path to the original file
	//			$os - thumbnail operation system style
	//			$srcExt - file extension
	//
	//		Returns file content
	//
	{
		$thumbPath = findThumbnailFile( $filePath, $ext );

		if ( !is_null($thumbPath) ) {
			return filemtime($thumbPath);

		} else {
			$fileNameData = pathinfo( $filePath );

			$baseDir = "../../../common/html/thumbnails";
			$filePath = "$baseDir/$srcExt.$os.32.gif";

			if ( !file_exists($filePath) )
				$filePath = "$baseDir/common.$os.32.gif";

			return filemtime($filePath);
		}

		return null;
	}

	//
	// Tree/document functions
	//

	function formatFolderRightsQualifier( $folderRights )
	//
	// Prepares colorized folder rights qualifier (RWF)
	//
	//		Parameters:
	//			$folderRights - folder rights
	//
	//		Returns HTML string
	//
	{
		$readQualifier = '<font color="#008000"><b>R</b></font>';
		$writeQualifier = '<font color="#FF0000"><b>W</b></font>';
		$folderQualifier = '<font color="#EE8F00"><b>F</b></font>';

		if ( $folderRights == 2 )
			return $readQualifier.$writeQualifier.$folderQualifier;

		if ( $folderRights == 1 )
			return $readQualifier.$writeQualifier;

		if ( $folderRights == 0 )
			return $readQualifier;
	}

	//
	// Tips & tricks
	//

	function loadBookPageList( $bookID, &$kernelStrings )
	//
	// Loads list of Quick Pages book pages
	//
	//		Parameters:
	//			$bookID - book identifier
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array
	//
	{
		global $qp_service_options;

		$soapclient = new SOAP_Client( WBS_TT_ENDPOINT );
		if( PEAR::isError($soapclient) )
			return $soapclient;

		$soapclient->setOpt( 'timeout', 0 );

		$parameters = array();
		$parameters['book_id'] = base64_encode( $bookID );
		$parameters['U_ID'] = base64_encode(WBS_TT_SOAP_USER);
		$parameters['PASSWORD'] = base64_encode(WBS_TT_SOAP_PWD);

		$res = $soapclient->call( "qp_publictree", $parameters, $qp_service_options );
		if ( PEAR::isError($res) ) {
			return $res;
		}

		if ( $res->error != 0 ) {
			return PEAR::raiseError( "Error loading book" );
		}

		return (array) unserialize( base64_decode( $res->pagelist ) );
	}

	function listQpBooks( &$kernelStrings )
	//
	// Returns list of published Quick Pages books
	//
	//		Parameters:
	//			$kernelStrings - Kernel localization strings
	//
	//		Returns array
	//
	{
		global $qp_service_options;

		$soapclient = new SOAP_Client( WBS_TT_ENDPOINT );
		if( PEAR::isError($soapclient) )
			return $soapclient;

		$soapclient->setOpt( 'timeout', 0 );

		$parameters = array();
		$parameters['U_ID'] = base64_encode(WBS_TT_SOAP_USER);
		$parameters['PASSWORD'] = base64_encode(WBS_TT_SOAP_PWD);

		$res = $soapclient->call( "qp_booklist", $parameters, $qp_service_options );
		if ( PEAR::isError($res) ) {
			return $res;
		}

		if ( $res->error != 0 ) {
			return PEAR::raiseError( "Error loading book list" );
		}

		return (array) unserialize( base64_decode( $res->booklist ) );
	}

	function loadBookPageContent( $bookID, $pageID, &$kernelStrings, &$pageTitle, $protocol="http" )
	//
	// Loads Quick Pages book page content
	//
	//		Parameters:
	//			$bookID - book identifier
	//			$pageID - page identifier
	//			$kernelStrings - Kernel localization strings
	//			$pageTitle - page title
	//
	//		Returns array
	//
	{
		global $qp_service_options;

		$soapclient = new SOAP_Client( WBS_TT_ENDPOINT );
		if( PEAR::isError($soapclient) )
			return $soapclient;

		$soapclient->setOpt( 'timeout', 0 );

		$parameters['book_id'] = base64_encode( $bookID );
		$parameters['page_id'] = base64_encode( $pageID );
		$parameters['U_ID'] = base64_encode(WBS_TT_SOAP_USER);
		$parameters['PASSWORD'] = base64_encode(WBS_TT_SOAP_PWD);

		$res = $soapclient->call( "qp_publicpage", $parameters, $qp_service_options );

		if ( PEAR::isError($res) )
			return $res;

		if ( $res->error != 0 )
			return PEAR::raiseError( "Error loading book" );

		$page = (array) unserialize( base64_decode( $res->pagedata ) );

		$pageTitle = $page['QPF_NAME'];

		if ( $protocol != "http" && $protocol != "https" )
			$protocol = "http";

		$page["QPF_CONTENT"] = preg_replace( '/(<[^>]*?="{0,1})([^">]*)'.$page["QPF_UNIQID"].'([^">]*?[^>]*>)/u', '$1'.$protocol.WBS_TT_IMG_PATH.$page["QPF_UNIQID"].'$3', $page["QPF_CONTENT"] );

		return $page['QPF_CONTENT'];
	}

	function getTipsAndTricksPage( $U_ID, &$kernelStrings, &$pageTitle, $showQuickStart, $protocol = "http" )
	//
	// Returns user tips and tricks book and page identifiers, and page title
	//
	//		Parameters:
	//			$U_ID - user identifier
	//			$kernelStrings - Kernel localization strings
	//			$pageTitle - page title
	//			$showQuickStart - return quick start page
	//
	//		Returns array
	//
	{
		global $global_applications;

		$language = strtolower(readUserCommonSetting( $U_ID, LANGUAGE ));

		if ( $showQuickStart ) {
			// Return startup page identifier
			//
			$bookID = "ttmw".($language=='rus'?'-rus':'');

			$bookPages = loadBookPageList( $bookID, $kernelStrings );
			if ( PEAR::isError($bookPages) )
				return PEAR::raiseError( $kernelStrings['tt_loadingbook_message'] );

			$pageKeys = array_keys( $bookPages );
			$pageKey = $pageKeys[0];
			$pageTextID = $bookPages[$pageKey]->QPF_TEXTID;

			$pageContent = loadBookPageContent( $bookID, $pageTextID, $kernelStrings, $pageTitle, $protocol );
			if ( PEAR::isError($pageContent) )
				return PEAR::raiseError( $kernelStrings['tt_loadingbook_message'] );

			return $pageContent;
		} else {
			// Return random application random book random page
			//
			$bookList = listQpBooks( $kernelStrings );
			if ( PEAR::isError($bookList) )
				return PEAR::raiseError( $kernelStrings['tt_loadingbook_message'] );

			$applications = array_keys( listUserScreens($U_ID) );
			$appBooks = array();
			foreach( $applications as $APP_ID ) {
				foreach ($bookList as $bookData ) {
					if ( $bookData->QPB_TEXTID == strtolower("tt".$APP_ID) )
						$appBooks[] = $bookData->QPB_TEXTID;
				}
			}

			$keyIndex = rand( 1, count($appBooks) )-1;
			$bookID = $appBooks[$keyIndex].($language=='rus'?'-rus':'');

			$bookPages = loadBookPageList( $bookID, $kernelStrings );
			if ( PEAR::isError($bookPages) )
				return PEAR::raiseError( $kernelStrings['tt_loadingbook_message'] );

			$resultPages = array();
			foreach( $bookPages as $key=>$value ) {
				if ( !($value->QPF_TEXTID == "quickstart" && $bookID=="ttmw") )
					$resultPages[$key] = $value;
			}

			$pageKeys = array_keys( $resultPages );
			$keyIndex = rand( 1, count($pageKeys) )-1;

			$pageKey = $pageKeys[$keyIndex];
			$pageTextID = $resultPages[$pageKey]->QPF_TEXTID;

			$pageContent = loadBookPageContent( $bookID, $pageTextID, $kernelStrings, $pageTitle, $protocol );
			if ( PEAR::isError($pageContent) )
				return PEAR::raiseError( $kernelStrings['tt_loadingbook_message'] );

			return $pageContent;
		}
	}

	//
	// Client initialization
	//

	loadTemplateList();
?>
