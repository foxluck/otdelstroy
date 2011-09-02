<?php

	//
	// WBS Kernel DBSM-dependent functions
	//
	// Realization for mySQL
	//

	function getDatabaseSize()
	//
	// Returns database size
	//
	//		Returns integer - database size, in bytes, or PEAR_Error
	//
	{
		global $qr_select_db_info;

		$qr = db_query( $qr_select_db_info );
		if ( PEAR::isError( $qr ) )
			return $qr;

		$result = 0;

		while ($row = db_fetch_array($qr) )
			$result += $row['Data_length'] + $row['Index_length'] + $row['Data_free'] + 8900; 

		db_free_result( $qr );

		return $result;
	}

	function getMySqlServerVersion( )
	//
	// Returns the MySql server version as array
	//
	{
		global $qr_select_server_version;

		$res = db_query_result( $qr_select_server_version, DB_FIRST, array() );
		if ( PEAR::isError($res) )
			return null;

		$parts = explode( '.', $res );

		return array( $parts[0], $parts[1] );
	}
?>