<?php
//	database functions :: MySQL
function ss_db_query($s){
	static $count = 0;
	$res = array();
	$res["resource"] = mysql_query($s);
	if(!$res['resource']){
		$debug=debug_backtrace();
		$clean_debug='';
		foreach ($debug as $call){
			if(isset($call['args']))unset($call['args']);
			$path=pathinfo($call['file']);
			$clean_debug.=$path['basename'].':'.$call['line'].' '.$call['function'].'<br>';
		}
		throw new Exception("<b>MySQL Error</b> : ".db_error()."<br><b>SQL query</b> : ".htmlentities($s,ENT_QUOTES,'utf-8').'<br><pre>'.$clean_debug.'</pre><br>');
	}
	
	$res["columns"]=array();
	if(!is_bool($res["resource"])){
		$column_index = 0;
		$column_count = mysql_num_fields($res["resource"]);
		while ($column_index < $column_count) {
			$xwer = mysql_fetch_field($res["resource"]);
			$res["columns"][$xwer->name] = $column_index;
			$column_index++;
		}
	}
	return $res;
}

function db_fetch_row($q) //row fetching
{
	$res = mysql_fetch_row($q["resource"]);
	if ( $res )
	{
		foreach( $q["columns"] as $column_name => $column_index )
			$res[$column_name] = $res[$column_index];
	}
	return $res;
}
function db_error() //database error message
{
	return mysql_error();
}

function db_get_all_tables()
{
	$res = array();
	$q=mysql_list_tables();
	while( $row=db_fetch_row($q) )
		$res[] = strtolower($row[0]);
	return $res;
}

/*
function db_get_all_ss_tables( $xmlFileName )
{
	$res = array();
	$tables = db_get_all_tables();
	$xmlNodeTableArray = GetXmlTableNodeArray( $xmlFileName );
	foreach( $xmlNodeTableArray as $xmlNodeTable )
	{
		$attr = $xmlNodeTable->GetXmlNodeAttributes();
		$existFlag = false;
		foreach( $tables as $tableName )
		{
			if ( strtolower($attr["NAME"]) == $tableName )
				$existFlag = true;
		}
		if ( $existFlag )
			$res[] = $attr["NAME"];
	}
	return $res;
}
*/
function db_delete_table( $tableName )
{
	ss_db_query( "drop table ".$tableName );
}
function db_table_exists($tableName)
{
	if(mysql_unbuffered_query('SELECT * FROM '.$tableName)){
		return true;
		
	}else{
		return false;
	}
}

function db_add_column( $tableName, $columnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		ss_db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr.
						" default ".$default );
	else
		ss_db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr );
}

function db_rename_column( $tableName, $oldColumnName, $newColumnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		ss_db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr." default ".$default );
	else
		ss_db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr );
}

function db_delete_column( $tableName, $columnName )
{
	ss_db_query( "alter table ".$tableName." drop column ".$columnName );
}

/**
 * retrieve columns information for table
 *
 * @param string $_TableName
 * @return array
 */
function db_getColumns($_TableName){
	
	$Columns = array();
	$sql = '
		SHOW COLUMNS FROM `'.$_TableName.'`
	';
	$Result = ss_db_query($sql);
	if(!db_num_rows($Result["resource"]))return $Columns;
	while ($_Row = db_fetch_row($Result)){
		
		$Columns[strtolower($_Row['Field'])] = $_Row;
	}
	return $Columns;
}
function ss_db_insert_id(){
	return mysql_insert_id();
}
?>