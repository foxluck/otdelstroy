<?php

	//
	// Generic database access functions
	//

	function db_connect()
	//
	// Executes connection to WBS database
	//
	//		Parameters:
	//			none
	//
	//		Returns true, or PEAR_Error
	//
	{
		global $wbs_database;
		global $databaseInfo;
		
		if ( !defined('DB_NAME') )
			return;

		$dsn = array(
			'phptype' => WBS_DATABASE_TYPE,
			'username' => DB_USER,
			'password' => DB_PASSWORD,
			'hostspec' => DB_HOST,
			'database' => DB_NAME,
		);

		if ( strlen(DB_PORT) )
			if ( is_integer(DB_PORT) )
				$dsn['port'] = DB_PORT;
			else {
				$dsn['protocol'] = DB_PORT;
				$dsn['port'] = DB_PORT;
			}
		$db = new DB();
		$wbs_database =$db->connect($dsn, false);
		if ( PEAR::isError($wbs_database) ) {
			$fh = fopen (WBS_DIR . "kernel/error_db_connect.log", "a");
			fwrite ($fh, date("Y-m-d H:i:s;") . $dsn["username"] . "@" . $dsn["password"] . ";" . $dsn["database"] . ";" . $dsn["hostspec"] . ";" . getenv("REMOTE_ADDR") . ";WA\n" );
			fclose ($fh);
			
			return $wbs_database;
		}

		$wbs_database->setErrorHandling( PEAR_ERROR_CALLBACK, 'handlePEARError' );

		if ( isset($databaseInfo[HOST_DBSETTINGS]) && isset($databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'])&& $databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'] ) {
			$charset = $databaseInfo[HOST_DBSETTINGS]['MYSQL_CHARSET'];
			mysql_query( 'set names '.$charset );
			mysql_query ("set character_set_client='$charset'");
			mysql_query ("set character_set_results='$charset'");
			mysql_query ("set collation_connection='${charset}_bin'");
		}
		
		return true;
	}

	function db_custom_connect( $db_name, $DB_USER = null, $DB_PASSWORD = null, $host = null, $port = null,  $charset = null)
	//
	// Executes connection to database
	//
	//		Parameters:
	//			$db_name - database name
	//			$DB_USER - database name
	//			$DB_PASSWORD - database password
	//			$host - database host
	//			$port - database port
	//
	//		Returns reference to database, or PEAR_ERROR
	//
	{
		if ( is_null($DB_USER) )
			$DB_USER = DB_USER;

		if ( is_null($DB_PASSWORD) )
			$DB_PASSWORD = DB_PASSWORD;

		$host = is_null($host) ? DB_HOST : $host;
		$port = is_null($port) ? DB_PORT : $port;

		$dsn = array(
			'phptype' => WBS_DATABASE_TYPE,
			'username' => $DB_USER,
			'password' => $DB_PASSWORD,
			'hostspec' => $host,
			'database' => $db_name,
		);

		if ( strlen($port) )
			if ( is_integer($port) )
				$dsn['port'] = $port;
			else {
				$dsn['protocol'] = $port;
				$dsn['port'] = $port;
			}

		$database = DB::connect($dsn, false);
		if ( PEAR::isError($database) )
			return $database;
			
		if ( !is_null($charset) && $charset ){
			@mysql_query( 'set names '.$charset );
			mysql_query ("set character_set_client='$charset'");
			mysql_query ("set character_set_results='$charset'");
			mysql_query ("set collation_connection='${charset}_bin'");
		}

		$database->setErrorHandling( PEAR_ERROR_CALLBACK, 'handlePEARError' );

		return $database;
	}

	function db_query( $sql, $params = null, $database = null )
	//
	// Prepares and implements SQL query.
	//		Query is implemented within WBS database, or within database, defined by parameted $database
	//
	//		Parameters:
	//			$sql - query string
	//			$database - reference to database
	//
	//	Returns object DB_Result, or PEAR_Error
	//
	{
		global $wbs_database;

		if ( is_null($params) )
			$params = array();
			

		if ( is_null($database) )
			$database = $wbs_database;
		if ( is_null($database) )
			return PEAR::raiseError('Couldn\'t get connection settings' );

		if ( is_object($params) )
			$params = (array)$params;

		preparePEARQuery( $sql, $params, $pear_sql, $pear_params );
		

		if ( PEAR::isError( $sth = $database->prepare($pear_sql) ) )
			return $sth;

		$res = $database->execute( $sth, $pear_params );

		if ( PEAR::isError($res) ){
			PEAR::raiseError( mysql_error().' - '.$res->userinfo );
		}

		return $res;
	}

	function db_free_result( $qr )
	//
	// Frees query result
	//
	//		Parameters:
	//			$qr - query result, object DB_Result
	//
	//	Returns true
	//
	{
		if ( is_object($qr) )
			$qr->free();

		return true;
	}

	function db_fetch_array( $qr, $mode = DB_FETCHMODE_ASSOC )
	//
	// Fetches row from query result
	//
	//		Parameters:
	//			$qr - query result, object DB_Result
	//			$mode - mode of row extraction (modes are defined in PEAR DB)
	//
	//	Returns an associative array containing string data, or null
	//
	{
		return $qr->fetchRow( $mode );
	}

	function db_result_num_rows( $qr )
	//
	// Fetches row from query result
	//
	//		Parameters:
	//			$qr - query result, object DB_Result
	//			$mode - mode of row extraction (modes are defined in PEAR DB)
	//
	//	Returns an associative array containing string data, or null
	//
	{
		return $qr->numRows( );
	}

	function db_query_result( $sql, $result_type = DB_FIRST, $params = null )
	//
	// Implements query and returns result in the form of an associative array, or value of first column
	//
	//		Parameters:
	//			$sql - query string
	//			$result_type - result type
	//			$params - parameters of query
	//
	//	Returns query result, or PEAR_Error
	//
	{
		$qr = db_query( $sql, $params );

		if ( PEAR::isError($qr) ) {
			return $qr;
		}

		switch ($result_type) {
			case DB_FIRST : {
					$values = db_fetch_array( $qr, DB_FETCHMODE_ORDERED );

					db_free_result( $qr );
					return $values[0];
				}
			case DB_ARRAY_ORDERED : {
					$values = db_fetch_array( $qr, DB_FETCHMODE_ORDERED );

					db_free_result( $qr );
					return $values;
				}
			case DB_ARRAY : {
					$values = db_fetch_array( $qr );

					db_free_result( $qr );
					return $values;
				}
		}
	}

	function preparePEARQuery( $sql, $params, &$pear_sql, &$pear_params )
	//
	// Converts query with named parameters and array of parameters into a query and parameters for PEAR, respectively.
	//		Query and parameters can be used by functions of DB_Common
	//
	//		Parameters:
	//			$sql - query string with named parameters
	//			$params - an associative array containing parameters
	//			$pear_sql - PEAR query
	//			$pear_params - PEAR parameters
	//
	//	Returns true, or PEAR_Error
	//
	{
		$pear_sql = $sql;
		$pear_params = array();

		// Fetch parameter names from SQL query
		//
		$pattern = "/('\![0-9A-Z_]+\!')/iu";
		@preg_match_all( $pattern, $sql, $paramNames );

		if ( !is_array($paramNames) )
			return true;

		$paramCount = count($paramNames[0]);

		for ( $i = 0; $i < $paramCount; $i++ ) {
			$paramName = $paramNames[0][$i];
			$paramName = substr( $paramName, 2, strlen($paramName)-4 );

			if ( array_key_exists($paramName, $params) )
				$pear_params[] = $params[$paramName];
			else
				$pear_params[] = null;
		};

		$pear_sql = @preg_replace( $pattern, "?", $sql );

		return true;
	}

	function exec_sql( $sql, $inputList, &$outputList, $dataExpected = true, $database = null )
	//
	// Places parameters and implements query.
	//		If dataExpected = true, function returns result in the form of an array.
	//		Query is implemented with WBS database, or within database, defined by parameter $database
	//
	//		Parameters:
	//			$sql - query
	//			$inputList - an array containing incoming parameters
	//			$outputList - reference to an array containing incoming parameters
	//			$dataExpected - if it is true, the result is returned in $outputList
	//			$database - reference to database
	//
	//		Returns null in case of successful implementation. Otherwise, PEAR_Error
	//
	{
		global $wbs_database;

		if ( !strlen( $sql ) )
			return PEAR::raiseError( "Empty SQL string passed" );

		if ( is_null($database) )
			$database = $wbs_database;
		if ( is_null($database) )
			return PEAR::raiseError('Couldn\'t get connection settings' );

		if ( is_null($inputList) )
			$inputList = array();

		preparePEARQuery( $sql, $inputList, $pear_sql, $pear_params );

		$sth = $database->prepare( $pear_sql );

		if ( PEAR::isError( $qr = $database->execute($sth, $pear_params) ) )
			return $qr;

		if ( $dataExpected ) {
			if ( PEAR::isError( $row = db_fetch_array( $qr ) ) )
				return $row;

			$outputList = (array)$row;
		} else {
			return $qr;
		}

		db_free_result( $qr );

		return null;
	}

	function nullSQLFields( $resultArr, $fieldNames = null )
	//
	// Replaces empty strings in array by null
	//
	//		Parameters:
	//			$resultArr - an associative array
	//			$fieldNames - an array of fields, which should be modified. If it is null, all fields are modified.
	//
	//		Returns modified array $resultArr
	//
	{
		foreach ( $resultArr as $fieldName => $fieldValue )
			if ( (is_null($fieldNames) || !is_array($fieldNames)) || ( in_array($fieldName, $fieldNames) ) )
				if ( !is_array($fieldValue) && !strlen($fieldValue) )
					$resultArr[$fieldName] = null;

		return $resultArr;
	}


	function incID( $id_value, $initValue = 1 )
	//
	// Increases identifier by 1. If identifier is not initialized, initial value is assigned
	//
	//		Parameters:
	//			$id_value - identifier value
	//			$initValue - initial value
	//
	//		Returns new value of identifier
	//
	{
		if ( !strlen($id_value) )
			return $initValue;

		return $id_value + 1;
	}

	function setSQLVars( $sql, $varArray )
	//
	// Internal function. Places parameters in query string.
	//
	//		Parameters:
	//			$sql - query string
	//			$varArray - an associative array containing parameters
	//
	//		Returns query string with placed parameters
	//
	{
		$str_search = "!%s!";
		$str_search_NULL = sprintf( "%s!%s!%s", SQL_STRING_DELITIMTER, "%s", SQL_STRING_DELITIMTER );

		$str = $sql;

		while ( list ($key, $val) = each ( $varArray ) )
			if ( !is_null($val) )
				$str = str_replace( sprintf( $str_search, strtoupper( $key ) ), $val, $str );
			else
				$str = str_replace( sprintf( $str_search_NULL, strtoupper( $key ) ), "null", $str );

		return $str;
	}

	function execPreparedQuery( $sql, $params )
	//
	// Places parameters and implements query.
	//		Query is transfered to SQL server in the form of string with already placed parameters.
	//		Function is recommended to be used only with SELECT queries,
	//		if PEAR library restrictions do not allow to impement query by the means of functions
	//		exec_sql(), db_query_result() � db_query()
	//
	//		Parameters:
	//			$sql - query string with named parameters
	//			$params - an associative array containing parameters
	//
	//		Returns query result, or PEAR_Error
	//
	{
		global $wbs_database;

		if ( !strlen( $sql ) )
			return PEAR::raiseError( "Empty SQL string passed" );

		$sql = setSQLVars( $sql, $params );

		$res = $wbs_database->simpleQuery( $sql );
		if(PEAR::isError($res))	return $res;

		return new DB_result($wbs_database, $res);
	}

	function db_insert_id($qr){
		global $wbs_database;
		return mysql_insert_id($wbs_database->connection);
	}
?>
