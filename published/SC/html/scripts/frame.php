<?php
	header('Content-Type: text/html; charset=UTF-8');

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/SC/sc.php" );
	//
	// Authorization
	//

	$fatalError = false;
	$popupStr = null;
	$errorStr = null;
	$SCR_ID = "FM";

	pageUserAuthorization( $SCR_ID, $SC_APP_ID, false );

	//
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];
	$scStrings = &$sc_loc_str[$language];
	$invalidField = null;

	global $language;
	$lang_data = sc_getLanguageByISO3($language);
	sc_setSessionData('LANGUAGE_ID', $lang_data['id']);
	sc_setSessionData('LANGUAGE_ISO3', strtolower($language));
	
	$top_menu = array();
	$admin_divisions = sc_getAdminDivs();
	$curentDid = isset($_GET['did'])?intval($_GET['did']):sc_getDefaultDivisionID();
	foreach ($admin_divisions as $_div){
		$is_first = true;
		
		$i = array(
			'title' => isset($scStrings[$_div['xName']])?$scStrings[$_div['xName']]:$_div['xName'],
			'id' => $_div['xID'],
			'active' => (($curentDid==$_div['xID'])?true:(($curentDid==-1&&$is_first&&($is_first=false))?true:false)),
			'url' => 'index.php?did='.$_div['xID'],
			'direct_url' => 'frame.php?did='.$_div['xID'],
			'sub_tabs' => array()
		
		);
		if(is_array($_div['sub_divs'])){
			$is_first_ = true;
			$curentDid_ = sc_getDefaultChildDivisionID($_div['xID']);
			
			foreach ($_div['sub_divs'] as $__div){
				if(!checkUserFunctionsRights( $currentUser, 'SC', 'SC__'.$__div['xID'], $kernelStrings ))continue;
				$active = 
				$i['sub_tabs'][] = array(
					'title' => isset($scStrings[$__div['xName']])?$scStrings[$__div['xName']]:$__div['xName'],
					'id' => $__div['xID'],
					'active' => (($curentDid==$__div['xID'])?true:(($curentDid==-1&&$is_first_&&($is_first_=false))?true:false)),
					'url' => 'index.php?did='.$__div['xID'],
					'direct_url' => 'frame.php?did='.$__div['xID'],
					);
			}
		}
		if(!count($i['sub_tabs']))continue;	
		$top_menu[] = $i;		
		/*
		$i = array(
			'title' => $scStrings[$_div['xName']],
			'xID' => $_div['xID'],
			'sub_divisions' => array()
		);
		if(is_array($_div['sub_divs'])){
			foreach ($_div['sub_divs'] as $__div){
			
				if(!checkUserFunctionsRights( $currentUser, 'SC', 'SC__'.$__div['xID'], $kernelStrings ))continue;
				$i['sub_divisions'][$scStrings[$__div['xName']]] = 'frame.php?did='.$__div['xID'];
			}
		}
		if(!count($i['sub_divisions']))continue;	
		$top_menu[] = $i;		

		 */
	}
//	var_dump($top_menu);
	
	//
	// Page implementation
	//
	$path = $_SERVER['SCRIPT_FILENAME']."/../../../../../";
	while (strpos($path,'\\')!==false) {
		$path=str_replace('\\','/',$path);
	}
	while (strpos($path,'//')!==false) {
		$path=str_replace('//','/',$path);
	}
	$res = array();
	$paths = explode('/',$path);
	foreach ($paths as $dir){
		if($dir == '..'){
			array_pop($res);
			continue;
		}
		if($dir == '.'){
			continue;
		}
		array_push($res,$dir);
	}
	$path = implode('/',$res);
	
	
	//for IIS
	if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['SCRIPT_FILENAME'])){
	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0-strlen($_SERVER['PHP_SELF'])));
	}; };
	if(!isset($_SERVER['DOCUMENT_ROOT'])){ if(isset($_SERVER['PATH_TRANSLATED'])){
	$_SERVER['DOCUMENT_ROOT'] = str_replace( '\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0-strlen($_SERVER['PHP_SELF'])));
	}; };
	
	$install_path = str_replace(array('\\','///','//'),'/','/'.substr($path.'/',strlen($_SERVER['DOCUMENT_ROOT'])));
	
	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $SC_APP_ID );
	
	$sub_tab_id = isset($_GET['did'])?$_GET['did']:sc_getDefaultDivisionID();
	$top_tab_disivion = sc_getParentDivision($sub_tab_id);
	if($top_tab_disivion['xUnicKey'] == 'admin'){
		$top_tab_id = $sub_tab_id;
		$sub_tab_id = sc_getDefaultChildDivisionID($top_tab_id);
	}else{
		$top_tab_id = $top_tab_disivion['xID'];
	}
	
	$preproc->assign( 'top_tab_id', $top_tab_id);
	$preproc->assign( 'sub_tab_id', $sub_tab_id);
	$preproc->assign( 'top_menu', $top_menu);
	$preproc->assign( PAGE_TITLE, $scStrings['sc_screen_long_name'] );
	$preproc->assign( FORM_LINK, 'frame.php' );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );
	$preproc->assign( "scStrings", $scStrings );
	
	
	$preproc->assign( 'SHOP_URL', (file_exists(WBS_DIR."/kernel/hosting_plans.php")?'/shop/':str_replace(array('///','//'),'/',(defined('WBS_INSTALL_PATH')&&strlen(WBS_INSTALL_PATH)?WBS_INSTALL_PATH:$install_path).'shop/') ));


	$preproc->display( "frame.htm" );
?>