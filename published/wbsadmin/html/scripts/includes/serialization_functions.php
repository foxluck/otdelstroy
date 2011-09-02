<?php
/*****************************************************************************
 *                                                                           *
 * Shop-Script PREMIUM                                                       *
 * Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
 *                                                                           *
 *****************************************************************************/
?><?php
// *****************************************************************************
// Purpose	
// Inputs   $xmlTable - table XML node
// Remarks	
// Returns	"select" SQL query to getting all data of this table
/*DEL*/function _getSelectStatement( $tableName, $columnsClause)
{
	$sql = "select ".$columnsClause." from ".$tableName;
	return $sql;
}


// *****************************************************************************
// Purpose	
// Inputs		$xmlTable	- table XML node
//				$row		- data to insret
// Remarks	
// Returns	"insert" SQL query to table corresponded to $xmlTable
/*DEL*/function _getInsertStatement( $xmlTable, $row, $columns = NULL, $attributes = NULL, $columnsClause )
{
	$sql = "insert into ";
	if (!$attributes)
		$attributes = $xmlTable->GetXmlNodeAttributes();
	$tableName = $attributes["NAME"];

	// exceptions
	if ( $tableName == CATEGORIES_TABLE )
		if ( $row["categoryID"] == 1 )
			return "";

	$sql .= $tableName;
	$valueClause	= "";

	if (!$columns)
		$columns = $xmlTable->SelectNodes("table/column");
	$i = 0;
	foreach( $columns as $xmlColumn )
	{
		$attributes = $xmlColumn->GetXmlNodeAttributes();
		$columnName = $xmlColumn->GetXmlNodeData();
		$columnName = trim($columnName);

		$type = strtoupper( $attributes["TYPE"] );
		if (	strstr($type, "CHAR") ||  
				strstr($type, "VARCHAR") || 
				strstr($type, "TEXT") ||
				strstr($type, "DATETIME") )
		{
			$cellValue = $row[$i];
			$cellValue = TransformStringToDataBase( $cellValue );
			$value = "'".$cellValue."'";
		}
		else
			$value = $row[ $i ];

	 	if ( $row[ $i ] == null && trim($row[ $i ]) == "" )
			$value = "NULL";

		if ( $i == 0 ) $valueClause .= $value;
		else $valueClause .= ", ".$value;

		$i++;
	}

	$sql .= "(".$columnsClause.") values(".$valueClause.")";
	return $sql;
}


// *****************************************************************************
// Purpose	compile delete SQL statement to delete all data in data base table
// Inputs   $xmlTable - table XML node
// Remarks	
// Returns	"delete from" SQL statements
/*DEL*/function _getDeleteStatement( $xmlTable )
{
	$sql = "delete from ";
	$attributes = $xmlTable->GetXmlNodeAttributes();
	$tableName = $attributes["NAME"];
	$sql .= $tableName;
	return $sql;
}


// *****************************************************************************
// Purpose	read data from data base and transform it into SQL instructions ("insert into")
// Inputs   $xmlTable - table XML node
// Remarks	
// Returns	"insert into" SQL statements separated by ';'
/*DEL*/function _tableSerialization( $xmlTable )
{
	$res = "";



	$columnsClause	= "";

	$columns = $xmlTable->SelectNodes("table/column");
	$attributes = $xmlTable->GetXmlNodeAttributes();

	$i = 0;
	foreach( $columns as $xmlColumn )
	{
		$attr = $xmlColumn->GetXmlNodeAttributes();
		$columnName = $xmlColumn->GetXmlNodeData();
		$columnName = trim($columnName);

		if ( $i == 0 ) $columnsClause .= $columnName;
		else $columnsClause .= ", ".$columnName;
		$i++;
	}

	$tableName = $attributes["NAME"];


	$selectSql = _getSelectStatement( $tableName, $columnsClause );
	$q = ss_db_query( $selectSql );
	while( $row = db_fetch_row($q) )
	{
		$insertSql = _getInsertStatement( $xmlTable, $row, $columns, $attributes, $columnsClause );
		if ( $insertSql == "" )
			continue;
		$res .= $insertSql.";\n";
	}
	return $res;
}

/*DEL*/function _isIdentityTable($tableName)
{
	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( DATABASE_STRUCTURE_XML_PATH );
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	foreach($array as $xmlTable)
	{
		$attributes = $xmlTable->GetXmlNodeAttributes();
		if ( strtoupper($tableName) == strtoupper($attributes["NAME"]) )
		{
			$columns = $xmlTable->SelectNodes("table/column");
			foreach( $columns as $xmlColumn )
			{
				$attributes = $xmlColumn->GetXmlNodeAttributes();
				if ( isset($attributes["IDENTITY"]) )
				{
					if (  trim(strtoupper($attributes["IDENTITY"])) == "TRUE"  )
						return  true;
					else
						return true;
				}
				else
					return false;
			}
		}
	}
	return null;
}



// *****************************************************************************
// Purpose	read all products and categories from data base and 
//					transform it into SQL instructions ("insert into")
// Inputs   $fileName - file to write
// Remarks	
// Returns	
/*DEL*/function serProductAndCategoriesSerialization($fileName)
{
	$f = fopen( $fileName, "w" );
	$xmlTables = new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( DATABASE_STRUCTURE_XML_PATH );
	$array = $xmlTables->SelectNodes("DataBaseStructure/tables/table");
	foreach($array as $xmlTable)
	{
		$attrubtes = $xmlTable->GetXmlNodeAttributes();
	 	if ( isset($attrubtes["PRODUCTANDCATEGORYSYNC"]) )
			if ( strtoupper($attrubtes[ "PRODUCTANDCATEGORYSYNC" ]) == "TRUE" )
			{
				$res = _tableSerialization( $xmlTable );
				fputs( $f, $res."\n" );
			}
	}
	fclose( $f );
}



// *****************************************************************************
// Purpose	clear all products and categories
// Inputs   nothing
// Remarks	
// Returns	nothing
/*DEL*/function serDeleteProductAndCategories()
{
	/* SLOW OBSOLETE METHOD
	
	$q = ss_db_query( "select categoryID from ".CATEGORIES_TABLE." where categoryID<>0" );
	while( $row=db_fetch_row($q) )
		DeleteAllProductsOfThisCategory( $row["categoryID"] );

	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( DATABASE_STRUCTURE_XML_PATH );
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	foreach($array as $xmlTable)
	{
		$attrubtes = $xmlTable->GetXmlNodeAttributes();
	 	if ( isset($attrubtes["PRODUCTANDCATEGORYSYNC"]) )
			if ( strtoupper($attrubtes[ "PRODUCTANDCATEGORYSYNC" ]) == "TRUE" )
			 	ss_db_query( _getDeleteStatement($xmlTable) );
	}

	//add root category
	ss_db_query("insert into ".CATEGORIES_TABLE."( name, parent, categoryID )".
			"values( 'ROOT', NULL, 1 )");
	*/

	imDeleteAllProducts();
}


/*DEL*/function _getChar( &$fileContent, $pointer, $strlen )
{
	if ( $pointer <= $strlen - 1 ) 
		return $fileContent[$pointer];
	else	
		return ' ';
}


/*DEL*/function _passSeparator( &$fileContent, &$pointer, $strlen )
{
	while( (_getChar( $fileContent, $pointer, $strlen ) == ' ' || _getChar( $fileContent, $pointer, $strlen ) == '\n' ||
		    _getChar( $fileContent, $pointer, $strlen ) == '\t' || _getChar( $fileContent, $pointer, $strlen ) == '\r' ||
			_getChar( $fileContent, $pointer, $strlen ) == ';'  ) &&
		      $pointer <= $strlen-1 )
		$pointer ++;
}

/*DEL*/function _passSpaces( &$fileContent, &$pointer, $strlen )
{
	while( (_getChar( $fileContent, $pointer, $strlen ) == ' ' || _getChar( $fileContent, $pointer, $strlen ) == '\n' ||
		    _getChar( $fileContent, $pointer, $strlen ) == '\t' || _getChar( $fileContent, $pointer, $strlen ) == '\r') &&
		      $pointer <= $strlen-1 )
		$pointer ++;
}

/*DEL*/function _passOpenBracket( &$fileContent, &$pointer, $strlen )
{
	while( (_getChar( $fileContent, $pointer, $strlen ) == ' ' || _getChar( $fileContent, $pointer, $strlen ) == '\n' ||
		    _getChar( $fileContent, $pointer, $strlen ) == '\t' || _getChar( $fileContent, $pointer, $strlen ) == '\r' || 
			_getChar( $fileContent, $pointer, $strlen ) == '(' ) && $pointer <= $strlen-1 )
		$pointer ++;
}

/*DEL*/function _passValues( &$fileContent, &$pointer, $strlen )
{
	$inQuotes = 0;
	while(1)
	{
		if ( _getChar( $fileContent, $pointer, $strlen ) == "\\" && $inQuotes == 1 && 
					_getChar( $fileContent, $pointer + 1, $strlen ) == "'" )
					$pointer += 2;
		if ( _getChar( $fileContent, $pointer, $strlen ) == "'" && $inQuotes == 0 )
			$inQuotes = 1;
		else if ( _getChar( $fileContent, $pointer, $strlen ) == "'" && $inQuotes == 1 )
			$inQuotes = 0;
		else if ( _getChar( $fileContent, $pointer, $strlen ) == ')' && $inQuotes == 0 )
			return;
		$pointer++;
	}
}

/*DEL*/function _passCloseBracket( &$fileContent, &$pointer, $strlen )
{
	while( _getChar( $fileContent, $pointer, $strlen ) != ')' && $pointer <= $strlen-1 )
		$pointer ++;
	$pointer ++;
}

/*DEL*/function _getWord( &$fileContent, &$pointer, $strlen )
{
	$begin = $pointer;
	while( _getChar( $fileContent, $pointer, $strlen ) != ' ' &&  _getChar( $fileContent, $pointer, $strlen ) != '\n' &&
		   _getChar( $fileContent, $pointer, $strlen ) != '\t' && _getChar( $fileContent, $pointer, $strlen ) != '\r' && 
			_getChar( $fileContent, $pointer, $strlen ) != '(' && _getChar( $fileContent, $pointer, $strlen ) != ')' && 
				$pointer <= $strlen - 1 )
		$pointer ++;
	return substr( $fileContent, $begin, $pointer - $begin );
}


// *****************************************************************************
// Purpose	
// Inputs   nothing
// Remarks	
// Returns  string contained "insert into ... " statement
/*DEL*/function _getNextSqlInsertStatement( &$fileContent, &$pointer, $strlen )
{
	if ( $pointer >= $strlen - 1  )
		return null;

	$res = array();
	$res["Error"] = true;
	
	_passSeparator( $fileContent, $pointer, $strlen );

	if ( $pointer >= $strlen - 1  )
		return null;

	$begin = $pointer;

	// "insert"
	_passSpaces( $fileContent, $pointer, $strlen );
	$insertWord = _getWord( $fileContent, $pointer, $strlen );
	if ( strcasecmp( "insert", trim($insertWord) ) )
		return $res;

	// "into"
	_passSpaces( $fileContent, $pointer, $strlen );
	$intoWord = _getWord( $fileContent, $pointer, $strlen );
	if ( strcasecmp( "into", $intoWord ) )
		return $res;

	// table name
	_passSpaces( $fileContent, $pointer, $strlen );
	$tableWord = _getWord( $fileContent, $pointer, $strlen );
		$res["tableName"] = $tableWord;

	_passOpenBracket( $fileContent, $pointer, $strlen );
	_passCloseBracket( $fileContent, $pointer, $strlen );

	// "values"
	_passSpaces( $fileContent, $pointer, $strlen );
	$valuesWord = _getWord( $fileContent, $pointer, $strlen );
	if ( strcasecmp( "values", $valuesWord ) )
		return $res;

	_passOpenBracket( $fileContent, $pointer, $strlen );
	_passValues( $fileContent, $pointer, $strlen );

	_passSpaces( $fileContent, $pointer, $strlen );
	
	unset($res["Error"]);
	$res["statement"] = substr( $fileContent, $begin, $pointer - $begin + 1 );

	$pointer++;
	return $res;
}



/*DEL*/function _testSpace( $char )
{
	return ($char == " " || $char == "\t" || $char == "\n" || $char == "\r" || $char == ";");
}

// *****************************************************************************
// Purpose	replace table constant name in insert statement to table name
// Inputs   $insertSqlQuery -  SQL insert statement
// Remarks	
// Returns  null if /*DEL*/function failed
//			insert SQL statement
/*DEL*/function _serReplaceConstantName( $insertSqlQuery )
{
	$insertClause = "INSERT";
	$intoClause = "INTO";
	$charIndex = 0;

	// pass spaces
	for( ; $charIndex<strlen($insertSqlQuery); $charIndex++ )
	{
		if (  !_testSpace( $insertSqlQuery[$charIndex] )   )
			break;
	}

	// pass "INSERT" word
	$i = 0;
	for( ; $i < strlen($insertClause); $i++,$charIndex++ )
		if (  strtoupper($insertClause[$i]) != strtoupper($insertSqlQuery[$charIndex])  )
			return null;

	// pass spaces
	for( ; $charIndex<strlen($insertSqlQuery); $charIndex++ )
		if ( !_testSpace( $insertSqlQuery[$charIndex] ) )
			break;

	// pass "INTO" word
	$i = 0;
	for( ; $i < strlen($intoClause); $i++,$charIndex++ )
		if (  strtoupper($intoClause[$i]) != strtoupper($insertSqlQuery[$charIndex])  )
			return null;

	// pass spaces
	for( ; $charIndex<strlen($insertSqlQuery); $charIndex++ )
		if ( !_testSpace($insertSqlQuery[$charIndex]) )
			break;

	if ( $charIndex == strlen($insertSqlQuery) )
		return null;

	$constantNameBeginIndex = $charIndex;

	// pass constant name
	$constantName = "";
	for( ; $charIndex<strlen($insertSqlQuery); $charIndex++ )
	{
		if ( $insertSqlQuery[$charIndex] != ' '  && $insertSqlQuery[$charIndex] != '\t' &&
			 $insertSqlQuery[$charIndex] != '\n' && $insertSqlQuery[$charIndex] != '\r' &&
			 $insertSqlQuery[$charIndex] != '(' )
			$constantName .= $insertSqlQuery[$charIndex];
		else
			break;
	}

	$tableName = constant( $constantName );

	$begin	= substr( $insertSqlQuery, 0, $constantNameBeginIndex );
	$end	= substr( $insertSqlQuery, $constantNameBeginIndex+strlen($constantName) );
	return $begin." ".$tableName." ".$end;
}



function _serImport( $fileName, $replaceConstantName, $autoIncrementId = false )
{
	$pointer = 0;
	$str = myfile_get_contents( $fileName );
	$str = trim( $str );

	$tableName = "";

	$strlen = strlen( $str );

	while( ($sqlInsret = _getNextSqlInsertStatement( $str, $pointer, $strlen )) != null )
	{

		if ( isset($sqlInsret["Error"]) )
			return false;

		if ( $replaceConstantName )
			ss_db_query( _serReplaceConstantName($sqlInsret["statement"]) );
		else
			ss_db_query( $sqlInsret["statement"] );
	}
	return true;
}


// *****************************************************************************
// Purpose	
// Inputs   nothing
// Remarks	
// Returns  nothing
function serImportWithConstantNameReplacing( $fileName, $autoIncrementId = false )
{
	_serImport( $fileName, true, $autoIncrementId );
}

// *****************************************************************************
// Purpose	
// Inputs   nothing
// Remarks	
// Returns  nothing
/*DEL*/function serImport( $fileName, $autoIncrementId = false )
{
	_serImport( $fileName, false, $autoIncrementId );
}




/*DEL*/function _filterBadSymbolsToExcel( $str )
{
	$str = str_replace( "\r\n", "", $str );
	$str = str_replace( "<br>", " ", $str );

	$semicolonFlag = false;
	for( $i=0; $i<strlen($str); $i++ )
	{
		if ( $str[$i] == ";" )
		{
			$semicolonFlag = true;
			break;
		}
	}

	if ( !$semicolonFlag )
		return $str;
	else
	{
		$res = "";
		for( $i=0; $i<strlen($str); $i++  )
		{
			if ( $str[$i] == "\"" )
				$res .= "\"\"";
			else
				$res .= $str[$i];
		}
		return "\"".$res."\"";
	}
}

// *****************************************************************************
// Purpose	
// Inputs   nothing
// Remarks	
// Returns  nothing
/*DEL*/function serExportCustomersToExcel( $customers )
{
	$maxCountAddress = 0;
	foreach( $customers as $customer )
	{
		$q = ss_db_query("select count(addressID) from ".CUSTOMER_ADDRESSES_TABLE.
				" where customerID=".$customer["customerID"] );
		$countAddress = db_fetch_row( $q );
		$countAddress = $countAddress[0];
		if ( $maxCountAddress < $countAddress )
			$maxCountAddress = $countAddress;
	}

	// open file to write
	$f = fopen( "./temp/customers.csv", "w" );

	// head table generate
	$headLine = "Login;First name;Last name;Email;Group;Registered;Newsletter subscription;";
	$q = ss_db_query( "select reg_field_ID, reg_field_name from ".CUSTOMER_REG_FIELDS_TABLE.
			" order by sort_order " );
	while( $row = db_fetch_row($q) )
		$headLine .= _filterBadSymbolsToExcel( $row["reg_field_name"] ).";";

	for( $i=1; $i<=$maxCountAddress; $i++ )
		$headLine .= "Address ".$i.";";
	fputs( $f, $headLine."\n" );

	foreach( $customers as $customer ) 
	{
		$q = ss_db_query( "select Login, first_name, last_name, Email, custgroupID, reg_datetime, subscribed4news from ".CUSTOMERS_TABLE.
			" where addressID=".(int)$customer["addressID"] );
		$row_cust = db_fetch_row( $q );

		if ( $row_cust["custgroupID"] != null )
		{
			$q = ss_db_query( "select custgroup_name from ".CUSTGROUPS_TABLE.
				" where custgroupID=".$row_cust["custgroupID"] );
			$row = db_fetch_row($q);
			$row_cust["custgroup_name"] = $row["custgroup_name"];
		}
		else
			$row_cust["custgroup_name"] = "";

		if ( $row_cust["subscribed4news"] )
			$row_cust["subscribed4news"] = "+";
		else
			$row_cust["subscribed4news"] = "";

		$line = "";
		$line .= _filterBadSymbolsToExcel( $row_cust["Login"] ).";";
		$line .= _filterBadSymbolsToExcel( $row_cust["first_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $row_cust["last_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $row_cust["Email"] ).";";
		$line .= _filterBadSymbolsToExcel( $row_cust["custgroup_name"] ).";";
		$line .= _filterBadSymbolsToExcel( $row_cust["reg_datetime"] ).";";
		$line .= $row_cust["subscribed4news"].";";

		$q_reg_param = ss_db_query( "select reg_field_ID, reg_field_name from ".CUSTOMER_REG_FIELDS_TABLE.
			" order by sort_order " );
		while( $row = db_fetch_row($q_reg_param) )
		{
			$q_reg_value = ss_db_query( "select reg_field_value from ".CUSTOMER_REG_FIELDS_VALUES_TABLE.
					" where reg_field_ID=".$row["reg_field_ID"]." AND customerID=".
							$customer["customerID"] );
			$value = db_fetch_row( $q_reg_value );
			$value = $value["reg_field_value"];

			$line .= _filterBadSymbolsToExcel( $value ).";";
		}

		$countAddress = 0;
		$addresses = regGetAllAddressesByLogin( regGetLoginById($customer["customerID"]) );
		foreach( $addresses as $address )
		{
			$line .= _filterBadSymbolsToExcel( regGetAddressStr($address["addressID"], true) ).";";
			$countAddress ++;
		}

		for( $i=1; $i<=$maxCountAddress-$countAddress; $i++ )
			$line .= ";";

		fputs( $f, $line."\n" );
	}

	fclose($f);
}

/**
 * Check new constants in database and if they exist will not install them
 *
 * @param string $_FileName - file with new constants sqls
 */
/*DEL*/function serImportConstWithChecking($_FileName){
	
	$pointer = 0;
	$str = myfile_get_contents( $_FileName );
	$str = trim( $str );
	$SettingNames = array();

	$tableName = "";

	$strlen = strlen( $str );
	
	$sql = '
		SELECT settings_constant_name FROM '.SETTINGS_TABLE.'
	';
	$Result = ss_db_query($sql);
	while ($_Row = db_fetch_row($Result)){
		
		$SettingNames[] = $_Row['settings_constant_name'];
	}
	
	while( ($sqlInsret = _getNextSqlInsertStatement( $str, $pointer, $strlen )) != null )
	{

		$Continue = false;
		if ( isset($sqlInsret["Error"]) )
			return false;

		$_TC = count($SettingNames)-1;
		for( ; $_TC>=0; $_TC--){
			
			if(strpos($sqlInsret["statement"], $SettingNames[$_TC])!==false){
				
				$Continue = true;
				break;
			}
		}
		if ($Continue) {
			
			continue;
		}
		ss_db_query( _serReplaceConstantName($sqlInsret["statement"]) );
	}
	return true;
}
?>