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

$WBS_URL = '';
if($xml = file_get_contents(WBS_DIR . 'kernel/wbs.xml'))
{
	$sxml = new SimpleXMLElement($xml);
	if(isset($sxml->WBS_URL))
		$WBS_URL = (string)$sxml->WBS_URL[0];
}

$logPath = WBS_DIR.'temp/log/send.log';
$log = fopen($logPath, 'a');
if($log) echo "\nlauncher.php started\n\n";
else echo "\nlauncher.php error: can't open log ".WBS_DIR."temp/log/send.log\n\n";

if($WBS_URL) {

	if(IS_HOSTING) {
		$dbkeys = CronEventDispatcher::getDBKEYS(convertToSqlDateTime(time()));
		if( PEAR::isError( $dbkeys ) )
			die( $dbkeys->message );
	}
	else {
		$dbkeys = array();

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

				$dbkeys[] = substr( $name, 0, strlen($name)-strlen($fileExt)-1 );
			}
		}
		closedir( $handle );
	}

	foreach($dbkeys as $DB_KEY) {

		if(IS_HOSTING)
			$host = 'webasyst.'.$WBS_URL;
		else
			$host = $WBS_URL;					

		$host = explode('/', $host, 2);

		$get = (isset($host[1]) ? '/'.$host[1] : '') . '/common/scripts/sendmail.php?DB_KEY='.base64_encode($DB_KEY);
		$host = $host[0];

		if($log) fwrite($log, date("Y-m-d H:i:s")." <- put $host $get ($DB_KEY)\n");

		$fp = fsockopen($host, 80, $errno, $error, 5);
		if($fp) {

			$query = "GET $get HTTP/1.1\r\nHost: $host\r\nConnection: close\r\n\r\n";
			fputs($fp, $query);
			sleep(1);
			fclose($fp);
		}
		else {
			if($log) fwrite($log, date("Y-m-d H:i:s")." error opening socket $host:80 for $DB_KEY---\n");
		}
	}
}

?>