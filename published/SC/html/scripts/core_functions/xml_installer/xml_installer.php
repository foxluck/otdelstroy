<?php
// *****************************************************************************
// Purpose	get array xml node corresponded to data base table 
// Inputs   file name
// Remarks		
// Returns	see 'Purpose'
function GetXmlTableNodeArray( $fileName )
{
	$xmlTables	= new XmlNode();
	$xmlTables->LoadInnerXmlFromFile( $fileName );
	$array = $xmlTables->SelectNodes( "DataBaseStructure/tables/table" );
	return $array;
}


// *****************************************************************************
// Purpose  call install functions such as ostInstall and etc.
// Inputs   nothing
// Remarks		
// Returns  nothing
function CallInstallFunctions()
{
	ostInstall();
	catInstall();
	settingInstall();	
	verInstall();
}

// *****************************************************************************
// Purpose	creates tables corresponded to structure database XML file
// Inputs   file name
// Remarks		
// Returns	SQL script to be shown
function CreateTablesStructureXML($fileName)
{
	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile($fileName);
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	$sqlToShow="<table>";

	// adds "create table" SQL statements into $sql
	foreach($array as $xmlTable)
	{
		$tableSql = GetCreateTableSQL($xmlTable);
		if ( is_bool($tableSql) )
			return "ERROR";
		else
		{
			$sqlToShow .= "<tr><td>".GetIB_IdentityGenerator( $xmlTable );
			db_query( $tableSql );
			$sqlToShow .= $tableSql;
			$sqlToShow .= GetIB_IdentityTrigger( $xmlTable );
			$sqlToShow .= "</td></tr>";
		}
	}
	$sqlToShow .= "</table>";

	// install functions
	CallInstallFunctions();

	return $sqlToShow;
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
				db_query( $constraintSql );
				$sqlToShow .= "<tr><td>".$constraintSql."</td></tr>";
			}
		}
	}

 	unset( $_SESSION["ForeignKeyIndex"] );

	$sqlToShow .= "</table>";
	return $sqlToShow;
}

// *****************************************************************************
// Purpose	creates refer constraints corresponded to structure database XML file
// Inputs   file name
// Remarks		
// Returns	SQL script to be shown
function DestroyReferConstraintsXML($fileName)
{
	if ( DBMS == "mysql" || DBMS == "ib" )
		return;

	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile($fileName);
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	$sqlToShow = "<table>";

	foreach($array as $xmlTable)
	{
		$attr = $xmlTable->GetXmlNodeAttributes();
		$tableName = $attr["NAME"];
		$foreignKeys = $xmlTable->SelectNodes("table/ForeignKey");
		foreach( $foreignKeys as $foreignKey )
		{
			$attributes = $foreignKey->GetXmlNodeAttributes();
			$splitAttr	= explode( ".", $attributes["REFERTO"] );
			$constraintName = GetForeignKeyName( $tableName, $foreignKey );
			$sql =
				"ALTER TABLE ".$tableName.
				" DROP CONSTRAINT ".$constraintName;
			db_query( $sql );
			$sqlToShow .= "<tr><td>".$sql."</td></tr>";
		}
	}

	$sqlToShow .= "</table>";
	return $sqlToShow;
}


// *****************************************************************************
// Purpose	creates tables.inc.php with define directive for each database table 
//				defined in database XML file
// Inputs   
//			$TablesIncFfileName - tables.inc.php in cfg directory
//			$XmlFileName		- database XML file name
// Remarks		
//			forech table in XML file this function writes define directive
//				define('<table_alias>', '<table_name>');
//				where 
//					<table_alias> correspondes alias attribute of table node
//					<table_name>  correspondes name attribute of table node
// Returns	nothing
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


function CompareArrays($columns1, $columns2)
{
	if (  count($columns1) != count($columns2) )
		return false;
	for($i=0; $i < count($columns2); $i++)
		if ( trim($columns1[$i]) != trim($columns2[$i]) )
			return false;
	return true;
}



function GetForeignKeyName( $tableName, $xmlForeignKey )
{
	if ( DBMS == "mssql" )
	{
		$attrubtes = $xmlForeignKey->GetXmlNodeAttributes();
		$data	   = $xmlForeignKey->GetXmlNodeData();
		$splitAttr = explode( ".", $attrubtes["REFERTO"] );

		$foreignKey = trim($data);
		$array = explode( ",", $foreignKey);
		if ( count($array) != 1 )
		{
			$foreignKey = "";
			foreach( $array as $val )
				$foreignKey .= trim($val);			
		}

		$primaryKey = $splitAttr[1];
		$array = explode( ",", $primaryKey );
		if ( count($array) != 1 )
		{
			$primaryKey = "";
			foreach( $array as $val )
				$primaryKey .= trim($val);
		}

		$constraintName = trim($tableName)."___".trim($splitAttr[0])."_".
				$foreignKey."_".$primaryKey;
		return $constraintName;
	}
	else
		return "FK_".$_SESSION["ForeignKeyIndex"]."ID";
}


// *****************************************************************************
// Purpose	gets refer constraints
// Inputs   table node ( that is XmlNode object )
// Remarks		
// Returns	array of SQL "alter table ... add constraint ... foreign key" 
//				statement to be executed
function GetReferConstraint($xmlTable)
{
	if ( DBMS == "mysql" ) return array();
	if ( DBMS == "ib" ) return array();
	$attrubtes = $xmlTable->GetXmlNodeAttributes();
	$tableName = $attrubtes["NAME"];
	$array=$xmlTable->SelectNodes("table/ForeignKey");
	$sqlArray = array();
	foreach( $array as $xmlForeignKey )
	{
		$attrubtes = $xmlForeignKey->GetXmlNodeAttributes();
		$data	   = $xmlForeignKey->GetXmlNodeData();
		$splitAttr = explode( ".", $attrubtes["REFERTO"] );
		$constraintID = "";

		$constraintName = GetForeignKeyName( $tableName, $xmlForeignKey );

		if ( DBMS == "mysql" )
		{
			$constraintID = "ForeignKey".$_SESSION["ForeignKeyIndex"]."ID";
			$_SESSION["ForeignKeyIndex"] ++;
		}
		if ( DBMS != "ib" )
		{
			$sql = " ALTER TABLE ".$tableName." ADD CONSTRAINT ".
				$constraintName." FOREIGN KEY ".
				$constraintID." ".
				"( ".
						$data.
				") REFERENCES ".$splitAttr[0]."  ".
				"(".
					$splitAttr[1].
				")";
			$sqlArray[] = $sql;
		}
		else
		{
			$sql = "";
/*
			$columns1 = explode(",", $data);
			$columns2 = explode(",", $splitAttr[1]);
			if ( !CompareArrays($columns1, $columns2) )
				return "";
			$constraintName = substr( $tableName."___".$splitAttr[0], 0, 31 );
			$sql .= "ALTER TABLE ".$tableName." ADD CONSTRAINT ".
				$constraintName." FOREIGN KEY ".
				"( ".
					$data.
				") REFERENCES ".$splitAttr[0];
*/
		}
	}
	return $sqlArray;
}


// *****************************************************************************
// Purpose	gets InterBase SQL statement to generate identity generator
// Inputs   $xmlTable - table XML node
// Remarks	
// Returns	SQL code to be executed
function GetIB_IdentityGenerator( $xmlTable )
{
	if ( DBMS != "ib" )
		return "";

	$attrubtes = $xmlTable->GetXmlNodeAttributes();
	$tableName = $attrubtes["NAME"];
	$array=$xmlTable->SelectNodes("table/column");

	$sql = "";
	foreach($array as $xmlColumn)
	{
		$attributes = $xmlColumn->GetXmlNodeAttributes();
		$columnName = trim($xmlColumn->GetXmlNodeData());
		if ( isset( $attributes["IDENTITY"] ) )
			if ( $attributes["IDENTITY"] == "true" )
			{
				$generatorName = $tableName."_".$columnName."_GEN";
				$generatorName = substr( $generatorName, 0, 31 );
				$createGeneratorSQL = "CREATE GENERATOR ".$generatorName." ";
				db_query( $createGeneratorSQL );
				$sql .= $createGeneratorSQL;
			}
	}
	return $sql;
}


// *****************************************************************************
// Purpose	gets InterBase SQL statement to generate identity trigger
// Inputs   $xmlTable - table XML node
// Remarks		
// Returns	SQL code to be executed
function GetIB_IdentityTrigger( $xmlTable )
{
	if ( DBMS != "ib" )
		return "";

	$attrubtes = $xmlTable->GetXmlNodeAttributes();
	$tableName = $attrubtes["NAME"];
	$array=$xmlTable->SelectNodes("table/column");

	$sql = "";
	foreach($array as $xmlColumn)
	{
		$attributes = $xmlColumn->GetXmlNodeAttributes();
		$columnName = trim($xmlColumn->GetXmlNodeData());
		if ( isset( $attributes["IDENTITY"] ) )
			if ( $attributes["IDENTITY"] == "true" )
			{
				$generatorName = $tableName."_".$columnName."_GEN";
				$generatorName = substr( $generatorName, 0, 31 );
				$triggerName = $tableName."_NEW";
				$triggerName = substr( $triggerName, 0, 31 );
				$createTriggerSQL =
				        "CREATE TRIGGER ".$triggerName." FOR ".$tableName.
						" ACTIVE ".
						"BEFORE INSERT POSITION 0 AS ".
						"begin ".
						"		if (new.".$columnName." is null) ".
						"		then new.".$columnName." = gen_id(".$generatorName.", 1);".
						"end ";
				db_query($createTriggerSQL);
				$sql .= $createTriggerSQL;
			}
	}
	return $sql;
}


// *****************************************************************************
// Purpose	parses table node
// Inputs   table node ( that is XmlNode object )
// Remarks		
// Returns	SQL "create table" statement to be executed
function GetCreateTableSQL($xmlTable)
{
	$attributes=$xmlTable->GetXmlNodeAttributes();
	$sql = "CREATE TABLE ".trim($attributes["NAME"])." (";
	$array=$xmlTable->SelectNodes("table/column");

	if ( DBMS == "mysql" ){
		
		$_indexes = GetIndexesSQL($array);
		if($_indexes) $sql .= $_indexes.',';
	}

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
	if ( DBMS == "mysql" ){
	
		$sql .= " TYPE=InnoDB";
		$ServerVersion = db_get_server_version();
		if(preg_match('/^5\.|^4\.[1-9]\./',$ServerVersion))$sql .= ' CHARACTER SET '.MYSQL_CHARSET;
	}
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


// *****************************************************************************
// Purpose	
// Inputs   column node ( that is XmlNode object )
// Remarks		
// Returns	SQL column clause
function GetTypeColumnSQL($type)
{
	if ( strstr( $type, "VARCHAR" ) )
		return $type;
	else if ( strstr( $type, "CHAR" ) )
		return $type;
	else if ( $type == "FLOAT" )
	{
		if ( DBMS == "ib" )
			return "double precision";
		else
			return "FLOAT";
	}
	else if ( $type == "INT" )
	{
		if ( DBMS == "ib" )
			return "INTEGER";
		else
			return $type;
	}
	else if ( $type == "BIT" )
	{
		if ( DBMS == "ib" )
			return "INTEGER";
		else
			return 'BOOL';
	}
	else if ( $type == "DATETIME" )
	{
		if ( DBMS == "ib" )
			return "TIMESTAMP";
		else
			return $type;
	}
	else if ( $type == "TEXT" )
	{
		if ( DBMS == "ib" )
			return "VARCHAR(8192)";
		else
			return $type;
	}
	else if ( $type == "DATE" )
	{
		if ( DBMS == "ib" )
			return "TIMESTAMP";
		else
			return $type;
	}
}


// *****************************************************************************
// Purpose	
// Inputs   
// Remarks		
// Returns	
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

// *****************************************************************************
// Purpose	
// Inputs   
// Remarks		
// Returns	
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


// *****************************************************************************
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

			case 'ML':
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



// *****************************************************************************
// Purpose	gets column clause for IB DBMS
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
function GetColumnIB_SQL($columnName, $type, 
			$nullable, $primaryKey, 
			$identity, $defaultValue, $isComplexPrimaryKey)
{
	$sql = "";
	if ( $nullable )
		$nullableStr = "";
	else
		$nullableStr = "NOT NULL";
	$defaultValueClause = GetDefaultValueClause($type, $defaultValue);
	if ( $primaryKey && !$isComplexPrimaryKey )
		$sql .= $columnName." ".$type." NOT NULL PRIMARY KEY ";
	else if ( $primaryKey && $isComplexPrimaryKey )
		$sql .= $columnName." ".$type." NOT NULL ";
	else
		$sql .= $columnName." ".$type." ".$nullableStr." ".$defaultValueClause;
	return $sql;
}


// *****************************************************************************
// Purpose	gets column clause for MS SQL Server DBMS
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
function GetColumnMSSQL($columnName, $type, 
			$nullable, $primaryKey, $identity, $defaultValue, $isComplexPrimaryKey)
{
	$sql = "";
	if ( $nullable )
		$nullableStr = "NULL";
	else
		$nullableStr = "NOT NULL";
	if ( $identity )
		$identityStr = "IDENTITY(1,1)";
	else
		$identityStr = "";
	$defaultValueClause = GetDefaultValueClause($type, $defaultValue);
	if ( $primaryKey && !$isComplexPrimaryKey )
		$sql .= $columnName." ".$type." PRIMARY KEY ".$identityStr;
	else if ( $primaryKey && $isComplexPrimaryKey )
		$sql .= $columnName." ".$type." ".$identityStr;
	else
		$sql .= $columnName." ".$type." ".$nullableStr." ".$identityStr." ".$defaultValueClause;
	return $sql;
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
	if ( DBMS == "mysql" || DBMS == "ib" )
	{
		$defaultClauseOpen	= "DEFAULT ";
		$defaultClauseClose = "";
	}
	else
	{
		$defaultClauseOpen	= "DEFAULT(";
		$defaultClauseClose = ")";
	}
	if ( strstr("VARCHAR",strtoupper($type)) )
		return $defaultClauseOpen."'".$defaultValue."'".$defaultClauseClose;
	else
		return $defaultClauseOpen.$defaultValue.$defaultClauseClose;
}


function GetColumnDataType( $columnName, $tableName, $fileName )
{
	$xmlTables=new XmlNode();
	$xmlTables->LoadInnerXmlFromFile($fileName);
	$array=$xmlTables->SelectNodes("DataBaseStructure/tables/table");
	foreach($array as $xmlTable)
	{
		$attr = $xmlTable->GetXmlNodeAttributes();
		$tableName = $attr["NAME"];
		if ( trim($tableName) == trim($tableName) )
		{
			$arrayColumn = $xmlTable->SelectNodes("table/column");
			foreach( $arrayColumn as $xmlColumn )
			{
				if ( trim($xmlColumn->GetXmlNodeData()) == trim($columnName) )
				{
					$attributes = $xmlColumn->GetXmlNodeAttributes();
					return strtoupper( $attributes["TYPE"] );
				}
			}
		}
	}
	return false;
}




// *****************************************************************************
// Purpose	upgrades install file 
// Inputs   
//				$xmlFileName	- source xml file ( database_structure.xml  )
//				$tableIncFile	- tables.inc.php ( it is modified by Shop Script owner )
//				$targetFile		- target ( result ) file
// Remarks	Shop Script owner can change table name, 
//				this function change table names in XML file
// Returns	
function ReWriteInstallXmlFile( $xmlFileName, $tableIncFile, $targetFile )
{
	include( $tableIncFile );
	$xmlTableNodeArray = GetXmlTableNodeArray( $xmlFileName );

	$f = fopen( $targetFile, "w" );
	fwrite( $f, "<DataBaseStructure ApplicationVersion='Shop-Script 2.0'>\n" );
	fwrite( $f, "\n" );
	fwrite( $f, "\t<tables>\n" );

		
 	foreach( $xmlTableNodeArray as $xmlTableNode )
	{
		$attributes = $xmlTableNode->GetXmlNodeAttributes();
		$xmlTableDefinition = "";
		if ( defined($attributes["ALIAS"]) )
			$xmlTableDefinition .= "\t\t<table name='".constant($attributes["ALIAS"]).
					"' alias='".$attributes["ALIAS"]."' ";
		else
			$xmlTableDefinition .= "\t\t<table name='".$attributes["NAME"].
					"' alias='".$attributes["ALIAS"]."' ";

		foreach( $attributes as $key => $val )
		{
			if ( $key != "ALIAS" && $key != "NAME" )
				$xmlTableDefinition .= " ".$key."='".$val."' ";
		}
		$xmlTableDefinition .= "> \n";

		$xmlTableColumnNodeArray = $xmlTableNode->SelectNodes( "table/column" );
		foreach( $xmlTableColumnNodeArray as $xmlTableColumnNode )
		{
			$xmlTableDefinition .= "\t\t\t<column ";
			$attributes = $xmlTableColumnNode->GetXmlNodeAttributes();
			foreach( $attributes as $key => $value )
			{
				$xmlTableDefinition .= $key;
				$xmlTableDefinition .= "=";
				$xmlTableDefinition .= "'$value' ";
			}
			$xmlTableDefinition .= ">";
			$columnName = $xmlTableColumnNode->GetXmlNodeData();
			$xmlTableDefinition .= trim($columnName);
			$xmlTableDefinition .= "</column>\n";
		}

		$xmlForeignKeyNodeArray = $xmlTableNode->SelectNodes( "table/ForeignKey" );
		foreach( $xmlForeignKeyNodeArray as $xmlForeignKeyNode )
		{
			$xmlTableDefinition .= "\t\t\t<ForeignKey ";
			$attributes = $xmlForeignKeyNode->GetXmlNodeAttributes();
			foreach( $attributes as $key => $value )
			{
				$xmlTableDefinition .= $key;
				$xmlTableDefinition .= "=";
				$xmlTableDefinition .= "'$value' ";
			}
			$xmlTableDefinition .= ">";
			$foreignKeyColumnName = $xmlForeignKeyNode->GetXmlNodeData();
			$xmlTableDefinition .= trim($foreignKeyColumnName);
			$xmlTableDefinition .= "</ForeignKey>\n";
		}
		$xmlTableDefinition .= "\t\t</table>\n";
		fwrite( $f, $xmlTableDefinition );
		fwrite( $f, "\n" );
	}


	fwrite( $f, "\t</tables>\n" );
	fwrite( $f, "</DataBaseStructure>" );
	fclose( $f );
}

function updateTablesStructure(){
	
	$xmlDatabase = file_get_contents(DATABASE_STRUCTURE_XML_PATH);
	
	$xmlTree = new XmlNodeX();
	$xmlTree->renderTreeFromInner($xmlDatabase);
	
	$TableNodes = $xmlTree->xPath('/DataBaseStructure/tables/table');
	
	$_TC = count($TableNodes)-1;
	
	$ExistedTables  = db_get_all_tables();
	
	for (;$_TC>=0;$_TC--){
		
		$TableNode = &$TableNodes[$_TC];
		if(!defined($TableNode->getAttribute('alias'))){
			
			define($TableNode->getAttribute('alias'), $TableNode->getAttribute('name'));
		}
		$TableName = constant($TableNode->getAttribute('alias'));
		
//		if(!in_array(strtolower($TableName), $ExistedTables))continue;
		
		$Columns = db_getColumns($TableName);
		$ColumnNodes = $TableNode->xPath('/table/column');
		
		$_j = count($ColumnNodes)-1;
		for(;$_j>=0;$_j--){
			
			if(!isset($Columns[strtolower($ColumnNodes[$_j]->getData())])){
//				print $ColumnNodes[$_j]->getData().'<br />';
//				print '<pre>';
//				print_r($Columns);
//				print '</pre>';
				$Nullable = is_null($ColumnNodes[$_j]->getAttribute('NULLABLE')) || strtolower($ColumnNodes[$_j]->getAttribute('NULLABLE')!='false');
				db_add_column($TableName, $ColumnNodes[$_j]->getData(), $ColumnNodes[$_j]->getAttribute('TYPE'), $ColumnNodes[$_j]->getAttribute('DEFAULT'), $Nullable);
			}
		}
	}
}
?>