<?php

	function checkDBExists( $hostData, $locStrings )
	//
	// Checks if database exists
	//
	//		Parameters:
	//			$hostData - database profile data
	//			$locStrings - Kernel localization strings
	//
	//		Returns boolean or PEAR_Error
	//
	{
		global $qr_select_databases;
		global $qr_select_tables;
		global $qr_create_database;
		global $wbs_sqlServers;

		$serverName = $hostData[HOST_DBSETTINGS][HOST_SQLSERVER];
		$database = $hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE];

		if ( !array_key_exists($serverName, $wbs_sqlServers) )
			return PEAR::raiseError( sprintf($locStrings['app_servernotfound_message'], $serverName), ERRCODE_APPLICATION_ERR );

		$sqlServerParams = $wbs_sqlServers[$serverName];

		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		$adminUser = $hostData[HOST_DB_CREATE_OPTIONS][HOST_DATABASE_USER];
		$adminPassword = $hostData[HOST_DB_CREATE_OPTIONS][HOST_PASSWORD];

		$db_host = $dbHost;

		if ( strlen($dbPort) )
			$db_host = sprintf( "%s:%s", $db_host, $dbPort );

		$dbh = mysql_connect( $db_host, $adminUser, $adminPassword );
		if ( !$dbh )
			return PEAR::raiseError( $locStrings['app_invsqlconnect_message']."<BR>".mysql_error(), ERRCODE_APPLICATION_ERR );

		$qr = mysql_query( $qr_select_databases, $dbh );

		$result = false;

		while ( list($db_name) = mysql_fetch_row($qr) )
			if ( strtoupper( $database ) == strtoupper($db_name) ) {
				$result = true;
				break;
			}

		mysql_free_result( $qr );
			
		if ( !$result ) {
			mysql_close( $dbh );
			return PEAR::raiseError( "Database does not exist. Please enter name of <b>existing</b> empty database.", ERRCODE_APPLICATION_ERR );
		}

		if ( !($res = mysql_select_db( $database, $dbh )) ) {
			mysql_close( $dbh );
			return PEAR::raiseError( sprintf($locStrings['app_invdbconnect_message']."&nbsp;<br>".mysql_error(), $database), ERRCODE_APPLICATION_ERR );
		}

		$qr = mysql_query( $qr_select_tables, $dbh );
		$tablesNum = mysql_num_rows($qr);
		mysql_free_result( $qr );
		
		if ( $tablesNum != 0 ) {
			mysql_close( $dbh );
			return PEAR::raiseError( "Database is not empty. Please enter name of existing <b>empty</b> database.", ERRCODE_APPLICATION_ERR );
		}

		mysql_close( $dbh );

		return $result;
	}

	function installDBCheckCreate( $database, $dbdata, $locStrings, $serverName, $createFlag, $isadm )
	//
	// Creates databases with prior existence check
	//
	//		Parameters:
	//			$database - database name
	//			$locStrings - an array containing strings in specific language
	//			$serverName - mySQL Server name
	//
	//			Returns boolean, or PEAR_Error
	//
	{
		global $qr_select_databases;
		global $qr_create_database;

		global $wbs_sqlServers;

		if ( !array_key_exists($serverName, $wbs_sqlServers) )
			return PEAR::raiseError( sprintf($locStrings['app_invdbconnect_message'], $serverName), ERRCODE_APPLICATION_ERR );

		$sqlServerParams = $wbs_sqlServers[$serverName];

		$adminPassword = $sqlServerParams[WBS_ADMIN_PASSWORD];
		$adminUser = rtrim( $sqlServerParams[WBS_ADMIN_USERNAME] );
		$dbHost = $sqlServerParams[WBS_HOST];
		$dbPort = $sqlServerParams[WBS_PORT];

		if ( $isadm == 0 )
		{
			$adminUser = $dbdata[HOST_DATABASE_USER];
			$adminPassword = $dbdata[HOST_PASSWORD];
		}

		$db_host = $dbHost;

		if ( strlen($dbPort) )
			$db_host = sprintf( "%s:%s", $db_host, $dbPort );

		$dbh = mysql_connect( $db_host, $adminUser, $adminPassword );
		if ( !$dbh )
			return PEAR::raiseError( $locStrings['app_invsqlconnect_message']."<br>".mysql_error(), ERRCODE_APPLICATION_ERR );

		$qr = mysql_query( $qr_select_databases );

		$result = false;

		while ( list($db_name) = mysql_fetch_row($qr) )
			if ( strtoupper( $database ) == strtoupper($db_name) ) {
				$result = true;
				break;
			}

		if ( ( $createFlag  && $result ) || ( !$createFlag  && !$result ) ) {
			mysql_close( $dbh );
			return false;
		}

		mysql_free_result( $qr );

		if (  $createFlag )
		{
			$qr = mysql_query( sprintf( $qr_create_database, $database ), $dbh );
			if ( !$qr ) {
				mysql_close( $dbh );
				return PEAR::raiseError( "Cannot create database. Database with this name may already exist.", ERRCODE_APPLICATION_ERR );
			}
		}

		if ( !mysql_select_db( $database, $dbh ) ) {
			mysql_close( $dbh );
			return PEAR::raiseError( "Cannot use database.", ERRCODE_APPLICATION_ERR );
		}

		$filePath = sprintf( "%spublished/wbsadmin/administrative_metadata.sql", WBS_DIR );

		if ( !@file_exists( $filePath ) ) {
			mysql_close( $dbh );
			return PEAR::raiseError( sprintf($locStrings['app_filenotfound_message'], basename($filePath)) );
		}

		$fileData = @file_get_contents($filePath);

		$statements = explode( ";", $fileData );

		if ( is_array($statements) && count($statements) )
			for ( $i = 0; $i < count($statements); $i++ )  {
				$statement = trim($statements[$i]);

		if ( !strlen($statement) )
			continue;

		if ( !mysql_query( $statement, $dbh  ) )
			mysql_close( $dbh );
			return PEAR::raiseError( sprintf("Cannot create metadata %s [$statement]", mysql_error()) );
		}


		mysql_close( $dbh );

		return true;
	}


	function wbsadmin_addmodSQLServer( $action, $serverData, $kernelStrings, $db_strings )
	//
	// Adds/Modifies SQL server to WBS server list
	//
	//		Parameters:
	//			$action - form mode (new/edit)
	//			$serverData - array containing SQL server description
	//			$kernelStrings - Kernel localization strings
	//			$db_strings - WebAsyst Administrator localization strings
	//
	//		Returns null or PEAR_Error
	//
	{
		global $_PEAR_default_error_mode;
		global $_PEAR_default_error_options;

		$serverData = trimArrayData( $serverData );

		$requiredFields = array( WBS_HOST );
		if ( $action == ACTION_NEW )
			$requiredFields = array_merge( array('SERVER_NAME', WBS_ADMINRIGHTS), $requiredFields );

		if ( PEAR::isError( $invalidField = findEmptyField($serverData, $requiredFields) ) ) {
			$invalidField->message = $kernelStrings[ERR_REQUIREDFIELDS];

			return $invalidField;
		}

		// Check mySQL connection in case of administrative rihghts
		//
		$isAdmin = $serverData[WBS_ADMINRIGHTS] == WBS_TRUEVAL;

		if ( $isAdmin ) {
			$host = (strlen($serverData[WBS_PORT])) ? $serverData[WBS_HOST].":".$serverData[WBS_PORT] : $serverData[WBS_HOST];

			$res = @mysql_connect( $host, $serverData[WBS_ADMIN_USERNAME], $serverData[WBS_ADMIN_PASSWORD] );

			if ( $res ) {
				$ver = mysql_get_server_info( $res );

				mysql_close($res);

				if (preg_match ("/^(\d+)\.(\d+)\.(\d+)/", $ver, $regs) ) {

					if ( !( ( $regs[1] == 4 && $regs[2] >= 1  ) || $regs[1] >= 5 ) ){
						return PEAR::raiseError( sprintf($db_strings[59],$ver), ERRCODE_APPLICATION_ERR, $_PEAR_default_error_mode, $_PEAR_default_error_options, WBS_ADMIN_USERNAME );
					}
				}
				else
				{
					return PEAR::raiseError( sprintf($db_strings[59],$ver), ERRCODE_APPLICATION_ERR, $_PEAR_default_error_mode, $_PEAR_default_error_options, WBS_ADMIN_USERNAME  );
				}
			} else 
				return PEAR::raiseError( sprintf( $db_strings[60], mysql_error() ), ERRCODE_APPLICATION_ERR, $_PEAR_default_error_mode, $_PEAR_default_error_options, WBS_ADMIN_USERNAME  );
		}

		if ( $action == ACTION_NEW )
			if ( !checkIDSymbols($serverData['SERVER_NAME'], ID_SYMBOLS) )
				return PEAR::raiseError ( $db_strings[13], ERRCODE_INVALIDFIELD, $_PEAR_default_error_mode, $_PEAR_default_error_options, 'SERVER_NAME' );

		$filePath = sprintf( "%skernel/wbs.xml", WBS_DIR );

		$dom = domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( $db_strings[12], ERRCODE_APPLICATION_ERR );
 
		$xpath = xpath_new_context($dom);

		if ( !( $sqlserversnode = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SQLSERVERS) ) )
			return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

		if ( !( $sqlservers = &xpath_eval($xpath, "/".WBS_WBS."/".WBS_SQLSERVERS."/".WBS_SQLSERVER) ) )
			return PEAR::raiseError( $kernelStrings[ERR_XML], ERRCODE_APPLICATION_ERR );

		$currentServerNode = null;
		foreach( $sqlservers->nodeset as $sqlserver ) {
			if ( strtoupper($sqlserver->get_attribute(WBS_NAME)) == strtoupper($serverData['SERVER_NAME']) ) {
				$currentServerNode = $sqlserver;

				break;
			}
		}

		if ( $action == ACTION_NEW && !is_null($currentServerNode) )
			return PEAR::raiseError( $db_strings[14], ERRCODE_APPLICATION_ERR );

		if ( $action == ACTION_EDIT && is_null($currentServerNode) )
			return PEAR::raiseError( $db_strings[15], ERRCODE_APPLICATION_ERR );

		if ( $action == ACTION_NEW ) {
			$currentServerNode = @create_addElement( $dom, $sqlserversnode->nodeset[0], WBS_SQLSERVER );
			if ( !$currentServerNode )
				return PEAR::raiseError( $db_strings[16], ERRCODE_APPLICATION_ERR );
		}

		if ( !$isAdmin ) {

			// Clear mySQL user name and password if user has no administrative rights
			//
			$serverData[WBS_ADMIN_USERNAME] = null;
			$serverData[WBS_ADMIN_PASSWORD] = null;
		}

		$currentServerNode->set_attribute( WBS_HOST, $serverData[WBS_HOST] );
		$currentServerNode->set_attribute( WBS_PORT, $serverData[WBS_PORT] );
		$currentServerNode->set_attribute( WBS_DBCHARSET, $serverData[WBS_DBCHARSET] );
		$currentServerNode->set_attribute( WBS_WEBASYSTHOST, $serverData[WBS_WEBASYSTHOST] );
		$currentServerNode->set_attribute( WBS_ADMIN_USERNAME, $serverData[WBS_ADMIN_USERNAME] );
		$currentServerNode->set_attribute( WBS_ADMINRIGHTS, $serverData[WBS_ADMINRIGHTS] );

		if ( $action == ACTION_NEW ) 
			$currentServerNode->set_attribute( WBS_NAME, $serverData['SERVER_NAME'] );

		if ( strlen($serverData[WBS_ADMIN_PASSWORD]) )
			$currentServerNode->set_attribute( WBS_ADMIN_PASSWORD, $serverData[WBS_ADMIN_PASSWORD] );
		else
			$currentServerNode->set_attribute( WBS_ADMIN_PASSWORD, null );

		if ( $serverLanguagesNode = &xpath_eval($xpath, WBS_LANGUAGES, $currentServerNode) ) 
			if ( count( $serverLanguagesNode->nodeset ) ) {
				$serverLanguagesNode = $serverLanguagesNode->nodeset[0];

				if ( $serverLanguages = &xpath_eval($xpath, WBS_LANGUAGE, $serverLanguagesNode) )
					foreach( $serverLanguages->nodeset as $serverLanguage )
						$serverLanguagesNode->remove_child($serverLanguage); 
			} else
				$serverLanguagesNode = @create_addElement( $dom, $currentServerNode, WBS_LANGUAGES );

		foreach( $serverData[WBS_LANGUAGES] as $lang_id=>$lang_name ) {
			$languageNode = @create_addElement( $dom, $serverLanguagesNode, WBS_LANGUAGE );

			$languageNode->set_attribute( WBS_LANGUAGE_ID, $lang_id );
			$languageNode->set_attribute( WBS_LANGUAGE_NAME, $lang_name );
		}

		@$dom->dump_file( $filePath, false, true );

		return null;
	}

?>