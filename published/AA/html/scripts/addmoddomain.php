<?php

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once( WBS_DIR.'/published/AA/aa.php' );

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
	$invalidField = null;

	$de_add_domain_comment = sprintf( $kernelStrings['de_add_domain_comment'], 'href=#');
	$de_edit_domain_comment = $kernelStrings['de_edit_domain_comment'];

	switch( true )
	{
			case true :
			{
				if ( $fatalError )
					break;

				if ( (!isset($edited) || !$edited) && $action == ACTION_EDIT )
				{
					$domainData['DOMAIN_NAME'] = $DOMAIN;
 					$domainData['DOMAIN_OLDNAME'] = $DOMAIN;
				}
			}
	}

	$btnIndex = getButtonIndex( array(BTN_CANCEL, BTN_SAVE, "deletebtn"), $_POST );

	switch ( $btnIndex )
	{
		case 0 :
			redirectBrowser( PAGE_AA_DOMAINS, array() );

		case 1 :
			$res = addmodDomain( $action, prepareArrayToStore($domainData), $kernelStrings );

			if ( PEAR::isError( $res ) )
			{
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD || $res->getCode() == ERRCODE_INVALIDLENGTH )
					$invalidField = $res->getUserInfo();

				break;
			}
			redirectBrowser( PAGE_AA_DOMAINS, array() );

		case 2 :
			$res = addmodDomain( ACTION_DELETE, prepareArrayToStore($domainData), $kernelStrings );

			if ( PEAR::isError( $res ) )
			{
				$errorStr = $res->getMessage();

				if ( $res->getCode() == ERRCODE_INVALIDFIELD || $res->getCode() == ERRCODE_INVALIDLENGTH )
					$invalidField = $res->getUserInfo();

				break;
			}
			redirectBrowser( PAGE_AA_DOMAINS, array() );

	}

	//
	// Page implementation
	//

	$preproc = new php_preprocessor( $templateName, $kernelStrings, $language, $AA_APP_ID );

	$preproc->assign( PAGE_TITLE, ($action == ACTION_NEW) ? $kernelStrings['de_pageadd_title'] : $kernelStrings['de_pagemodify_title'] );
	$preproc->assign( ACTION, $action );
	$preproc->assign( FORM_LINK, PAGE_AA_ADDMODDOMAIN );
	$preproc->assign( INVALID_FIELD, $invalidField );
	$preproc->assign( ERROR_STR, $errorStr );
	$preproc->assign( FATAL_ERROR, $fatalError );

	if ( $action == ACTION_EDIT )
		$preproc->assign( de_domain_comment, $de_edit_domain_comment);
	else
		$preproc->assign( de_domain_comment, $de_add_domain_comment);

	if ( !$fatalError )
	{
		if ( isset($domainData) )
			$preproc->assign( 'domainData', prepareArrayToDisplay($domainData, null, isset($edited) && $edited) );
	}

	$preproc->display( 'addmoddomain.htm' );
?>