<?php

	require_once( "../../../common/html/includes/httpinit.php" );

	//
	// Authorization
	//

	$fatalError = false;
	$popupStr = null;
	$errorStr = null;
	$SCR_ID = "CP";
	$language = LANG_ENG;

	$CON_TYPE = CONTACT_BASIC_TYPE;

	pageUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	$kernelStrings = $loc_str[$language];

	if ( $fatalError )
		die( $kernelStrings[ERR_GENERALACCESS] );

	if ( !isset($field) || !isset($contact) )
		die( $kernelStrings[ERR_GENERALACCESS] );

	$C_ID = base64_decode($contact);
	$fieldID = base64_decode($field);

	// Load type description
	//
	$typeDesc = getContactTypeDescription( $CON_TYPE, $language, $kernelStrings, false );
	if ( PEAR::isError($typeDesc) )
		die( $typeDesc->getMessage() );

	// Obtain columns descriptions as a plain array
	//
	$fieldsPlainDesc = getContactTypeFieldsSummary( $typeDesc, $kernelStrings, true );
	if ( PEAR::isError($fieldsPlainDesc) )
		die( $fieldsPlainDesc->getMessage() );

	// Load contact data
	//
	$contactData = db_query_result( $qr_selectcontact, DB_ARRAY, array('C_ID'=>$C_ID) );
	if ( PEAR::isError($contactData) )
		die( $kernelStrings[ERR_QUERYEXECUTING] );

	$contactData = applyContactTypeDescription( $contactData, array(), $fieldsPlainDesc, $kernelStrings, UL_LIST_VIEW );
	if ( PEAR::isError($contactData) )
		die( $contactData->getMessage() );

	if ( !isset($contactData[$fieldID]) )
		die( $kernelStrings[ERR_GENERALACCESS] );

	$fieldData = $contactData[$fieldID];

	$attachmentPath = getContactsAttachmentsDir();
	$attachmentPath .= "/".base64_decode($fieldData[CONTACT_IMGF_DISKFILENAME]);

	$fileType = $fieldData[CONTACT_IMGF_MIMETYPE];
	$fileName = $fieldData[CONTACT_IMGF_FILENAME];
	$fileSize = $fieldData[CONTACT_IMGF_SIZE];

	if ( !file_exists($attachmentPath) || is_dir($attachmentPath) )
		die( "Error: file not found" );

	@ini_set( 'async_send', 1 );

	header("Content-type: $fileType");
	header('Content-Disposition: inline; filename="' . $fileName . '"');
	header("Accept-Ranges: bytes");
	header("Content-Length: $fileSize");
	header("Expires: 0");
	header("Cache-Control: private");
	header("Pragma: public");
	header("Connection: close");

	$fp = @fopen($attachmentPath, 'rb');

	while (!feof($fp)) {
		print @fread($fp, 1048576 );
		@flush();
	}

	@fclose($fp)

?>