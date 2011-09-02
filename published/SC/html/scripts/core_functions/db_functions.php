<?php
define('MYSQL_CHARSET','UTF8');

define('DBRFETCH_FIRST', '0');
define('DBRFETCH_ROW', '1');
define('DBRFETCH_ASSOC', '2');
define('DBRFETCH_ROW_ALL', '4');
define('DBRFETCH_ASSOC_ALL', '8');
define('DBRFETCH_FIRST_ALL', '16');

include_once(DIR_FUNC."/placeholders_functions.php" );

function db_connect($host,$user,$pass) //create connection
{
	$r = mysql_connect($host,$user,$pass);
	if(preg_match('/^5\./',mysql_get_server_info($r)))db_query('SET SESSION sql_mode=0');
	return $r;
}

function db_select_db($name) //select database
{
	return mysql_select_db($name);
}

function db_query($s,$ignore_error = false) //database query
{
	
	if(isset($_COOKIE['debug'])&&(in_array($_COOKIE['debug'],array('sql','log')))){
		$time = microtime(true);
	}else{
		$time = false;
	}

	$res = array();
	$res["resource"] = mysql_query($s);
	global $debug_total_sql_query;
	++$debug_total_sql_query;

	if($time){
		$time = microtime(true)-$time;
		global $debug_sql_query_stack;
		if(!is_array($debug_sql_query_stack)){
			$debug_sql_query_stack = array();
			$debug_sql_query_stack[] = array(
				'#'=>'#',
				'time'=>'time, ms',
				'query'=>'query',
			);
		}
		$debug_sql_query_stack[] = array(
			'#'=>$debug_total_sql_query,
			'time'=>sprintf('%01.2f',$time*1000),
			'query'=>$s,
		);
	}
	
	if(!$res['resource']&&!$ignore_error){
		$err = db_error();
	//	throw new Exception($err);
		print $err;
		print "<hr>".htmlentities($s);
		
		PEAR::raiseError($err);
		die;
	}
	
	$res["columns"]=array();
	$column_index = 0;
	
	if(is_resource($res["resource"])){
		
		while($xwer = mysql_fetch_field($res["resource"])){
			
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

function db_fetch_assoc($Result){
	
	return mysql_fetch_assoc($Result['resource']);
}

function db_insert_id($gen_name = "") //id of last inserted record
				//$gen_name is used for InterBase
{
	return mysql_insert_id();
}

function db_error() //database error message
{
	return mysql_error();
}

function db_get_all_tables()
{
	$q = db_query( "show tables" );
	$res = array();
	while( $row=db_fetch_row($q) )
		$res[] = strtolower($row[0]);
	return $res;
}

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

function db_delete_table( $tableName )
{
	db_query( "drop table ".$tableName );
}

function db_delete_all_tables()
{
	$tableArray = db_get_all_tables();
	foreach( $tableArray as $tableName )
		db_query( "drop table ".$tableName );
}

function db_add_column( $tableName, $columnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr.
						" default ".$default );
	else
		db_query( "alter table ".$tableName." add column ".$columnName." $type ".$nullableStr );
}

function db_rename_column( $tableName, $oldColumnName, $newColumnName, $type, $default, $nullable )
{
	if ( $nullable )
		$nullableStr = " NULL ";
	else
		$nullableStr = " NOT NULL ";
	if ( $default != null )
		db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr." default ".$default );
	else
		db_query( "alter table ".$tableName." change ".$oldColumnName." ".
				$newColumnName." ".$type." ".$nullableStr );
}

function db_delete_column( $tableName, $columnName, $check = false ){
	
	if($check){
		
		$column = db_getColumn($tableName, $columnName);
		if(is_null($column))return false;
	}
	db_query( "alter table ".$tableName." drop column ".$columnName );
	
	return true;
}

/**
 * return number of rows in result after sql-query
 *
 * @param resource $_result
 * @return integer
 */
function db_num_rows($_result){

	return mysql_num_rows($_result);
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
	$Result = db_query($sql);
	if(!db_num_rows($Result["resource"]))return $Columns;
	while ($_Row = db_fetch_row($Result)){
		
		$Columns[strtolower($_Row['Field'])] = $_Row;
	}
	return $Columns;
}

function db_getColumn($table_name, $column_name){

	$column = null;
	$Result = db_query('SHOW COLUMNS FROM `'.$table_name.'`');
	if(!db_num_rows($Result["resource"]))return $column;
	
	while ($row = db_fetch_assoc($Result)){
		
		if($row['Field'] != $column_name)continue;
		
		return $row;
	}
	return $column;
}

/**
 * Return table indexes
 * 
 * @param string $table_name
 * @return array - in row: Key_name Column_name
 */
function db_getIndexes($table_name){
	
	$indexes = array();
	$dbres = db_query('SHOW INDEXES FROM `'.$table_name.'`');
	
	if(!db_num_rows($dbres['resource']))return $indexes;
	
	while($row = db_fetch_assoc($dbres)){
		
		$indexes[] = $row;
	}
	return $indexes;
}

/**
 * Return table index
 * 
 * @param string $table_name
 * @param string $key_name - index key name
 * @return array - Key_name Column_name
 */
function db_getIndexByKey($table_name, $key_name){
	
	$index = null;
	$dbres = db_query('SHOW INDEXES FROM `'.$table_name.'`');
	
	if(!db_num_rows($dbres['resource']))return $index;
	
	while($row = db_fetch_assoc($dbres)){
		
		if($row['Key_name']!=$key_name)continue;
		return $row;
	}
	return $index;
}

/**
 * Add index for columns
 * 
 * @param string $table_name
 * @param string $columns - columns with comma delimiter
 */
function db_addIndex($table_name, $columns){

	$columns = explode(',', $columns);
	foreach ($columns as $_i=>$_column){
		$columns[$_i] = trim($_column);
	}
	
	if(1){//Check existing indexes
		
		$indexes = db_getIndexes($table_name);
		$indexes_found_columns = array();
		foreach ($indexes as $_index){
			if(in_array($_index['Column_name'], $columns))$indexes_found_columns[$_index['Key_name']] = isset($indexes_found_columns[$_index['Key_name']])?$indexes_found_columns[$_index['Key_name']]+1:1;
		}
		
		if(in_array(count($columns), $indexes_found_columns))return false;
	}
	
	db_query('ALTER TABLE `'.$table_name.'` ADD INDEX ( `'.implode('`,`', $columns).'` )');
	return true;
}

function db_phquery(){
	
	$args = func_get_args();
	$tmpl = array_shift($args);
	$sql = sql_placeholder_ex($tmpl, $args, $error);
	if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
	return db_query($sql);
}

function db_phquery_array(){
	
	$args = func_get_args();
	$tmpl = array_shift($args);
	$args_clean=array();
	
	foreach($args as $value)
	{
		if(is_array($value))
		$args_clean=array_merge($args_clean,$value);
		else
		$args_clean[]=$value;
	}
	$sql = sql_placeholder_ex($tmpl, $args_clean, $error);
	if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
	return db_query($sql);
}

function db_phfetch_query(){
	
	$args = func_get_args();
	$tmpl = array_shift($args);
	$sql = sql_placeholder_ex($tmpl, $args, $error);
	return $sql;
}

/**
 * Execute query and fetch results
 *
 * @param int $fetch_type - DBRFETCH_*
 * @param string $dbq - query
 */
function db_phquery_fetch(){
	
	$args = func_get_args();
	$fetch_type = array_shift($args);
	$dbq = array_shift($args);
	$sql = sql_placeholder_ex($dbq, $args, $error);
	if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
	$dbres = db_query($sql);
	
	$result = null;
	
	switch ($fetch_type){
		case DBRFETCH_FIRST:
			$result = db_fetch_row($dbres);
			$result = isset($result[0])?$result[0]:'';
			break;
		case DBRFETCH_ROW:
			$result = db_fetch_row($dbres);
			break;
		case DBRFETCH_ASSOC:
			$result = db_fetch_assoc($dbres);
			break;
		case DBRFETCH_FIRST_ALL:
			$result = array();
			while ($row = db_fetch_row($dbres))$result[] = isset($row[0])?$row[0]:'';
			break;
		case DBRFETCH_ROW_ALL:
			$result = array();
			while ($row = db_fetch_row($dbres))$result[] = $row;
			break;
		case DBRFETCH_ASSOC_ALL:
			$result = array();
			while ($row = db_fetch_assoc($dbres))$result[] = $row;
			break;
	}
	
	db_free_result($dbres['resource']);
	return $result;
}

function db_table_exists($table){
	
	/*$dbres = db_query('SHOW TABLES LIKE "'.xEscapeSQLstring($table).'"');
	return db_num_rows($dbres['resource'])>0;*/
	$dbres = db_query('SELECT 1 FROM `'.xEscapeSQLstring($table).'` LIMIT 1',true);
	return $dbres['resource']?true:false;
}

function db_free_result($resource){
	if(is_resource($resource)){
		mysql_free_result($resource);
	}
}

/**
 * return number of fetched rows after sql-query
 *
 * @return integer
 */
function db_affected_rows(){

	return mysql_affected_rows();
}
?>