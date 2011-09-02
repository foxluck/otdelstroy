<?php

	//
	// Database access constants
	//

	if ( !( isset($init_required) && !$init_required ) ) {
		$databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];

		$createOpt = $databaseInfo[HOST_DBSETTINGS][HOST_DB_CREATE_OPTION];

		$DB_NAME = $databaseInfo[HOST_DBSETTINGS][HOST_DBNAME];

		$dbPassword = $databaseInfo[HOST_DBSETTINGS][HOST_DBPASSWORD];
		$db_user = $databaseInfo[HOST_DBSETTINGS][HOST_DBUSER];

		define( "DB_NAME", $DB_NAME );
		define( "DB_PASSWORD", $dbPassword );
		define( "DB_USER", $db_user );
	}

?>