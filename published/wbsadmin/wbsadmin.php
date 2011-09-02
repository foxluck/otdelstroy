<?php
	@ini_set( 'memory_limit', '64M' );
	header("Content-type:text/html; charset=UTF8");

	require_once("wbs_consts.php");
	require_once("wbs_auth.php");
	require_once("wbs_functions.php");
	require_once("wbs_dbfunctions.php");
	require_once("html/scripts/upgrade.php");

	//
	// Load localizatoin strings
	//

	$localizationPath = sprintf("../../../%s/localization", AA_APP_ID);
	$loc_str = loadLocalizationStrings( $localizationPath, strtolower(AA_APP_ID) );
	
	$localizationPath = "../../localization";
	$db_loc_str = loadLocalizationStrings( $localizationPath, "wbs" );
	

	$language = LANG_ENG;
	$configPath = WBS_DIR."/kernel/wbs.xml";
	if(file_exists($configPath)){
		$content = file( $configPath );
		$content = implode( '', $content );

		$dom = @domxml_open_mem( $content );
		if ($dom ){
			$xpath = @xpath_new_context($dom);
			$nodePath = '/WBS/LANGUAGES/LANGUAGE';
			if (( $langsnode = xpath_eval($xpath, $nodePath) ) ){
				if ( count($langsnode->nodeset) ){
					$langnode = $langsnode->nodeset[0];
					$language = $langnode->get_attribute( 'ID' );
				}
			}
		}
	}
	
	$kernelStrings = $loc_str[$language];
	$LocalizationStrings = $db_loc_str[$language];
	
	$templateName = "classic";
	//init menus for all pages
	$mainMenu = array();
	$mainMenu[PAGE_SECTION_BUY] = array('title'=>'main_menu_buy','link'=>PAGE_SECTION_BUY,'description'=>'','info'=>'');	
	$mainMenu[PAGE_SECTION_DIAGNOSTIC] = array('title'=>'main_menu_diagnostic','link'=>PAGE_SECTION_DIAGNOSTIC,'description'=>'','info'=>'');
	$mainMenu[PAGE_SECTION_SETUP] = array('title'=>'main_menu_setup','link'=>PAGE_SECTION_SETUP,'description'=>'','info'=>'');
	$mainMenu[PAGE_SECTION_UPDATE] = array('title'=>'main_menu_update','link'=>PAGE_SECTION_UPDATE,'description'=>'','info'=>'');
	$mainMenu[PAGE_DB_WBSADMIN] = array('title'=>'main_page_name','link'=>PAGE_DB_WBSADMIN,'img'=>'../classic/images/i_home.gif');
	
	$installInfo = wbs_getInstallInformation();
?>