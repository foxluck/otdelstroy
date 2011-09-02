<?php
	define( "WBS_UR_SCREENS_FILE", "_screens.php" );
	
	if ( isset( $host_applications ) )
	{
		$applist = sortApplicationList( $host_applications );
		foreach ($applist  as $application ) {
			if ( file_exists( WBS_PUBLISHED_DIR . UR_DELIMITER. strtoupper( $application ) . UR_DELIMITER .WBS_UR_SCREENS_FILE ) )
				require_once( WBS_PUBLISHED_DIR . UR_DELIMITER. strtoupper( $application ) . UR_DELIMITER .WBS_UR_SCREENS_FILE );
		}
	}
?>