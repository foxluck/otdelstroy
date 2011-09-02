<?php
/**
 * Read general system settings and setup variables
 *
 */
class SystemSettings
{
	private $settings;
	private $fields;
	private $cache_file_path;
	private $DB_KEY;

	static function &getInstance()
	{
		static $systemSettings;
		if ($systemSettings instanceof SystemSettings) {
			return $systemSettings;
		}
		$systemSettings = new SystemSettings();
		$systemSettings = &$systemSettings;
		$systemSettings->init();
		return $systemSettings;
	}

	static function get($fields = null)
	{
		$systemSettings = self::getInstance();
		/*@var $systemSettings SystemSettings*/
		return $systemSettings->_get($fields);
	}

	private function _get($fields = null)
	{
		$result = null;
		if(is_array($fields)){
			$result = array();
			foreach($fields as $key=>$field){
				if(is_numeric($key)){
					$key = $field;
				}
				$result[$key] = isset($this->settings[$field])?$this->settings[$field]:null;
			}
		}elseif(isset($fields)){
			$result = isset($this->settings[$fields])?$this->settings[$fields]:null;
		}else{
			$result = $this->settings;
		}
		return $result;
	}

	static function is_backend(){
		static $res;
		if(isset($res)){
			return $res;
		}
		$res = true;
		if(isset($_GET['frontend']))$res=false;
		if(defined('CAPTURE_IMG'))$res=false;
		if(isset($_GET['frontend']))unset($_GET['frontend']);
		return $res;
	}

	static function is_hosted(){
		return file_exists(WBS_DIR."/kernel/hosting_plans.php");
	}

	function __construct()
	{
		$this->fields = array(
		'DB_HOST',
		'DB_USER',
		'DB_PASS',
		'DB_NAME',
		'DB_KEY',
		'SC_INSTALLED',
		'MOD_REWRITE_SUPPORT',
		'FRONTEND',
		'SHOW_POWERED_BY',
		'SERVER_TZ',
		'SERVER_TIME_ZONE_ID',
		'SERVER_TIME_ZONE_DST',
		'SMTP_HOST',
		'SMTP_PORT',
		'SMTP_HELO',
		'SMTP_AUTH',
		'SMTP_USER',
		'SMTP_PASS',
		'WBS_INSTALL_PATH',
		'WBS_DATA_DIRECTORY',
		'PROXY_HOST',
		'PROXY_PORT',
		'PROXY_USER',
		'PROXY_PASS',
		'EXPIRE_DATE',
		);
		$this->DB_KEY=strtoupper(self::getDB_KEY());
		$this->settings = array('DB_KEY'=>$this->DB_KEY);

		$this->cache_file_path = WBS_DIR.'/temp/scdb/.settings.'.$this->DB_KEY;
		if(!file_exists(WBS_DIR.'/temp/scdb')){
			mkdir(WBS_DIR.'/temp/scdb');
		}
	}

	function init()
	{
		//check cache exists
		if((self::is_hosted()||!self::is_backend())&&$this->readCache()){//attempt read cache
		}else{//else read data from xml
			$serverName = false;
			$this->readDbkeyXML($serverName);
			if($serverName&&$this->readWbsXml($serverName)){
				//fix not setted values
				foreach($this->fields as $field){
					if(!isset($this->settings[$field])){
						$this->settings[$field] = null;
					}
				}
				if(self::is_hosted()||!self::is_backend()){
					$this->writeCache();
				}
			}
		}
		
		//optional fix system variables
	}

	function initPaths()
	{

	}



	private function getDB_KEY()
	{
		if(self::is_hosted()){
			if(isset($_SERVER['HTTP_DBKEY'])&&$_SERVER['HTTP_DBKEY']){
				return strtoupper($_SERVER['HTTP_DBKEY']);
			}
			if (preg_match('/(.*?)\.([a-z0-9\.\-]+)/ui', $_SERVER['HTTP_HOST'], $matches)){
				$account_name = strtolower($matches[1]);
			}else{
				return null;
			}
			$accordance_file = WBS_DIR.'/dblist/dbnames';
			if(!file_exists($accordance_file)) return;

			$fp = fopen($accordance_file, 'r');
			while (!feof($fp)) {

				$__t = explode(' ', trim(fgets($fp, 1024)), 2);
				$cur_account_name = $__t[0];
				if(strtolower($cur_account_name) !== $account_name)continue;
				fclose($fp);
				return strtoupper(isset($__t[1])?$__t[1]:'');
				break;
			}
		}else{
			if(self::is_backend()){
				$DB_KEY = (isset($_SESSION['wbs_dbkey'])&&$_SESSION['wbs_dbkey'])?$_SESSION['wbs_dbkey']:'';
			}
			if(!isset($DB_KEY)||!strlen($DB_KEY)){
				if(file_exists(WBS_DIR."/kernel/wbs.xml")){
					$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
					return strtoupper((string)$xml->FRONTEND['dbkey']);
				}else{
					die("not exists kernel/wbs.xml");
				}
			}else{
				return strtoupper($DB_KEY);
			}
		}

	}

	private function readDbkeyXML(&$serverName)
	{
		$serverName = false;
		$xml_file_path=WBS_DIR.'/dblist/'.$this->DB_KEY.'.xml';
		if(file_exists($xml_file_path)){
			$db_xml=simplexml_load_file($xml_file_path);
			$_databaseInfo=$db_xml->xpath('/DATABASE/DBSETTINGS');
			if(!count($_databaseInfo)){
				die('invalid file '.$xml_file_path);
			}
			$_databaseInfo=$_databaseInfo[0];
			$serverName=(string)$_databaseInfo['SQLSERVER'];

			$this->settings['DB_USER'] = (string)$_databaseInfo['DB_USER'];
			$this->settings['DB_PASS'] = (string)$_databaseInfo['DB_PASSWORD'];
			$this->settings['DB_NAME'] = (string)$_databaseInfo['DB_NAME'];

			$systemInfo=$db_xml->xpath('/DATABASE/APPLICATIONS/APPLICATION');


			$this->settings['EXPIRE_DATE']=(int)strtotime($_databaseInfo['EXPIRE_DATE']);
			$this->settings['SC_INSTALLED'] = false;
			foreach ($systemInfo as $app){
				if(((string)$app['APP_ID'])=='SC'){
					$this->settings['SC_INSTALLED']=true;
					break;
				}
			}
			if(!$this->settings['SC_INSTALLED'] &&self::is_hosted()){

				$plan = (string)$_databaseInfo['PLAN'];
				global $mt_hosting_plan_settings,$databaseInfo,$mt_commerce_applications;
				require_once(WBS_DIR.'/kernel/hosting_plans.php');
				require_once(WBS_DIR.'/kernel/sysconsts.php');


				$databaseInfo = array(HOST_DBSETTINGS => array(
				HOST_FREE_APPS =>(string)$_databaseInfo[HOST_FREE_APPS]),
				HOST_APPLICATIONS=>$systemInfo);
				$appList = array_merge(array_keys(getCustomApps($plan)),array_keys(getFreeInstalledApps()));
				$this->settings['SC_INSTALLED']  = in_array('SC',$appList)?true:$this->settings['SC_INSTALLED'];
			}
		}
	}

	private function readWbsXml($serverName)
	{
		if(file_exists(WBS_DIR."/kernel/wbs.xml")){
			$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
			$servers=$xml->xpath('/WBS/SQLSERVERS/SQLSERVER');
			foreach ($servers as $server){
				if($serverName!=((string)$server['NAME']))
				continue;
				$host=((string)$server['HOST']);
				$port=((string)$server['PORT']);
				$host=$host.($port?':'.$port:'');
				break;
			}

			$tz = $xml->xpath( '/WBS/SERVER_TIME_ZONE');

			if ($tz && isset( $tz[0] ) ){
				$tz = $tz[0];
			}else{
				$tz= null;
			}
			if ( !is_null( $tz ) ){
				$SERVER_TZ = $tz['ENABLE'] == 1 ? 1 : 0;
				$SERVER_TIME_ZONE_ID = (int) $tz['ID'];
				$SERVER_TIME_ZONE_DST = $tz['SERVER_TIME_ZONE_DST'] == 1 ? 1 : 0;
			}else{
				$SERVER_TZ = 0;
				$SERVER_TIME_ZONE_ID = "";
				$SERVER_TIME_ZONE_DST = 0;
			}



			$this->settings['DB_HOST'] = $host;
			//TIME ZONE settings
			$this->settings['SERVER_TZ'] = $SERVER_TZ;
			$this->settings['SERVER_TIME_ZONE_ID'] = $SERVER_TIME_ZONE_ID;
			$this->settings['SERVER_TIME_ZONE_DST'] = $SERVER_TIME_ZONE_DST;
			//SMTP settings
			$smptp = $xml->xpath('/WBS/SMTP_SERVER');
			if(is_array($smptp) && isset($smptp[0])){
				$smptp = $smptp[0];
				$this->settings['SMTP_HOST'] = (string) $smptp['host'];
				$this->settings['SMTP_PORT'] = (string) $smptp['port'];
				$this->settings['SMTP_USER'] = (string) $smptp['user'];
				$this->settings['SMTP_PASS'] = (string) $smptp['password'];
				$this->settings['SMTP_HELO'] = (string) $smptp['helo'];
				//TODO: add smtp auth
				$this->settings['SMTP_AUTH'] = false;//(string) $smptp['password'];
			}
				
			//INSTALL PATH settings
			$install_path = $xml->xpath('/WBS/DIRECTORIES/WEB_DIRECTORY');
			$this->settings['WBS_INSTALL_PATH'] = '';
			if($install_path&&isset($install_path[0])){
				$install_path = $install_path[0];
				$this->settings['WBS_INSTALL_PATH'] = (string) $install_path['PATH'];
			}
			//MOD_REWRITE settings
			$this->settings['MOD_REWRITE_SUPPORT'] = false;
			$this->settings['SHOW_POWERED_BY'] = true;
			if(self::is_hosted()){
				$this->settings['MOD_REWRITE_SUPPORT'] = true;
				$this->settings['FRONTEND'] = 'login';
			}else{
				$mod_rewrite = $xml->xpath('/WBS/FRONTEND');
				if($mod_rewrite&&isset($mod_rewrite[0])){
					$mod_rewrite = $mod_rewrite[0];
					$this->settings['MOD_REWRITE_SUPPORT'] = (string)$mod_rewrite['mod_rewrite'];
					$this->settings['FRONTEND'] = (string)$mod_rewrite['type'];
					$this->settings['SHOW_POWERED_BY']=((isset($mod_rewrite['disable_powered_by'])&&((int)$mod_rewrite['disable_powered_by']=="1")))?false:true;
				}
			}

			//DATA PATH settings
			$data_path = $xml->xpath('/WBS/DIRECTORIES/DATA_DIRECTORY');
			if($data_path&&isset($data_path[0])){
				$data_path = $data_path[0];
				$this->settings['WBS_DATA_DIRECTORY'] = realpath(str_replace('[WBS_PATH]',WBS_DIR,(string) $data_path['PATH']));
			}
			//PROXY settings
			$proxy = $xml->xpath('/WBS/PROXY');
			if($proxy&&isset($proxy[0])){
				$proxy = $proxy[0];
				$this->settings['PROXY_HOST'] = (string) $proxy['host'];
				$this->settings['PROXY_PORT'] = (string) $proxy['port'];
				$this->settings['PROXY_USER'] = (string) $proxy['user'];
				$this->settings['PROXY_PASS'] = (string) $proxy['password'];
			}

		}else{
			die("not exists ".WBS_DIR."/kernel/wbs.xml");
		}
		return true;
	}

	private function readCache()
	{
		$res = false;
		$corrupted_data = false;
		if(file_exists($this->cache_file_path)){
			$file = implode('',file($this->cache_file_path));
			$data = unserialize(get_magic_quotes_gpc()?stripslashes($file):$file);
			if($data&&is_array($data)){
				foreach($this->fields as $field){
					if(array_key_exists($field,$data)){
						$this->settings[$field] = $data[$field];
					}else{
						$corrupted_data = true;
						//TODO: log message about changed settings fields
						break;
					}
				}
				if($corrupted_data){
					unlink($this->cache_file_path);
				}else{
					$res = true;
				}
			}else{
				unlink($this->cache_file_path);
			}
		}
		return $res;
	}

	private function writeCache()
	{
		$res = false;
		if($fp = fopen($this->cache_file_path,'wb')){
			$res = fwrite($fp,serialize($this->settings));
			fclose($fp);
		}
		return $res;
	}

}

/*
 if($fhandle=fopen($dbCachefilePath,'r')){
	$res=fread($fhandle,filesize($dbCachefilePath));
	$res = unserialize(get_magic_quotes_gpc()?stripslashes($res):$res);
	fclose($fhandle);
}
 */
?>