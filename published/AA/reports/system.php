<?php

	require_once( "../../common/reports/reportsinit.php" );

	//
	// System information report
	//

	//
	// Authorization
	//

	$fatalError = false;
	$errorStr = null;
	$SCR_ID = "SYS";

	reportUserAuthorization( $SCR_ID, $AA_APP_ID, false );

	// 
	// Page variables setup
	//

	$kernelStrings = $loc_str[$language];

	switch ( true ) {
		case true : {
						$db_size = getDatabaseSize();
						if ( PEAR::isError($db_size) )
							$db_size = $kernelStrings['sys_errgettingdbsize_message'];

						$QuotaManager = new DiskQuotaManager();

						$attachmentsSize = $QuotaManager->GetUsedSpaceTotal( $kernelStrings, false );

						$totalSize = $attachmentsSize + $db_size;

						if ( DATABASE_SIZE_LIMIT > 0 )
							$ratio = round( $totalSize/(DATABASE_SIZE_LIMIT*MEGABYTE_SIZE)*100 );
						else
							$ratio = null;

						$dateFormat = $dateFormats[DATE_DISPLAY_FORMAT];
		}
	}

	$preprocessor = new print_preprocessor( $AA_APP_ID, $kernelStrings, $language );

	$preprocessor->assign( REPORT_TITLE, $kernelStrings['sys_screen_long_name'] );
	$preprocessor->assign( ERROR_STR, $errorStr );
	$preprocessor->assign( FATAL_ERROR, $fatalError );

	if ( !$fatalError ) {
		$preprocessor->assign( "attachmentsSize", formatFileSizeStr( $attachmentsSize ) );
		$preprocessor->assign( "db_size", formatFileSizeStr( $db_size ) );
		$preprocessor->assign( "totalSize", formatFileSizeStr( $totalSize ) );
		$preprocessor->assign( "totalLimit", formatFileSizeStr( DATABASE_SIZE_LIMIT*MEGABYTE_SIZE ) );		
		$preprocessor->assign( "dateFormat", $dateFormat );

		if ( !is_null($ratio) )
			$preprocessor->assign( "ratio", sprintf( $kernelStrings['sys_availspace_text'], $ratio ) );
		else
			$preprocessor->assign( "ratio", "" );
	}

	$preprocessor->display( "system.htm" );
?>