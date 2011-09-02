<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}
$btnIndex = getButtonIndex( array("returnbtn", "addlanguage"), $_POST );

switch( $btnIndex ) {
	case 0 :redirectBrowser( PAGE_DB_WBSADMIN, array( 'lang_id'=>$lang_id ) );
	case 1 :redirectBrowser( PAGE_DB_ADDMODLANGUAGE, array( ACTION=>ACTION_NEW, 'lang_id'=>$lang_id ) );
}

switch ( true ) {
	case (true) :
		$sys_languages = wbs_listSysLanguages();
		if ( PEAR::isError($sys_languages) ) {
			$errorStr = $sys_languages->getMessage();
			$fatalError = true;
			break;
		}

		foreach( $sys_languages as $key=>$lang_data ) {
			$params = array( 'lang'=>$lang_data[WBS_LANGUAGE_ID],
							 ACTION=>ACTION_EDIT,
							 'lang_id'=>$key );

			$lang_data['ROW_URL'] = prepareURLStr( PAGE_DB_ADDMODLANGUAGE, $params );

			$lang_data['LOC_URL'] = prepareURLStr( PAGE_DB_LOCALIZATION, array( 'lang_id'=>$lang_data[WBS_LANGUAGE_ID], 'app_id'=>AA_APP_ID ) );
			$lang_data['IMPORT_URL'] = prepareURLStr( PAGE_DB_IMPORTEXPORTLANGUAGE, array( 'lang'=>$lang_data[WBS_LANGUAGE_ID] ) );
			$lang_data['EXPORT_URL'] = prepareURLStr( PAGE_DB_EXPORTLANGUAGE, array( 'lang_id'=>$lang_data[WBS_LANGUAGE_ID] ) );

			$sys_languages[$key] = $lang_data;
		}
}
$preproc->assign( FORM_LINK, PAGE_DB_LANGUAGES );
$preproc->assign( PAGE_TITLE, 'lll_page_title');
if ( !$fatalError ) {
	$preproc->assign( "sys_languages", $sys_languages );
}
$preproc->assign( "mainTemplate","languages.htm" );
?>