<?php

	require_once( "../../../common/html/includes/modules/JsHttpRequest/config.php" );
	require_once( "../../../common/html/includes/modules/JsHttpRequest/Php.php" );

	require_once( "../../../common/html/includes/httpinit.php" );
	require_once "SOAP/Client.php";
	require_once "SOAP/Value.php";

	//
	// Authorization
	//

	$errorStr = null;
	$fatalError = false;

	pageUserAuthorization( null, $AA_APP_ID, true );

	$kernelStrings = $loc_str[$language];

	$JsHttpRequest =& new Subsys_JsHttpRequest_Php($html_encoding);

	$pageTitle = null;
	$page = getTipsAndTricksPage( $currentUser, $kernelStrings, $pageTitle, $showQuickStart, $protocol );
	if ( PEAR::isError($page) ) {
		$error = $page->getMessage();
		$_RESULT = array( "error"=>1, "data"=>$error );
	} else {
		$page = "$page";

		$_RESULT = array( "error"=>0, "data"=>$page, "title"=>$pageTitle );
	}

?>