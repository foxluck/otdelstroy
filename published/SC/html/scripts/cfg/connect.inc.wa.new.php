<?php
	$_SESSION['timestamp'] = time();
	$WBSPath = DIR_ROOT."/../../../../";
	define( "WBS_DIR",realpath($WBSPath)."/");

	
	if(!function_exists('sc_onWebasystServer'))
	{
		function sc_onWebasystServer () {
			return file_exists(DIR_ROOT."/../../../../kernel/hosting_plans.php");
		}
	}
	if(!function_exists('sc_getSessionData')){
		function sc_getSessionData($key){//needed

			return isset($_SESSION['__WBS_SC_DATA'][$key])?$_SESSION['__WBS_SC_DATA'][$key]:'';
		}
	}
	if(!function_exists('sc_setSessionData')){

		function sc_setSessionData($key, $val){//needed

			$_SESSION['__WBS_SC_DATA'][$key] = $val;
		}
	}
		
	if(!db_getConnectData('SC_INSTALLED')){
		if(sc_onWebasystServer()){
			header("HTTP/1.0 404 Not Found");
			die("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL {$_SERVER['REQUEST_URI']} was not found on this server.</p>
<p>Additionally, a 404 Not Found
error was encountered while trying to use an ErrorDocument to handle the request.</p>
<hr>
</body></html>");
		}
		$url = 'http://'.str_replace('//','/',($_SERVER['SERVER_NAME'].WBS_INSTALL_PATH.'/'));
		print '<html><head><title>Error</title>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8;"></head><body>';
		print '<br><b>Your online store is not yet installed.</b><br><br> To activate your installation simply <a href="'.$url.'login/">login to your WebAsyst account</a> &mdash; this will complete your storefront setup (if you have WebAsyst Shoping Cart application installed).';
		print '</body></html>';
		die;
	}
	
	if(file_exists(WBS_DIR."/kernel/hosting_plans.php")){
		$mod_rewrite=true;
	
	}elseif(file_exists(WBS_DIR."/kernel/wbs.xml")){
		$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
		$mod_rewrite=(string)$xml->FRONTEND['mod_rewrite'];
		$mod_rewrite=isset($mod_rewrite)&&$mod_rewrite?true:false;
	}else{
		$mod_rewrite=false;
	}
	define('MOD_REWRITE_SUPPORT', $mod_rewrite); //TODO: to config or autodetect/done
	
	if(MOD_REWRITE_SUPPORT&&false)
	{
		if(true){
			define('URL_IMAGES', URL_ROOT.'/images');
			define('URL_THEMES', URL_ROOT.'/themes');
			define('URL_PRODUCTS_PICTURES', URL_ROOT.'/products_pictures');
		}else{
			define('URL_IMAGES',WBS_INSTALL_PATH.'/images');
			define('URL_THEMES', WBS_INSTALL_PATH.'/themes');
			define('URL_PRODUCTS_PICTURES', WBS_INSTALL_PATH.'/products_pictures');
		}
	}else{
		define('URL_PUBDATA_ROOT', str_replace('//','/',WBS_INSTALL_PATH.'/published/publicdata'));
		$DB_KEY=db_getConnectData('DB_KEY');
		foreach(array('products_pictures','images','themes') as $fld)
		{
			/*if(file_exists(DIR_ROOT.'/../../../..'.URL_PUBDATA_ROOT.'/'.$DB_KEY.'/attachments/SC/'.$fld))
			{*/
				define('URL_'.strtoupper($fld), file_exists(WBS_DIR."/kernel/hosting_plans.php")?$fld:URL_PUBDATA_ROOT.'/'.$DB_KEY.'/attachments/SC/'.$fld);
			/*}
			else
			{
				define('URL_'.strtoupper($fld), URL_PUBDATA_ROOT.'/__DEFAULT/attachments/SC/'.$fld);
			};*/
		};
	}
	// Copy general images to user directory
	if(!file_exists(WBS_DIR."/kernel/hosting_plans.php")){
		$sourcePath=WBS_DIR.'published/SC/html/scripts/images/';
		$targetPath=sprintf(WBS_DIR.'published/publicdata/%s/attachments/SC/images/',$DB_KEY);
		$errStr='';
		__copyDirectory($sourcePath,$targetPath,$errStr);
		__copyDirectory($sourcePath.'flags/',$targetPath.'flags/',$errStr);
	}
	
	if(!defined('DIR_PUBLICDATA_SC')){
		$DB_KEY=strtoupper(db_getConnectData('DB_KEY'));
		//print "<BR><B>{$DB_KEY}</b><BR>";
		define('DIR_PUBLICDATA_SC', $WBSPath.'/published/publicdata/'.$DB_KEY.'/attachments/SC');
		define('DIR_DATA_SC', $WBSPath.'/data/'.$DB_KEY.'/attachments/SC');
		define('DIR_IMG', DIR_PUBLICDATA_SC.'/images');
		define('DIR_THEMES',DIR_PUBLICDATA_SC.'/themes');
		define('DIR_PRODUCTS_PICTURES', DIR_PUBLICDATA_SC.'/products_pictures');
		define('DIR_PRODUCTS_FILES', DIR_DATA_SC.'/products_files');
		define('DIR_COMPILEDTEMPLATES', DIR_ROOT.'/../../../../kernel/includes/smarty/compiled/SC/templates_c/'.$DB_KEY);
		define('DIR_TEMP', DIR_DATA_SC.'/temp');
		define('DIR_SURVEY', DIR_DATA_SC.'/survey');
		define('DIR_FLAGS', DIR_IMG.'/flags');
		define('URL_FLAGS', URL_IMAGES.'/flags');
	}
	
	function db_getConnectData($key = null){
		static $return;
		
		$fpath=WBS_DIR.'/temp/.frontend';
		if(!$return){
			$return=array();
			if(sc_onWebasystServer()||is_backend()){
				$return=db_getConnectDataFromWBS();
	
				if(!sc_onWebasystServer()&&!is_backend()){
					$fhandle=fopen($fpath,'w');
					fwrite($fhandle,serialize($return));
					fclose($fhandle);
	
				}
			}elseif(file_exists($fpath)){
				$fhandle=fopen($fpath,'r');
				$return=unserialize(fread($fhandle,filesize($fpath)));
				fclose($fhandle);
			}else{
				$return=db_getConnectDataFromWBS();
			
			}
		}
		if(!is_null($key) && isset($return[$key]))return $return[$key];
		else return $return;
	}
	function db_getConnectDataFromWBS()
	{
		db_getConnectDataFromXML(db_getDB_KEY());
			
		return array(
		'DB_HOST' => sc_getSessionData('DB_HOST'),
		'DB_USER' => sc_getSessionData('DB_USER'),
		'DB_PASS' => sc_getSessionData('DB_PASS'),
		'DB_NAME' => sc_getSessionData('DB_NAME'),
		'DB_KEY'  => sc_getSessionData('DB_KEY'),
		'SC_INSTALLED'=> sc_getSessionData('SC_INSTALLED'),
		);
	}
	function db_getConnectDataFromXML($DB_KEY='')
	{
		if(!$DB_KEY)die('couldn\'t get DB_key');
		$DB_KEY=strtoupper($DB_KEY);
		$dbfilePath=WBS_DIR.'/dblist/'.$DB_KEY.'.xml';
		$dbCachefilePath=WBS_DIR.'/temp/scdb/.settings.'.$DB_KEY;
		$res = false;
		if(file_exists($dbCachefilePath)){
			$fhandle=fopen($dbCachefilePath,'r');
			$res=unserialize(fread($fhandle,filesize($dbCachefilePath)));
			fclose($fhandle);
			if($res){
				$host = $res['host'];
				$_databaseInfo = $res['databaseInfo'];
				$SC_INSTALLED = $res['SC_INSTALLED'];
			}			
		}elseif(!$res&&file_exists($dbfilePath)){
			$db_xml=simplexml_load_file($dbfilePath);
			$_databaseInfo=$db_xml->xpath('/DATABASE/DBSETTINGS');
			$systemInfo=$db_xml->xpath('/DATABASE/APPLICATIONS/APPLICATION');
			if(!count($_databaseInfo)){
				die('invalid file '.$dbfilePath);
			}
			$_databaseInfo=$_databaseInfo[0];
			$SC_INSTALLED=false;
			foreach ($systemInfo as $app){
					
					if(((string)$app['APP_ID'])=='SC'){
						$SC_INSTALLED=true;
						break;
					}
			}
			if(!$SC_INSTALLED&&sc_onWebasystServer){
				
				$plan = (string)$_databaseInfo['PLAN'];
				
				require_once(WBS_DIR.'/kernel/hosting_plans.php');
				require_once(WBS_DIR.'/kernel/sysconsts.php');
				
				global $mt_hosting_plan_settings,$databaseInfo,$mt_commerce_applications;
				$databaseInfo = array(HOST_DBSETTINGS => array(HOST_FREE_APPS =>(string)$_databaseInfo[HOST_FREE_APPS]));
				
				//$databaseInfo[HOST_DBSETTINGS][HOST_FREE_APPS]=$databaseInfo[HOST_FREE_APPS];
				//$free_inst_temp = explode(',', (string)$databaseInfo['FREE_APPS']);
				//if(!$free_inst_temp)$free_inst_temp = array();
				//var_dump(array(getCustomApps($plan),getFreeInstalledApps()));		
				$appList = array_merge(array_keys(getCustomApps($plan)),array_keys(getFreeInstalledApps()));
				$SC_INSTALLED = in_array('SC',$appList)?true:$SC_INSTALLED;
			}

			
			$serverName=(string)$_databaseInfo['SQLSERVER'];
	
			if(file_exists(WBS_DIR."/kernel/wbs.xml")){
				$xml= simplexml_load_file(WBS_DIR."/kernel/wbs.xml");
				$xml=$xml->xpath('/WBS/SQLSERVERS/SQLSERVER');
				foreach ($xml as $server){
					if($serverName!=((string)$server['NAME']))
					continue;
					$host=((string)$server['HOST']);
					$port=((string)$server['PORT']);
					$host=$host.($port?':'.$port:'');
					$fhandle=fopen($dbCachefilePath,'w');
					fwrite($fhandle,
					serialize(array('host'	=>$host,
									'databaseInfo'	=>array('DB_USER'	 =>(string)$_databaseInfo['DB_USER'],
 															'DB_PASSWORD'=>(string)$_databaseInfo['DB_PASSWORD'],
 															'DB_NAME'	 =>(string)$_databaseInfo['DB_NAME']),
 									'SC_INSTALLED' => $SC_INSTALLED)));
					fclose($fhandle);
					break;
				}
			}else{
				die("not exists ".WBS_DIR."/kernel/wbs.xml");
			}
	
		}
	
	
		sc_setSessionData('DB_HOST', $host);
		sc_setSessionData('DB_USER', (string)$_databaseInfo['DB_USER']);
		sc_setSessionData('DB_PASS', (string)$_databaseInfo['DB_PASSWORD']);
		sc_setSessionData('DB_NAME', (string)$_databaseInfo['DB_NAME']);
		sc_setSessionData('DB_KEY', $DB_KEY);
		sc_setSessionData('SC_INSTALLED', $SC_INSTALLED);
		
		return array(
		'DB_HOST' => sc_getSessionData('DB_HOST'),
		'DB_USER' => sc_getSessionData('DB_USER'),
		'DB_PASS' => sc_getSessionData('DB_PASS'),
		'DB_NAME' => sc_getSessionData('DB_NAME'),
		'DB_KEY'  => sc_getSessionData('DB_KEY'),
		'SC_INSTALLED'=> sc_getSessionData('SC_INSTALLED'),
		);
	}
	function db_getDB_KEY()
	{
		if(file_exists(WBS_DIR."/kernel/hosting_plans.php")){
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
			if(is_backend()){
				$DB_KEY = (isset($_SESSION['wbs_dbkey'])&&$_SESSION['wbs_dbkey'])?$_SESSION['wbs_dbkey']:'';
			}
			if(!strlen($DB_KEY)){
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
	function is_backend(){
		static $res=true;
		if(isset($_GET['frontend']))$res=false;
		if(defined('CAPTURE_IMG'))$res=false;
		if(isset($_GET['frontend']))unset($_GET['frontend']);
		return $res;
	}
	function __copyDirectory($sourcePath,$targetPath,&$errStr=null)
	{
		if(!__createDirectory($targetPath,$errStr)){
			return false;
		}
		if(file_exists($sourcePath)&&is_dir($sourcePath)){
			$dir=opendir($sourcePath);
			while (false!==($file=readdir($dir))){
				$destiny=$targetPath.'/'.$file;
				$source=$sourcePath.'/'.$file;
				if(!is_dir($source)&&file_exists($source)){
					if(file_exists($destiny))
						break;
		
					if(!copy($source,$destiny)){
						$sourcePath=str_replace('//','/',str_replace('\\','/',$sourcePath));
						$targetPath=str_replace('//','/',str_replace('\\','/',$targetPath));
						$errStr.="Couldn't copy file {$file} from {$sourcePath} to {$targetPath}";
						return false;
					}
				}
			}
		}
		return strlen($errStr)==0;
	
	}
	function __createDirectory($dirPath,&$errStr=null)
	{
		$currentDir=getcwd();
		if ( is_null($baseDir) )
			$baseDir =WBS_DIR;

		$baseDir = trim(str_replace(array('\\','//'),'/',$baseDir));
		$dirPath = trim(str_replace(array('\\','//'),'/',$dirPath));
		$strlen = strlen( $baseDir );
		if ( $baseDir[$strlen-1] == "/")
			$baseDir=substr( $baseDir, 0, --$strlen );
			
		if ( strcmp(strtolower(substr($dirPath, 0, $strlen)),strtolower($baseDir))==0 )
			$dirPath = substr( $dirPath, ++$strlen );
		
		$path=$dirPath;
		while (strpos($path,'\\')!==false) {
			$path=str_replace('\\','/',$path);
		}
		while (strpos($path,'//')!==false) {
			$path=str_replace('//','/',$path);
		}
		$dirs = explode('/', $path);
    	$dir=$baseDir.(strlen($baseDir)?'/':'');
    	$oldMask = @umask(0);
    	foreach ($dirs as $part) {
    		if(strlen($part)==0)
    			continue;
       		$dir.=$part.'/';
        	if (!is_dir($dir) && strlen($dir)>0)
        	{
            	if(!@mkdir($dir, 0777))
					$errStr = sprintf( "Unable to create directory %s", $dir );
            	@umask($oldMask);
            }
        }
		chdir( $currentDir );
		return (strlen($errStr)>0)?false:true;
	}
	
	
	
	
	define('ADMIN_LOGIN', 'admin');
	
?>