<?php
if(!defined('WBA_SETUP_PAGE')){
	$init_required = false;
	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR."/published/wbsadmin/wbsadmin.php" );
	redirectBrowser( PAGE_SECTION_SETUP, array() );
}
$infoStr = null;
$showMessages = false;

$action = ACTION_EDIT;

switch (true) {
	case (true) : ;
}

$btnIndex = getButtonIndex( array( "savebtn", "cancelbtn", "exportbtn" ), $_POST );

switch( $btnIndex ) {
	case 0 :
		$langData = prepareArrayToStore( $langData );

		if ( !isset($replacelocfiles) )
		$replacelocfiles = false;

		if ( $createopt == 1 )
		{
			$copy_lang_id = null;
			$copy_empty_lang = null;

			if ( !isset($importfile['name']) || !strlen($importfile['name']) ) {
				$invalidField = 'FILE';
				$errorStr = $LocalizationStrings[27];
				break;
			}

			$tmpFileName = uniqid( TMP_FILES_PREFIX );
			$importFile = WBS_TEMP_DIR."/".$tmpFileName;

			if ( !@move_uploaded_file( $importfile['tmp_name'], $importFile ) ) {
				$errorStr = $LocalizationStrings[28];
				break;
			}
		} else
		if ( $createopt == 2 )
		{
			$copy_lang_id = null;
			$importFile = null;
		}
		else
		$importFile = null;

		$messageStack = array();


		$res = wbs_addmodlanguage( $lang, $langData, $action, $kernelStrings, $LocalizationStrings, $createopt,
		$copy_lang_id, $importFile, $messageStack, $replacelocfiles );

		if ( PEAR::isError( $res ) ) {
			$errorStr = $res->getMessage();
			if ( $res->getCode() == ERRCODE_INVALIDFIELD )
			$invalidField = $res->getUserInfo();

			break;
		}

		$showMessages = true;

		break;


	case 1 :
		redirectBrowser( PAGE_DB_LANGUAGES, array( 'lang_id'=>$lang_id, 'app_id'=>$app_id, 'type_id'=>$type_id ) );

	case 2 :

		redirectBrowser( PAGE_DB_EXPORTLANGUAGE, array( 'lang_id'=>$lang) );

}

switch ( true ) {
	case true:
		$sys_languages = wbs_listSysLanguages();
		if ( PEAR::isError($sys_languages) ) {
			$errorStr = $sys_languages->getMessage();
			$fatalError = true;

			break;
		}

		foreach ( $sys_languages as $key=>$value ) {
			$lang_ids[] = $key;
			$lang_names[] = $value[WBS_LANGUAGE_NAME];
		}

		if ( !isset($edited) ) {
			if ( !array_key_exists($lang, $sys_languages) ) {
				$errorStr = $LocalizationStrings[21];
				$fatalError = true;

				break;
			}

			$langData = $sys_languages[$lang];
		} else {
			if ( !isset($replacelocfiles) )
			$replacelocfiles = false;
		}


}

$pageTitle = ($action == ACTION_NEW) ? $LocalizationStrings[19] : $LocalizationStrings[20];
$preproc->assign( PAGE_TITLE, sprintf('%s &mdash; %s',$LocalizationStrings['lll_page_title'],  $LocalizationStrings[51]));

$preproc->assign( FORM_LINK, PAGE_DB_IMPORTEXPORTLANGUAGE );
$preproc->assign( INVALID_FIELD, $invalidField );
$preproc->assign( ACTION, $action );

if ( !$fatalError ) {
	$preproc->assign( "showMessages", $showMessages );
	if ( $showMessages )
	$preproc->assign( "messageStack", implode( "<br>", $messageStack ) );

	$preproc->assign( "langData", $langData );
	$preproc->assign( "lang", $lang );

	$preproc->assign( "lang_ids", $lang_ids );
	$preproc->assign( "lang_names", $lang_names );


	if ( $action == ACTION_NEW ) {
		$preproc->assign( "createopt", $createopt );
		$preproc->assign( "copy_lang_id", $copy_lang_id );
		$preproc->assign( "replacelocfiles", $replacelocfiles );
	}
}
$preproc->assign( "mainTemplate","importexportlang.htm" );
?>