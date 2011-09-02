<?php

require_once('classes/class.arraybuffer.php');

function wbsadmin_getBasicHostData( $host_key, &$hostInfo, $kernelStrings )
//
// Returns basic account information - company name, subscriber name etc
//
//		Parameters:
//			$host_key - database key
//			$hostInfo - variable to put host information
//			$kernelStrings - Kernel localization strings
//
//		Returns null or PEAR_Error
//
{
	$hostInfo = array();

	$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($host_key) );
	$dom = @domxml_open_file( realpath($filePath) );
	if ( !$dom )
	return PEAR::raiseError( $kernelStrings[ERR_XML] );

	$element = @getElementByTagname( $dom, HOST_DBSETTINGS );
	if ( is_null($element) )
	return PEAR::raiseError( $kernelStrings[ERR_XML] );

	$hostInfo[HOST_DBSETTINGS] = getAttributeValues( $element );

	$element = @getElementByTagname( $dom, HOST_FIRSTLOGIN );
	if ( is_null($element) )
	return PEAR::raiseError( $kernelStrings[ERR_XML] );

	$hostInfo[HOST_FIRSTLOGIN] = getAttributeValues( $element );
	$hostInfo[HOST_DB_KEY] = $host_key;

	$applications = @getElementByTagname( $dom, HOST_APPLICATIONS);
	if ( is_null($applications) )
	return PEAR::raiseError( $kernelStrings[ERR_XML] );

	$appList = array();
	$applications = $applications->get_elements_by_tagname(HOST_APPLICATION);
	foreach( $applications as $application ) {
		$app_id = $application->get_attribute( HOST_APP_ID );
		$appList[$app_id] = array( HOST_APP_ID=>$app_id );
	}

	$hostInfo[HOST_APPLICATIONS] = $appList;

	return null;
}

function wbsadmin_getAccountNum()
//
// Returns number of registered accounts
//
//		Returns integer
//
{
	$targetDir = WBS_DBLSIT_DIR;
	$fileExt = "xml";

	if ( !($handle = opendir($targetDir)) )
	return false;

	$count = 0;
	while ( false !== ($name = readdir($handle)) )
	if ( $name != "." && $name != ".." ) {
		$filename = $targetDir.'/'.$name;

		if ( is_dir($filename) )
		continue;

		$path_parts = pathinfo($filename);
		if ( $path_parts["extension"] != $fileExt )
		continue;

		$count++;
	}

	closedir( $handle );

	return $count;
}

function wbsadmin_listRegisteredSystems( $kernelStrings, $fullInfo = true )
//
// Returns registered systems list
//
//		Parameters:
//			$kernelStrings - Kernel localization strings
//
//		Returns array array( DB_KEY1=>DB_DATA1 ), where DB_DATA -
//			is an array returned by loadHostDataFile() function.
//			Returns PEAR_Error in case of error.
//
{
	$result = array();

	$targetDir = WBS_DBLSIT_DIR;
	$fileExt = "xml";

	if ( !($handle = opendir($targetDir)) )
	return false;

	while ( false !== ($name = readdir($handle)) ) {
		if ( $name != "." && $name != ".." ) {
			$filename = $targetDir.'/'.$name;

			if ( is_dir($filename) )
			continue;

			$path_parts = pathinfo($filename);
			if ( $path_parts["extension"] != $fileExt )
			continue;

			$db_key = substr( $name, 0, strlen($name)-strlen($fileExt)-1 );
				
			if($fullInfo){
				$hostInfo = null;
				$res = wbsadmin_getBasicHostData( $db_key, $hostInfo, $kernelStrings );
				if ( !PEAR::isError($res) )
				$result[$db_key] = $hostInfo;
			}else{
				$result[$db_key] = $db_key;
			}
		}
	}

	closedir( $handle );

	return $result;
}

function wbsadmin_deleteSQLServer( $serverData, $kernelStrings, $db_strings )
//
// Deletes SQL server from WBS server list
//
//		Parametes:
//			$serverData - array containing SQL server description
//			$kernelStrings - Kernel localization strings
//			$db_strings - WebAsyst Administrator localization strings
//
//		Returns null or PEAR_Error
//
{
	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );

	$dom = domxml_open_file( realpath($filePath) );
	if ( !$dom )
	return PEAR::raiseError( $db_strings[12], ERRCODE_APPLICATION_ERR );

	$xpath = xpath_new_context($dom);

	if ( !( $sqlserversnode = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SQLSERVERS) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$sqlserversnode = $sqlserversnode->nodeset[0];

	if ( !( $sqlservers = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SQLSERVERS."/".WBS_SQLSERVER) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$currentServerNode = null;
	foreach( $sqlservers->nodeset as $sqlserver ) {
		if ( strtoupper($sqlserver->get_attribute(WBS_NAME)) == strtoupper($serverData['SERVER_NAME']) ) {
			$currentServerNode = $sqlserver;

			break;
		}
	}

	if ( is_null($currentServerNode) )
	return PEAR::raiseError( $db_strings[15], ERRCODE_APPLICATION_ERR );

	$sqlserversnode->remove_child( $currentServerNode );

	@$dom->dump_file( $filePath, false, true );

	return null;
}

function wbs_saveCommonSettings( $commonData, $kernelStrings, $db_strings )
//
// Saves common WBS settings
//
//		Parametes:
//			$commonData - array containing form data
//			$kernelStrings - Kernel localization strings
//			$db_strings - WebAsyst Administrator localization strings
//
//		Returns null or PEAR_Error
//
{
	global $_PEAR_default_error_mode;
	global $_PEAR_default_error_options;

	$commonData = trimArrayData( $commonData );

	$requiredFields = array( 'COMPANY','LICENSE','TIMEOUT', 'PORT', 'DATA_PATH' );
	if($commonData['SMTP_HOST']){
		$requiredFields = array_merge($requiredFields,array('SMTP_PORT'));
	}

	if ( PEAR::isError( $invalidField = findEmptyField($commonData, $requiredFields) ) ) {
		$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

		return $invalidField;
	}
	$check_integer_fields = array('TIMEOUT', 'PORT', WBS_MEMORYLIMIT,'PROXY_PORT');
	if($commonData['SMTP_HOST']){
		$check_integer_fields = array_merge($check_integer_fields,array('SMTP_PORT'));
	}

	if ( PEAR::isError( $res = checkIntegerFields($commonData, $check_integer_fields, $kernelStrings) ) )
	return $res;

	$dataPath = $commonData['DATA_PATH'];
	$dataPath = str_replace( WBS_WBS_PATH, realpath(WBS_DIR), $dataPath );

	if ( !@file_exists($dataPath) )
	return PEAR::raiseError ( sprintf($db_strings[17], basename($dataPath)), ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode, $_PEAR_default_error_options, 'DATA_PATH' );

	if ( strlen($commonData[WBS_MEMORYLIMIT]) && $commonData[WBS_MEMORYLIMIT] < WBS_MIMMEMORYSETTING )
	return PEAR::raiseError( sprintf($db_strings[58], WBS_MIMMEMORYSETTING), ERRCODE_INVALIDFIELD,
	$_PEAR_default_error_mode, $_PEAR_default_error_options, WBS_MEMORYLIMIT );

	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );

	$dom = domxml_open_file( realpath($filePath) );
	/* @var $dom DOMDocument */

	if ( !$dom )
	return PEAR::raiseError( $db_strings[12], ERRCODE_APPLICATION_ERR );

	$xpath = xpath_new_context($dom);

	if ( !( $wbsnode = &xpath_eval($xpath, "/".WBS_WBS) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$wbsnode = $wbsnode->nodeset[0];

	if ( !( $emailnode = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_EMAIL) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$emailnode = $emailnode->nodeset[0];

	$emailnode->set_attribute( WBS_ENABLED, $commonData['EMAIL'] );
	$emailnode->set_attribute( WBS_ROBOTEMAIL, $commonData['ROBOTEMAIL'] );

	if ( !( $tznode = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SERVER_TIME_ZONE) ) || !count($tznode->nodeset) ) {
		$tznode = @create_addElement( $dom, $wbsnode, WBS_SERVER_TIME_ZONE );
		if ( !$tznode )
		return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	} else
	$tznode = $tznode->nodeset[0];

	$tznode->set_attribute( WBS_SERVER_TIME_ZONE_ENABLE, intval( $commonData['SERVER_TZ'] ) );
	$tznode->set_attribute( WBS_SERVER_TIME_ZONE_ID, $commonData['SERVER_TIME_ZONE_ID'] );
	$tznode->set_attribute( WBS_SERVER_TIME_ZONE_DST, intval( $commonData['SERVER_TIME_ZONE_DST'] ) );

	if ( !( $systemnode = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SYSTEM) ) || !count($systemnode->nodeset) ) {
		$systemnode = @create_addElement( $dom, $wbsnode, WBS_SYSTEM );
		if ( !$systemnode )
		return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	} else
	$systemnode = $systemnode->nodeset[0];

	$systemnode->set_attribute( WBS_MEMORYLIMIT, $commonData[WBS_MEMORYLIMIT] );
	$systemnode->set_attribute( 'COMPANY', $commonData['COMPANY']);
	$systemnode->set_attribute( 'LICENSE', $commonData['LICENSE']);

	if ( !( $html_settings = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_HTML_SETTINGS) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$html_settings = $html_settings->nodeset[0];

	$html_settings->set_attribute( WBS_HTML_HTTPS_PORT, $commonData['PORT'] );
	$html_settings->set_attribute( WBS_SESSION_TIMEOUT, $commonData['TIMEOUT'] );

	if ( !( $dataDir = xpath_eval($xpath, "/".WBS_WBS."/".WBS_DIRECTORIES."/".WBS_DATA_DIRECTORY) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	$dataDir = $dataDir->nodeset[0];

	$dataDir->set_attribute( WBS_PATH, $commonData['DATA_PATH'] );

	//if ( !( $fe_node = &xpath_eval($xpath, "/".WBS_WBS."/".'FRONTEND') ) || !count($fe_node->nodeset) ) {
	//	$fe_node = @create_addElement( $dom, $wbsnode, 'FRONTEND' );
	//	if ( !$fe_node )
	//	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	//}


	if ( !( $webDir = xpath_eval($xpath, "/".WBS_WBS."/".WBS_DIRECTORIES."/".WBS_WEB_DIRECTORY) ) ||!count($webDir->nodeset)){
		$directoties = xpath_eval($xpath, "/".WBS_WBS."/".WBS_DIRECTORIES);
		//var_dump($directoties);exit;
		$directoties = $directoties->nodeset[0];
		$webDir = @create_addElement( $dom, $directoties, WBS_WEB_DIRECTORY );
		/* @var $webDir DOMElement */
		if(!$webDir){
			return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
		}
	}else{
		$webDir = $webDir->nodeset[0];
	}

	if(strlen($commonData['WBS_INSTALL_PATH'])){
		$commonData['WBS_INSTALL_PATH'] = str_replace(array('\\','///','//'),'/','/'.$commonData['WBS_INSTALL_PATH'].'/');
	}
	$webDir->set_attribute( WBS_PATH, str_replace(array('//','\\',),array('/','/'),$commonData['WBS_INSTALL_PATH']) );
	//PROXY settings
	if ( !( $proxyNode = xpath_eval($xpath, "/".WBS_WBS."/PROXY") ) ||!count($proxyNode->nodeset)){
		$mainNode = xpath_eval($xpath, "/".WBS_WBS);
		//var_dump($directoties);exit;
		$mainNode = $mainNode->nodeset[0];
		$proxyNode = @create_addElement( $dom, $mainNode, "PROXY" );
		/* @var $webDir DOMElement */
		if(!$proxyNode){
			return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
		}
	}else{
		$proxyNode = $proxyNode->nodeset[0];
	}


	$proxyNode->set_attribute( 'host', $commonData['PROXY_HOST'] );
	$proxyNode->set_attribute( 'port', $commonData['PROXY_PORT'] );
	$proxyNode->set_attribute( 'user', $commonData['PROXY_USER'] );
	$proxyNode->set_attribute( 'password', $commonData['PROXY_PASS'] );

	//SMTP settings
	if ( !( $smtpNode = xpath_eval($xpath, "/".WBS_WBS."/SMTP_SERVER") ) ||!count($smtpNode->nodeset)){
		$mainNode = xpath_eval($xpath, "/".WBS_WBS);
		//var_dump($directoties);exit;
		$mainNode = $mainNode->nodeset[0];
		$smtpNode = @create_addElement( $dom, $mainNode, "SMTP_SERVER" );
		/* @var $webDir DOMElement */
		if(!$smtpNode){
			return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
		}
	}else{
		$smtpNode = $smtpNode->nodeset[0];
	}
	$smtpNode->set_attribute( 'host', $commonData['SMTP_HOST'] );
	$smtpNode->set_attribute( 'port', $commonData['SMTP_PORT'] );
	$smtpNode->set_attribute( 'user', $commonData['SMTP_USER'] );
	$smtpNode->set_attribute( 'password', $commonData['SMTP_PASSWORD'] );
	$smtpNode->set_attribute( 'helo', $commonData['SMTP_HELO'] );
	//$smtpNode->set_attribute( 'robot_account', $commonData['SMTP_ROBOT'] );


	@$dom->dump_file( $filePath, false, true );
	wbs_deleteCacheDirectorys(WBS_TEMP_DIR.'/scdb/',array('.htaccess'));

	return null;
}

function wbs_makeSysDirs()
//
// Creates system directores - dblist, temp...
//
//		Returns null
//
{
	$dir = "../../../../data";
	if ( !file_exists( $dir ) )
	@mkdir( $dir );

	$dir = "../../../../dblist";
	if ( !file_exists( $dir ) )
	@mkdir( $dir );

	$dir = "../../../../temp";
	if ( !file_exists( $dir ) )
	@mkdir( $dir );
}

function wbs_listSysLanguages()
//
// Returns a list of system languages
//
//		Returns array
//
{
	global $langListFileName;

	$result = array();

	$filePath = WBS_DIR."kernel/$langListFileName";
	if ( !file_exists( $filePath ) )
	return PEAR::raiseError( "Error loading file: $langListFileName" );

	if($handle = fopen( $filePath, "r" )){
		while ( ($data = fgetcsv($handle, 100, "\t") ) !== FALSE ) {
			$result[$data[0]] = array( WBS_LANGUAGE_ID=>$data[0],  WBS_LANGUAGE_NAME=>$data[1], WBS_ENCODING=>$data[2] );
		}
		fclose($handle);
	}else{
		return PEAR::raiseError( "Error reading file: $langListFileName" );
	}

	return $result;
}

function wbs_addmodlanguage( $lang_id, $langData, $action, $kernelStrings, $db_locStrings,
$createOption, $copy_from_lang, $importFile, &$messageStack,
$replaceLocFiles )
//
// Adds/modifies language
//
//		Parameters:
//			$lang_id - language ID
//			$langData - array containing language data
//			$action - form action (new/edit)
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$createOption - create option (0 - copy strings from another language, 1 - load from file)
//			$copy_from_lang - language ID to copy strings from
//			$importFile - file name to import
//			$messageStack - array variable to put error messages
//			$replaceLocFiles - replace licalization files
//
//		Returns null or PEAR::Error
//
{
	if ( $action  == ACTION_NEW )
	$requiredFields = array( WBS_LANGUAGE_ID, WBS_LANGUAGE_NAME, WBS_ENCODING );
	else
	$requiredFields = array( WBS_LANGUAGE_NAME, WBS_ENCODING );

	if ( PEAR::isError( $invalidField = findEmptyField($langData, $requiredFields) ) )
	{
		$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];
		return $invalidField;
	}

	if ( $invalidField = checkStringLengths($langData, array(WBS_LANGUAGE_ID), array(3)) ) {
		$invalidField->message = $kernelStrings[ERR_TEXTLENGTH];

		return $invalidField;
	}

	$symbols = ALPHA_SYMBOLS."- 1234567890";

	if ( PEAR::isError( $invalidField = checkFieldInvalidSymbols($langData, $requiredFields, $symbols) ) )
	{
		$invalidField->message = sprintf( $db_locStrings[22], $invalidField->getUserInfo() );
		$invalidField->code = ERRCODE_INVALIDFIELD;
		return $invalidField;
	}

	$sys_languages = wbs_listSysLanguages();
	if ( PEAR::isError($sys_languages) )
	return $sys_languages;

	$lang_id = strtolower( $lang_id );

	if ( $createOption != null )
	{
		$langData[WBS_LANGUAGE_ID] = strtolower( $langData[WBS_LANGUAGE_ID] );

		$lang_id = $langData[WBS_LANGUAGE_ID];

		if ( $createOption == 0 )
		$res = wbs_copyLocalization( $copy_from_lang, $lang_id, $kernelStrings, $db_locStrings, $messageStack, $langData, $replaceLocFiles );
		else
		if ( $createOption == 1 )
		$res = wbs_importLocalizationFile( $importFile, $lang_id, $kernelStrings, $db_locStrings, $messageStack, $langData, $replaceLocFiles );
		else
		if ( $createOption == 2 )
		$res = wbs_copyEmptyLocalization( $lang_id, $kernelStrings, $db_locStrings, $messageStack, $langData, $replaceLocFiles );

		if ( PEAR::isError($res) )
		return $res;

		if ( $action == ACTION_NEW && array_key_exists($lang_id, $sys_languages) )
		return PEAR::raiseError( $db_locStrings[23], ERRCODE_APPLICATION_ERR );
		else
		if ( $action != ACTION_NEW && !array_key_exists($lang_id, $sys_languages) )
		return PEAR::raiseError( $db_locStrings[52], ERRCODE_APPLICATION_ERR );
	}

	//TODO copy special folders at applications
	if($action == ACTION_NEW){
		foreach(array('PD','UG') as $APP_ID){
			$sourcePath = sprintf(WBS_DIR.'/published/%s/js/%s/',$APP_ID,'en');
			$targetPath = sprintf(WBS_DIR.'/published/%s/js/%s/',$APP_ID,substr($lang_id,0,2));
			if(file_exists($sourcePath)){
				$errStr = '';
				if(!wbs_copyDirectory($sourcePath,$targetPath,$errStr,true)){
					return PEAR::raiseError($errStr);
				}
			}
		}
	}

	$sys_languages[$lang_id] = $langData;

	$res = wbs_saveLanguageList( $sys_languages, $kernelStrings, $db_locStrings );
	if ( PEAR::isError($res) )
	return $res;

	return $res;
}

function wbs_importLocalizationFile( $fileName, $lang_id, $kernelStrings, $db_locStrings, &$messageStack, $langData, $replaceLocFiles  )
//
// Import localization file
//
//		Parameters:
//			$fileName - file to import
//			$lang_id - language id
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$messageStack - array variable to put error messages
//			$langData - array containing language data
//			$replaceLocFiles - replace licalization files
//
//		Returns null
//
{
	$fileContent = array();

	$handle = fopen( $fileName, "r" );
	while ( ( ($str = fgets( $handle )  )  != FALSE ) && !feof( $handle ) )
	{
		if ( trim($str) == "" )
		continue;

		$data = explode( "\t", $str );
		if ( count($data) != 5 )
		return PEAR::raiseError( $db_locStrings[29], ERRCODE_APPLICATION_ERR );

		$fileContent[$data[0]][] = array( $data[1], $data[2], $data[3], $data[4] );
	}

	fclose($handle);
	global $language;
	$appList = listPublishedApplications( $language, true );
	if ( !is_array( $appList ) )
	return PEAR::raiseError( $db_locStrings[1] );

	$appList = array_merge( array(AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[25]) ), MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[64]) ) ), $appList );

	foreach( $appList as $key=>$value )
	{
		if ( isset($fileContent[$key]) )
		{
			$userStrings = $fileContent[$key];

			$app_name = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

			$res = wbs_createLocalizationFile( $lang_id, $key, $userStrings, $kernelStrings, $db_locStrings, $langData, $app_name, $messageStack, $replaceLocFiles );
			if ( PEAR::isError($res) )
			return $res;
		}
	}

	@unlink($fileName);
}

function wbs_exportLocalizationFile( $lang_id, $locStrings, $db_locStrings, &$messageStack )
//
// Import localization file
//
//		Parameters:
//			$lang_id - language id
//			$locStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$langData - array containing language data
//
//		Returns null
//
{
	global $language;
	$appList = listPublishedApplications( $language, true );
	if ( !is_array( $appList ) )
	return PEAR::raiseError( $db_locStrings[1] );

	$appList = array_merge( array(AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[25]) ), MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[64]) ) ), $appList );

	foreach( $appList as $key=>$value )
	{

		$srcPath = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), $lang_id );

		$app_name = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

		if ( !file_exists($srcPath) )
		{
			$messageStack[] = sprintf( "<font color=blue><b>WARNING:</b></font> " . $db_locStrings[31], $sys_languages[$src_lang_id][WBS_LANGUAGE_NAME], $app_name );
			continue;
		}

		$handle = fopen( $srcPath, "r" );
		while ( ( ($str = fgets( $handle )  )  != FALSE ) && !feof( $handle ) )
		{
			if ( trim($str) == "" )
			continue;

			$data = explode( "\t", $str );
			if ( count($data) != 4 ) {
				return PEAR::raiseError( $db_locStrings[29], ERRCODE_APPLICATION_ERR );
			}
			foreach( $data as $key1=>$value1 )
			{
				$tempStr = trim( $value1);
				$data[$key1]= ( $tempStr=="" ) ? " " : $tempStr;
			}
			echo $key."\t".$data[0]."\t".$data[1]."\t".$data[2]."\t".$data[3]."\n";
		}
	}
}


function wbs_copyEmptyLocalization( $dest_lang_id, $kernelStrings, $db_locStrings, &$messageStack, $langData, $replaceLocFiles )
//
// Copies localization strings from one language to another
//
//		Parameters:
//			$dest_lang_id - destination language id
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$messageStack - array variable to put error messages
//			$langData - array containing language data
//			$replaceLocFiles - replace licalization files
//
//		Returns null
//
{

	$src_lang_id = LANG_ENG;

	$sys_languages = wbs_listSysLanguages();
	if ( PEAR::isError($sys_languages) )
	return $sys_languages;

	global $language;
	$appList = listPublishedApplications( $language, true );
	if ( !is_array( $appList ) )
	return PEAR::raiseError( $db_locStrings[1] );

	$appList = array_merge( array(AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[25]) ), MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[64]) ) ), $appList );

	foreach( $appList as $key=>$value )
	{
		$srcPath = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), LANG_ENG );
		$destPath = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), $dest_lang_id );

		$app_name = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

		if ( file_exists($destPath) && !$replaceLocFiles )
		{
			$messageStack[] = sprintf( "<font color=blue><b>WARNING:</b></font> " . $db_locStrings[30], $langData[WBS_LANGUAGE_NAME], $app_name?$app_name:$key );
			continue;
		}

		if ( !file_exists($srcPath) )
		$messageStack[] = sprintf( "<font color=red><b>2ERROR:</b></font> " . $db_locStrings[31], $sys_languages[$src_lang_id][WBS_LANGUAGE_NAME], $app_name?$app_name:$key );

		if ( file_exists($srcPath) && (!file_exists($destPath) || $replaceLocFiles ) )
		{
			$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, strtoupper($key) );
			$loc_str = loadLocalizationStrings( $localizationPath, strtolower($key), NULL, true );

			$fh = @fopen( $destPath, "wt" );
			if ( !$fh )
			{
				$messageStack[] = sprintf(  "<font color=red><b>ERROR ( $app_name ):</b></font> " . $db_locStrings[24] );
				continue;
			}

			foreach( $loc_str[LANG_ENG] as $strData )
			{
				$str = sprintf( "%s\t%s\t%s\t%s\n", $strData[0], $strData[1], $strData[2], "" );
				fwrite( $fh, $str );
			}

			@fclose($fh);
		}

		$messageStack[] = sprintf(  $db_locStrings[54], $app_name );

	}

	return null;
}


function wbs_copyLocalization( $src_lang_id, $dest_lang_id, $kernelStrings, $db_locStrings, &$messageStack, $langData, $replaceLocFiles )
//
// Copies localization strings from one language to another
//
//		Parameters:
//			$src_lang_id - source language id
//			$dest_lang_id - destination language id
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$messageStack - array variable to put error messages
//			$langData - array containing language data
//			$replaceLocFiles - replace licalization files
//
//		Returns null
//
{
	$sys_languages = wbs_listSysLanguages();
	if ( PEAR::isError($sys_languages) )
	return $sys_languages;

	global $language;
	$appList = listPublishedApplications( $language, true );
	if ( !is_array( $appList ) )
	return PEAR::raiseError( $db_locStrings[1] );

	$appList = array_merge( array(AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[25]) ), MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[64]) ) ), $appList );

	foreach( $appList as $key=>$value )
	{
		$srcPath = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), $src_lang_id );
		$destPath = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), $dest_lang_id );

		$app_name = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

		if ( file_exists($destPath) && !$replaceLocFiles )
		$messageStack[] = "<font color=blue><b>WARNING:</b></font> " . sprintf( $db_locStrings[30], $langData[WBS_LANGUAGE_NAME], $app_name );

		if ( !file_exists($srcPath) )
		$messageStack[] = "<font color=red><b>ERROR:</b></font> " . sprintf( $db_locStrings[31], $sys_languages[$src_lang_id][WBS_LANGUAGE_NAME], $app_name );

		if ( file_exists($srcPath) && (!file_exists($destPath) || $replaceLocFiles ) ) {
			@copy( $srcPath, $destPath );
			$messageStack[] = sprintf(  $db_locStrings[55], $app_name );
		}
	}

	return null;
}

function wbs_deleteLanguage( $langData, $kernelStrings, $db_locStrings )
//
// Deletes language from language list
//
//		Parameters:
//			$langData - array containing language data
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//
//		Returns null or PEAR::Error
//
{
	$sys_languages = wbs_listSysLanguages();
	if ( PEAR::isError($sys_languages) )
	return $sys_languages;

	$lang_id = $langData[WBS_LANGUAGE_ID];
	$lang_id = strtolower( $lang_id );

	if ( $lang_id == DEF_LANG_ID )
	return null;

	if ( array_key_exists( $lang_id, $sys_languages ) )
	unset( $sys_languages[$lang_id] );

	$res = wbs_saveLanguageList( $sys_languages, $kernelStrings, $db_locStrings );
	if ( PEAR::isError( $res ) )
	return $res;

	global $language;
	$appList = listPublishedApplications( $language, true );
	if ( !is_array( $appList ) )
	return PEAR::raiseError( $db_locStrings[1] );

	$appList = array_merge( array(AA_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[25]) ), MW_APP_ID => array(APP_REG_APPLICATION => array(APP_REG_LOCAL_NAME=>$db_locStrings[64]) ) ), $appList );

	foreach( $appList as $key=>$value )
	{
		$path = sprintf( "%s/%s/localization/%s.%s", WBS_PUBLISHED_DIR, strtoupper($key), strtolower($key), $lang_id );
		if ( @file_exists($path) )
		@unlink( $path );
	}

	//TODO remove special folders at applications
	foreach(array('PD','UG') as $APP_ID){
		$path = sprintf('%s/%s/js/%s',WBS_PUBLISHED_DIR,$APP_ID,substr($lang_id,0,2));
		wbs_deleteCacheDirectorys($path);
	}

	return null;
}

function wbs_saveLanguageList( $languageList, $kernelStrings, $db_locStrings )
//
// Saves language list to file
//
//		Parameters:
//			$languageList - list of languages
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//
//		Returns null or PEAR::Error
//
{
	global $langListFileName;

	$filePath = WBS_DIR."kernel/$langListFileName";

	$fh = @fopen( $filePath, "wt" );
	if ( !@$fh )
	return PEAR::raiseError( $db_locStrings[24] );

	foreach ( $languageList as $lang_data ) {
		$str = sprintf( "%s\t%s\t%s\n",  $lang_data[WBS_LANGUAGE_ID], $lang_data[WBS_LANGUAGE_NAME], $lang_data[WBS_ENCODING] );
		@fwrite( $fh, $str );
	}

	@fclose( $fh );

	return null;
}

function wbs_loadApplicationStrings( $APP_ID, $lang_id, $type )
//
// Loads application localization strings
//
//		Parameters:
//			$APP_ID - application identifier
//			$lang_id - language identifier
//			$type - strings type to load
//
//		Returns array or PEAR_Error
//
{
	$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
	$appStrings = loadLocalizationStrings( $localizationPath, strtolower($APP_ID), $type, true );

	if ( isset($appStrings[$lang_id]) )
	return $appStrings[$lang_id];
	else
	return array();
}

function wbs_listApplicationStringGroups( $APP_ID, $lang_id )
//
// Returns application localization string groups
//
//		Parameters:
//			$APP_ID - application identifier
//			$lang_id - language identifier
//
//		Returns array or PEAR_Error
//
{
	$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
	$appStrings = loadLocalizationStrings( $localizationPath, strtolower($APP_ID), null, true );

	$result = array();

	if ( !is_array($appStrings)||!array_key_exists($lang_id, $appStrings) )
	return $result;

	foreach( $appStrings[$lang_id] as $strData ) {
		$result[$strData[1]] = 1;
	}

	return array_keys($result);
}

function wbs_saveFullLocalizationStrings( $lang_id, $app_id, $userIds, $userGroups, $userStrings, $userDescr, $kernelStrings, $db_locStrings, $decode = false )
//
// Saves user localization strings
//
//		Parameters:
//			$lang_id - language identifier
//			$APP_ID - application identifier
//			$userStrings - strings array to save
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//
//		Returns null or PEAR_Error
//
{
	//HACK for DD/2.0
	$use2col = false;
	if(strtolower($app_id)=='wbsadmin'){
		$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $app_id);
		$app_id = substr($app_id,0,3);
	}elseif(strlen($app_id)>2){
		$localizationPath = sprintf( "%s/%s/2.0/localization", WBS_PUBLISHED_DIR, substr($app_id,0,2));
		$use2col = true;
	}else{
		$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $app_id );
	}
	$localizationPath .= sprintf( "/%s.%s", strtolower(str_replace(' ','',$app_id)), strtolower($lang_id) );



	$fh = @fopen( $localizationPath, "wt" );
	if ( !$fh )
	return PEAR::raiseError( $db_locStrings[24] );

	foreach( $userStrings as $key=>$value )
	{
		//HACK for DD/2.0
		if($use2col){
			$str = sprintf( "%s\t%s\n", $userIds[$key], trim(stripslashes($userStrings[$key]) ));
		}else{
			$descr = isset($userDescr[$key])?trim( stripslashes( $userDescr[$key] ) ):null;
			if ( $decode && $descr != null ){
				$descr = base64_decode( $descr );
			}
			$str = sprintf( "%s\t%s\t%s\t%s\n", $userIds[$key], $userGroups[$key], $descr, trim( stripslashes( $userStrings[$key] ) ) );
		}

		fwrite( $fh, $str );
	}

	@fclose($fh);

	return null;
}

function wbs_saveLocalizationStrings( $lang_id, $APP_ID, $userStrings, $kernelStrings, $db_locStrings )
//
// Saves user localization strings
//
//		Parameters:
//			$lang_id - language identifier
//			$APP_ID - application identifier
//			$userStrings - strings array to save
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//
//		Returns null or PEAR_Error
//
{
	$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
	$appStrings = loadLocalizationStrings( $localizationPath, strtolower($APP_ID), null, true );

	if ( !is_array($appStrings) )
	return PEAR::raiseError( $db_locStrings[] );

	$appStrings = $appStrings[$lang_id];

	foreach( $userStrings as $key=>$value )
	$appStrings[$key][3] = $value;

	$localizationPath .= sprintf( "/%s.%s", strtolower($APP_ID), strtolower($lang_id) );

	$fh = @fopen( $localizationPath, "wt" );
	if ( !$fh )
	return PEAR::raiseError( $db_locStrings[24] );

	foreach( $appStrings as $key=>$value ) {
		$str = sprintf( "%s\t%s\t%s\t%s\n", $value[0], $value[1], trim( stripslashes( $value[2] ) ), trim( stripslashes( $value[3] ) ) );

		fwrite( $fh, $str );
	}

	@fclose($fh);

	return null;
}

function wbs_createLocalizationFile( $lang_id, $APP_ID, $userStrings, $kernelStrings, $db_locStrings, $langData, $appName, &$messageStack, $replaceLocFiles )
//
// Creates application localization strings
//
//		Parameters:
//			$lang_id - language identifier
//			$APP_ID - application identifier
//			$userStrings - strings array to save
//			$kernelStrings - Kernel localization strings
//			$db_locStrings - WebAsyst admin localization strings
//			$messageStack - array variable to put error messages
//			$langData - array containing language data
//			$appName - application name
//			$messageStack - array variable to put error messages
//			$replaceLocFiles - replace licalization files
//
//		Returns null or PEAR_Error
//
{
	$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
	$localizationPath .= sprintf( "/%s.%s", strtolower($APP_ID), strtolower($lang_id) );

	if ( file_exists($localizationPath) && !$replaceLocFiles ) {
		$messageStack[] = "<font color=blue><b>WARNING:</b></font> " . sprintf( $db_locStrings[30], $langData[WBS_LANGUAGE_NAME], $appName );
		return null;
	}

	$fh = @fopen( $localizationPath, "wt" );
	if ( !$fh )
	return PEAR::raiseError( $db_locStrings[24] );

	foreach( $userStrings as $key=>$value )
	{
		$value[3] = str_replace( "\n", "", $value[3] );
		$str = sprintf( "%s\t%s\t%s\t%s\n", $value[0], $value[1], $value[2], $value[3] );
		fwrite( $fh, $str );
	}

	@fclose($fh);

	$messageStack[] = sprintf(  $db_locStrings[55], $appName );

	return null;
}


function wbs_loadCheckedLocalizationStrings( $lang_id, $appList )
//
// Returns language localization string compared with English localization files
//
//		Parameters:
//			$lang_id - language identifier
//
//		Returns array or PEAR_Error
//
{
	$result = array();

	foreach( $appList as $key=>$value )
	{
		$engErr = 0;
		$locErr = 0;

		$APP_ID = $key;

		$result[$APP_ID]['NAME'] = $value[APP_REG_APPLICATION][APP_REG_LOCAL_NAME];

		//HACK for DD/2.0
		if(strtolower($APP_ID) == 'wbsadmin'){
			$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID);
		}elseif(strlen($APP_ID)>2){
			$localizationPath = sprintf( "%s/%s/2.0/localization", WBS_PUBLISHED_DIR, substr($APP_ID,0,2));
		}else{
			$localizationPath = sprintf( "%s/%s/localization", WBS_PUBLISHED_DIR, $APP_ID );
		}
		$loc_str = loadLocalizationStrings( $localizationPath, (strtolower($APP_ID) == 'wbsadmin')?substr($APP_ID,0,3):strtolower(str_replace(' ','',$APP_ID)), NULL, true,false );

		if ( !array_key_exists($lang_id, $loc_str ) && !array_key_exists(LANG_ENG, $loc_str ) )
		{
			$result[$APP_ID]['ERR'] = "MissingAll";
			continue;
		}

		$errStr = "";
		$copyLang = $lang_id;

		if ( !array_key_exists($lang_id, $loc_str ) )
		$result[$APP_ID]['ERR'] = "MissingLoc";
		else
		if ( !array_key_exists(LANG_ENG, $loc_str ) )
		$result[$APP_ID]['ERR'] = "MissingEng";

		$grList=array();

		if ( isset($loc_str[$copyLang]) )
		foreach( $loc_str[$copyLang] as $strData )
		$grList[$strData[1]] = 1;

		foreach( $loc_str[LANG_ENG] as $strData )
		$grList[$strData[1]] = 1;

		$temp_res=array();

		foreach( $grList as $grKey=>$grValue )
		{
			foreach( $loc_str[LANG_ENG] as $strData )
			{
				if ( $strData[1] ==  $grKey )
				{
					if ( !isset($loc_str[$copyLang]) || !array_key_exists($strData[0], $loc_str[$copyLang] ) )
					{
						$temp_res[$grKey][$strData[0]] = $strData;
						$temp_res[$grKey][$strData[0]]['ERR'] = "MissingLoc";
						++$locErr;
					}
				}

				if ( $strData[2] != "" && isset( $loc_str[$copyLang][$strData[0]] ) )
				{
					$loc_str[$copyLang][$strData[0]][2] = $strData[2];
					$loc_str[$copyLang][$strData[0]]["encoded"] = base64_encode( $strData[2] );
				}
			}

			if ( isset($loc_str[$copyLang]) ) {
				foreach( $loc_str[$copyLang] as $strData )
				{
					if ( $strData[1] == $grKey )
					{
						$temp_res[$grKey][$strData[0]] = $strData;

						if ( !array_key_exists($strData[0], $loc_str[LANG_ENG] ) )
						{
							$temp_res[$grKey][$strData[0]]['ERR'] = "MissingEng";
							++$engErr;
						}
					}
				}
			}
		}

		$result[$APP_ID]['DATA'] =  $temp_res;
		$result[$APP_ID]['ENG_ERR'] = $engErr;
		$result[$APP_ID]['LOC_ERR'] = $locErr;

	}

	return $result;
}

function wbsadmin_addAdminDB( $dbData, $adminDBData, $locStrings, $db_strings, $isadm )
//
// Adds/Modifies SQL server to WBS server list
//
//		Parameters:
//			$action - form mode (new/edit)
//			$adminDBData - array containing SQL server description
//			$locStrings - kernel localization strings
//			$db_strings - wbs admin localization strings
//
//		Returns null or PEAR_Error
//
{
	global $_PEAR_default_error_mode;
	global $_PEAR_default_error_options;
	global $wbs_sqlServers;

	$action = ACTION_NEW;

	$adminDBData = trimArrayData( $adminDBData );

	$requiredFields = array( 'DATABASE', 'CREATE_OPTION' );

	if ( PEAR::isError( $invalidField = findEmptyField($adminDBData, $requiredFields) ) ) {
		$invalidField->message = $locStrings[ERR_REQUIREDFIELDS];
		return $invalidField;
	}

	if ( PEAR::isError( $result = installDBCheckCreate( $adminDBData['DATABASE'], $adminDBData, $locStrings, $dbData['SQLSERVER'], $adminDBData['CREATE_OPTION'] == 'new', $isadm ) ) )
	return $result;

	if ( !$result )
	return PEAR::raiseError( ( $adminDBData['CREATE_OPTION'] == 'use' ) ? "Database does not exist" : "Database already exists", ERRCODE_APPLICATION_ERR );

	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );

	$dom = domxml_open_file( realpath($filePath) );
	if ( !$dom )
	return PEAR::raiseError( $db_strings[12], ERRCODE_APPLICATION_ERR );

	$xpath = xpath_new_context($dom);

	if ( !( $rootnode = &xpath_eval($xpath, "/".WBS_WBS ) ) )
	return PEAR::raiseError( $locStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

	if ( !( $adminDBNode = &xpath_eval($xpath, "/".WBS_WBS."/ADMINDB" ) ) )
	return PEAR::raiseError( $locStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	else
	{
		if ( !count ( $adminDBNode->nodeset ) )
		{

			$currentAdminDBNode = @create_addElement( $dom, $rootnode->nodeset[0], 'ADMINDB' );
			if ( !$currentAdminDBNode )
			return PEAR::raiseError( $db_strings[16], ERRCODE_APPLICATION_ERR );

			$currentAdminDBNode = $currentAdminDBNode;

		}
		else
		$currentAdminDBNode = $adminDBNode->nodeset[0];
	}

	$currentAdminDBNode->set_attribute( 'PASSWORD', $adminDBData['PASSWORD'] );
	$currentAdminDBNode->set_attribute( 'DB_USER', $adminDBData['DATABASE_USER'] );
	$currentAdminDBNode->set_attribute( 'DB_NAME', $adminDBData['DATABASE'] );
	$currentAdminDBNode->set_attribute( 'SERVER', $dbData['SQLSERVER'] );

	if ( !$dom->dump_file( $filePath, false, true ) )
	return PEAR::raiseError( "Can't dump XML file. May be wrong permissions.", ERRCODE_APPLICATION_ERR );


	return null;
}
function wbs_saveFrontendSettings( $commonData, $kernelStrings, $db_strings, $is_install = false )
//
// Saves common WBS settings
//
//		Parametes:
//			$commonData - array containing form data
//			$kernelStrings - Kernel localization strings
//			$db_strings - WebAsyst Administrator localization strings
//
//		Returns null or PEAR_Error
//
{
	global $_PEAR_default_error_mode;
	global $_PEAR_default_error_options;
	$errorStr = '';

	$commonData = trimArrayData( $commonData );

	$requiredFields = array();

	if ( PEAR::isError( $invalidField = findEmptyField($commonData, $requiredFields) ) ) {
		$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

		return $invalidField;
	}


	$type = str_replace(array('/','\\'),'',$commonData['CURRENT_SERVICE_ID']);
	$mod_rewrite = $commonData['MOD_REWRITE']?true:false;

	$filePath = sprintf( "%s/kernel/wbs.xml", WBS_DIR );
	$frontendConfPath = sprintf( "%s/temp/.frontend", WBS_DIR );
	if(file_exists($frontendConfPath))
	unlink($frontendConfPath);

	//////////////////
	$dom = domxml_open_file( realpath($filePath) );
	if ( !$dom )
	return PEAR::raiseError( $db_strings[12], ERRCODE_APPLICATION_ERR );

	$xpath = xpath_new_context($dom);



	if ( !( $wbsnode = &xpath_eval($xpath, "/".WBS_WBS) ) )
	return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	$wbsnode = $wbsnode->nodeset[0];

	if ( !( $fe_node = &xpath_eval($xpath, "/".WBS_WBS."/".'FRONTEND') ) || !count($fe_node->nodeset) ) {
		$fe_node = @create_addElement( $dom, $wbsnode, 'FRONTEND' );
		if ( !$fe_node )
		return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );
	} else{
		$fe_node = $fe_node->nodeset[0];
	}

	$fe_node->set_attribute( 'type', $type );
	$fe_node->set_attribute( 'dbkey', $commonData['CURRENT_DBKEY'] );
	$fe_node->set_attribute( 'mod_rewrite', $mod_rewrite);
	$fe_node->set_attribute( 'disable_powered_by', isset($commonData['DISABLE_POWERED_BY'])&&$commonData['DISABLE_POWERED_BY']?'1':'0');

	if ( !$dom->dump_file( $filePath, false, true ) )
	return PEAR::raiseError( "Can't dump XML file. May be wrong permissions.", ERRCODE_APPLICATION_ERR );

	/*
	 //////////////
	 if(!file_exists($filePath))
		return PEAR::raiseError('File dosn\'t exists '.$filePath);
		$wbsXmlStr = file($filePath);
		if(!$wbsXmlStr){
		return PEAR::raiseError('Error read file '.$filePath);
		}
		$wbsXmlStr = implode('',$wbsXmlStr);
		$wbsXml = new SimpleXMLElement($wbsXmlStr);
		//	$wbsXml->addChild('FRONTEND');
		echo (string)$wbsXml->
		$wbsXml->FRONTEND['type']=$type;
		$wbsXml->FRONTEND['dbkey']=$commonData['CURRENT_DBKEY'];
		$wbsXml->FRONTEND['mod_rewrite']=$mod_rewrite;

		var_dump(htmlentities($wbsXml->asXML()));exit;

		if(!$wbsXml->asXML($filePath)){
		$errorStr = 'Error Save data to '.$filePath.'<br>';
		}
		////////////////////
		*/

	$htaccesUser = str_replace(array('\\','//'),'/',sprintf( "%s/published/wbsadmin/html/configs/.htaccess.user", WBS_DIR));


	//Copy user .htaccess file on install
	if($is_install){
		$htaccesUserSource = str_replace(array('\\','//'),'/',sprintf( "%s/.htaccess", WBS_DIR));
		if(file_exists($htaccesUserSource)&&!file_exists($htaccesUser)){
			copy($htaccesUserSource,$htaccesUser);
		}
	}

	$htaccesType=($mod_rewrite?'mod_rewrite.':'').$type;
	$htaccesSource=str_replace(array('\\','//'),'/',sprintf( "%s/published/wbsadmin/html/configs/.htaccess.%s", WBS_DIR,$htaccesType));
	$htaccesPath=str_replace(array('\\','///','//'),'/',sprintf( "%s/.htaccess", WBS_DIR));
	if(file_exists($htaccesSource)){
		$contentSystem = file($htaccesSource);
		if(!$contentSystem){
			$errorStr .= ('Error read .htaccess file at '.$htaccesPath.'<br>');

		}else{
			$contentSystem = "\n# WebAsyst .htaccess config\n".implode('',$contentSystem);
			if(file_exists($htaccesUser)&&($contentUser = file($htaccesUser))){
				if(!$contentUser){
					$errorStr .= 'Error add user instruction to '.$htaccesPath;
				}else{
					$contentSystem = "# User custom .htaccess config (/published/wbsadmin/html/configs/.htaccess.user)
\n".implode('',$contentUser)."\n\n".$contentSystem;
				}
			}

			if($fp = fopen($htaccesPath,'w')){
				fwrite($fp,$contentSystem);
				fclose($fp);
			}else{
				$errorStr .= ('Error write .htaccess file at '.$htaccesPath.'<br>');
			}


		}
	}


	$SCpath = WBS_DIR.'/published/SC/html/scripts';
	if(file_exists($SCpath)&&is_dir($SCpath)){
		$htaccesType=($mod_rewrite?'mod_rewrite.':'').'SC_';
		$htaccesSource=str_replace(array('\\','//'),'/',sprintf( "%s/published/wbsadmin/html/configs/.htaccess.%s", WBS_DIR,$htaccesType));
		$htaccesPath=str_replace(array('\\','//'),'/',sprintf( "%s/.htaccess", $SCpath));
		if(file_exists($htaccesSource)){
			if(!copy($htaccesSource,$htaccesPath)){
				$errorStr .= 'Error replace .htaccess file at '.$htaccesPath.'<br>';
			}
		}
	}
	wbs_resetCache(array($commonData['CURRENT_DBKEY']));






	return $errorStr?PEAR::raiseError($errorStr):null;
}

function wbs_getFrontendSettings()
{
	$res=array();
	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
	clearstatcache();
	$wbsXml=simplexml_load_file($filePath);
	$res['CURRENT_SERVICE_ID']=(string)$wbsXml->FRONTEND['type'];
	$res['CURRENT_DBKEY']=(string)$wbsXml->FRONTEND['dbkey'];
	$res['MOD_REWRITE']=((string)$wbsXml->FRONTEND['mod_rewrite'])?true:false;
	$res['DISABLE_POWERED_BY']=((int)$wbsXml->FRONTEND['disable_powered_by'])?true:false;
	return $res;
}

function wbs_getProxySettings()
{
	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
	if(file_exists($filePath)){
		$wbsXml=simplexml_load_file($filePath);
		$res = array(
		'PROXY_HOST'=>(string)@$wbsXml->PROXY['host'],
		'PROXY_PORT'=>(string)@$wbsXml->PROXY['port'],
		'PROXY_USER'=>(string)@$wbsXml->PROXY['user'],
		'PROXY_PASS'=>(string)@$wbsXml->PROXY['password'],
		);
	}else{
		$res = array();
	}
	return $res;
}

function wbs_getSmtpSettings()
{
	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
	if(file_exists($filePath)){
		$wbsXml=simplexml_load_file($filePath);
		$res = array(
		'SMTP_HOST'=>(string)@$wbsXml->SMTP_SERVER['host'],
		'SMTP_PORT'=>(string)@$wbsXml->SMTP_SERVER['port'],
		'SMTP_USER'=>(string)@$wbsXml->SMTP_SERVER['user'],
		'SMTP_PASSWORD'=>(string)@$wbsXml->SMTP_SERVER['password'],
		'SMTP_HELO'=>(string)@$wbsXml->SMTP_SERVER['helo'],
		);
	}else{
		$res = array();
	}
	return $res;
}

function wbs_resetFrontendSettingsCache()
{
	wbs_deleteCacheDirectorys(WBS_TEMP_DIR.'/scdb');
	/*$frontendConfPath=sprintf( "%s/kernel/.frontend", WBS_DIR );
	 if(file_exists($frontendConfPath))
	 unlink($frontendConfPath);*/
}
function wbs_getInstallInformation()
{
	$res=array();
	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
	$wbsXml=simplexml_load_file($filePath);
	$res['COMPANY']=stripslashes(strip_tags((string)$wbsXml->SYSTEM['COMPANY']));
	$res['LICENSE']=stripslashes(strip_tags((string)$wbsXml->SYSTEM['LICENSE']));

	return $res;

}

function wbs_saveInstallInformation($installInformation)
{
	if(!array($installInformation))
	$installInformation=array('COMPANY'=>'','LICENSE'=>'','WBS_INSTALL_PATH');

	$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );
	$wbsXml=@simplexml_load_file($filePath);
	if($wbsXml){
		$wbsXml->SYSTEM['COMPANY'] = $installInformation['COMPANY'];
		$wbsXml->SYSTEM['LICENSE'] = $installInformation['LICENSE'];
		$wbsXml->DIRECTORIES->WEB_DIRECTORY['PATH'] = $installInformation['WBS_INSTALL_PATH'];
		$wbsXml->asXML($filePath);
	}

	return $res;

}


function wbs_getsystemConfiguration()
{
	$res=array();
	if(isset($_SERVER['SERVER_SOFTWARE']))
	$res['ServerName']=$_SERVER['SERVER_SOFTWARE'].', ';
	//$_SERVER['SERVER_SOFTWARE']
	$res['PHPversion'].='PHP: '.phpversion();
	$res['info']=implode(' ',$res);
	$res['status']='System configuration is Ok (wbs_functions.php)';
	return $res;
}
function wbs_getSystemStatistics()
{
	$res=array();
	$res['companyInfo']=wbs_getInstallInformation();
	$updateManager=new updateManager();
	$res['systemInfo']=$updateManager->getSystemInfo();
	$res['systemInfo']['link']=PAGE_SECTION_UPDATE;
	$res['systemConfiguration']=array();
	$res['systemConfiguration']['SERVER']='Server: '.$_SERVER['SERVER_SOFTWARE'];
	if(strpos(strtolower($res['systemConfiguration']['SERVER']),'php')===false){
		$res['systemConfiguration']['PHP']='PHP '.phpversion();
	}
	//$res['systemConfiguration']['phpinfo']='(<a href="JavaScript:showDetailsWindow(\'updatewa.php?action=phpinfo\');">phpinfo</a>)';
	$res['systemConfiguration']['MySQL']='MySQL '.mysql_get_client_info();
	$res['systemConfiguration']['info']=implode(' ',$res['systemConfiguration']);
	$requieriment=new requirementsControl();
	$res['systemConfiguration']['status']=$requieriment->check_all($msg);
	//$res['systemConfiguration']['link']="JavaScript:showDetailsWindow('updatewa.php?action=systemconfiguration');";
	$res['systemConfiguration']['link']="updatewa.php?action=systemconfiguration";
	//$systemInfo['applicationCount']


	/*
	 $systemConfiguration['info'].' - <a href="'.$systemConfiguration['link'];
	 if($systemConfiguration['status']){
	 */


	return $res;

}
///////////////////////////////
// Migrate from SS function
///////////////////////////////
function wbs_createTempSStables($SSversion)
{
	require('wbs_sql_strings.php');
	$errStr='';

	foreach ($SS_tables as $table){
		$SQLselect='SELECT *, 1 AS TT FROM `'.$table.'` LIMIT 1';//c
		if(mysql_query($SQLselect)){//$table exists
			$SQLcreate='CREATE TABLE `__temp_'.$table.'` ( LIKE `'.$table.'`)';//TEMPORARY
			$SQLshowCreate='SHOW CREATE TABLE `'.$table.'`';//TEMPORARY ALT
			if(!mysql_query($SQLcreate)){
				$errBuf=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQLcreate}<hr>";
				if(!$createRes = mysql_query($SQLshowCreate)){
					$errStr.=$errBuf;
					$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQLshowCreate}<hr>";
					break;
				}else{
					if($row=mysql_fetch_row($createRes)){
						$SQLcreateAlt = $row[1];
						$replaceCount = 0;
						$SQLcreateAlt = str_replace($table,'__temp_'.$table,$SQLcreateAlt,$replaceCount);
						if($replaceCount>1||strlen($SQLcreateAlt)==0){
							$errStr.="<BR>error parse SQL string<br>{$SQLcreateAlt}<hr>";
							break;
						}
						if(!mysql_query($SQLcreateAlt)){
							$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQLcreateAlt}<hr>";
							break;
						}
					}else{
						$errStr.="<BR>empty result<br>{$SQLshowCreate}<hr>";
						break;
					}
				}
			}

			$SQLinsert='INSERT INTO `__temp_'.$table.'` SELECT * FROM `'.$table.'`';
			if(!mysql_query($SQLinsert)){
				return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQLinsert}");
			}

		}else{
			$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQLselect}<hr>";
		}
	}
	//ignore errors - missed tables will be created on update temp tables

	//upgrade if it required
	require('html/scripts/update_ss.php');
	try{
		_upgradeOldDataBase($SSversion);
	}catch (Exception $e){

		return PEAR::raiseError($e->getMessage().$dbname);
	}
	return true;//$errStr;
}
function wbs_deleteTempSStables()
{
	require('wbs_sql_strings.php');

	foreach ($SS_tables as $table){
		if(true){//$table exists
			$SQL='DROP TABLE IF EXISTS `__temp_'.$table.'`';
			if(!mysql_query($SQL)){
				return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}");
			}
		}
	}
}

function wbs_converDbResult( &$row, $SScharset,$SSdataCharset = 'cp1251' )
{
	if (  is_array($row) )
	foreach($row as &$row_item){
		if($SScharset != 'utf8'){
			$row_item = mb_convert_encoding($row_item,'utf-8',$SSdataCharset);
		}
		$row_item = html_entity_decode( $row_item, ENT_QUOTES, "utf-8" );
		$row_item = preg_replace('/(\r\n){2,}/','\r\n',$row_item);
	}
}

function wbs_connectShopScript($connectionSettings,$SScharset)
{
	$dbh = mysql_connect( $connectionSettings['DB_HOST'], $connectionSettings['DB_USER'], $connectionSettings['DB_PASS'] );
	if ( !$dbh )
	return PEAR::raiseError( $kernelStrings['app_invsqlconnect_message']." \"{$connectionSettings['DB_USER']}@{$connectionSettings['DB_HOST']}\" ".mysql_error());
	if(!mysql_select_db($connectionSettings['DB_NAME'],$dbh))
	return PEAR::raiseError( sprintf($kernelStrings['app_invdbconnect_message'],$connectionSettings['DB_NAME']));

	//if you have problems with database encoding you can set $__not_utf8 = 'latin1';  or so

	//$SScharset = "utf8";

	mysql_query ("set character_set_client='".$SScharset."'",$dbh);
	mysql_query ("set character_set_results='".$SScharset."'",$dbh);
	mysql_query ("set collation_connection='".$SScharset."_general_ci'",$dbh);
	return $dbh;
}

function wbs_getShopScriptData($kernelStrings,$language,$connectionSettings,$SSversion,$SScharset = 'utf8',$SSdataCharset = 'cp1251')
{
	require('wbs_sql_strings.php');
	$buferInsertQuries = new arrayBuffer('SSdataInsert');
	$buferPrepareQuries = new arrayBuffer('SSdataPrepare');
	$dbh = wbs_connectShopScript($connectionSettings,$SScharset);
	if(PEAR::isError($dbh)){
		return $dbh;
	}

	$prepare=wbs_deleteTempSStables($SSversion);
	if(PEAR::isError($prepare))return $prepare;

	$migrateWarnings=wbs_createTempSStables($SSversion);
	if(PEAR::isError($migrateWarnings))return $migrateWarnings;

	$SQLdata=array();
	foreach ($SQLstrings as $strings){
		$SQL=$strings['select'];
		$data='';
		$insertQuerys=array();
		$template=$strings['data'];
		if(strlen($SQL)){
			if(!mysql_ping($dbh)){
				mysql_close($dbh);
				sleep(10);
				$dbh = wbs_connectShopScript($connectionSettings,$SScharset);
				if(PEAR::isError($dbh)){
					return $dbh;
				}
				if(!mysql_ping($dbh)){
					return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error());
				}
			}
			$q=mysql_query($SQL,$dbh);
			if(!$q){
				$SQL=htmlentities($SQL);
				return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}<br>");
			}
			while($res=mysql_fetch_row($q)){
				wbs_converDbResult($res,$SScharset,$SSdataCharset);
				$i=0;
				$str='';
				foreach ($res as $value){
					switch($template[$i]){
						
						case 0: {
							if($value||($value===0)||($value=='0')){
								$value = str_replace(',','.',floatval(str_replace(',','.',$value)));
							}else{
								$value = 'NULL';
							}
							break;
						}
						case 2: {
							if($value){
								$value=mysql_real_escape_string($value);
								$value = str_replace(array('\r','\n'),array("\r","\n"),$value);
								$value = "'".$value."'";
							}else{
								$value = 'NULL';
							}
							break;
						}
						case 1:
						default: {
							$value=mysql_real_escape_string($value);
							$value = str_replace(array('\r','\n'),array("\r","\n"),$value);
							$value = "'".$value."'";
							break;
						}
					}
					$str.=(strlen($str)?',':'').$value;
					$i++;
				}
				if(strlen($str))
				$data.=(strlen($data)?',(':'(').$str.')';
				if(strlen($data)>16384){
					$sqlString = str_replace('%DEF_LANG%',$language,$strings['insert']).$data.';';
					$buferInsertQuries->addItem($sqlString);
					//$insertQuerys[]= $sqlString;
					unset($sqlString);
					$data='';
				}

			}
			if(strlen($data)){
				$sqlString = str_replace('%DEF_LANG%',$language,$strings['insert']).$data.';';
				$buferInsertQuries->addItem($sqlString);
				//$insertQuerys[]= $sqlString;
				unset($sqlString);
			}
			mysql_free_result($q);
		}
		if(is_array($strings['prepare'])){
			foreach($strings['prepare'] as $stringPrepare){
				$buferPrepareQuries->addItem($stringPrepare);
			}
		}elseif($strings['prepare']){
			$buferPrepareQuries->addItem($strings['prepare']);
		}

		//$SQLdata[]=array('insert'=>$insertQuerys,'prepare'=>$strings['prepare']);
	}

	//get list of settings to copy in new store modules settings
	$SQLselect=$SettingsCopy['select'];
	$SQLupdate=$SettingsCopy['update'];
	foreach ($SettingsCopy['params'] as $param){
		$select=sprintf($SQLselect,$param);
		mysql_ping();
		if($res=mysql_query($select)){
			$row=mysql_fetch_row($res);
			if(is_array($row)){
				wbs_converDbResult($row,$SScharset,$SSdataCharset);
				if(in_array($param,$SettingsCopy['ml_params'])){
					$row[0] = serialize(array($language => $row[0]));
				}
				$sqlString = sprintf($SQLupdate,mysql_real_escape_string($row[0]),$param);
				$buferInsertQuries->addItem($sqlString);
				//$SQLdata[]=array('insert'=>$sqlString,'prepare'=>'');
				unset($sqlString);
			}
		}else{
			$SQL=htmlentities($select);
			return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}");
		}
		mysql_free_result($res);
	}

	//get status list
	$statusList=array();
	$complianceID=array();
	foreach ($SQLstatusStrings['selectPre'] as $status=>$selectPre){
		mysql_ping();
		if($res=mysql_query($selectPre)){
			while ($row=mysql_fetch_row($res)){
				wbs_converDbResult($row,$SScharset,$SSdataCharset);
				//$statusList[]=sprintf($SQLinsert,$data[1],mysql_real_escape_string($data[2]));
				$complianceID[$row[0]]=$status;
			}
		}else{
			$SQL=nl2br(htmlentities($selectPre));
			return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}");
		}
	}


	$insert=str_replace('%DEF_LANG%',$language,$SQLstatusStrings['insert']);
	$statusList[0]=$SQLstatusStrings['prepare'];
	if($res=mysql_query($SQLstatusStrings['selectCustom'])){
		while ($row=mysql_fetch_row($res)){
			wbs_converDbResult($row,$SScharset,$SSdataCharset);
			$statusList[$row[0]]=sprintf($insert,$row[1],mysql_real_escape_string($row[2]));
			$complianceID[$row[0]]=-1;
		}
	}else{
		$SQL=nl2br(htmlentities($SQLstatusStrings['selectCustom']));
		return PEAR::raiseError( $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}");
	}


	$res=wbs_deleteTempSStables();
	if(PEAR::isError($res))return $res;

	//	var_dump($statusList);
	/*return PEAR::raiseError("Just a pause<br><pre>".htmlentities(var_export($SQLdata,true))."</pre>");*/

	mysql_close( $dbh );
	return array('SQLstrings'=>$SQLdata,'complianceID'=>$complianceID,'SQLstatusList'=>$statusList,'msg'=>$migrateWarnings);
}

function wbs_insertSCdata($kernelStrings,$SQLstrings,$complianceID,$SQLstatusList,$language,$DB_KEY)
{
	$connection=wbs_getConnectionSettings($DB_KEY);
	if(PEAR::isError($connection))
	return $connection;
	$insert=0;
	$errStr='';
	if ( strlen($db_port) )
	$db_host = sprintf( "%s:%s", $db_host, $db_port );

	$dbh = mysql_connect( $connection['DB_HOST'], $connection['DB_USER'], $connection['DB_PASS'] );
	if ( !$dbh )
	return PEAR::raiseError($kernelStrings['app_invsqlconnect_message']);
	if(!mysql_select_db($connection['DB_NAME'],$dbh))
	return PEAR::raiseError( $kernelStrings['app_dbkeynotfound_message']);

	mysql_query ("set character_set_client='utf8'");
	mysql_query ("set character_set_results='utf8'");
	mysql_query ("set collation_connection='utf8_general_ci'");


	$prepareStrings = new arrayBuffer('SSdataPrepare',false);
	$prepareSucces = true;
	while(is_array($prepareString = $prepareStrings->getItem())){
		if($SQLprepare = $prepareString['value']){
			if(!mysql_unbuffered_query($SQLprepare,$dbh)){
				$SQLprepare=htmlentities($SQLprepare,ENT_QUOTES,'utf-8');
				$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLprepare}</small><hr>";
				$prepareSucces = false;
			}
		}
	}
	$prepareStrings->deleteBuffer();
	unset($prepareStrings);

	if($prepareSucces){
		$insertStrings = new arrayBuffer('SSdataInsert',false);
		while(is_array($insertString = $insertStrings->getItem())){
			if($SQLinsert = $insertString['value']){
				if(!mysql_unbuffered_query($SQLinsert,$dbh)){
					$SQLinsert=htmlentities($SQLinsert,ENT_QUOTES,'utf-8');
					$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLinsert}</small><hr>";
				}
			}
		}
		$insertStrings->deleteBuffer();
		unset($insertStrings);
	}

	//clear target tables

	/*
	 foreach ($SQLstrings as $SQL){
		$SQLprepare=$SQL['prepare'];

		if(!is_array($SQLprepare)&&strlen($SQLprepare)){
		$SQLprepares = array($SQLprepare);
		}elseif (is_array($SQLprepare)){
		$SQLprepares = $SQLprepare;
		}else{
		$SQLprepares = array();
		}

		$SQLdata=$SQL['insert'];
		if(!is_array($SQLdata)&&strlen($SQLdata)){
		$SQLdatas = array($SQLdata);
		}elseif (is_array($SQLdata)){
		$SQLdatas = $SQLdata;
		}else{
		$SQLdatas = array();
		}

		if(count($SQLprepares)){
		$prepareSucces = true;
		foreach ($SQLprepares as $SQLprepare){
		if(!mysql_unbuffered_query($SQLprepare,$dbh)){
		$SQLprepare=htmlentities($SQLprepare);
		$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLprepare}</small><hr>";
		$prepareSucces = false;
		}
		}
		if($prepareSucces&&count($SQLdatas)){
		foreach ($SQLdatas as $SQLdata){
		if(!($res=mysql_query($SQLdata,$dbh))){
		$SQLdata=htmlentities($SQLdata);
		$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLdata}</small><hr>";
		}elseif(false){
		$SQLdata=htmlentities($SQLdata);
		$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLdata}</small><hr>";
		}
		}
		}

		}elseif(count($SQLdatas)){
		foreach ($SQLdatas as $SQLdata){
		if(!($res=mysql_query($SQLdata,$dbh))){
		$SQLdata=htmlentities($SQLdata);
		$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLdata}</small><hr>";
		}elseif(false){
		$SQLdata=htmlentities($SQLdata);
		$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLdata}</small><hr>";
		}
		}
		}
		}

		*/
	//generate slug for products and categories
	require('wbs_sql_strings.php');
	foreach ($SQLslugStrings as $SQL){
		$SQLprepare=str_replace('%DEF_LANG%',$language,$SQL['select']);
		$SQLupdate=$SQL['update'];
		if(!($res=mysql_query($SQLprepare,$dbh))){
			$SQLprepare=htmlentities($SQLprepare,ENT_QUOTES,'utf-8');
			$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLprepare}</small><hr>";
		}else{
			while ($row=mysql_fetch_row($res)) {
				$SQLdata=sprintf($SQLupdate,mysql_real_escape_string(wbs_make_slug($row[1])),(int)$row[0]);
				if(!mysql_query($SQLdata,$dbh)){
					$SQLdata=htmlentities($SQLdata,ENT_QUOTES,'utf-8');
					$errStr.=$locStrings['err']."<BR><b>".mysql_error()."</b><br><small>{$SQLdata}</small><hr>";
				}
			}
			mysql_free_result($res);
		}
	}
	//set default language

	if($res=mysql_query('SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`=\'CONF_DEFAULT_LANG\'')){
		$row=mysql_fetch_row($res);
		if($row[0]){

			if(!mysql_query('UPDATE `SC_settings` SET `settings_value`=(SELECT `id` FROM `SC_language` WHERE `iso2`=\''.$language.'\') WHERE `settings_constant_name`=\'CONF_DEFAULT_LANG\'',$dbh))
			$errStr.='<br>'.mysql_error().'<hr>';
		}else{
			if(!mysql_query('INSERT INTO `SC_settings` (`settings_value`,`settings_constant_name`) VALUES ((SELECT `id` FROM `SC_language` WHERE `iso2`=\''.$language.'\'),\'CONF_DEFAULT_LANG\')',$dbh))
			$errStr.='<br>'.mysql_error().'<hr>';//.
		}
		mysql_free_result($res);
	}else{
		$errStr.='<br>'.mysql_error()."<hr>";
	}

	//set default currency

	if($res=mysql_query('SELECT COUNT(*) FROM `SC_settings` WHERE `settings_constant_name`=\'CONF_DEFAULT_CURRENCY\'')){
		$row=mysql_fetch_row($res);
		if($row[0]){

			if(!mysql_query('UPDATE `SC_settings` SET `settings_value`=(SELECT `CID` FROM `SC_currency_types` WHERE `currency_value`=1 LIMIT 1) WHERE `settings_constant_name`=\'CONF_DEFAULT_CURRENCY\'',$dbh))
			$errStr.='<br>'.mysql_error().'<hr>';
		}else{
			if(!mysql_query('INSERT INTO `SC_settings` (`settings_value`,`settings_constant_name`,`settings_html_function`) VALUES ((SELECT `CID` FROM `SC_currency_types` WHERE `currency_value`=1 LIMIT 1),\'CONF_DEFAULT_CURRENCY\',\'settingCONF_DEFAULT_CURRENCY()\')',$dbh))
			$errStr.='<br>'.mysql_error().'<hr>';//
		}
		mysql_free_result($res);
	}else{
		$errStr.='<br>'.mysql_error()."<hr>";
	}

	///////////////////
	//
	///////////////////
	//insert custom statuses
	foreach ($SQLstatusList as $sourceID=>$SQL){
		if(mysql_query($SQL,$dbh)){
			if($sourceID)$complianceID[$sourceID]=mysql_insert_id($dbh);
		}else{
			$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
			$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
		}
	}

	//get max status ID
	if($res=mysql_query('SELECT MAX(`statusID`) FROM `SC_order_status`',$dbh)){
		if($row=mysql_fetch_row($res)){
			$max_id=$row[0]+1;
		}
	}
	$SQLtemplate='UPDATE `SC_orders` SET `statusID`=%s WHERE `statusID`=%s';

	//move to temp
	foreach ($complianceID as $sourceID=>$targetID){
		if($sourceID==$targetID)continue;
		$SQL=sprintf($SQLtemplate,($targetID+$max_id),$sourceID);
		if(!mysql_query($SQL,$dbh)){
			$SQL=nl2br(htmlentities($SQLstatusStrings['selectCustom'],ENT_QUOTES,'utf-8'));
			$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
		}
	}

	//move to final values
	foreach ($complianceID as $sourceID=>$targetID){
		if($sourceID==$targetID)continue;
		$SQL=sprintf($SQLtemplate,$targetID,($targetID+$max_id));
		if(!mysql_query($SQL,$dbh)){
			$SQL=nl2br(htmlentities($SQLstatusStrings['selectCustom'],ENT_QUOTES,'utf-8'));
			$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
		}
	}


	//add divisions for aux_pages
	$SQL = 'SELECT `xID` FROM `SC_divisions` WHERE `xUnicKey` LIKE \'TitlePage\' LIMIT 1';
	if(!($res = mysql_query($SQL,$dbh))){
		$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
		$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
	}else{
		$parenId = mysql_fetch_row($res);
		$parenId = $parenId[0];
	}

	$SQL = 'SELECT `ModuleID` FROM `SC_modules` WHERE `ModuleClassName` LIKE \'divisionsadministration\' LIMIT 1';
	if(!($res = mysql_query($SQL,$dbh))){
		$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
		$errStr.=$kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
	}else{
		$moduleId = mysql_fetch_row($res);
		$moduleId = $moduleId[0];
	}
	if($parenId&&$moduleId){
		$SQL = 'SELECT `aux_page_ID` FROM `SC_aux_pages`';
		if($res=mysql_query($SQL)){
			$auxpagesID = array();
			while ($row=mysql_fetch_row($res)){
				$auxpagesID[]=intval($row[0]);
			}

			foreach ($auxpagesID as $auxpageID){
				$dataDiv = "('pgn_ap_{$auxpageID}','auxpage_{$auxpageID}',{$parenId},0)";

				$SQL = "INSERT INTO `SC_divisions` (`xName`, `xUnicKey`, `xParentID`, `xEnabled`) VALUES {$dataDiv}";
				//$errStr.=$SQL;
				if(!mysql_query($SQL)){
					$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
					$errStr.= $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}<hr>";
					continue;

				}
				$divisionId = mysql_insert_id();
				$dataInt = "({$divisionId},'{$moduleId}_auxpage_{$auxpageID}')";
				$SQL = "INSERT INTO `SC_division_interface` (`xDivisionID`,`xInterface`) VALUES {$dataInt}";
				//$errStr.=$SQL;
				if(!mysql_query($SQL)){
					$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
					$errStr.= $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}<hr>";
				}
			}

		}else{
			$SQL=nl2br(htmlentities($SQL,ENT_QUOTES,'utf-8'));
			$errStr.= $kernelStrings['app_queryerr_message']."<BR>".mysql_error()."<br>{$SQL}";
		}

	}

	//$errStr.="<br><pre>".var_export($complianceID,true)."<pre>";

	mysql_close( $dbh );
	if(strlen($errStr))
	return PEAR::raiseError( $errStr);
	return $insert;
}
function wbs_copyFiles($SSpath,$DB_KEY)
{
	$SSpath=str_replace(array('///','//','\\'),'/',$SSpath.'/');
	if((strpos($SSpath,'/')!==0)&&(strpos($SSpath,':/')!==1))
	$SSpath=WBS_DIR.$SSpath;
	$SSpath=str_replace(array('//','\\'),array('/','/'),$SSpath);
	$SSpath=realpath($SSpath);

	$sourcePath=$SSpath.'/products_files/';
	$targetPath=sprintf(WBS_DIR.'/data/%s/attachments/SC/products_files/',$DB_KEY);
	if(!wbs_copyDirectory($sourcePath,$targetPath,$errStr)){
		return PEAR::raiseError($errStr);
	}

	$sourcePath=$SSpath.'/products_pictures/';
	$targetPath=sprintf(WBS_DIR.'/published/publicdata/%s/attachments/SC/products_pictures/',$DB_KEY);
	if(!wbs_copyDirectory($sourcePath,$targetPath,$errStr)){
		return PEAR::raiseError($errStr);
	}

	return true;
}
function wbs_getSSconnectionSettings($SSpath)
{
	global $_PEAR_default_error_mode;
	global $_PEAR_default_error_options;
	$SSpath = str_replace(array('///','//','\\'),'/',$SSpath.'/');
	if((strpos($SSpath,'/')!==0)&&(strpos($SSpath,':/')!==1))
	$SSpath=WBS_DIR.$SSpath;
	if(!realpath($SSpath)){
		return PEAR::raiseError( translate('migrate_path_not_exist'), ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
		$_PEAR_default_error_options, 'PATH' );
	}
	$SSpath=realpath($SSpath);
	$SSdbSettings=$SSpath.'/cfg/connect.inc.php';
	if(!file_exists($SSdbSettings))
	return PEAR::raiseError( sprintf(translate('migrate_path_not_valid'),$SSpath), ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,
	$_PEAR_default_error_options, 'PATH' );

	require($SSdbSettings);

	$params=array('DB_HOST','DB_USER','DB_PASS','DB_NAME');
	foreach ($params as $param)
	if(!defined($param)){
		return PEAR::raiseError(sprintf(translate('migrate_missed_param'),$param));
	}
	$connection['DB_HOST']=DB_HOST;
	$connection['DB_USER']=DB_USER;
	$connection['DB_PASS']=DB_PASS;
	$connection['DB_NAME']=DB_NAME;
	//return PEAR::raiseError("debug:<br><pre>".var_export($connection,true)."</pre>");
	return $connection;

}
function wbs_copyDirectory($sourcePath,$targetPath,&$errStr=null,$recursive = false)
{
	if(!wbs_createDirectory($targetPath,$errStr)){
		return false;
	}
	$sourcePath =  str_replace('//','/',fixPathSlashes($sourcePath));
	$targetPath =  str_replace('//','/',fixPathSlashes($targetPath));
	$dir=opendir($sourcePath);
	while (false!==($file=readdir($dir))){
		if(($file == '.')||($file == '..')){
			continue;
		}
		$destiny=$targetPath.'/'.$file;
		$source=$sourcePath.'/'.$file;
		if(file_exists($source)){
			if(is_dir($source)){
				if($recursive){
					if(!wbs_copyDirectory($source,$destiny,$errStr,$recursive)){
						return false;
					}
				}
			}else{
				if(!copy($source,$destiny)){
					$sourcePath=str_replace('//','/',str_replace('\\','/',$sourcePath));
					$targetPath=str_replace('//','/',str_replace('\\','/',$targetPath));
					$errStr.=sprintf(translate('migrate_copy_error'),$file,$sourcePath,$targetPath);
					return false;
				}
			}
		}
	}
	return strlen($errStr)==0;

}
function wbs_createDirectory($path,&$errStr=null)
{
	$currentDir=getcwd();
	$path=str_replace('\\','/',$path);
	$path=str_replace('//','/',$path);
	$path=substr($path,strlen(WBS_DIR));
	$dirs = explode('/', $path);
	$dir=WBS_DIR;
	$oldMask = @umask(0);
	foreach ($dirs as $part) {
		$dir.=$part.'/';
		$dir=str_replace('//','/',$dir);
		if (!is_dir($dir) && strlen($dir)>0)
		{
			if(!@mkdir($dir, 0777)){
				$errStr = sprintf( "Unable to create directory %s", $dir );
				break;
			}
			@umask($oldMask);
		}
	}
	chdir( $currentDir );
	return (strlen($errStr)>0)?false:true;

}
function wbs_getConnectionSettings($DB_KEY)
{
	global $wbs_sqlServers;
	$connection=array();
	$databaseInfo=loadHostDataFile($DB_KEY,null);
	if(PEAR::isError($databaseInfo))
	return $databaseInfo;
	$connection['DB_HOST']=$wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['HOST'].(isset($wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT'])&&$wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT']?':'.$wbs_sqlServers[$databaseInfo[HOST_DBSETTINGS][HOST_SQLSERVER]]['PORT']:'');
	$connection['DB_USER']=$databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];
	$connection['DB_PASS']=$databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];
	$connection['DB_NAME']=$databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];
	return $connection;
}

function wbs_getLanuageList($DB_KEY)
{
	$dbq_select_language = "SELECT `id`, `name`, `iso2` FROM SC_language WHERE `enabled`=1 ORDER BY `priority`";
	$languageList=array();
	$connectionSettings=wbs_getConnectionSettings($DB_KEY);
	if(PEAR::isError($connectionSettings))
	return $connectionSettings;
	$dbh = mysql_connect( $connectionSettings['DB_HOST'], $connectionSettings['DB_USER'], $connectionSettings['DB_PASS'] );
	if ( !$dbh )
	return PEAR::raiseError( $locStrings['err']."<BR>".mysql_error()."<br>{$connectionSettings['DB_USER']}@{$connectionSettings['DB_HOST']}");
	if(!mysql_select_db($connectionSettings['DB_NAME'],$dbh)){
		mysql_close( $dbh );
		return PEAR::raiseError( $locStrings['err']."<BR>".mysql_error()."{$connectionSettings['DB_NAME']}");
	}
	mysql_query ("set character_set_client='utf8'");
	mysql_query ("set character_set_results='utf8'");
	mysql_query ("set collation_connection='utf8_general_ci'");

	if(!($qr=mysql_query($dbq_select_language,$dbh))){
		$SQL=htmlentities($dbq_select_language);
		return PEAR::raiseError( $locStrings['err']."<BR>".mysql_error()."<br>{$SQL}");
	}

	while($row=mysql_fetch_assoc($qr)){
		$languageList[]=$row;
	}

	mysql_free_result( $qr );
	mysql_close( $dbh );
	return $languageList;
}
function wbs_getExportParams($kernelStrings,$commonData)
{
	global $_PEAR_default_error_mode;
	global $_PEAR_default_error_options;


	$DB_KEY=$commonData['DB_KEY'];
	$SSversion=null;

	switch($commonData['DB_TARGET']){
		case 'custom':{
			$sourceConnectionSettings=$commonData['CUSTOM'];
			$requiredFields = array( 'DB_NAME','DB_USER','DB_HOST','VERSION');
			if ( PEAR::isError( $invalidField = findEmptyField($sourceConnectionSettings, $requiredFields) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}

			if ( PEAR::isError( $res = checkIntegerFields($sourceConnectionSettings, array('DB_PORT'), $kernelStrings) ) )
			return $res;
			if($sourceConnectionSettings['DB_PORT'])
			$sourceConnectionSettings['DB_HOST']=sprintf('%s:%s',$sourceConnectionSettings['DB_HOST'],$sourceConnectionSettings['DB_PORT']);
			if(isset($sourceConnectionSettings['VERSION']))unset($sourceConnectionSettings['VERSION']);
			$SSversion=$commonData['CUSTOM']['VERSION'];
			break;
		}
		case 'default':{
			$requiredFields = array('VERSION');
			if ( PEAR::isError( $invalidField = findEmptyField($commonData['DEFAULT'], $requiredFields) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}

			$sourceConnectionSettings=wbs_getConnectionSettings($DB_KEY);
			if(PEAR::isError($sourceConnectionSettings)){

				return $sourceConnectionSettings;
			}
			$SSversion=$commonData['DEFAULT']['VERSION'];
			break;
		}
		case 'auto':{
			$requiredFields = array( 'PATH','VERSION');
			if ( PEAR::isError( $invalidField = findEmptyField($commonData['AUTO'], $requiredFields) ) ) {
				$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

				return $invalidField;
			}
			$sourceConnectionSettings=wbs_getSSconnectionSettings($commonData['AUTO']['PATH']);
			if(PEAR::isError($sourceConnectionSettings)){

				return $sourceConnectionSettings;
			}
			$SSversion=$commonData['AUTO']['VERSION'];

			break;
		}

	}
	if(!$commonData['AGREE']){
		$fieldName='AGREE';
		return PEAR::raiseError ( "", ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode,$_PEAR_default_error_options, $fieldName );
	}
	return array('DB_KEY'=>$DB_KEY,'DB_SETTINGS'=>$sourceConnectionSettings,'VERSION'=>$SSversion);
}

//generate slug for products and categories
function __translit( $str, $UTF8 = true){
	if($UTF8){
		$str = iconv("UTF-8", "WINDOWS-1251",$str);
	}

	$result = "";
	//Use ASCII Page codes
	$compliances = array(184=>'yo',168=>'Yo',
	192=>"A","B","V","G","D","E","Zh","Z","I","J","K",
	"L","M","N","O","P","R","S","T","U","F","H","C","Ch",
	"Sh","Sh","","Y","'","E","Ju","Ja",
	"a","b","v","g","d","e","zh","z","i","j","k",
	"l","m","n","o","p","r","s","t","u","f","h","c","ch",
	"sh","sh",'',"y","'","e","ju","ja");

	$strlen = strlen($str);
	for ($i = 0; $i < $strlen; $i++) {
		$char = substr($str,$i,1);
		$symbol = ord($char);
		$result .= isset($compliances[$symbol])?$compliances[$symbol]:$char;

	}
	if($UTF8){
		$result = iconv("WINDOWS-1251","UTF-8", $result);
	}


	return $result;
}

function wbs_make_slug($str){
	$str = strtolower(__translit($str,true));
	$str = preg_replace('/ /', '-', $str);
	$str = preg_replace('/[^a-z0-9\-_]/ui', '', $str);
	$str = preg_replace('/\-+/', '-', $str);
	$str = preg_replace('/_+/', '_', $str);
	return $str == '-'?'':$str;
}

///////////////////////////////////
// End of migrate functions
///////////////////////////////////

/**
 * Checking mod_rewrite
 * return -2 if not Apache Server
 * return -1 if PHP not Apache module
 * return 0 if mod_rewrite disabled
 * return 1 if mod_rewrite enabled
 *
 * @return integer
 */
function wbs_check_mod_rewrite()
{
	/*$server=strtolower($_SERVER['SERVER_SOFTWARE'].(isset($_SERVER['SERVER_SIGNATURE'])?$_SERVER['SERVER_SIGNATURE']:''));
		if(strpos($server,'apache')===false){
		return -2;
		}
		if(strpos($server,'php')===false){
		return -1;
		}*/
	if(!function_exists('apache_get_modules')){//hunn probably ^_^
		return -1;//CGI or non apache?
	}
	return in_array('mod_rewrite',apache_get_modules())?1:0;
}
function wbs_resetCache($db_keys = array())
{
	$cacheDirectorys = array('/temp/','/kernel/includes/smarty/compiled/');
	foreach ($db_keys as $db_key){
		$cacheDirectorys[]=sprintf('/data/%s/attachments/SC/temp/loc_cache/',$db_key);
		//$cacheDirectorys[]=sprintf('/data/%s/attachments/SC/temp/',$db_key);
	}
	$excludeFiles = array('.htaccess','.wbs_protect','update_manager.log',
	'.update_state','.displaylog');
	foreach ($cacheDirectorys as $directory){
		wbs_deleteCacheDirectorys(WBS_DIR.$directory,$excludeFiles);
	}
	$languageCaches = array('/published/wbsadmin/localization/',
	'/published/AA/localization/');
	$dir=@opendir(WBS_DIR.'/published/');
	if($dir){
		while (false!==($app=readdir($dir))){

			$languageCache = WBS_DIR.'/published/'.$app.'/localization/.cache.'.strtolower(substr($app,0,3)).'.php';
			if(file_exists($languageCache)){
				@unlink($languageCache);
			}
			$languageCache = WBS_DIR.'/published/'.$app.'/2.0/localization/.cache.'.strtolower(substr($app,0,3)).'.php';
			if(file_exists($languageCache)){
				@unlink($languageCache);
			}
		}
		closedir($dir);
	}
	require_once('classes/class.diagnostictools.php');
	$tools = new DiagnosticTools(WBS_DIR);
	$res = true;
	$tools->cleanCache('temp',$errorStr,'/^\.cache\.|^\.settings\./');
	$tools->cleanCache('kernel/includes/smarty/compiled',$errorStr,'/\.php$/');
	$applications = array('wbsadmin/localization');
	$applicationFolders = scandir(WBS_DIR.'/published');
	foreach($applicationFolders as $applicationFolder){
		if(preg_match('/^\w{2}$/',$applicationFolder)){
			$applications[] = 'published/'.$applicationFolder.'/localization/';
			$applications[] = 'published/'.$applicationFolder.'/2.0/localization/';
		}
	}
	$applications[] = 'published/wbsadmin/localization/';
	$SCFolders = scandir(WBS_DIR.'/data');
	foreach($SCFolders as $SCFolder){
		if(($SCFolder == '.')
		||($SCFolder == '..')
		||!is_dir(WBS_DIR.'/data/'.$SCFolder)
		){
			continue;
		}
		if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/')){
			$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/';
		}
		if(realpath(WBS_DIR.'/data/'.$SCFolder.'/attachments/SC/temp/loc_cache/')){
			$applications[] = 'data/'.$SCFolder.'/attachments/SC/temp/loc_cache/';
		}
	}
	$tools->cleanCache($applications,$errorStr,'/(^\.cache\.)|(^serlang.+\.cch$)/',null,false);

}
function wbs_deleteCacheDirectorys($path,$exclude = array())
{
	if(file_exists($path)&&is_dir($path)){
		$dir=opendir($path);
		if(!$dir)return;
		while (false!==($file=readdir($dir))){
			if(in_array($file,$exclude)){
				continue;
			}
			$source=$path.'/'.$file;
			if(!is_dir($source)&&file_exists($source)){
				@unlink($source);
			}elseif (is_dir($source)&&($file!='.')&&($file!='..')){
				wbs_deleteCacheDirectorys($source,$exclude);
			}
		}
		closedir($dir);
	}
}

function wbs_multiDbkeyEnabled()
{
	//return true;
	static $result = null;
	if(is_null($result)){
		$result = (file_exists(WBS_DIR.'/published/wbsadmin/html/scripts/dblist.php')&&file_exists(WBS_DIR.'/published/wbsadmin/html/scripts/multidbkey.php'));
	}
	return $result;
}
function wbs_getDefaultSimpleDbkey()
{
	$dbList = wbsadmin_listRegisteredSystems( $kernelStrings );
	$DB_KEY = null;
	if ( is_array( $dbList )&&count($dbList)) {
		$DB_KEYlist = array_keys($dbList);
		$DB_KEY = $DB_KEYlist[0];
	}else{
		$DB_KEY = false;
	}
	return $DB_KEY;
}

function fopenTimeout($file,$mode = 'rb',$timeout = 3){
	$old_timeout = ini_set('default_socket_timeout', $timeout);
	$fp = fopen($file, 'r');
	ini_set('default_socket_timeout', $old_timeout);
	$fp = &$fp;
	return $fp;
}

function simplexml_load_file_timeout($file,$timeout = 3)
{
	$old_timeout = ini_set('default_socket_timeout', $timeout);
	$res = simplexml_load_file($file);
	$res = &$res;
	ini_set('default_socket_timeout', $old_timeout);
	return $res;
}
?>
