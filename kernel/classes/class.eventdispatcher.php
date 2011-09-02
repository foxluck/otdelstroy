<?php

class CronEventDispatcher {
  private static $instance;
  private static $_db;
 
  private function __construct() {}
  private function __clone() {}
    
  public static function getInstance() {
    if (self::$instance === null) {
      self::$instance = new self;
    }
    
    if (PEAR::isError($res = self::dbcon()))
    	return $res;
    	
    return self::$instance;
  }
  
  /**
  * Reading config and connect to database
  */
  private function dbcon() {
	/**
	 * Config doesn't exists
	 */ 
	if (!file_exists(WBS_DIR . "kernel/wbs.xml"))
	{
		return PEAR::raiseError( 'ERROR: File wbs.xml doesn\'t exist', ERRCODE_APPLICATION_ERR );
	} 
  	
  	/**
  	 * Read XML config
  	 */
  	$xml = file_get_contents (WBS_DIR . "kernel/wbs.xml");
  	$sxml = new SimpleXMLElement($xml);  	
  	/**
  	 * COMMONLOGBASE section doesn't exists or parameters not set
  	 */
	if (sizeof((array)$sxml->COMMONLOGBASE[0]) == 0)
	{
		return PEAR::raiseError( 'COMMONLOGBASE section doesn\'t exists or parameters not set', ERRCODE_APPLICATION_ERR );		
	}
  	
  	/**
  	 * Get data...
  	 */
	$dsn = array(
		'phptype' => 'mysql',
		'username' => (string)$sxml->COMMONLOGBASE[0]->ADMIN_USERNAME,
		'password' => (string)$sxml->COMMONLOGBASE[0]->ADMIN_PASSWORD,
		'hostspec' => (string)$sxml->COMMONLOGBASE[0]->HOST,
		'port'     => (string)$sxml->COMMONLOGBASE[0]->PORT,
		'database' => (string)$sxml->COMMONLOGBASE[0]->DBNAME,
	);

	/**
  	 * Connect to MySQL and select database
  	 */
	self::$_db = DB::connect($dsn, false);
	
	/**
	 * ERROR Handling
	 */
		if ( PEAR::isError(self::$_db) ) {
			return  PEAR::raiseError('db error connect to database', ERRCODE_APPLICATION_ERR);
		}
  }

  public function addEvent($DBKEY, $APP, $TNAME, $DATE) {
  	$params = array (
  		'DBKEY'	=>	$DBKEY,
  		'APP' 	=>	$APP,
  		'TNAME'	=>	$TNAME,
  		'DATE'	=>	$DATE
  	);
	$SQL = "INSERT INTO SCHEDULE_TASK (`SCH_DBKEY`,`SCH_APP`,`SCH_TASKNAME`,`SCH_DATETIME`) VALUES ('!DBKEY!','!APP!','!TNAME!','!DATE!')";
	
	$result = db_query($SQL, $params, self::$_db);

	if ( isset($result->message) ) {

//echo "xxxxxx<pre>";
//print_r($result->message);
//exit;


		return PEAR::raiseError('db error #2', ERRCODE_APPLICATION_ERR);
	}

  }

  /**
  * Get inserted keys
  */
  public static function getDBKEYS($START_TIME) {
  	if (PEAR::isError($instance = self::getInstance()))
  		return $instance;
  		
  	$params = array (
  		'START_TIME'	=>	$START_TIME,
  	);
  	
  	$SQL = "SELECT DISTINCT `SCH_DBKEY` FROM `SCHEDULE_TASK` WHERE SCH_DATETIME <= NOW()";
    $res = db_query($SQL, $params, self::$_db); $keys = array();
    
    if (isset($res->message))
    	return PEAR::raiseError( 'db error #3', ERRCODE_APPLICATION_ERR );

    while ($row = mysql_fetch_array($res->result, MYSQL_NUM))
    	$keys[] = strtoupper($row[0]);
    return $keys;
  }

  /**
  * Delete keys
  */
  public static function deleteRecord($DBKEY, $START_TIME) {
  	if (PEAR::isError($instance = self::getInstance()))
  		return $instance;
  		
  	$params = array (
  		'DBKEY'			=>	$DBKEY,
  		'START_TIME'	=>	$START_TIME,
  	);
    $SQL = "DELETE FROM `SCHEDULE_TASK` WHERE SCH_DBKEY = '!DBKEY!' AND SCH_DATETIME <= '!START_TIME!'";
	$res = db_query($SQL, $params, self::$_db);
    if (isset($res->message))
    	return PEAR::raiseError( 'db error #4', ERRCODE_APPLICATION_ERR );
  }
  public function test() {print 'test';}
}

//if ($_GET['test_debud'])
//	CronEventDispatcher::getInstance()->test();

?>