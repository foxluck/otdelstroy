<?php

	//
	// Account Administrator intialization script
	//
	
	require_once( "aa_functions.php" );
	require_once( "aa_dbfunctions_cmn.php" );
	require_once( "aa_consts.php" );
	if (file_exists(WBS_DIR.'published/'.AA_APP_ID.'/aa_account_functions.php'))
		require_once 'aa_account_functions.php';
	if (file_exists(WBS_DIR.'published/'.AA_APP_ID.'/aa_domain_functions.php'))
		require_once 'aa_domain_functions.php';
	
?>