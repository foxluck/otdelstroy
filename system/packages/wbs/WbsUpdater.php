<?php

/**
 * This class updates metadata of the system
 *
 */
class WbsUpdater 
{
	const START_VERSION = 277;
	
	protected $app_id;
	protected $version = false;
	protected $log_handler;	
	
	
	/**
	 * Constructor for the Application Updater
	 * 
	 * @param string $app_id
	 */
	public function __construct($app_id, $version = false)
	{
		$this->app_id = $app_id;
		$this->version = $version;
	}
	
	public function __destruct()
	{
		if ($this->log_handler) {
			fclose($this->log_handler);
		}		
	}
	
	protected function log($str)
	{
		if (!$this->log_handler) {
			if (!file_exists(WBS_DIR."/temp/log")) {
				mkdir(WBS_DIR."/temp/log", 0775);
			}
			$this->log_handler = fopen(WBS_DIR."/temp/log/wbs-update.log", "a+");
		}
		$str = "DBKEY - ".Wbs::getDbKey()."; APP_ID - ".$this->app_id."; LOG - ".$str."\n";
		fwrite($this->log_handler, $str);
	}
	
	
	
	/**
	 * Returns version of the system of the current account
	 * Read from dblist/DBKEY.xml
	 * 
	 * @return array()
	 */
	public function getCurrentVersion()
	{
		$version = Wbs::getDbkeyObj()->getVersion($this->app_id);
		$version = explode(".", $version ? $version : self::START_VERSION);		
		if (!isset($version[1])) {
			$version[1] = 0;
		}
		return $version;
		
	}
	
	/**
	 * Returns available version of the system
	 * Read from kernel/wbs.xml 
	 * 
	 * @return array()
	 */
	public function getAvailableVersion()
	{
		$version = $this->version ? $this->version : Wbs::getSystemObj()->getVersion();
		$version = explode(".", $version);
		if (!isset($version[1])) {
			$version[1] = 0;
		}
		return $version;
	}
	
	/**
	 * Check the appication, and update metadata, if it's need
	 * 
	 * @param string $app_id - ID of the application
	 * @return bool 
	 */
	public function check()
	{
		
		$current_version = $this->getCurrentVersion();
		$available_version = $this->getAvailableVersion();

	
		// If these versions are equals, returns
		if ($current_version[0] >= $available_version[0] && $current_version[1] >= $available_version[1]) {
			return false;
		}
		
	
		// Gets available versions
		include($this->getUpdatePath()."versions.php");
		
		$version = false;

		// Get last available verion of the updates for the current service
		$last_version = array();
		$versions = array_keys($_VERSIONS);
		$last_version[0] = end($versions);
		$last_version[1] = isset($_VERSIONS[$last_version[0]]['SUBVERSIONS']) ? end($_VERSIONS[$last_version[0]]['SUBVERSIONS']) : 0; 

		if ($last_version[0] > $available_version[0] || ($last_version[0] == $available_version[0] && $last_version[1] > $available_version[1])) {
			$last_version = $available_version;			
		}
	
		if ($current_version[0] >= $last_version[0] && $current_version[1] >= $last_version[1]) {
			return false;
		}
				

		if(!@mysql_ping()){
			return false;
		}
		
		$this->log("CURRENT_VERSION=".$current_version[0].", LAST_VERSION=".$last_version[0]);
	
		if ($current_version[0] > 277) {

			$sql = "SELECT VALUE FROM USER_SETTINGS WHERE U_ID = '' AND APP_ID = '' AND NAME = 'UPDATE_STARTED'";
	        $res = @mysql_query($sql);

			if (!$res) {
			    return false;
			}
		
		    $row = @mysql_fetch_assoc($res);
		    // If update is already run
		    if (time() - $row['VALUE'] < 30) {
				// header("Location: wait.php");
				return false;
		    }
		
		    $sql = "REPLACE INTO USER_SETTINGS SET U_ID = '', APP_ID = '', NAME = 'UPDATE_STARTED', VALUE = '". time() ."'";
		    $res = @mysql_query($sql);
		
		    if (!$res) {
			return false;
		    }
		}
		// Find updates		
		for ($v = $current_version[0]; $v <= $last_version[0]; $v++) {

				// If this update is available
				if (isset($_VERSIONS[$v])) {
				// Apply major update
				if ($v > $current_version[0]) {
					$filename  = $this->getUpdateFile($v);
					if ($filename) {
					    try {
							include($filename);
					    } catch (Exception $e) {
					    	if (defined('DEVELOPER')) {
					    		throw new Exception($e->getMessage(), $e->getCode());
					    	} else {
								return false;
					    	}
					    }
					    $version = $v;
					}
				}
				// Then apply minor updates if they available
				if ($_VERSIONS[$v]['SUBVERSIONS']) {
					foreach ($_VERSIONS[$v]['SUBVERSIONS'] as $sv) {
						if (
							($v == $current_version[0] && $sv > $current_version[1]) ||
							($v == $available_version[0] && ($available_version[1] == 0 || $sv <= $available_version[1])) ||
							($v != $current_version[0] && $v != $available_version[0]) 
							) {
							// Run update
							$filename  = $this->getUpdateFile($v, $sv);
							if ($filename) {
								try {
									include($filename);
								} catch (Exception $e) {
									if (defined('DEVELOPER')) {									
										throw new Exception($e->getMessage(), $e->getCode());
									} else {
										return false;	
									}
								}
								$version = $v.($sv ? ".".$sv : "");
							}
														
						}
					}
				}
			}
		}
		// If new update is installed
		if ($version) {
			$this->saveCurrentVersion($version);
			$sql = "DELETE FROM USER_SETTINGS WHERE U_ID = '' AND APP_ID = '' AND NAME = 'UPDATE_STARTED'";
			mysql_query($sql);
		}
	}
	
	/**
	 * Saves the current version of the system in DBKEY
	 * 
	 * @param $version
	 */
	public function saveCurrentVersion($version)
	{
		$filePath = sprintf( "%sdblist/%s.xml", WBS_DIR, strtoupper(Wbs::getDbkeyObj()->getDbkey()));
		$xml = simplexml_load_file($filePath);
		if (isset($xml->VERSIONS)) {
			$xml->VERSIONS[$this->app_id] = $version;	
		} else {
			$versions = $xml->addChild('VERSIONS');
			$versions->addAttribute($this->app_id, $version);
		}
		$f = fopen($filePath, "w+");
		if ($f) {
			fwrite($f, $xml->asXML());
			fclose($f);
			return true;
		}
		return false;
	}
	
	/**
	 * Return filename of the update if file exists
	 * 
	 * @param int $v - version
	 * @param int $sv - subversion (for developer)
	 * 
	 * @return string
	 */
	public function getUpdateFile($v, $sv = false)
	{
		$file = $this->getUpdatePath()."update".$v.($sv ? ".".$sv : "").".php";
		if (file_exists($file)) {
			return $file;		
		} 
		else {
			return false;
		}
	}
	
	/**
	 * Returns path to the updates folder
	 * 
	 * @return string
	 */
	public function getUpdatePath()
	{
		if ($this->app_id == 'SYSTEM') {
			return WBS_DIR."system/updates/";
		} else {
			return WBS_DIR."published/".$this->app_id."/updates/";
		}
	}
	
}

?>