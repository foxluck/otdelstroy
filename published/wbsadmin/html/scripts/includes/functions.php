<?php
/*****************************************************************************
*                                                                           *
* Shop-Script PREMIUM                                                       *
* Copyright (c) 2005 WebAsyst LLC. All rights reserved.                     *
*                                                                           *
*****************************************************************************/
?><?php
//frequently used functions

define( 'STRING_PRODUCT_NAME', 'PREMIUM' );
define( 'STRING_VERSION', '1.26' );
define( 'STRING_COULDNT_REWRITE_FILE', 'cfg....' );

function CallInstallfunctions()
{
	ostInstall();
	catInstall();
	settingInstall();	
	verInstall();
}
function CreateTablesIncFile($TablesIncFfileName, $XmlFileName)
{
	$f = fopen($TablesIncFfileName,"w");
	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( $XmlFileName );
	$array=$xmlTables->SelectNodes( "DataBaseStructure/tables/table" );

	fputs( $f, "<?php\n");
	fputs( $f, "\n");
	foreach( $array as $xmlTable )
	{
		$attrubtes = $xmlTable->GetXmlNodeAttributes();
		fputs( $f, "if (  !defined('".$attrubtes["ALIAS"]."')  ) \n" );
		fputs( $f, "{\n" );
		$s = "	define('".$attrubtes["ALIAS"]."', '".$attrubtes["NAME"]."');";
		fputs( $f, $s."\n" );
		fputs( $f, "\n" );
		fputs( $f, "}\n" );
	}
	fputs( $f, "?>"  );
	fclose( $f);
}

function myfile_get_contents( $fileName )
{
	return implode( "", file($fileName) );
}
function TransformStringToDataBase( $str )
{
	if (is_array($str))
	{
		foreach ($str as $key => $val)
		{
			$str[$key] = stripslashes($val);
		}
		$str = str_replace("\\","\\\\",$str);
	}
	else
	{
		$str = str_replace("\\","\\\\",stripslashes($str));
	}
	return str_replace( "'", "''", $str );
}
function regGetIdByLogin( $login )
{
	$q = ss_db_query("select customerID from ".CUSTOMERS_TABLE.
	" where Login='".xEscapeSQLstring($login)."'");
	if (  ($r=db_fetch_row($q)) )
	return $r["customerID"];
	else
	return NULL;
}
function get_current_time() 	// gets current date and time as a string in MySQL format
{
	return strftime("%Y-%m-%d %H:%M:%S", time());
}
function catInstall()
{
	ss_db_query("insert into ".CATEGORIES_TABLE."( name, parent, categoryID )".
	"values( 'ROOT', NULL, 1 )");
}

// *****************************************************************************
// Purpose
// Inputs
// Remarks
// Returns
function auxpgAddAuxPage( 	$aux_page_name,
$aux_page_text, $aux_page_text_type,
$meta_keywords, $meta_description  )
{
	$aux_page_name		= TransformStringToDataBase( $aux_page_name );
	$meta_keywords		= TransformStringToDataBase( $meta_keywords );
	$meta_description	= TransformStringToDataBase( $meta_description );
	$aux_page_text		= TransformStringToDataBase( $aux_page_text );
	ss_db_query( "insert into ".AUX_PAGES_TABLE.
	" ( aux_page_name, aux_page_text, aux_page_text_type, meta_keywords, meta_description )  ".
	" values( '$aux_page_name', '$aux_page_text', $aux_page_text_type, ".
	" '$meta_keywords', '$meta_description' ) " );
}
/*
function ostInstall()
{
ss_db_query("insert into ".ORDER_STATUES_TABLE.
" ( status_name, sort_order ) ".
" values( 'STRING_CANCELED_ORDER_STATUS', 0 ) ");
}

*/
function verInstall()
{
	ss_db_query("insert into ".SYSTEM_TABLE.
	" ( varName, value ) ".
	" values( 'version_number', '".STRING_VERSION."' ) ");

	ss_db_query("insert into ".SYSTEM_TABLE.
	" ( varName, value ) ".
	" values( 'version_name', '".STRING_PRODUCT_NAME."' ) ");
}

function ostInstall()
{
	ss_db_query("insert into ".ORDER_STATUES_TABLE.
			" ( status_name, sort_order ) ".
			" values( 'STRING_CANCELED_ORDER_STATUS', 0 ) ");
}
	
function settingInstall()
{
	ss_db_query("insert into ".SETTINGS_GROUPS_TABLE.
		" ( settings_groupID, settings_group_name, sort_order ) ".
		" values( ".settingGetFreeGroupId().", 'MODULES', 0 ) " );
}
function settingGetFreeGroupId()
{
	return 1;
}

function verGetPackageVersion()
{
	$q = ss_db_query("select varName, value from ".SYSTEM_TABLE);
	$row = array("");
	while ( $row && strcmp($row[0], "version_number") )
	{
		$row = db_fetch_row($q);
	}
	return (float) $row[1];
}

function verUpdatePackageVersion()
{
	ss_db_query("update ".SYSTEM_TABLE." set value = '".STRING_VERSION."' where varName = 'version_number'");
}

function verUpdatePackageName(){

	ss_db_query("update ".SYSTEM_TABLE." set value = '".STRING_PRODUCT_NAME."' where varName = 'version_name'");
}
//functions from xml_installer.php
function GetXmlTableNodeArray( $fileName )
{
	$xmlTables	= new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( $fileName );
	$array = $xmlTables->SelectNodes( "DataBaseStructure/tables/table" );
	return $array;
}

function GetCreateTableSQL($xmlTable)
{
	$attributes=$xmlTable->GetXmlNodeAttributes();
	$sql = "CREATE TABLE ".trim($attributes["NAME"])." (";
	$array=$xmlTable->SelectNodes("table/column");

	$_indexes = GetIndexesSQL($array);
	if($_indexes) $sql .= $_indexes.',';

	$firstFlag=true;
	$isComplexPrimaryKey = IsComplexPrimaryKey($array);
	foreach($array as $xmlColumn)
	{
		$columnSql=GetColumnSQL($xmlColumn, $isComplexPrimaryKey);
		if ( is_bool($columnSql) )
			return false;
		if ( $firstFlag )
			$sql .= GetColumnSQL($xmlColumn, $isComplexPrimaryKey);
		else
			$sql .= ", ".GetColumnSQL($xmlColumn, $isComplexPrimaryKey);
		$firstFlag = false;
	}
	if ( $isComplexPrimaryKey )
		$sql .= ", ".GetComplexPrimaryKeySQL($array);
	$sql .= ")";
	//$sql .= " TYPE=InnoDB";
	return $sql;
}

/**
 * Return indexes sql-injection
 *
 * @param array $array - columns
 * @return string - sql-injection
 */
function GetIndexesSQL($array){

	$sql = array();
	foreach($array as $xmlColumn)
	{
		$attributes=$xmlColumn->GetXmlNodeAttributes();
		foreach($attributes as $key => $value)
		{
			if ( $key == "INDEX" )
			{
				$value = strtoupper($value);
				$columnName = trim($xmlColumn->GetXmlNodeData());
				$sql[] = '
					KEY '.$value.' (`'.$columnName.'`)';
				break;
			}
		}
	}
	return implode(',', $sql);
}

// Purpose	determine complex primary key fact
// Inputs   array of column node
// Remarks		
// Returns	true if primary key is complex false otherwise
function IsComplexPrimaryKey($array)
{
	$primaryKeyCountPart = 0;
	foreach($array as $xmlColumn)
	{
		$attributes=$xmlColumn->GetXmlNodeAttributes();
		foreach($attributes as $key => $value)
		{
			if ( $key == "PRIMARYKEY" )
			{
				$primaryKeyCountPart++;
				break;
			}
		}
	}
	return ( $primaryKeyCountPart > 1 );
}

// Purpose	parses column node
// Inputs   column node ( that is XmlNode object )
// Remarks		
// Returns	SQL column clause
function GetColumnSQL($xmlColumn, $isComplexPrimaryKey)
{
	
	$attributes=$xmlColumn->GetXmlNodeAttributes();
	$type			= "";
	$nullable		= true;
	$defaultValue	= false;
	$primaryKey		= false;
	$identity		= false;
	
	foreach($attributes as $key => $value)
	{
		$value = strtoupper($value);
		switch( $key )
		{
			case "TYPE" :
				if ( _verifyVarChar($value) )
					$type = GetTypeColumnSQL( $value );
				else if ( _verifyChar($value) )
					$type = GetTypeColumnSQL( $value );
				else if ( 
							$value == "BIT" || $value == "INT" || 
							$value == "DATETIME" || $value == "FLOAT"  ||
							$value == "TEXT" || $value == 'DATE' 
						)
					$type = GetTypeColumnSQL( $value );
				else
				{
					echo( "Unknown datatype ".$value );
					return false;
				}
			break;

			case "NULLABLE" :
				if ( $value=="TRUE" )
					$nullable = true;
				else if ( $value=="FALSE" )
					$nullable = false;
				else 
				{
					echo( "Invalid 'NULLABLE' attribute value '".$value."'" );
					return false;
				}
			break;

			case "DEFAULT" :
				$defaultValue = $value;
			break;

			case "PRIMARYKEY" :
				$primaryKey = true;
			break;

			case "IDENTITY" :
				$identity = true;
				break;
			
			case "INDEX":
				break;

			default :
				echo( "Unknown attribute '".$key."'" );
				return false;
		}
	}
	$columnName = trim($xmlColumn->GetXmlNodeData());
	return GetColumnMYSQL($columnName, $type, 
			$nullable, $primaryKey, $identity, $defaultValue, $isComplexPrimaryKey);
}

function _verifyVarChar($value)
{
	if ( strstr( $value, "VARCHAR" ) )
	{
		$val=str_replace( "VARCHAR", "", $value);
		$val=trim($val);
		if ( $val[0] == '(' && $val[ strlen($val) - 1 ] == ')' )
		{
			$val = str_replace( "(", "", $val );
			$val = str_replace( ")", "", $val );
			$val = (int)$val;
			return !( $val == 0 );
		}
		return false;
	}
}

function _verifyChar($value)
{
	if ( strstr( $value, "CHAR" ) )
	{
		$val=str_replace( "CHAR", "", $value);
		$val=trim($val);
		if ( $val[0] == '(' && $val[ strlen($val) - 1 ] == ')' )
		{
			$val = str_replace( "(", "", $val );
			$val = str_replace( ")", "", $val );
			$val = (int)$val;
			return !( $val == 0 );
		}
		return false;
	}
}

// Inputs   column node ( that is XmlNode object )
// Remarks		
// Returns	SQL column clause
function GetTypeColumnSQL($type)
{
	if ( strstr( $type, "VARCHAR" ) )
		return $type;
	else if ( strstr( $type, "CHAR" ) )
		return $type;
	else{
		return $type;
	}
}

// *****************************************************************************
// Purpose	gets column clause for MYSQL DBMS
// Inputs   
//			$columnName - column name	(string)
//			$type		- data type		(string)
//			$nullable	- true if column is nullable		(bool)
//			$primaryKey	- true if column is primary key		(bool)
//			$identity	- true if column is identity		(bool)
//			$defaultValue - false if column does not have default value 
//			$isComplexPrimaryKey - true if primary key is complex (bool)
// Remarks		
// Returns	SQL column clause
function GetColumnMYSQL($columnName, $type, 
			$nullable, $primaryKey, $identity, $defaultValue, $isComplexPrimaryKey)
{
	$sql = "";
	if ( $nullable )
		$nullableStr = "NULL";
	else
		$nullableStr = "NOT NULL";
	if ( $identity )
		$identityStr = "AUTO_INCREMENT";
	else
		$identityStr = "";
	$defaultValueClause = GetDefaultValueClause($type, $defaultValue);
	if ( $primaryKey && !$isComplexPrimaryKey )
		$sql .= $columnName." ".$type." PRIMARY KEY ".$identityStr;
	else if ( $primaryKey && $isComplexPrimaryKey )
		$sql .= $columnName." ".$type." NOT NULL ".$identityStr;
	else
		$sql .= $columnName." ".$type." ".$nullableStr." ".$identityStr." ".$defaultValueClause;
	return $sql;
}

// *****************************************************************************
// Purpose	gets default value clause
// Inputs   
//			$type		- data type		(string)
//			$defaultValue - false if column does not have default value 
// Remarks		
// Returns	
function GetDefaultValueClause($type, $defaultValue)
{
	if ( is_bool($defaultValue) )
		return "";

		$defaultClauseOpen	= "DEFAULT ";
		$defaultClauseClose = "";
	
	print ( strstr("VARCHAR",strtoupper($type)) );
	if ( strstr("VARCHAR",strtoupper($type)) )
		return $defaultClauseOpen."'".$defaultValue."'".$defaultClauseClose;
	else
		return $defaultClauseOpen.$defaultValue.$defaultClauseClose;
}

// *****************************************************************************
// Purpose	gets primary key clause for complex key
// Inputs   $array is array of column node
// Remarks		
// Returns	
function GetComplexPrimaryKeySQL($array)
{
	$columns = "";
	$firstFlag = true;
	foreach($array as $xmlColumn)
	{
		$attributes=$xmlColumn->GetXmlNodeAttributes();
		foreach($attributes as $key => $value)
		{
			if ( $key == "PRIMARYKEY" )
			{
				if ( $firstFlag )
				{
					$columns .= $xmlColumn->GetXmlNodeData();
					$firstFlag = false;
				}
				else
					$columns .= ", ".$xmlColumn->GetXmlNodeData();
				break;
			}
		}
	}
	return "PRIMARY KEY (".$columns.")";
}

// *****************************************************************************
// Purpose	creates refer constraints corresponded to structure database XML file
// Inputs   file name
// Remarks		
// Returns	SQL script to be shown
function CreateReferConstraintsXML($fileName)
{
	$_SESSION["ForeignKeyIndex"] = 0;

	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile($fileName);
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	$sqlToShow = "<table>";

	// adds "alter table " SQL statements into $sqlToShow
	foreach($array as $xmlTable)
	{
		$sqlArray = GetReferConstraint($xmlTable);
		foreach( $sqlArray as $constraintSql )
		{
			if ( $constraintSql != "" )
			{
				ss_db_query( $constraintSql );
				$sqlToShow .= "<tr><td>".$constraintSql."</td></tr>";
			}
		}
	}

 	unset( $_SESSION["ForeignKeyIndex"] );

	$sqlToShow .= "</table>";
	return $sqlToShow;
}

function GetReferConstraint($xmlTable)
{
	return array();
}


//functions from settings_functions.php
function _setSettingOptionValue( $settings_constant_name, $value )
{
	$value = xEscapeSQLstring( $value );
	ss_db_query("update ".SETTINGS_TABLE." set settings_value='$value' ".
		" where settings_constant_name='$settings_constant_name'" );
}
?>