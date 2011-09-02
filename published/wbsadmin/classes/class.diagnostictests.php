<?php
@include_once(WBS_DIR.'/published/wbsadmin/html/configs/env.php');
if(!defined('WA_ENV_WEB_VERSION_FILENAME')){
	define('WA_ENV_WEB_VERSION_FILENAME','http://my.webasyst.net/cc/version.xml');
}
if(!defined('WA_ENV_WEB_CHANGE_FILENAME')){
	define('WA_ENV_WEB_CHANGE_FILENAME','http://my.webasyst.net/cc/changelog.xml');
}
if(!defined('WA_ENV_WEB_URL')){
	define('WA_ENV_WEB_URL','my.webasyst.net');
}

class DiagnosticTests
{
	var $testList;
	var $toolList;
	var $result = array();
	var $currentGroup = null;
	var $currentSubGroup = null;

	const SERVER 	= 0;
	const PHP 		= 1;
	const INSTALL 	= 2;
	const MySQL 	= 3;
	const UPGRADE 	= 4;


	function __call($method,$params)
	{
		if(in_array($method,get_class_methods(__CLASS__))){
			return $this->$method($params);
		}else{
			print "<p><b style=\"color:red;\">method {$method} not exists</b></p>\n";
			return false;
		}
	}

	function DiagnosticTests()
	{

		$this->toolList = array();
		$this->testList = array();
		$methodNames = get_class_methods(__CLASS__);
		//$methodVars = get_class_vars(__CLASS__);
		/*if($methodVars){
			$methodVars = array_keys($methodVars);
			}*/

		foreach($methodNames as $methodName){
			if(strpos($methodName,'my_test') === 0){
				$test = array('method'=>$methodName,'name'=>str_replace('my_test','',$methodName));
				$this->testList[] = $test;
			}
			/*if(strpos($methodName,'my_tool') === 0){
				$tool = array('method'=>$methodName,'description'=>str_replace('my_tool','',$methodName));
				$descriptionVar = $methodName.'_description';
				if(in_array($descriptionVar,$methodVars)){
				$tool['description'] = $methodVars[$descriptionVar].' || '.$this->$descriptionVar;
				}else{
				$tool['description'] = $this->$descriptionVar;
				}
				$this->toolList = $tool;
				}*/
		}
	}

	function runTest($testNames = null,$params = null)
	{
		static $counter = 0;
		static $successCounter = 0;
		if(!is_array($testNames)){
			$testNames = $testNames?array($testNames):array();
		}
		$executed = false;

		foreach($this->testList as $test){
			$method = $test['method'];
			if((!$testNames || in_array($test['name'],$testNames))){
				$counter++;
				if($success = $this->$method($params))$successCounter++;
				$this->setSubGroupResult($success,($success?'test_success':'test_failed'));
				if($testNames){
					$key = array_search($test['name'],$testNames);
					if($key !== false){
						unset($testNames[$key]);
					}
					if(!count($testNames)){
						break;
					}
				}
			}
		}
		$this->sortResult();
		return $testNames;
	}

	function initGroup($group,$subGroup,$description = '')
	{
		$subGroup = preg_replace('/^my_test/','',$subGroup);
		if(!isset($this->result[$group])||!is_array($this->result[$group])){
			$this->result[$group] = array();
		}
		$this->result[$group][$subGroup] = array('description'=>$description,'data'=>array(),'result'=>true,'value'=>'');
		$this->currentGroup = $group;
		$this->currentSubGroup = $subGroup;
	}

	function setResult($param,$result = false,$value = null,$info='')
	{
		$this->result[$this->currentGroup][$this->currentSubGroup]['data'][$param] = array(
		'result'=>intval($result),'value'=>$value,'info'=>$info);
	}

	function sortResult()
	{
		ksort($this->result);
	}
	function setSubGroupResult($result,$value='')
	{
		$this->result[$this->currentGroup][$this->currentSubGroup]['result']=$result;
		$this->result[$this->currentGroup][$this->currentSubGroup]['value']=$value;

	}

	function getResult()
	{
		return $this->result;
	}

	function resultToBoolean($result){
		if(($result === 1)||($result === true)){
			$result = translate('test_php_settings_on');
		}
		if(($result === 0)||($result === false)||($result === '')){
			$result = translate('test_php_settings_off');
		}
		return $result;
	}

	/////////////////////////////////////////////////////
	// test functions
	/////////////////////////////////////////////////////

	//check file structure

	function my_testServer()
	{
		$description = 'test_description_server_variables';
		$this->initGroup(DiagnosticTests::SERVER,__FUNCTION__,$description);
		$success = true;
		$my_SERVER = &$_SERVER;
		//var_dump($_SERVER);
		$params = array(

		'SERVER_SOFTWARE'=>'my_SERVER',
		'SERVER_NAME'=>'my_SERVER',
		'SERVER_PORT'=>'my_SERVER',
		'SERVER_SIGNATURE'=>'my_SERVER',
		'DOCUMENT_ROOT'=>'my_SERVER',
		'GATEWAY_INTERFACE'=>'my_SERVER',
		'REQUEST_URI'=>'my_SERVER',
		'SCRIPT_FILENAME'=>'my_SERVER',
		);

		foreach($params as $param => $var){
			$Folder = $systemFolder;
			$systemFolder = WBS_DIR.'/'.$systemFolder;
			$varName = str_replace('my','',$var);
			$this->setResult("\${$varName}[{$param}]",
			($res = isset(${$var})&&isset(${$var}[$param]))?(-2):(-1),
			$res?${$var}[$param]:translate('test_value_not_exists'),
			'');
			if(!$res)$success = false;
				
		}
		$success = true;
		return $success;
	}


	function my_testPHP()
	{
		$description = 'check_php_variables_and_extensions';
		$this->initGroup(DiagnosticTests::PHP,__FUNCTION__,$description);

		$phpExtensions = array(
		'mysql'=>true,
		'mbstring'=>true,
		'simplexml'=>true,
		'dom'=>true,
		'gd'=>true,
		'imagick'=>false,
		'zlib'=>true,
		'iconv'=>true,
		'curl'=>true,
		'gettext'=>true,
		'eaccelerator'=>false,
		'xcache'=>false,
		'suhosin'=>false,
		);
		$phpIniSettings = array(
		'allow_url_fopen'=>true,
		'max_execution_time'=>false,
		'memory_limit'=>false,
		'upload_max_filesize'=>false,
		'safe_mode'=>true,
		'get_magic_quotes_gpc'=>false,
		'get_magic_quotes_runtime'=>false,
		//'get_magic_quotes_sybase'=>false,
		'memory_get_usage'=>false,
		);

		$success = true;
		$versionString = phpversion();
		$version = explode('.',$versionString);
		$this->setResult('test_php_version',
		$res = (($version[0]>=5)?1:0),
		'<span class="nobr">'.$versionString.'&nbsp;<a href="?section=systeminfo&amp;action=phpinfo" target="_blank">phpinfo&nbsp;<img src="../classic/images/new_window_icon.gif" alt=""></a></span>',
		translate('test_php_version_description'));

		if(!$res)$success = false;

		foreach($phpExtensions as $phpExtension=>$isStrong){
			$this->setResult($phpExtension,
			($res = extension_loaded($phpExtension))?($isStrong?1:(-1)):($isStrong?0:(-1)),
			$res?(strlen($version = phpversion($phpExtension))?$version:'loaded'):'not loaded',
			'test_php_extension_'.$phpExtension);
			if(!$res&&$isStrong)$success = false;
		}
		foreach($phpIniSettings as $phpIniSetting=>$isStrong){
			$value = function_exists($phpIniSetting)?call_user_func($phpIniSetting):ini_get($phpIniSetting);
			$this->setResult(str_replace('get_','',$phpIniSetting),
			($res = isset($value))?($isStrong?1:(-1)):($isStrong?0:(-1)),
			$res?$this->resultToBoolean($value):'unknown',
			'test_php_settings'.$phpIniSetting);
			if(!$res&&$isStrong)$success = false;
		}
		return $success;
	}

	function my_testConnectivity()
	{
		$description = 'test_connectivity';
		$this->initGroup(DiagnosticTests::SERVER,__FUNCTION__,$description);
		$success = true;
		
		$this->setResult("test_connectivity",
		$res = (($fp = fopenTimeout(WA_ENV_WEB_VERSION_FILENAME))&&fclose($fp)),
		translate($res?'test_success':'test_check_your_connection'),
		'test_connectivity_description');
		$host = gethostbyname(preg_replace('/^.+@/','',WA_ENV_WEB_URL));
		$this->setResult("test_network",
		$host_res = preg_match('/^(\d+(\.|$)){4}/',$host),
		translate($host_res?'test_success':'test_check_your_dns')." <br>(".WA_ENV_WEB_URL.")",
		WA_ENV_WEB_URL." ({$host})");

		if(!$host_res)$success = false;

		if(!$res)$success = false;

		return $success;
	}

	//check file structure
	/*	function my_testFileStructure()
	 {
		$description = 'test_validate_file_structure';
		$this->initGroup(DiagnosticTests::INSTALL,__FUNCTION__,$description);

		$systemFolders = array(
		'data',
		'dblist',
		'kernel',
		'kernel/includes/smarty/compiled',
		'published',
		'published/publicdata',
		'published/AA',
		'published/wbsadmin',
		'installer',
		'temp');
		$success = true;
		foreach($systemFolders as $systemFolder){
		$Folder = $systemFolder;
		$systemFolder = WBS_DIR.'/'.$systemFolder;
		$this->setResult($Folder,
		$res = (file_exists($systemFolder)&&is_dir($systemFolder)),
		$res?'test_folder_exists':'test_folder_not_exists',
		'test_system_folder');
		if(!$res)$success = false;
		}
		return $success;
		}
		*/



	//check file structure && rights
	function my_testFolderWrite()
	{
		$description = 'test_folder_structure';
		$this->initGroup(DiagnosticTests::INSTALL,__FUNCTION__,$description);

		$writableFoldersPattern = array(
		'data',
		'data/%DB_KEY%',
		'dblist',
		'kernel',
		'kernel/includes/smarty/compiled',
		'SC'=>'kernel/includes/smarty/compiled/SC/%DB_KEY%',
		'published',
		'published/publicdata',
		'published/publicdata/%DB_KEY%',
		'published/%APP_ID%',
		'^UG'=>'published/%APP_ID%/localization',
		'published/wbsadmin',
		'published/wbsadmin/localization',
		'installer',
		'temp');
		$success = true;
		$appList = $this->getApplicationList();
		$DBKeyList = $this->getDBKeyList();
		$writableFolders = array();
		foreach($writableFoldersPattern as $APP_ID=>$FolderPattern){
			if(preg_match('/^[A-Z]{2}$/u',$APP_ID)&&(!in_array($APP_ID,$appList)))continue;
			if(preg_match('/^\^([A-Z]{2})$/u',$APP_ID,$matches)&&(in_array($matches[1],$appList))){
				$skip_APP_ID = $matches[1];
			}else{
				$skip_APP_ID = false;
			}
			if(count($appList)){
				foreach($appList as $APP_ID){
					if($skip_APP_ID && ($skip_APP_ID == $APP_ID))continue;
					$countAPP_ID = null;
					$Folder = str_replace('%APP_ID%',$APP_ID,$FolderPattern,$countAPP_ID);
					if(count($DBKeyList)){
						foreach($DBKeyList as $DBKey){
							$countDBKey = 0;
							$Folder = str_replace('%DB_KEY%',$DBKey,$Folder,$countDBKey);
							$writableFolders[$Folder] = $Folder;
							if(!$countDBKey)break;
						}
					}else{
						if(strpos($Folder,'%DB_KEY%'))continue;
						$writableFolders[$Folder] = $Folder;
					}
					if(!$countAPP_ID)break;
				}
			}else{
				if(strpos($FolderPattern,'%APP_ID%'))continue;
				if(count($DBKeyList)){
					foreach($DBKeyList as $DBKey){
						$countDBKey = 0;
						$Folder = str_replace('%DB_KEY%',$DBKey,$FolderPattern,$countDBKey);
						$writableFolders[$Folder] = $Folder;
						if(!$countDBKey)break;
					}
				}else{
					if(strpos($FolderPattern,'%DB_KEY%'))continue;
					$writableFolders[$FolderPattern] = $FolderPattern;
				}
			}
		}
		
		foreach($writableFolders as $Folder){
			$writableFolder = WBS_DIR.'/'.$Folder;
			$exist = (file_exists($writableFolder)&&is_dir($writableFolder));
			$perm = $exist?sprintf(' %03o',fileperms($writableFolder)&0777):'';
			$this->setResult($Folder,
							$res = $exist&&is_writable($writableFolder),
							translate($res?'test_folder_exists':($exist?'test_folder_not_writable':'test_folder_not_exists')),//.' '.$perm
							'test_folder_structure_description');
			if(!$res)$success = false;
		}
		return $success;
	}

	//check file rights
	function my_testFolderSecurity()
	{
		$description = 'test_folder_protection';
		$this->initGroup(DiagnosticTests::INSTALL,__FUNCTION__,$description);

		$secureFolders = array(
		'data',
		'dblist',
		'kernel',
		'temp');
		$success = true;

		foreach($secureFolders as $secureFolder){
			$Folder = $secureFolder;
			$secureFolder = WBS_DIR.'/'.$secureFolder;
			$this->setResult($Folder,
			($res = file_exists($secureFolder.'/.htaccess'))?2:0,
			translate($res?'test_folder_protected':((file_exists($secureFolder)&&is_dir($secureFolder))?'test_folder_not_protected':'test_folder_not_exists')),
			'test_folder_protection_description');
			if(!$res)$success = false;
		}
		return $success;
	}

	//check file rights
	function my_testSystemFiles()
	{
		$description = 'test_system_file';
		$this->initGroup(DiagnosticTests::INSTALL,__FUNCTION__,$description);

		$systemFiles = array(
		'kernel/wbs.xml',
		);
		$success = true;
		foreach($systemFiles as $systemFile){
			$File = $systemFile;
			$systemFile = WBS_DIR.'/'.$systemFile;
			$this->setResult('test_system_file_'.$File,
			$res = (file_exists($systemFile)&&!is_dir($systemFile)&&is_writable($systemFile)),
			translate($res?'test_folder_exists':((file_exists($systemFile)&&!is_dir($systemFile))?'test_folder_not_writable':'test_folder_not_exists')),
			'test_system_file_description');
			if(!$res)$success = false;

		}
		return $success;
	}


	function my_testDatabaseFolders()
	{
		$description = 'test_data_folder';
		$this->initGroup(DiagnosticTests::INSTALL,__FUNCTION__,$description);
		$databaseFolders = array(
		'data/%s',
		'published/publicdata/%s',
		);
		$success = true;
		$value = '';
		$dbKey;
		$dblist = 'dblist';
		$dbKeyCount = 0;
		if(file_exists(WBS_DIR.'/'.$dblist)&&is_dir(WBS_DIR.'/'.$dblist)){
			$files = scandir(WBS_DIR.'/'.$dblist);
			foreach($files as $file){
				if(preg_match('/([^\?]+)\.xml/',$file,$matches)){
					$dbKey = $matches[1];
					$dbKeyCount++;
					foreach($databaseFolders as $folder){
						$folder = sprintf($folder,$dbKey);
						$_folder = $folder;
						$folder = WBS_DIR.'/'.$folder;
						$this->setResult($_folder,
						$res = (file_exists($folder)&&is_dir($folder)&&is_writable($folder)),
						translate($res?'test_folder_exists':((file_exists($folder)&&is_dir($folder))?'test_folder_not_writable':'test_folder_not_exists')),
						'test_data_folder_description');
						if(!$res)$success = false;
					}

				}
			}
			if(!$dbKeyCount){
				$this->setResult("<!--{$dblist}-->",false,translate("test_database_list_empty"),translate("test_database_list_empty_description"));
				$success = false;
			}
		}else{
			$this->setResult($dblist,false,	translate('test_value_not_exists'));
			$success = false;
		}

		return $success;
	}

	function my_testDatabase()
	{
		$description = 'test_database';
		$this->initGroup(DiagnosticTests::MySQL,__FUNCTION__,$description);
		$success = true;

		global $messageStr;
		/*
		 (extension_loaded('simplexml')
		 &&($xml = simplexml_load_file($xmlFile))
		 &&($xmlSevers = $xml->xpath('SQLSERVERS'))){
			*/
		//get SQL server params
		$SQLservers = array();
		$xmlFile = 'kernel/wbs.xml';
		$xml = null;
		$xmlServers = null;
		$this->setResult(translate('test_database_server').' '.$connectionSettings['SQLSERVER'],
		$res = (extension_loaded('simplexml')
		&&($xml = simplexml_load_file(WBS_DIR.'/'.$xmlFile))
		&&($xmlServers = $xml->xpath('SQLSERVERS/SQLSERVER'))
		),
		$res?'OK':($xml?($xmlSevers?('ok'):'error xpath'):'error load '.$xmlFile),
		'test_database_server_list');
		if(!$res)$success = false;
		foreach($xmlServers as $xmlServer){
			/*@var $xmlServer SimpleXMLElement*/
			$attributes_ = $xmlServer->attributes();
			$attributes = array();
			foreach($attributes_ as $param=>$value){
				$attributes[$param]=(string)$value;
			}
			$name = $attributes['NAME'];
			$SQLservers[$name] = array('HOST'=>isset($attributes['HOST'])?$attributes['HOST']:'localhost',
			'PORT'=>isset($attributes['PORT'])?$attributes['PORT']:null);
			$SQLservers[$name]['ADRESS'] = $SQLservers[$name]['HOST'].($SQLservers[$name]['PORT']?(':'.$SQLservers[$name]['PORT']):'');
		}
		//$messageStr .= nl2br(var_export($SQLservers,true));

		$dbKey;
		$dblist = 'dblist';
		$dbKeyCount = 0;
		if(file_exists(WBS_DIR.'/'.$dblist)&&is_dir(WBS_DIR.'/'.$dblist)){
			$files = scandir(WBS_DIR.'/'.$dblist);
			foreach($files as $file){
				if(preg_match('/([^\?]+)\.xml/',$file,$matches)){
					$dbKey = $matches[1];
					$dbKeyCount++;
					$xmlFile = WBS_DIR.'/'.$dblist.'/'.$file;
					$file = $dblist.'/'.$file;
					//print "{$dbKey}<br>\n<ul>";
					$res = false;
					if(extension_loaded('simplexml')&&($xml = simplexml_load_file($xmlFile))&&($xmlSettings = $xml->xpath('DBSETTINGS'))){
						$xmlSettings = $xmlSettings[0];
						/*@var $xmlSettings SimpleXMLElement*/
						$dbSettings = $xmlSettings->attributes();
						$dbParams = array('DB_NAME','DB_PASSWORD','DB_USER','SQLSERVER','MYSQL_CHARSET');
						$connectionSettings = array();
						foreach($dbParams as $param){
							$connectionSettings[$param] = isset($dbSettings[$param])?(string)$dbSettings[$param]:null;
						}

						$installedApplications = array('AA'=>'AA','UG'=>'UG','MW'=>'MW','WG'=>'WG');
						$xmlInstalledApplications = $xml->xpath('APPLICATIONS/APPLICATION');
						foreach($xmlInstalledApplications as $xmlInstalledApplication){
							/*@var $xmlInstalledApplication SimpleXMLElement*/
							$attributes = $xmlInstalledApplication->attributes();
							$appId = (string)$attributes['APP_ID'];
							$installedApplications[$appId] = $appId;
						}

						//server connect
						$dbh = mysql_connect(
						isset($SQLservers[$connectionSettings['SQLSERVER']])?$SQLservers[$connectionSettings['SQLSERVER']]['ADRESS']:'localhost',
						$connectionSettings['DB_USER'],$connectionSettings['DB_PASSWORD']);
							
						$this->setResult(translate('test_mysql_connectivity').': '.$connectionSettings['SQLSERVER'],
						$res = ($dbh?1:0),
						$res?'OK':sprintf('#%d: %s',mysql_errno(),mysql_error()),
						'test_mysql_connectivity_description');
						if(!$res)$success = false;

						//server version
						$rows = null;
						$row = null;
						$version = null;
							
						$this->setResult(translate('test_mysql_version').': '.$connectionSettings['SQLSERVER'],
						($res = ($dbh&&($rows = mysql_query('SHOW VARIABLES LIKE \'version\'',$dbh))
						&&($row = mysql_fetch_row($rows))
						&&(preg_match('/^(\d+)\.(\d+)\.(\d+)/',$row[1],$version)))
						&&(($version[1]>=5)
						||(($version[1]>=4)&&($version[2]>=1)&&(($version[2]>1)||($version[3]>3)))))?1:0,
						$res?$version[0]:($version?$version[0]:($dbh?mysql_error():translate('test_skipped'))),
						'test_mysql_version_description');

						if(!$res)$success = false;
						//select db
						$db_res = true;
						$this->setResult('<!-- '.$connectionSettings['DB_NAME'].'@'.$connectionSettings['SQLSERVER'].' -->',
						-1,
						'&nbsp;',
						'&nbsp;');

						$this->setResult('<b>'.translate('test_mysql_database').'</b>: '.$connectionSettings['DB_NAME'].'@'.$connectionSettings['SQLSERVER'],
						$res = $dbh&&mysql_select_db($connectionSettings['DB_NAME'],$dbh),
						$res?'OK':($dbh?mysql_error():translate('test_skipped')),
						'test_mysql_database_description');
						if(!$res)$success = false;
						if($res){
							$applicationList = $this->getApplicationList();
							foreach($applicationList as $ApplicationId){
								$tables = $this->getTablesList($ApplicationId);
								$totalCount = count($tables);
								$succesCount = 0;
								$missedTables = '';
								foreach($tables as $c=>$table){
									$c++;
									if(mysql_query('SELECT 1 FROM '.$table.' LIMIT 1',$dbh)){
										$succesCount++;
									}else{
										$missedTables .= "{$table}<br>\n";
									}
										
								}
								if(strlen($missedTables)){$missedTables = '<br>'.translate('test_tables_missed').':<br>'.$missedTables;}
								$this->setResult('<span class="nobr">&nbsp;&nbsp;&nbsp;'.translate('test_mysql_database_tables').': '.$ApplicationId.'@'.$dbKey.'</span>',
								$db_res = (($succesCount == $totalCount)||!isset($installedApplications[$ApplicationId]))?1:0,
								(($succesCount == $totalCount)?($totalCount.'&nbsp;'.translate('test_tables_label')):($succesCount.'/'.$totalCount.'&nbsp;'.translate('test_tables_label'))).((isset($installedApplications[$ApplicationId])?($missedTables):'&nbsp;'.translate('test_application_not_installed'))),
								'test_mysql_database_tables_description');
								if(!$db_res)$res = false;
							}
								
						}
						if($dbh){
							mysql_close($dbh);
							$dbh = null;
						}

						//$res = true;
					}else{
						//print "<li><b style=\"color:red;\">error parse dblist/{$file}</b>\n";
						$success = false;
					}
						
					$this->setResult('<b>'.translate('test_mysql_database_total').'</b>: '.$dbKey.'@'.$connectionSettings['SQLSERVER'],
					$res,
					translate($res?'test_success':(extension_loaded('simplexml')?'test_failed':'test_skipped')),
					'test_database_settings');
					if(!$res)$success = false;
				}
			}
			if(!$dbKeyCount){
				//print "<li><b style=\"color:red;\">database settings not found</b>\n";
				$success = false;
			}
		}else{
			//print "<li><b style=\"color:red;\">{$dblist}</b> not exist\n";
			$success = false;
		}



		return $success;
	}


	function my_testLocale()
	{
		$description = 'test_description_locale';
		$this->initGroup(DiagnosticTests::PHP,__FUNCTION__,$description);
		$success = true;
		$params = array(
		'iconv_get_encoding'=>'iconv_get_encoding',
		'mb_get_info'=>'mb_get_info',
		'mb_list_encodings'=>'mb_list_encodings',
		'mb_list_encodings_alias_names'=>'mb_list_encodings_alias_names',
		);

		foreach($params as $function => $description){
			$this->setResult($function,
			($res = function_exists($function))?(-2):(-1),
			$res?('<pre>'.var_export(call_user_func($function),true).'</pre>'):translate('test_value_not_exists'),
			'test_system_variables');
			//if(!$res)$success = false;
				
		}
		return $success;
	}
	/////////////////////////////////////////////////////
	// tools functions
	/////////////////////////////////////////////////////

	function getDBKeyList()
	{
		static $dbKeys = array();
		$dblist = 'dblist';
		$dbKeyCount = 0;
		if(file_exists(WBS_DIR.'/'.$dblist)&&is_dir(WBS_DIR.'/'.$dblist)){
			$files = scandir(WBS_DIR.'/'.$dblist);
			foreach($files as $file){
				if(preg_match('/([^\?]+)\.xml/',$file,$matches)){
					$dbKeys[] = $matches[1];
				}
			}
		}
		return $dbKeys;
	}

	function getApplicationList()
	{
		static $applications = array();
		if(!count($applications)){
			$dirs = scandir(WBS_DIR.'/published');
			foreach($dirs as $dir){
				if(preg_match('/^\w{2}$/',$dir)&&($dir!='..')){
					$applications[] = $dir;
				}
			}
		}
		return $applications;
	}

	function getTablesList($applicationId)
	{
		static $tables = array();
		static $pattern = '/create\s+table\s+(if\s+not\s+exists\s+)?[\'`]?(\w+)[\'`]?/si';
		if(is_array($tables[$applicationId])){
			return $tables[$applicationId];
		}
		$tables[$applicationId] = array();
		$file = sprintf('%s/published/%s/%s_metadata.sql',WBS_DIR,$applicationId,strtolower($applicationId));
		if(file_exists($file)&&($fileContent = file($file))){
			$fileContent = implode('',$fileContent);
			if(preg_match_all($pattern,$fileContent,$matches)&&isset($matches[2])&&is_array($matches[2])){
				foreach($matches[2] as $tableName){
					if(array_search($tableName,$tables[$applicationId])===false){
						$tables[$applicationId][] = $tableName;
					}
				}
			}else{
			}
		}else{
			$curdir = getcwd();
			chdir( sprintf('%s/published/%s',WBS_DIR,$applicationId));
			$files = glob(sprintf('%s_metadata.*.sql',strtolower($applicationId)));
			chdir($curdir);
			if($files){
				foreach($files as $file){
					$file = ( sprintf('%s/published/%s/%s',WBS_DIR,$applicationId,$file));
					if(file_exists($file)&&($fileContent = file($file))){
						$fileContent = implode('',$fileContent);
						if(preg_match_all($pattern,$fileContent,$matches)){
							foreach($matches[2] as $tableName){
								if(array_search($tableName,$tables[$applicationId])===false){
									$tables[$applicationId][] = $tableName;
								}
							}
								
						}
					}
				}
			}
		}
		sort($tables[$applicationId]);
		return $tables[$applicationId];
	}

}
?>