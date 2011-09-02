<?
/**
 * Interface to database
 *
 */
class DataBase{
	
	var $link;
	var $type;
	var $host;
	var $user;
	var $pass;
	var $database;
	var $result;
	var $last_sql;
	
	/**
	 * Connect to server
	 * 
	 * @param string host
	 * @param string user_name
	 * @param string password
	 * @param string database_type
	 */
	function connect($_host, $_user, $_pass, $_type = 'mysql'){
		$this->type = $_type;
		$this->host = $_host;
		$this->user = $_user;
		switch ($this->type){
			case 'mysql':
				$this->link = mysql_pconnect($_host, $_user, $_pass);
				if(!$this->link) {
					//log unsuccesfull connection 
					if(sc_onWebasystServer()){
						$fpath = WBS_DIR.'/kernel/error_db_connect.log';
						$data = array();
						$data[] = date('Y-m-d H:i:s');
						$data[] = sprintf("%s@%s",$_user,$_pass);
						$data[] = db_getConnectData('DB_KEY');
						$data[] = getenv("REMOTE_ADDR");
						$data[] = "SC";
						if($fp = fopen($fpath,'a')){
							fwrite($fp,sprintf("%s\n",implode(";",$data)));
							fclose($fp);
						}
					}
					die("Error connect to mysql");
				}
				$ServerVersion = $this->getServerVersion();
				if(preg_match('/^5\./',$ServerVersion))$this->query('SET SESSION sql_mode=0');
				if(preg_match('/^5\.|^4\.[1-9]\./',$ServerVersion))$this->query('SET NAMES '.MYSQL_CHARSET);
					$this->query (sprintf("set character_set_client='%s'",MYSQL_CHARSET));
	$this->query (sprintf("set character_set_results='%s'",MYSQL_CHARSET));
	$this->query (sprintf("set collation_connection='%s_general_ci'",MYSQL_CHARSET));
			break;
		}
	}
	
	function selectDB($_dbName){
		
		$this->database = $_dbName;
		$url = str_replace('//','/',WBS_INSTALL_PATH.'/login/');
		switch ($this->type){
			case 'mysql':
				if(!mysql_select_db($this->database, $this->link)) {
					if(isset($_GET['debug']))
					{
						print htmlentities(mysql_error())."<br>";
					}
				$this->printMessage($url,"Couldn't select database");
				}
			break;
		}
		if(!$this->TableExists(DIVISIONS_TBL)){
			
			$this->printMessage($url);

		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $_sql
	 * @return DBResource
	 */
	function query ($_sql){
		
/*
@features My
*/
if(isset($_GET['debug'])&&$_GET['debug']=='time'){
	
	$this->last_sql = $_sql;
	$res = db_query($_sql);
	$this->result = $res["resource"];
	return new DBResource($this->result, $this->type, $this->link, $this->last_sql);
	
	ClassManager::includeClass('timer');
	static $cnt=0;
	$cnt++;
	$T = new Timer();
	$T->timerStart();
}
/*
@features
*/
		$this->last_sql = $_sql;
		
		switch ($this->type){
			case 'mysql':
				mysql_select_db($this->database,$this->link);
				$this->result = mysql_query($this->last_sql, $this->link);
				if(!$this->result){
					pear_handler();
					pear_handler(PEAR::raiseError("Error executing query: ".$this->last_sql.'<br />'.mysql_error($this->link)));
					die("Error executing query: ".$this->last_sql.'<br />'.mysql_error($this->link));
				}
			break;
		}
/*
@features My
*/
if(isset($_GET['debug'])&&$_GET['debug']=='time'){
	
	print '<br /><span onclick="document.getElementById(\'sqldebug__'.$cnt.'\').style.display=document.getElementById(\'sqldebug__'.$cnt.'\').style.display!=\'block\'?\'block\':\'none\'">=</span>'.sprintf('%03d',$cnt).' - <strong>'.$T->timerStop().'</strong> - '.xHtmlSpecialChars($this->last_sql);
	ob_start();
	print_r(debug_backtrace());
	$_t=preg_replace('/(\[function\]\s+\=\>\s+)([^\n]+)/msi','$1<span style=color:red;>$2</span>','<pre>'.xHtmlSpecialChars(ob_get_contents()).'</pre>');
	ob_end_clean();
	?>
	<div id="sqldebug__<?=$cnt;?>" style="display:none;">
	<?php
	echo $_t;
	?>
	</div>
	<?php
}
/*
@features
*/
		return new DBResource($this->result, $this->type, $this->link, $this->last_sql);
	}
	
	/**
	 * place holder sintax based function
	 * @return DBResource
	 */
	function ph_query(){
		
		$args = func_get_args();
		$tmpl = array_shift($args);
		$error = '';
		$sql = sql_placeholder_ex($tmpl, $args, $error);
		if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
		
		return $this->query($sql);
	}
	
	function ph_fetch(){
		
		$args = func_get_args();
		$tmpl = array_shift($args);
		$error = '';
		$sql = sql_placeholder_ex($tmpl, $args, $error);
		if ($sql === false) $sql = PLACEHOLDER_ERROR_PREFIX.$error;
		return $sql;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function fetch_row (){
		
		switch ($this->type){
			case 'mysql':
				return mysql_fetch_row($this->result);
			break;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function fetch_assoc (){
		
		switch ($this->type){
			case 'mysql':
				return mysql_fetch_assoc($this->result);
			break;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function num_rows (){
		
		switch ($this->type){
			case 'mysql':
				return mysql_num_rows($this->result);
			break;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	function insert_id (){
		
		return mysql_insert_id($this->link);
	}

	function getInsertedID(){
		
		return $this->insert_id();
	}
	
	function createTableFromXMLObject(&$xmlTable){
		
		$xmlFields = $xmlTable->xPath('/table/field');
		$sql = 'CREATE TABLE `'.$xmlTable->getAttribute('name').'` (';
		$TC = count($xmlFields);
		for ($j=0;$j<$TC;$j++){
			
			$sql .= ($j?',':'').'`'.$xmlFields[$j]->getAttribute('name').'` '.$xmlFields[$j]->getAttribute('type').
				(!is_null($xmlFields[$j]->getAttribute('length'))?'('.$xmlFields[$j]->getAttribute('length').')':'').
				(!is_null($xmlFields[$j]->getAttribute('attribute'))?' '.$xmlFields[$j]->getAttribute('attribute'):'').
				(!is_null($xmlFields[$j]->getAttribute('null'))?' '.$xmlFields[$j]->getAttribute('null'):' not null').
				(!is_null($xmlFields[$j]->getAttribute('extra'))?' '.$xmlFields[$j]->getAttribute('extra'):'');
		}
		$xmlIndexes = $xmlTable->xPath('/table/index');
		$TC = count($xmlIndexes);
		for ($j=0;$j<$TC;$j++){
			
			$sql .= ','.$xmlIndexes[$j]->getAttribute('type').' `'.$xmlIndexes[$j]->getAttribute('name').'`('.$xmlIndexes[$j]->getAttribute('fields').')';
		}
		$sql .= ')';
		$this->query($sql);
	}
	
	function getAllTables(){
		
		$Result = $this->query('show tables');
		$Tables = array();
		while($_Row = $Result->fetchRow())$Tables[] = strtolower($_Row[0]);
		return $Tables;
	}
	
	function TableExists($_s_TableName){
		
		return $this->isTableCreated($_s_TableName);
	}
	
	function isTableCreated($_TableName){
		
		/* @var $Result DBResource */
		$Result = $this->query('SHOW TABLES LIKE "'.xEscapeSQLstring($_TableName).'"');
		if($Result->getNumRows())return true;
		else false;
	}
	
	function dropTable($_s_TableName){
		
		$this->query('DROP TABLE '.xEscapeSQLstring($_s_TableName));
	}
	
	function getServerVersion(){

		$result = $this->query('SHOW VARIABLES LIKE "VERSION"');
		$data = $result->fetchRow();
		if(isset($data[1])){
			return $data[1];
		}else return 0;
	}

	function affectedRows(){
		
		switch ($this->type){
			case 'mysql':
				return mysql_affected_rows($this->link);
			break;
		}
	}
	function printMessage($url,$msg = '')
	{
			if(sc_onWebasystServer()){
				header("HTTP/1.0 404 Not Found");
				die($msg);
			}
			print '<html><head><title>Error</title>
					<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"></head><body>';
			if($msg) print "<p>{$msg}</p>";
			print '<br><b>Your online store is not yet installed.</b><br><br> To activate your installation simply <a href="'.$url.'">login to your WebAsyst account</a> &mdash; this will complete your storefront setup (if you have WebAsyst Shoping Cart application installed).';
			print '</body></html>';
			die;
	}
	function freeResult()
	{
		return mysql_free_result($this->link);
	}
}
?>