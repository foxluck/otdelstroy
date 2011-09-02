<?php

$init_required = false;

chdir( dirname(__FILE__) );

require_once 'robotinit.php';

define( "ONCE_WITHOUT_DBKEY", -1 );
define( "ONLY_CREATED", 0 );
define( "ALL_DB", 1 );

$filePath = WBS_DIR."kernel/hosting_plans.php";
define( "IS_HOSTING", file_exists($filePath));
if ( IS_HOSTING ) 
	require_once($filePath);			

require_once(WBS_DIR . 'kernel/classes/class.eventdispatcher.php');

class robotTask
{
	var $name = "";
	var $robotfile = "";
	var $group = "";
	var $mode = ONLY_CREATED;
	var $applications = null;
	var $dbkey = "";

	function robotTask($name, $filename, $mode, $group, $dbkey = null, $applications = null )
	{
		$this->name = $name;
		$this->robotfile = $filename;
		$this->mode = $mode;
		$this->group = $group;
		$this->dbkey = $dbkey;
		$this->applications = $applications;
	}
}

class robotLauncher
{
	var $php_path;
	var $quite = true;
	var $timing = false;
	var $tasks;
	var $locStrings;
	var $registeredSystems;
	var $group = null;

	function robotLauncher( )
	{
	}

	function robotLauncherSetup( $_ARGS, $locStrings = array() )
	{

		if ( !isset( $_ARGS["PHP_PATH"] ) )
			return PEAR::raiseError( "PHP_PATH does not set." );
		$this->php_path = $_ARGS["PHP_PATH"];

		$this->php_path = isset( $_ARGS["PHP_PATH"] ) ? $_ARGS["PHP_PATH"] : 'php';

		$this->locStrings = $locStrings;

		$this->group = isset( $_ARGS["GROUP"] ) ? $_ARGS["GROUP"] : null;
		$this->php_config_path = isset( $_ARGS["PHP_CONFIG_PATH"] ) ? $_ARGS["PHP_CONFIG_PATH"] : null;

		$ret = $this->registeredSystems = $this->listRegisteredSystems();

		if ( PEAR::isError( $ret ) )
			return $ret;

		$this->tasks = array();

		return null;
	}

	function addTask( $task )
	{
		$this->tasks[] = $task;
	}

	function launch()
	{
		foreach( $this->tasks as $task )
		{
			if ( PEAR::isError( $ret = $this->launchTask( $task ) ) )
				return PEAR::raiseError( "Task ".$task->name." failed. Error: ".$ret->getMessage() );
		}

		return null;
	}

	function launchTask( $task )
	{
		global $WBS_URL;

		if ( $this->group != null && $this->group != $task->group )
			return null;

		if ( $task->mode == ONCE_WITHOUT_DBKEY )
		{
			if ($this->php_config_path) {
				exec( $this->php_path." -c ". $this->php_config_path . " " . $task->robotfile );
			}
			else {
				exec( $this->php_path." ".$task->robotfile );
			}
			return true;
		}
		foreach( $this->registeredSystems as $DB_KEY=>$hostInfo )
		{
			
			if (isset($task->dbkeys) && !in_array ($DB_KEY, $task->dbkeys))
				continue;
			if ( !is_null( $task->dbkey) && strtoupper( $task->dbkey ) !=	strtoupper(	$DB_KEY ) )
				continue;
			if ( is_array( $task->applications ) )
			{
				if ( count( array_intersect( $task->applications, $hostInfo[HOST_APPLICATIONS] ) ) != count( $task->applications ) )
					continue;
			}

			$loginFlag = $hostInfo[HOST_DBSETTINGS][HOST_FIRSTLOGIN];

			$expired = false;
			if ( isset($hostInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) && strlen($hostInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE]) )
			{
				$dbStamp = sqlTimestamp( $hostInfo[HOST_DBSETTINGS][HOST_EXPIRE_DATE] );

				if ( $dbStamp <= time() )
					$expired = true;
			}

			if ( $expired )
				continue;

			if ( $task->mode == ONLY_CREATED && !$loginFlag )
				continue;

			if ( $this->timing )
				putTimeMarker( "run", "Running task ".$task->name." for $DB_KEY.", "\n" );

			if ( !$this->quite )
				echo "Running task ".$task->name." for $DB_KEY.\n";

			if ($task->robotfile == "chargesms.php")
			{
				if ($this->php_config_path)
					exec( $this->php_path." -c " . $this->php_config_path. " ".$task->robotfile." DB_KEY $DB_KEY" );
				else
					exec( $this->php_path." ".$task->robotfile." DB_KEY $DB_KEY" );

				if ( IS_HOSTING )
					CronEventDispatcher::getInstance()->deleteRecord($DB_KEY, $task->startTime);
			}

			if ( $this->timing )
				putTimeMarker( "run", "Done.", "\n" );
			
			if ( !$this->quite )
				echo "DONE.\n";
		}
		return null;
	}

	function listRegisteredSystems( )
	{
		$result = array();

		$targetDir = WBS_DBLSIT_DIR;
		$fileExt = "xml";

		if ( !($handle = opendir($targetDir)) )
			return false;

		while ( false !== ($name = readdir($handle)) )
		{
			if ( $name != "." && $name != ".." )
			{
				$filename = $targetDir.'/'.$name;

				if ( is_dir($filename) )
					continue;

				$path_parts = pathinfo($filename);
				if ( $path_parts["extension"] != $fileExt )
					continue;

				$db_key = substr( $name, 0, strlen($name)-strlen($fileExt)-1 );

				$res = $this->getBasicHostData( $db_key );
				if ( !PEAR::isError($res) )
					$result[$db_key] = $res;
			}
		}

		closedir( $handle );

		return $result;
	}


	function getBasicHostData( $host_key )
	//
	// Returns basic account information - company name, subscriber name etc
	//
	//		Parameters:
	//			$host_key - database key
	//			$hostInfo - variable to put host information
	//
	//		Returns null or PEAR_Error
	//
	{
		$hostInfo = array();

		$filePath = sprintf( "%s/%s.xml", WBS_DBLSIT_DIR, strtoupper($host_key) );
		$dom = @domxml_open_file( realpath($filePath) );
		if ( !$dom )
			return PEAR::raiseError( "Error opening databse profile $host_key" );

		$element = @getElementByTagname( $dom, HOST_DBSETTINGS );
		if ( is_null($element) )
			return PEAR::raiseError( "XML Error" );

		$hostInfo[HOST_DBSETTINGS] = getAttributeValues( $element );
		$hostInfo[HOST_PLAN_DB] = getAttributeValues( $element );

		$element = @getElementByTagname( $dom, HOST_FIRSTLOGIN );
		if ( is_null($element) )
			return PEAR::raiseError( "XML Error" );

		$hostInfo[HOST_FIRSTLOGIN] = getAttributeValues( $element );
		$hostInfo[HOST_DB_KEY] = $host_key;

		$applications = @getElementByTagname( $dom, HOST_APPLICATIONS);
		if ( is_null($applications) )
			return PEAR::raiseError( $kernelStrings[ERR_XML] );

		$appList = array();
		$applications = $applications->get_elements_by_tagname(HOST_APPLICATION);

		foreach( $applications as $application )
			$appList[] = $application->get_attribute( HOST_APP_ID );

		// Billing plans
		if ( IS_HOSTING ) {
			global $mt_hosting_plan_settings;
			$plan = $hostInfo[HOST_PLAN_DB][HOST_PLAN_DB];
			if (array_key_exists($plan, $mt_hosting_plan_settings) && $plan != HOST_DEFAULT_PLAN && $plan != HOST_CUSTOM_PLAN) 
				$appList = array_merge ($appList, array_keys($mt_hosting_plan_settings[$plan]));
	 		$appList = array_unique(array_merge ($appList, explode(',', $hostInfo[HOST_DBSETTINGS][HOST_FREE_APPS])));
			//print_r($appList);
		}
		
		$hostInfo[HOST_APPLICATIONS] = $appList;

		return $hostInfo;
	}

}

$quite=false;
$timing=false;

$launcher = new robotLauncher( );

if ( PEAR::isError( $ret = $launcher->robotLauncherSetup( $_ARGS ) ) )
	die( $ret->getMessage() );

$launcher->timing = $timing;
$launcher->quite = $quite;

$chargeSMS = new robotTask( "KERNEL: Charge SMS", "chargesms.php", ONLY_CREATED, "1D" );

$launcher->addTask( $chargeSMS );

if ( $timing )
	putTimeMarker( "TOTAL", "LAUNCH", "\n\n" );

if ( PEAR::isError( $ret = $launcher->launch( ) ) )
	die( $ret->getMessage() );

if ( $timing )
	putTimeMarker( "TOTAL", "STOP", "\n\n" );

?>
