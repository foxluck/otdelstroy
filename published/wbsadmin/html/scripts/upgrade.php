<?php
if(!defined('WBS_DIR'))
define('WBS_DIR',str_replace('\\','/',realpath('../../../../')).'/');
if ( version_compare(PHP_VERSION,'5','>=') ) {
	if(!function_exists('domxml_new_doc')){
		require_once(WBS_DIR."kernel/domxml-php4-to-php5.php" );	
	}

	define( "PHP5", true );
} else{
	define( "PHP5", false );
}

if(!function_exists('fopenTimeout')){
	function fopenTimeout($file,$mode = 'rb',$timeout = 3)
	{
		$old_timeout = ini_set('default_socket_timeout', $timeout);
		$fp = fopen($file, 'r');
		ini_set('default_socket_timeout', $old_timeout);
		$fp = &$fp;
		return $fp;
	}
}
if(!PHP5)die("PHP 5.0.0 or later required");

@ini_set( 'memory_limit', '64M' );
@ini_set('max_execution_time', 3600 );
@ini_set('allow_url_fopen','On');
@ini_set("magic_quotes_runtime",0);
if(function_exists('set_magic_quotes_runtime')&&!preg_match('/^5\.3/',PHP_VERSION)){
	set_magic_quotes_runtime(false);
}

$errorStr = null;
$invalidField = null;
$fatalError = false;

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


class updateLog
{
	var $logHandle;
	var $logCache;
	var $logDisplay;
	function __construct()
	{
		$this->logCache='';
		$this->logDisplay=array();
	}
	function __destruct()
	{
		if($this->logHandle){
			$this->write("*** close log");
			//$this->write(var_export(debug_backtrace(),true));
			fclose( $this->logHandle );
		}
	}
	
	function __writeDisplayLog()
	{
		if(count($this->logDisplay))
		{
			$fp=fopen(WBS_DIR."/temp/.displaylog",'w');
			if($fp){
				fwrite($fp,implode("\n",$this->logDisplay));
				fclose($fp);
			}
		}
	}
	
	function restoreDisplayLog()
	{
		$logName = WBS_DIR."/temp/.displaylog";
		$fp=fopen($logName,'r');
		if($fp){
			$fcontent = fread($fp,filesize($logName));
			fclose($fp);
			$this->logDisplay = explode("\n",$fcontent);
		}
	}
	function write($str='')
	{
		if(!$this->logHandle)
		$this->logHandle = @fopen(WBS_DIR.'temp/update_manager.log', 'a' );
		$date=($str)?date('y.m.d H:i:s '):'';
		$str .= "\n";
		$this->logCache .= $str;
		fwrite( $this->logHandle,  $date.$str);
	}
	function displayLog($str='')
	{
		$this->logDisplay[]=$str;
		$this->__writeDisplayLog();
		/*	if(strlen($this->logDisplay))
		{
		$fp=fopen(WBS_DIR."/temp/.displaylog",'a');
		if($fp){
		fwrite(serialize($this->logDisplay));
		fclose($fp);
		}
		}*/
	}
	function showDisplay()
	{
		return implode( "<br>", $this->logDisplay );
	}

};

class updateManager
{
	private $sysVersion;
	private $sysInstallDate;
	private $updateVersion;
	private $webVersionDate;
	private $applicationUpdates =array();
	private $applicationCount;
	public $updateSize;
	private $webVersion;
	public $updateNotCompleted;
	/**
	 * @var updateLog
	 */
	private $log;

	private $step;
	public $errorStr;
	public $invalidField;
	public $fatalError;

	private $scriptName;
	private $scriptPath;
	private $serverName;

	private $dbAdminURL;
	private $loginURL;
	private $licenseID;

	const SYS_VERSION_FILE		= 'kernel/wbs.xml';
	const UPDATE_VERSION_FILE	= 'temp/updates/update.xml';
	const MAX_DBLIST_NUM		= 10;

	const DISTRIBUTIVE_FILENAME_ = 'temp/updates/wbs.tgz';
	const DISTRIBUTIVE_FILENAME = 'temp/updates/distr/wbs.tgz';
	const DOWNLOAD_FILENAME 	= 'temp/updates/.webasyst_setup.tar.gz';
	const UPDATE_PATH			= 'temp/updates';
	
	
	const WEB_VERSION_FILENAME	= WA_ENV_WEB_VERSION_FILENAME;
	const WEB_CHANGE_FILENAME	= WA_ENV_WEB_CHANGE_FILENAME;
	const WEB_URL				= WA_ENV_WEB_URL;

	const LOCAL_CHANGE_FILENAME	= 'temp/.changelog.xml';

	const SYS_SETTINGS_FILE 	= 'kernel/wbs.xml';
	const COMPLETE_FLAG 		= 'kernel/complete';
	const HTACCESSREPLACED_FLAG = 'kernel/htareplaced';
	const NEWADMIN_FLAG 		= 'published/wbsadmin/html/scripts/step3.php';
	const UPDATE_SETTINGS_FILE 	= 'temp/updates/settings.xml';
	const GUIDE_FILE 			= 'help/webasystinstallguide.htm';

	const UPDATE_STATE_FILE		= 'temp/.update_state';

	const ACTION_NOACTION		= -1 ;
	const ACTION_INSTALL		= 0 ;
	const ACTION_REPAIR			= 1 ;
	const ACTION_UPDATE			= 2 ;
	const ACTION_NOVERSION		= 3 ;
	const ERRCODE_NOVERINFO		= 1 ;

	const STATE_UPDATE_CHECK	= 'update_check';
	const STATE_UPDATE_AVAILABLE= 'update_available';
	const STATE_UPDATE_CHECK_ERROR= 'update_check_error';
	const STATE_DOWNLOAD		= 'download';
	const STATE_DOWNLOAD_RESTART= 'download_restart';
	const STATE_DOWNLOAD_COMPLETE= 'download_complete';
	const STATE_DOWNLOAD_ERROR  = 'download_error';
	const STATE_UNPACK			= 'unpack';
	const STATE_UNPACK_COMPLETE = 'unpack_complete';
	const STATE_UNPACK_ERROR	= 'unpack_error';
	const STATE_INSTALL_SCRIPTS = 'install_scripts';
	const STATE_INSTALL_RESTART = 'install_restart';
	const STATE_INSTALL_COMPLETE= 'install_complete';
	const STATE_INSTALL_ERROR	= 'install_error';
	const STATE_UPDATE          = 'update_metada';
	const STATE_UPDATE_COMPLETE = 'update_metada_complete';
	const STATE_UPDATE_ERROR    = 'update_metada_error';
	const STATE_NONE			= 'none';
	
	private $timeStart;


	function __construct($LicenseID='')
	{
		$this->licenseID=strtoupper(str_replace('-','',$LicenseID));
		$this->updateNotCompleted=false;
		$this->errorStr = null;
		$this->invalidField = null;
		$this->fatalError = false;
		$this->log = new updateLog();
		$this->timeStart = microtime(true);

		$this->scriptName = $_SERVER['SCRIPT_NAME'];
		$this->scriptPath = substr( $this->scriptName, 0, strlen($this->scriptName)-strlen(basename($this->scriptName)) );
		$this->serverName = $_SERVER['SERVER_NAME'];

		$this->dbAdminURL = sprintf( "http://%s%spublished/admin.php", $this->serverName, $this->scriptPath );
		$this->loginURL = sprintf( "http://%s%spublished/login.php", $this->serverName, $this->scriptPath );

		$this->getSystemInfo();
	}
	function __destruct()
	{
		/*$fp=fopen(updateManager::SER_F_NAME,'w');
		fwrite($fp,serialize($this));
		fclose($fp);*/
	}
	function getProcessTime()
	{
		return (microtime(true)-$this->timeStart);		
	}
	function runInstall()
	{
		//global $displayLog;



		$sysVersion = null;
		$updateVersion = null;
		$actionType = $this->getActionType( $sysVersion, $updateVersion );
		if ( PEAR::isError($actionType) ) {
			$this->errorStr = $actionType->getMessage();
			$this->fatalError = true;
			break;
		}

		$updateVersion = $this->getSystemVersion(WBS_DIR.updateManager::UPDATE_VERSION_FILE, true );
		if ( PEAR::isError($updateVersion) ) {
			$this->errorStr = $updateVersion->getMessage();
			$this->fatalError = true;
			break;
		}
		$this->updateVersion = $updateVersion;

		switch ( $actionType ) {
			case updateManager::ACTION_REPAIR :
			case updateManager::ACTION_NOVERSION:
			case updateManager::ACTION_INSTALL : $res = $this->installScripts(); break;
			case updateManager::ACTION_UPDATE :  $res = $this->updateSystem(true); break;
		}

		if ( PEAR::isError($res) ) {
			$this->errorStr = $res->getMessage();
			$this->fatalError = true;
			break;
		}

		$res = $this->setSystemVersion( WBS_DIR.updateManager::SYS_VERSION_FILE, $updateVersion );
		if ( PEAR::isError($res) ) {
			$this->errorStr = $res->getMessage();
			$this->fatalError = true;
			break;
		}


	}
	public function getSystemInfo()
	{
		if(!$this->sysVersion){
			$this->checkLocalVersion();
			$this->checkUpdateVersion();
			$this->checkWebVersion();
		}
		$res=array('localVersion'=>$this->sysVersion,
		'installDate'=>$this->sysInstallDate,
		'downloadedVersion'=>$this->updateVersion,
		'webVersion'=>$this->webVersion,
		'webVersionDate'=>$this->webVersionDate,
		'applicationUpdates'=>$this->applicationUpdates,
		'applicationCount'=>$this->applicationCount,
		'updateSize'=>$this->updateSize);
		$state=self::getState();
		$res['state']=$state['state'];
		$res['error']=$this->getErrorState();
		$res['updateAvailable']=($state['state']!=updateManager::STATE_NONE
								&&$state['state']!=updateManager::STATE_INSTALL_COMPLETE);
		$res['time']=time()-$state['timeStart'];
		$res['msg']=$state['msg'];
		return $res;
	}
	public function getReport()
	{
		return $this->log->showDisplay();
	}
	private function checkLocalVersion()
	{
		$this->sysVersion = $this->getSystemVersion(WBS_DIR.updateManager::SYS_VERSION_FILE, false);
		if ( PEAR::isError($this->sysVersion) ) {
			$this->errorStr .= $this->sysVersion->getMessage()."\n";
			$this->fatalError = true;
			$this->sysVersion='unknown';
		}
		$this->sysInstallDate = $this->getSystemInstallDate(WBS_DIR.updateManager::SYS_VERSION_FILE, false);
		if ( PEAR::isError($this->sysInstallDate) ) {
			$this->errorStr .= $this->sysInstallDate->getMessage()."\n";
			$this->fatalError = true;
			$this->sysInstallDate=null;
		}

	}
	private function checkUpdateVersion()
	{
		/*$state=self::getState();
		$this->updateVersion=null;
		if(($state['state']==updateManager::STATE_UNPACK_COMPLETE)){
			$msg=unserialize($state['msg']);
			$this->updateVersion=intval($msg['version']);
		}*/
		$this->updateVersion = $this->getSystemVersion(WBS_DIR.updateManager::UPDATE_VERSION_FILE, true);
		if ( PEAR::isError($this->updateVersion) ) {
			$this->errorStr .= $this->updateVersion->getMessage()."\n";
			$this->fatalError = true;
			//$this->updateVersion = null;
			$this->updateVersion = $this->updateVersion->getMessage();
		}
	}
	private function checkWebVersion()
	{
		//get from web xml file with version information
		$stat=self::getState();
		$strongCheck=false;
		if($stat['state']==updateManager::STATE_UPDATE_AVAILABLE||$stat['state']==updateManager::STATE_NONE )
		{
			$buf=unserialize($stat['msg']);
			if($buf){
				$this->webVersion=$buf[0];
				$this->webVersionDate=$buf[1];
				$this->applicationCount=$buf[2];
				$this->applicationUpdates=$buf[3];
			}else{
				$strongCheck=true;
			}
		}
		//check current state and if check web
		if($stat['state']!=updateManager::STATE_INSTALL_COMPLETE
		&&$stat['state']!=updateManager::STATE_NONE
		&&$stat['state']!=updateManager::STATE_UPDATE_AVAILABLE ){
			return;
		}

		//check update not frequcy then every 15 minutes
		if (!$strongCheck&&isset($stat['timeStart'])&&$stat['timeStart']&&(time()-$stat['timeStart']<1200)){
			return;
		}
		//$this->setState(updateManager::STATE_UPDATE_CHECK,updateManager::WEB_VERSION_FILENAME);
		$wbsXml=simplexml_load_file_timeout(updateManager::WEB_VERSION_FILENAME);
		if(!$wbsXml){
			$this->setState(updateManager::STATE_UPDATE_CHECK_ERROR,serialize(array('fname'=>updateManager::WEB_VERSION_FILENAME,'errCode'=>'err_read_version_file')));
			$this->setState($stat['state'],$stat['msg']);
			$this->fatalError = true;
			$this->webVersion=null;
			return;
		}else{
			$this->webVersion=intval((string)$wbsXml['VERSION']);
			$this->webVersionDate=(string)$wbsXml['DATE'];
			$this->applicationCount=0;
			foreach ($wbsXml->APPLICATIONS->children() as $application)
			{
				$this->applicationCount++;
			}
		}

		//difference critical updates/ patches & bug-fix/ new abilitys
		if(($this->webVersion!==null)&&$this->webVersion>$this->sysVersion){
			$applicationUpdates=$this->getChangeLog();
			if($applicationUpdates != -1){
				global $language;
				$applicationInstalled = listPublishedApplications( $language, true );
				//var_dump($applicationInstalled);

				//check updates for setuped services
				$res=false;
				$this->applicationUpdates=array();
				foreach ($applicationUpdates as $APP_ID=>$changes)
				{
					//	if(is_array($changes)&&count($changes)&&isset($applicationInstalled[$APP_ID]))
					if(is_array($changes)&&count($changes)
					&&(isset($applicationInstalled[$APP_ID])||$APP_ID=="KERNEL")){
						$this->applicationUpdates[$APP_ID]=true;
						$res=true;
					}
				}

				if($res){
					$this->setState(updateManager::STATE_UPDATE_AVAILABLE ,
					serialize(array($this->webVersion,$this->webVersionDate,
					$this->applicationCount,$this->applicationUpdates)));
				}elseif($stat['state']==updateManager::STATE_UPDATE_AVAILABLE){
					$this->setState(updateManager::STATE_NONE ,
					serialize(array($this->webVersion,$this->webVersionDate,
					$this->applicationCount,$this->applicationUpdates)));
				}else{

					$this->setState($stat['state'],$stat['msg']);
				}
			}else{
				$this->webVersion=null;
				$this->setState(updateManager::STATE_NONE ,updateManager::WEB_CHANGE_FILENAME.' Couldn\'t get newer version info');
			}
		}elseif ($this->webVersion===null){
			$this->setState(updateManager::STATE_NONE ,updateManager::WEB_VERSION_FILENAME.' Couldn\'t get newer version info');
		}

	}
	public function getChangeLog($APP_ID=null,$fullChangeLog = false)
	{
		global $language;
		if(file_exists(WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME)){
			$wbsXml=simplexml_load_file(WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME);
		}
		//check changelog version
		if((!$wbsXml)||($wbsXml&&isset($wbsXml['VERSION'])&&intval($wbsXml['VERSION'])<$this->webVersion))
		{


			if($wbsXml&&intval($wbsXml['VERSION'])<$this->webVersion){
				unlink(WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME);
			}
			$wbsXml=null;
			if(!copy(updateManager::WEB_CHANGE_FILENAME,WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME)){
				$this->log->write('Error download new version of changelog from '.updateManager::WEB_CHANGE_FILENAME.
				' to '.WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME);
			}else{
				$wbsXml=simplexml_load_file(WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME);
			}

		}
		if(!$wbsXml){
			$this->setState(updateManager::STATE_UPDATE_CHECK_ERROR,
			serialize(array('fname'=>WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME,'errCode'=>'err_read_version_file')));
			$this->setState($stat['state'],$stat['msg']);
			$this->errorStr .= "Error reading file ".WBS_DIR.updateManager::LOCAL_CHANGE_FILENAME."\n";
			$this->fatalError = true;
			$this->webVersion='unknown';
			return -1;
		}else{
			$applicationInstalled = listPublishedApplications( $language, true );
			$this->webVersion=intval((string)$wbsXml['VERSION']);
			//connect to web server and check updates
			$changes=array();
			foreach ($wbsXml->children() as $change){
				/*@var $change SimpleXMLElement*/
				$version=intval((string)$change['VERSION']);
				if(!$fullChangeLog&&$version<=$this->sysVersion)
				continue;

				foreach ($change->children() as $changeApp){
					/*@var $changeApp SimpleXMLElement*/
					$currAPP_ID=strtoupper((string)$changeApp['APP_ID']);
					if(!isset($applicationInstalled[$currAPP_ID])&&$currAPP_ID!='KERNEL')
					continue;

					if(!$APP_ID||($APP_ID&&strcmp($APP_ID,$currAPP_ID)==0)){
						//$changeString=base64_decode((string)$changeApp['CONTENT']);
						//if(!strlen($changeString))
						
						$changeAppContent = $changeApp->xpath(strtoupper($language));
						//var_dump(array(strtoupper($language),$changeAppContent,$changeApp,$changeApp->asXML()));
						if(is_array($changeAppContent)&&count($changeAppContent)){
							$changeAppContent = $changeAppContent[0];
							$changeString=(string)$changeAppContent['CONTENT'];
							if($changeString_ = base64_decode($changeString)){
								$changeString = $changeString_;
							}
						}else{						
							$changeString=(string)$changeApp['CONTENT'];
						}
						if(strlen($changeString)){
							
							if(!isset($changes[$currAPP_ID][(string)$change['VERSION']])){
								$changes[$currAPP_ID][(string)$change['VERSION']] = '';
							}
							$changes[$currAPP_ID][(string)$change['VERSION']].=nl2br($changeString."\n");
						}
					}
				}
			}
			foreach ($changes as $APP_ID=>$change)
			{
				//$changes[$APP_ID] = array_reverse(,true);
				ksort($change,SORT_NUMERIC);
				$changes[$APP_ID] = array_reverse($change,true);
			}
			return $changes;
		}
	}
	public function downloadUpdate($retry=false)
	{
		global $language;
		$state=self::getState();
		if($retry&&($state['state']==updateManager::STATE_DOWNLOAD_ERROR
			||$state['state']==updateManager::STATE_DOWNLOAD_RESTART
			||$state['state']==updateManager::STATE_NONE )){
			$this->setState(updateManager::STATE_NONE );
			$this->checkWebVersion();
			$state=self::getState();

		}

		if(($state['state']!=updateManager::STATE_UPDATE_AVAILABLE)
		&&(!$retry
		||($retry
		&&($state['state']!=updateManager::STATE_DOWNLOAD_ERROR
		&&$state['state']!=updateManager::STATE_INSTALL_ERROR
		&&$state['state']!=updateManager::STATE_NONE)
		)
		))
		return null;
		$time=microtime(true);
		$this->log->displayLog( sprintf("Download start"));

		if(file_exists(WBS_DIR.updateManager::DOWNLOAD_FILENAME)){
			unlink(WBS_DIR.updateManager::DOWNLOAD_FILENAME);
		}
		if(!file_exists(WBS_DIR.updateManager::UPDATE_PATH)){
			$result = @mkdir(WBS_DIR.updateManager::UPDATE_PATH,0777);
			if(!$result)
			$this->setState(updateManager::STATE_DOWNLOAD_ERROR  , serialize(array('errCode'=>'err_extract','fname'=>WBS_DIR.updateManager::UPDATE_PATH,'msg'=>'Couldn\'t create directory')));
		}

		$count=0;
		$md5hash=null;
		do{
			$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
			$url='http://'.updateManager::WEB_URL.'/cc/updatemanager.php?action=get_file_status&licenseNumber='.$this->licenseID.'&lang='.$language;
			$socket=fopenTimeout($url,'r');
			if(isset($_GET['test'])){print "url=[{$url}]<br>";if($_GET['test']== 'url')exit;}
			if(!$socket){
				$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>updateManager::WEB_URL,'errCode'=>'err_socket_update')));
				return;
			}else{
				$res = stream_get_contents($socket,4096);
				$headers=stream_get_meta_data($socket);
				$headers_data = array();
				if(isset($headers["wrapper_data"]["headers"])){
					$headers_data = $headers["wrapper_data"]["headers"];
				}elseif(isset($headers["wrapper_data"])){
					$headers_data = $headers["wrapper_data"];
				}
				if(isset($_GET['test'])){
					print "<hr><pre>";
					var_dump(array($headers,$headers_data));
					print "</pre><br><hr>";
				}
				$contenLength = 0;
				foreach ($headers_data as $header){
					$v=explode(':',$header);
					if(trim(mb_strtolower($v[0],'UTF-8'))=='content-length'){
						$contenLength=intval(trim($v[1]));
					}
					if(isset($_GET['test'])){
						var_dump($v);
						print "<br>";
					}
				}
				if($contenLength){
					$res=substr($res,0,$contenLength);
				}
				if(isset($_GET['test'])/*&&!$contenLength*/){
					print "<hr><pre>";
					var_dump(array($headers,$contenLength,$res));
					print "</pre><hr>";
					//print stream_get_contents($socket).'<br>';
				}
				fclose($socket);

//				$responseData=unserialize(trim(substr($res,strpos($res,"\r\n\r\n"))));
				$responseData=unserialize(trim(str_replace(array("\r","\n"),array('',''),$res)));

				if($responseData){
					$md5hash=$responseData['md5'];
					if(isset($responseData['succes'])&&!$responseData['succes']){
						$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>updateManager::WEB_URL,'errCode'=>'err_license_update','msg'=>$responseData['msg'])));
						return;
					}

				}else{
					$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
					if(isset($_GET['test']))print var_dump(array("line:".__LINE__,$res,$responseData));
						return;
				}
			}
			if(!$md5hash){
				if(($count++)>30){
					$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>updateManager::WEB_URL,'errCode'=>'err_md5_update')));
				return ;
				}
				$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
				sleep(10);
				$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
				sleep(10);
				$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
				sleep(10);
				$this->setState(updateManager::STATE_DOWNLOAD, serialize(array('source'=>updateManager::WEB_URL,'prepare'=>true)));
			}
		}while(!$md5hash);
		$url='http://'.updateManager::WEB_URL.'/cc/updatemanager.php?action=get_file&licenseNumber='.$this->licenseID.'&lang='.$language;
		if(isset($_GET['test'])){print "url=[{$url}]<br>";if($_GET['test']== 'url')exit;}
		$source=fopenTimeout($url,'r',3600);
		$destiny=fopen(WBS_DIR.updateManager::DOWNLOAD_FILENAME,'wb');




		if($source&&$destiny){
			//var_dump(array($source,$destiny));exit;



			$inf=stream_get_meta_data($source);
			$buf = stream_get_contents($source,4096);

			$headers_data = array();
			if(isset($inf["wrapper_data"]["headers"])){
				$headers_data = $inf["wrapper_data"]["headers"];
			}elseif(isset($inf["wrapper_data"])){
				$headers_data = $inf["wrapper_data"];
			}
			if(isset($_GET['test'])){
				print "<hr>Download file<br><pre>";
				var_dump(array($inf,$headers_data));
				print "</pre><br><hr>";
			}
			$this->updateSize = 0;
			foreach($headers_data as $v)
			{
				if (stristr($v,"content-length"))
				{
					$v= explode(":",$v);
					$this->updateSize=intval(trim($v[1]));
				}
				//if(stristr($v,''))
			}
			if(isset($_GET['test'])){
				print "<hr>File size {$this->updateSize}<hr>";
			}
			//$this->updateSize=$fileSize;
			$this->setState(updateManager::STATE_DOWNLOAD,
			serialize(array('source'=>$fname,'size'=>$this->updateSize,'downloadSize'=>$downloadSize,'progress'=>$this->updateSize?round(100*$downloadSize/$this->updateSize,0):'unknown')));

			if($buf){
				fwrite($destiny,$buf);
			}

			$downloadSize=4096;
			$sizePacket=max($this->updateSize?ceil($this->updateSize/10240000)*102400:102400,102400);
			$retry_counter = 0;
			do{
				$delta=stream_copy_to_stream($source,$destiny,$sizePacket);
				$downloadSize+=$delta;
				$this->setState(updateManager::STATE_DOWNLOAD,
				serialize(array('source'=>$fname,'size'=>$this->updateSize,'delta'=>var_export(array($delta,$sizePacket),true),'downloadSize'=>$downloadSize,'progress'=>$this->updateSize?round(100*$downloadSize/$this->updateSize,0):'unknown')));
				if($this->updateSize>$downloadSize&&!$delta&&($retry_counter<5)){
					sleep(2);
					$retry_counter++;
				}elseif($delta){
					$retry_counter = 0;
				}

			}while($delta);
			
			//stream_get_meta_data()

			fclose($source);
			fclose($destiny);

			if(!file_exists(WBS_DIR.updateManager::DOWNLOAD_FILENAME)){

				$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>WBS_DIR.updateManager::DOWNLOAD_FILENAME,'errCode'=>'err_save_update')));
			}else{
				$md5hashFile=md5_file(WBS_DIR.updateManager::DOWNLOAD_FILENAME);
				if($md5hash&&($md5hash!=$md5hashFile)) {
					if(file_exists(WBS_DIR.updateManager::DOWNLOAD_FILENAME)){
						unlink(WBS_DIR.updateManager::DOWNLOAD_FILENAME);
					}
					$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>WBS_DIR.updateManager::DOWNLOAD_FILENAME,'errCode'=>'err_md5_update','debug'=>array('md5_control'=>$md5hash,'md5_file'=>$md5hashFile,'file_size'=>$downloadSize,'real_size'=>filesize(WBS_DIR.updateManager::DOWNLOAD_FILENAME)))));
				}else{
					$time=sprintf('%4.3fs',microtime(true)-$time);
					$this->setState(updateManager::STATE_DOWNLOAD_COMPLETE,'time required: '.$time.' MD5:'.$md5hashFile);
					$this->log->displayLog( sprintf("Download complete on: %s", $time));
					
				}
			}
		}else{
			$destiny_file=WBS_DIR.updateManager::DOWNLOAD_FILENAME;
			$this->setState(updateManager::STATE_DOWNLOAD_ERROR,serialize(array('fname'=>($source?" succes open {$url}":" error open {$url}").($destiny?" succes open {$destiny_file}":" error open {$destiny_file}"),'errCode'=>'err_server_connect','msg'=>WBS_DIR.updateManager::DOWNLOAD_FILENAME)));
		}

		clearstatcache();

	}
	private function fsize($path)
	{
		$fp = fopenTimeout($path,"r");
		$inf = stream_get_meta_data($fp);
		fclose($fp);
		foreach($inf["wrapper_data"] as $v)
		if (stristr($v,"content-length"))
		{
			$v = explode(":",$v);
			return trim($v[1]);
		}
	}
	private function fcopy($source,$destiny)
	{
		$fps=fopenTimeout($source,'r');
		if(!$fps)return false;
		if(file_exists($destiny))
		unlink($destiny);
		$fpd=fopen($destiny,'w');
		if(!$fpd){fclose($fps);return false;}
		$bufer=null;
		do{
			$bufer=fread($fps,4096);
			fwrite($fpd,$bufer);


		}while($bufer);
		fclose($fps);
		fclose($fpd);
		return true;

	}
	public function unPack($retry=false)
	{
		$state=self::getState();
		if($state['state']!=updateManager::STATE_DOWNLOAD_COMPLETE
		&&(!$retry||$retry&&($state['state']!=updateManager::STATE_UNPACK_ERROR))  ){
			//$this->log->write($state['state'].':(((');
			return null;
		}
		if(!file_exists(WBS_DIR.updateManager::DOWNLOAD_FILENAME ))
		{
			$this->setState(updateManager::STATE_UNPACK_ERROR,serialize(array('errCode'=>'err_file_read','fname'=>WBS_DIR.updateManager::DOWNLOAD_FILENAME )));
			return null;
		}

		$this->setState(updateManager::STATE_UNPACK );
		$timeStart=microtime(true);
		require_once(WBS_DIR.'/published/wbsadmin/classes/class.fasttar.php');
		ob_start();
		$tar=new Archive_Tar(WBS_DIR.updateManager::DOWNLOAD_FILENAME ,true);
		$tar->setErrorHandling( PEAR_ERROR_PRINT );
		$result = $tar->extract(WBS_DIR.updateManager::UPDATE_PATH );
		//$list = $tar->listContent();
		//$tar->extractList($list,WBS_DIR.updateManager::UPDATE_PATH);
		$tar_out = ob_get_clean();

		if ( !$result)
		{
			$this->setState(updateManager::STATE_UNPACK_ERROR , serialize(array('errCode'=>'err_extract','fname'=>WBS_DIR.updateManager::DOWNLOAD_FILENAME,'msg'=>$tar_out)));
		}else{
			$totalTime=microtime(true)-$timeStart;
			unlink(WBS_DIR.updateManager::DOWNLOAD_FILENAME);
			$this->setState(updateManager::STATE_UNPACK_COMPLETE,serialize(array('time'=>sprintf('%3.3f',$totalTime),'destiny folder'=>WBS_DIR,'version'=>$this->webVersion)));
		}
	}

	function setState($state = updateManager::STATE_NONE,$msg='', $fast = false)
	{
		if(is_array($msg)){
			$msg = serialize($msg);
		}
		if(self::_setState($state,$msg,$fast)){
			$this->log->write(' ['.$state.'] '.$msg);
		}else{
			$this->log->write(' ['.$state.'] '.$msg.'Error write to file '.WBS_DIR.updateManager::UPDATE_STATE_FILE);
		}
		return $state;
	}
	function cleanState($msg,$info)
	{
		$file = WBS_DIR.updateManager::UPDATE_STATE_FILE;
		if(file_exists($file)){
			if(unlink($file)){
				$this->log->write(" [{$msg}] ".var_export($info,true));
			}else{
				$this->log->write("Error unlink file {$file} on {$msg} ".var_export($info,true));
			}
		}
	}
	static function _setState($state = updateManager::STATE_NONE,$msg='',$fast = false )
	{
		clearstatcache();
		if(is_array($msg)){
			$msg = serialize($msg);
		}
		$fp=fopen(WBS_DIR.updateManager::UPDATE_STATE_FILE,'w' );
		if($fp){
			$state=array('state'=>$state,'timeStart'=>$fast?0:time(),'msg'=>$msg);
			fwrite($fp,serialize($state));
			fclose($fp);
			clearstatcache();
			return $state;
		}else{
			return null;
		}
	}
	static function getState()
	{
		clearstatcache();
		$res=array('state'=>updateManager::STATE_NONE,'timeStart'=>null,'msg'=>'');
		if(!file_exists(WBS_DIR.updateManager::UPDATE_STATE_FILE)){
			updateManager::_setState(updateManager::STATE_NONE,array(),true);
			return $res;
		}
		$readBuf=file_get_contents(WBS_DIR.updateManager::UPDATE_STATE_FILE);
		if($readBuf!==false){
			$res['debug']=$readBuf;
			$readBuf=unserialize($readBuf);
			$res['debug_2']=$readBuf;
			if(isset($readBuf['state'])){//check 30min operation time_out
				if(in_array($readBuf['state'],
					array(updateManager::STATE_UNPACK,
						  updateManager::STATE_INSTALL_SCRIPTS))&&((time()-$readBuf['timeStart'])>1200)){
						  switch ($readBuf['state']){
								case updateManager::STATE_UNPACK :{
									$state=updateManager::STATE_UNPACK_ERROR;
									break;
								}
								case updateManager::STATE_INSTALL_SCRIPTS:{
									$state=updateManager::STATE_INSTALL_ERROR;
									break;
								}
						  		case updateManager::STATE_UPDATE:{
									$state=updateManager::STATE_INSTALL_ERROR;
									break;
								}
								default:
								$state=updateManager::STATE_NONE ;
						  }
						  updateManager::_setState($state,$readBuf['msg']);
						  	
						  	//time-
				}elseif(in_array($readBuf['state'],
					array( updateManager::STATE_INSTALL_SCRIPTS))
					&&((time()-$readBuf['timeStart'])>10)){//add support for partial extracting
					switch ($readBuf['state']){
						case updateManager::STATE_INSTALL_SCRIPTS:{
							$state=updateManager::STATE_INSTALL_RESTART;
							break;
						}
						default:{
						$state=updateManager::STATE_INSTALL_SCRIPTS ;
						}
					}
					return updateManager::_setState($state,$readBuf['msg']);
				}elseif(in_array($readBuf['state'],
					array( updateManager::STATE_DOWNLOAD))
					&&((time()-$readBuf['timeStart'])>50)){//add support for partial download //TODO: partial download
					switch ($readBuf['state']){
						case updateManager::STATE_DOWNLOAD:{
							$state=updateManager::STATE_DOWNLOAD_RESTART;
							break;
						}
						default:{
						$state=updateManager::STATE_DOWNLOAD ;
						}
					}
					return updateManager::_setState($state,$readBuf['msg']);
				}
				$res['state']=$readBuf['state'];
				if(isset($readBuf['timeStart'])&&$readBuf['timeStart'])
				$res['timeStart']=$readBuf['timeStart'];
				if(isset($readBuf['msg'])&&$readBuf['msg'])
				$res['msg']=$readBuf['msg'];
			}
		}else{
			$res['msg']='error read file '.WBS_DIR.updateManager::UPDATE_STATE_FILE.' not exists';
			//$this->log->write($res['msg']);
		}
		clearstatcache();
		return $res;
	}
	function getErrorState()
	{
		$state=self::getState();
		if(!in_array($state['state'],array(updateManager::STATE_DOWNLOAD_ERROR ,
		updateManager::STATE_INSTALL_ERROR,updateManager::STATE_UNPACK_ERROR,
		updateManager::STATE_UPDATE_CHECK_ERROR )))
		return null;
		$error=unserialize($state['msg']);
		if(!$error)
		return null;

		$error['time']=$state['timeStart'];
		$error['errCode']=sprintf('upd_m_%s',$error['errCode']);

		return $error;
	}
	private function getSystemVersion( $filePath, $updateVersion )
	{
		
		$content = $this->fileContent( $filePath );
		if(!$content)
		return null;//PEAR::raiseError( "Empty version file {$filePath}" );

		$dom = @domxml_open_mem( $content );
		if ( !$dom )
		return PEAR::raiseError( "Invalid structure of version file {$filePath}" );

		$xpath = @xpath_new_context($dom);
		$nodePath = ($updateVersion) ? '/METADATAUPDATE' : '/WBS';
		if ( !( $versionnode = xpath_eval($xpath, $nodePath) ) )
		return PEAR::raiseError( "Error parsing version file {$filePath}" );

		if ( !count($versionnode->nodeset) )
		return PEAR::raiseError( "Invalid version file {$filePath}" );

		$versionnode = $versionnode->nodeset[0];
		$versionValue = $versionnode->get_attribute( 'VERSION' );


		if ( !strlen($versionValue) && !$updateVersion )
		return PEAR::raiseError( "Version information is not found  {$filePath}", ERRCODE_NOVERINFO );

		return $versionValue;
	}
	private function getSystemInstallDate( $filePath)
	{
		$content = $this->fileContent( $filePath );
		if(!$content)
		return PEAR::raiseError( "Error opening version file {$filePath}" );

		$dom = @domxml_open_mem( $content );
		if ( !$dom )
		return PEAR::raiseError( "Error opening version file {$filePath}" );

		$xpath = @xpath_new_context($dom);
		if ( !( $versionnode = xpath_eval($xpath, '/WBS') ) )
		return PEAR::raiseError( "Error parsing version file {$filePath}" );

		if ( !count($versionnode->nodeset) )
		return PEAR::raiseError( "Invalid version file {$filePath}" );

		$versionnode = $versionnode->nodeset[0];
		$DateValue = $versionnode->get_attribute( 'UPDATEDATE' );


		if ( !strlen($DateValue) && !$DateValue )
		return PEAR::raiseError( "Install date information is not found  {$filePath}");

		return $DateValue;
	}
	private function setSystemVersion( $filePath, $version )
	{
		$content = $this->fileContent( $filePath );

		$dom = @domxml_open_mem( $content );
		if ( !$dom )
		return PEAR::raiseError( "Error opening version file  {$filePath}" );

		$xpath = @xpath_new_context($dom);
		if ( !( $versionnode = &xpath_eval($xpath, '/WBS') ) )
		return PEAR::raiseError( "Error parsing version file  {$filePath}" );

		if ( !count($versionnode->nodeset) )
		return PEAR::raiseError( "Invalid version file  {$filePath}" );

		$versionnode = $versionnode->nodeset[0];

		$versionnode->set_attribute( 'VERSION', $version );
		$versionnode->set_attribute( 'UPDATEDATE', date( 'Y-m-d' ) );

		$filePath = realpath( $filePath );
		return $dom->dump_file( $filePath, false, true );
	}
	public function getActionType( &$sysVersion, &$updateVersion )
	{
		$complete = true;

		if ( file_exists(WBS_DIR.updateManager::NEWADMIN_FLAG) )
		$complete = file_exists(WBS_DIR. updateManager::COMPLETE_FLAG );

		if ( !file_exists( WBS_DIR.updateManager::SYS_SETTINGS_FILE ) || !$complete )
		return updateManager::ACTION_INSTALL;

		$noSysVersion = false;

		$sysVersion = $this->getSystemVersion( WBS_DIR.updateManager::SYS_VERSION_FILE, false );
		if ( PEAR::isError($sysVersion) )
		if ( $sysVersion->getCode() != updateManager::ERRCODE_NOVERINFO )
		return $sysVersion;
		else
		$noSysVersion = true;

		$updateVersion = $this->getSystemVersion(WBS_DIR. updateManager::UPDATE_VERSION_FILE, true );
		if ( PEAR::isError($updateVersion) )
		return $updateVersion;

		if ( $noSysVersion ) {
			$sysVersion = $updateVersion;
			return updateManager::ACTION_NOVERSION;
		}

		if ( $sysVersion == $updateVersion )
		return updateManager::ACTION_REPAIR;

		if ( $sysVersion < $updateVersion )
		return updateManager::ACTION_UPDATE;

		if ( $sysVersion > $updateVersion )
		return updateManager::ACTION_NOACTION;
	}
	public function getUpdateDetails()
	{
		$res=array();
		$res['APP_list']=null;
		$res['targetDbList'] = $this->listTargetDatabases( $this->sysVersion, $this->updateVersion,$res['APP_list'] );
		if ( PEAR::isError($res['targetDbList']) ) {
			$errorStr = $res['targetDbList']->getMessage();
			$fatalError = true;
		}

		$res['targetDBCount'] = count($res['targetDbList']);
		$res['DBlistIsTruncated'] = $res['targetDBCount'] > updateManager::MAX_DBLIST_NUM;
		$res['truncatedDBList'] = array_slice( $res['targetDbList'], 0,  updateManager::MAX_DBLIST_NUM );

		if ( $res['DBlistIsTruncated'] )
		$res['fullDbList'] = sprintf( 'showdblist.php?list=%s', base64_encode( serialize($res['targetDbList']) ) );
		return $res;
	}
	public function getMetaDataUpdateDetails()
	{
		$result=array();
		$updateList = $this->parseUpdateFile( $this->sysVersion );
		if ( PEAR::isError($updateList) )
		return $updateList;
		foreach( $updateList as $app_id=>$app_updates ){
			$result[$app_id]=array();
			foreach( $app_updates as $update ) {
				$result[$app_id][] = base64_decode($update);
				$update= base64_decode($update);
				//echo $update."<br>";
			}
		}
		//return $updateList;
		return $result;

	}
	public function ShowMetaDataUpdateDetails($APP_ID_req=null)
	{
		$updateContent=$this->getMetaDataUpdateDetails();
		$appData = listPublishedApplications( $language, true );
		if ( !is_array( $appData ) ) {
			$appData=null;
		}else{
			$appData = sortPublishedApplications( $appData );
		}

		/*foreach ($appData as $APP_ID=>$application){
		if(isset($systemInfo['applicationUpdates'][$APP_ID])&&($systemInfo['applicationUpdates'][$APP_ID])){
		$applicationUpdates[$APP_ID]=$application['APPLICATION']['LOCAL_NAME'];
		//print "{$APP_ID}=='KERNEL'".($APP_ID=='KERNEL'?'true':'false')."<br>";
		//var_dump($appData['APPLICATION']);exit;
		}
		}
		if(isset($systemInfo['applicationUpdates']['KERNEL'])){
		$applicationUpdates['KERNEL']='WebAsyst KERNEL';
		}


		$ret=array();
		*/
		$appData=array_merge($appData,array('Kernel'=>''));

		foreach ($appData as $APP_ID=>$application)
		{
			if(strtoupper($APP_ID)=='KERNEL')$APP_ID='Kernel';
			if(!isset($updateContent[$APP_ID])||$APP_ID_req&&(strtoupper($APP_ID)!=strtoupper($APP_ID_req)))
			continue;
			$update=$updateContent[$APP_ID];
			if(strtoupper($APP_ID)=='KERNEL')$APP_ID='WebAsyst Kernel';
			if(strtoupper($APP_ID)=='KERNEL')$APP_ID=$application['APPLICATION']['LOCAL_NAME'];


			foreach ($update as $updateItem)
			{


				if(!is_array($ret[$APP_ID]))$ret[$APP_ID]=array('#'=>'',);

		$ret[$APP_ID]['#'].=''.nl2br(str_replace(array('DROP','CREATE TABLE','ALTER TABLE','UPDATE','INSERT INTO','DELETE','mysql_query'),
		array('<font color=red>DROP</font>','<font color=green>CREATE TABLE</font>','<font color=cyan>ALTER TABLE</font>','<font color=cyan>UPDATE</font>','<font color=green>INSERT INTO</font>','<font color=red>DELETE</font>','<font color=blue>mysql_query</font>'),
		$updateItem)).'<br>';

	}
}
return $ret;

$res='';
foreach( $updateContent as $app_id=>$app_updates ){
	$res.="<h2>{$app_id}</h2><br>";
	foreach( $app_updates as $update ) {
		$update = str_replace(array('DROP','CREATE TABLE','ALTER TABLE','UPDATE','INSERT INTO','DELETE','mysql_query'),
		array('<font color=red>DROP</font>','<font color=green>CREATE TABLE</font>','<font color=cyan>ALTER TABLE</font>','<font color=cyan>UPDATE</font>','<font color=green>INSERT INTO</font>','<font color=red>DELETE</font>','<font color=blue>mysql_query</font>'),
		$update);
		$res.="<pre>{$update}</pre>";
	}
}

return wordwrap($res,80,"<br>");
}

public function updateSystem($force=false,$restart = false)
{
	clearstatcache();
	//if(!$this->sysVersion){
	
	//}
	$state=self::getState();
	if(($state['state'] ==updateManager::STATE_UNPACK_COMPLETE)||
	($force&&($state['state']==updateManager::STATE_INSTALL_ERROR))||
	($restart&&($state['state']==updateManager::STATE_INSTALL_RESTART))
	){
		
	}else{
		return;
	}
	$this->checkLocalVersion();
	$this->checkUpdateVersion();
	
	$sysVersion=$this->sysVersion;
	$updateVersion=$this->updateVersion;
	
	if(!$restart){		
		$this->setState(updateManager::STATE_INSTALL_SCRIPTS,'Updating scripts...' );
		$this->setState(updateManager::STATE_INSTALL_SCRIPTS, sprintf("Update start: %s", date("M'd Y H:i:s") ) );
		$this->setState(updateManager::STATE_INSTALL_SCRIPTS, sprintf("Old version: %s", $sysVersion ) );
		$this->setState(updateManager::STATE_INSTALL_SCRIPTS, sprintf("New version: %s", $updateVersion ) );
		$this->setState(updateManager::STATE_INSTALL_SCRIPTS, "Updating scripts..." );
		
		$this->log->displayLog( sprintf("Update start: %s", date("M'd Y H:i:s") ) );	
		$this->log->displayLog( "Updating scripts..." );
		$this->log->displayLog( sprintf("Old version: %s", $sysVersion ) );
		$this->log->displayLog( sprintf("New version: %s", $updateVersion ) );
		$this->log->displayLog( sprintf( "<b>Start</b>: %s", date("M'd Y H:i:s") ) );
		$this->log->displayLog();
	}else{
		$this->log->restoreDisplayLog();
		$this->log->displayLog( sprintf( "<b>Resume installing</b>: %s", date("M'd Y H:i:s") ) );
	}
	
	// Install scripts
	//
	$res = $this->installScripts();
	if ( PEAR::isError( $res ) ) {//STATE_INSTALL_ERROR
		$this->log->displayLog( "Upgrading scripts: <font color=red>Error</font>" );
		$this->log->displayLog();
		$this->setState(updateManager::STATE_UNPACK_ERROR ,serialize(array('errCode'=>'err_install','msg'=>$res->getMessage())));
		return null;
	}
	
	if($this->getProcessTime()>10){
		$this->setState(self::STATE_INSTALL_RESTART,array(-1,time(),-1));
		$this->log->write('exit to resume on clean update');
		exit;
	}else{
		$this->log->write('continue because process time is '.$this->getProcessTime());
	}

	$this->log->displayLog( "Upgrading scripts: <font color=green>Successfully</font>" );
	$this->log->displayLog();

	$this->setState(updateManager::STATE_INSTALL_SCRIPTS, "Complete" );
	$this->setState(updateManager::STATE_INSTALL_SCRIPTS,'Upgrading scripts Successfully' );//update time stamp
	

	$this->setState(updateManager::STATE_UPDATE, "Updating database structure..." );
	$this->setState(updateManager::STATE_UPDATE,'Updating database structure...' );

	// Update metadata
	//
	$updateList = $this->parseUpdateFile( $sysVersion );
	if ( PEAR::isError($updateList) )
	{
		$this->setState(updateManager::STATE_INSTALL_ERROR ,serialize(array('errCode'=>'err_update_meta','msg'=>$updateList->getMessage())));
		return null;
	}

	$mdUpdateTriggered = false;

	if ( !count($updateList) ) {
		$this->log->write( "No metadata update required" );
	} else {
		$updateApplications = array_keys( $updateList );

		$wbs_sqlServers = $this->listSystemServers();
		if ( PEAR::isError($wbs_sqlServers) ) {
			$this->setState(updateManager::STATE_INSTALL_ERROR ,  serialize(array('errCode'=>'err_update_meta','msg'=>$wbs_sqlServers->getMessage() )));
			return null;
		}

		$accounts = $this->listRegisteredSystems();
		if ( PEAR::isError($accounts) ) {
			$this->setState(updateManager::STATE_INSTALL_ERROR ,serialize(array('errCode'=>'err_update_meta','msg'=> $accounts->getMessage() )));
			return null;
		}

		foreach ( $accounts as $account_key=>$account_data ) {
			$dbExists = true;
			$dbSkipReason = '';
			if ( !isset($account_data['DBSETTINGS']['CREATE_DATE']) || !strlen($account_data['DBSETTINGS']['CREATE_DATE']) ){
				$dbExists = false;
				$dbSkipReason = 'Database isn\'t created (DBSETTINGS\\CREATE_DATE)';
			}

			$profileFileName = $account_data['FILENAME'];

			$createDate = $account_data['DBSETTINGS']['CREATE_DATE'];

			$account_applications = array_keys( $account_data['APPLICATIONS'] );
			$account_applications = array_merge( array('Kernel'), $account_applications );
			$account_updateApplications = array_intersect( $updateApplications, $account_applications );
			if ( !count( $account_updateApplications ) )
			continue;

			$dbConnectionData = $this->getDBConnectionParameters( $account_key, $account_data, $wbs_sqlServers );
			if ( PEAR::isError($dbConnectionData) )
			{
				$dbExists = false;
				$this->setState(updateManager::STATE_INSTALL_ERROR ,serialize(array('errCode'=>'err_update_meta','msg'=> $dbConnectionData->getMessage())));
				return null;
			}

			if ( !$mdUpdateTriggered ) {
				$this->log->displayLog( "Upgrading database structure:" );

				$this->log->displayLog();
			}
			$mdUpdateTriggered = true;

			$this->setState(updateManager::STATE_INSTALL_SCRIPTS , sprintf( "Updating database %s", $account_key ) );

			if ( $dbExists ) {
				$dbh = @mysql_connect( $dbConnectionData['HOST'], $dbConnectionData['ADMIN_USERNAME'], $dbConnectionData['ADMIN_PASSWORD'] );
				if ( !$dbh ) {
					$this->log->write( sprintf( "Error connecting to mySQL server at %s", $dbConnectionData['HOST'] ) );
					$this->log->displayLog( "$account_key: <font color=red>Error</font>" );
					continue;
				}

				$res = @mysql_select_db( $dbConnectionData['DBNAME'] );
				if ( !$res ) {
					$this->log->write( sprintf( "Error selecting database %s", $accountDBName ) );
					$this->log->displayLog( "$account_key: <font color=red>Error</font>" );
					@mysql_close($dbh);
					continue;
				}
	
				$DB_NAME = $dbConnectionData['DBNAME'];
				$error_count = 0;
				foreach( $account_updateApplications as $accout_app ) {
					$this->log->write( sprintf( "Updating application: %s", $accout_app ) );
					
					foreach( $updateList[$accout_app] as $updateContent ) {
						$this->setState(updateManager::STATE_INSTALL_SCRIPTS );
						$updateContent = base64_decode( $updateContent );
	
						ob_start();
						try {
							eval( $updateContent );
						}catch(Exception $ex) {
							print $ex->getMessage();
						}
	
						$scriptResult = ob_get_clean();
	
						$updateRes = $scriptResult;
						//if  ( !strlen($updateRes) )
						//$updateRes = @mysql_error();
	
						if ( strlen($updateRes) ) {
							$error_count++;						
							$this->log->write( sprintf( "Error: %s", $updateRes ) );
							$this->log->displayLog( "{$account_key}: <font color=red>Error</font>" );
							//continue 3;
						}
					}
				}
				if($error_count){
				}else{
					$this->log->displayLog( "{$account_key}: <font color=green>Successfully</font>" );
				}
	
				@mysql_close($dbh);
			}else{
				$this->log->displayLog( "{$account_key}: <font color=yellow>Skipped</font>" );
				$this->log->displayLog($dbSkipReason);
			}

			$this->log->write( "Complete." );
		}
	}

	if ( $mdUpdateTriggered )
	$this->log->displayLog();

	$this->log->displayLog( "<b>Complete</b>: ".date("M'd Y H:i:s") );

	$this->log->write();
	$this->log->write( "Update complete: ".date("M'd Y H:i:s") );

	$complete = @fopen( WBS_DIR.updateManager::COMPLETE_FLAG, "w+" );
	@fputs( $complete, "DO NOT REMOVE THIS FILE!" );
	@fclose( $complete );
	$this->setSystemVersion(WBS_DIR.updateManager::SYS_VERSION_FILE,$updateVersion);
	//unlink(WBS_DIR.updateManager::UPDATE_VERSION_FILE );
	//unlink(WBS_DIR.updateManager::DISTRIBUTIVE_FILENAME );


	// Remove install.php, install.css and etc. files
	//
	$files=array('install.php','install.css','showmucontent.php','showdblist.php','domxml-php4-to-php5.php',
//	'update.xml'
);
	foreach ($files as $file){
		$file=WBS_DIR.updateManager::UPDATE_PATH.'/'.$file;
		if(file_exists($file)){
			unlink($file);
		}
	}

	$this->setState(updateManager::STATE_INSTALL_COMPLETE ,"Update complete: ".date("M'd Y H:i:s"));
}

public function installScripts( $writeLog = true )
{
	global $dbAdminURL;
	global $loginURL;

	// Unpack distributive
	//
	$archivePath = file_exists(WBS_DIR.updateManager::DISTRIBUTIVE_FILENAME)?WBS_DIR.updateManager::DISTRIBUTIVE_FILENAME:WBS_DIR.updateManager::DISTRIBUTIVE_FILENAME_;
	$targetPath = WBS_DIR;
	//DEBUG:
	//$targetPath = WBS_DIR.'/install_path';
	if(!file_exists($archivePath)){
		$this->setState(self::STATE_DOWNLOAD_ERROR,"Distributive file not found at {$archivePath}");
		return PEAR::raiseError( "Distributive file not found at {$archivePath}" );
	}

	require_once(WBS_DIR.'/published/wbsadmin/classes/class.fasttar.php');
	$resumeOffset = updateManager::getState();
	
	if(($resumeOffset = unserialize($resumeOffset['msg']))&&(($resumeOffset[1]-300)<time())){
		$tarSize = $resumeOffset[3];
		$resumeOffset = $resumeOffset[0];
		$this->setState(self::STATE_INSTALL_SCRIPTS,'resume install at '.$resumeOffset.' of '.$tarSize);
	}else{
		$resumeOffset = 0;
		$tarSize = null;
		$this->setState(self::STATE_INSTALL_SCRIPTS,'install in progress');
	}
	if($resumeOffset == -1){//this value used on resume for metaupdate
		return true;
	}
	
	$tar = new fastTar( $archivePath, true,$resumeOffset,$tarSize );
	//$tar->pLog = &$this->log;


	

	$state = self::getState();
	if($state['state'] == self::STATE_INSTALL_RESTART){
		$msg = unserialize($state['msg']);
		$this->log->write("Resume on block {$msg[0]} of {$tar->tarSize}");
	}
	
	$tar->setErrorHandling( PEAR_ERROR_PRINT );
	ob_start();
	$result = $tar->extract( $targetPath );
	$tar_out = ob_get_clean();
	/*
	 * $this->setState(self::STATE_INSTALL_SCRIPTS,
			serialize(array($currentPart,$partCount,round($currentPart*100/$partCount),
					'complete extract parts of tar',sprintf('%0.3f',$time))));
			*/
			
		

	if ( !$result )
	return PEAR::raiseError( "<p><b>Unable to extract files</b><p> $tar_out </p>" );
	
	/*
	// Prepare wbs.xml file
	//
	$configPath = "kernel/wbs.xml";
	if ( !file_exists($configPath) &&file_exists("settings.xml")){
		copy( "settings.xml", $configPath );
	}

	$htaccessPath = "published/.htaccess";
	if ( !file_exists($htaccessPath)){
		if(file_exists){
			copy( "access", $htaccessPath );
		}
	}else {
		// Replace .htaccess file
		//
		if ( !@file_exists(WBS_DIR.updateManager::HTACCESSREPLACED_FLAG) ) {
			$oldHtaccessPath = "published/bak.htaccess";
			rename( $htaccessPath, $oldHtaccessPath );
			copy( "access", $htaccessPath );

			$complete = fopen( WBS_DIR.updateManager::HTACCESSREPLACED_FLAG, "w+" );
			fputs( $complete, "DO NOT REMOVE THIS FILE!" );
			fclose( $complete );
		}
	}
	*/

	// Create extra directories
	//
	$this->mk_dir( WBS_DIR."/data" );
	$this->mk_dir( WBS_DIR."/dblist" );
	$this->mk_dir( WBS_DIR."/temp" );
	$this->mk_dir( WBS_DIR."/kernel/includes/smarty/compiled" );

	// Create public data directory
	//
	$this->mk_dir( WBS_DIR."/published/publicdata" );


	return null;
}
private function mk_dir( $dir )
{
	if ( !file_exists( $dir ) )
	@mkdir( $dir );
}
private function parseUpdateFile( $sysVersion )
{
	$updateList = array();

	$filePath = WBS_DIR.updateManager::UPDATE_VERSION_FILE;
	if ( !file_exists( $filePath ) )
	return PEAR::raiseError( "File update.xml is not found" );

	$content = $this->fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError( "Invalid update file" );

	$xpath = @xpath_new_context($dom);
	$query = sprintf( '/METADATAUPDATE/UPDATE[number(@VERSION) > number(%s)]/APPUPDATE', $sysVersion );
	if ( !( $updates = &xpath_eval($xpath, $query) ) )
	return $updateList;

	if ( !is_array( $updates->nodeset ) )
	return $updateList;

	foreach( $updates->nodeset as $update ) {
		$APP_ID = $update->get_attribute( 'APP_ID' );
		$content = $update->get_attribute( 'CONTENT' );

		$updateList[$APP_ID][] = $content;
	}

	uksort( $updateList, 'sortUpdateList' );

	return $updateList;
}
private function fileContent( $filePath )
{
	if((strpos($filePath,'http://')===false)&&!file_exists($filePath)){
		return null;
	}else{
		$fp=fopenTimeout($filePath,'r');
		if(!$fp)return null;
		$content=fread($fp,filesize($filePath));
		fclose($fp);
		return $content;
	}
}
private function listTargetDatabases($sysVersion, $updateVersion,&$updateApplications=null )
{
	$result = array();

	$updateList = $this->parseUpdateFile( $sysVersion );
	if ( PEAR::isError($updateList) )
	return $updateList;

	if ( !count($updateList) )
	return $result;
	else
	$updateApplications = array_keys( $updateList );

	$accounts = $this->listRegisteredSystems();
	if ( PEAR::isError($accounts) ) {
		$this->log->write( $accounts->getMessage() );
		return $accounts;
	}

	foreach ( $accounts as $account_key=>$account_data ) {
		$createDate = $account_data['DBSETTINGS']['CREATE_DATE'];

		$account_applications = array_keys( $account_data['APPLICATIONS'] );
		$account_applications = array_merge( array('Kernel'), $account_applications );

		$account_updateApplications = array_intersect( $updateApplications, $account_applications );

		if ( count( $account_updateApplications ) )
		$result[] = $account_key;
	}

	sort( $result );

	return $result;
}


private function getElementByTagname( &$dom, $tagName )
{
	$elements = $dom->get_elements_by_tagname($tagName);

	if ( !count($elements) )
	return null;

	return $elements[0];
}

private function getAttributeValues( &$node )
{
	$attrs = $node->attributes();

	$result = array();

	if ( !is_array( $attrs ) )
	return $result;

	for ( $i = 0; $i < count($attrs); $i++ ) {
		$attr = $attrs[$i];

		$result[$attr->name] = $attr->value;
	}

	return $result;
}

private function getHostData( $host_key, &$hostInfo )
{
	$hostInfo = array();

	$filePath = sprintf( WBS_DIR."dblist/%s.xml", strtoupper($host_key) );

	$content = $this->fileContent( $filePath );
	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError( "Error opening database profile" );

	$element = @$this->getElementByTagname( $dom, 'DBSETTINGS' );
	if ( is_null($element) )
	return PEAR::raiseError( "Error reading database profile" );

	$hostInfo['DBSETTINGS'] = $this->getAttributeValues( $element );

	$element = @$this->getElementByTagname( $dom, 'FIRSTLOGIN' );
	if ( is_null($element) )
	return PEAR::raiseError( "Error reading database profile" );

	$hostInfo['FIRSTLOGIN'] = $this->getAttributeValues( $element );
	$hostInfo['DB_KEY'] = $host_key;

	$applications = @$this->getElementByTagname( $dom, 'APPLICATIONS' );
	if ( is_null($applications) )
	return PEAR::raiseError( "Error reading database profile" );

	$appList = array();
	$applications = $applications->get_elements_by_tagname('APPLICATION');
	foreach( $applications as $application ) {
		$app_id = $application->get_attribute( 'APP_ID' );
		$appList[$app_id] = array( 'APP_ID'=>$app_id );
	}

	$hostInfo['APPLICATIONS'] = $appList;

	return null;
}

function listRegisteredSystems()
{
	$result = array();

	$targetDir = WBS_DIR."dblist";
	$fileExt = "xml";

	if ( !($handle = @opendir($targetDir)) )
	return PEAR::raiseError( "Error opening database list" );

	while ( false !== ($name = readdir($handle)) ) {
		if ( $name != "." && $name != ".." && $name != "" ) {
			$filename = $targetDir.'/'.$name;

			if ( is_dir($filename) )
			continue;

			$path_parts = pathinfo($filename);
			if ( $path_parts["extension"] != $fileExt )
			continue;

			$db_key = substr( $name, 0, strlen($name)-strlen($fileExt)-1 );
			$hostInfo = null;
			$res = $this->getHostData( $db_key, $hostInfo );
			if ( !PEAR::isError($res) ) {
				$hostInfo['FILENAME'] = realpath($filename);
				$result[$db_key] = $hostInfo;
			}
		}
	}

	closedir( $handle );

	return $result;
}

private function listSystemServers()
{
	$filePath = WBS_DIR."kernel/wbs.xml";

	$content = $this->fileContent( $filePath );

	$dom = @domxml_open_mem( $content );
	if ( !$dom )
	return PEAR::raiseError( "Error opening system settings file" );

	$xpath = xpath_new_context($dom);

	$result = array();

	if ( !( $sqlservers = xpath_eval($xpath, '/WBS/SQLSERVERS/SQLSERVER') ) )
	return $result;

	foreach( $sqlservers->nodeset as $sqlserver ) {
		$serverParams = $this->getAttributeValues($sqlserver);
		$serverName = $serverParams['NAME'];

		$result[$serverName] = $serverParams;
	}

	return $result;
}



private function getDBConnectionParameters( $account_key, $account_data, $wbs_sqlServers )
{
	// Find out database connection type
	//
	$dbNewType = false;
	if ( array_key_exists( 'DB_CREATE_OPTION', $account_data['DBSETTINGS'] ) )
	$dbNewType = true;

	$useExisting = false;
	if ( !$dbNewType )
	$useExisting = strlen( $account_data['DBSETTINGS']['DB_USER'] );
	else
	$useExisting = $account_data['DBSETTINGS']['DB_CREATE_OPTION'] == 'use';

	// Load connection parameters
	//
	$accountDBName = $account_data['DBSETTINGS']['DB_NAME'];

	if ( !strlen($accountDBName) )
	$accountDBName = 'DB'.$account_key;

	$server = $account_data['DBSETTINGS']['SQLSERVER'];

	if ( !array_key_exists( $server, $wbs_sqlServers ) )
	return PEAR::raiseError( "No server found for $account_key" );

	$serverData = $wbs_sqlServers[$server];

	$serverHost = $serverData['HOST'];
	if ( !strlen($serverHost) )
	$serverHost = 'localhost';

	if ( strlen($serverData['PORT']) )
	$serverHost = sprintf( "%s:%s", $serverHost, $serverData['PORT'] );

	$result = array();
	$result['HOST'] = $serverHost;

	if ( !$useExisting ) {
		$result['ADMIN_USERNAME'] = $serverData['ADMIN_USERNAME'];
		$result['ADMIN_PASSWORD'] = $serverData['ADMIN_PASSWORD'];
	} else {
		$result['ADMIN_USERNAME'] = $account_data['DBSETTINGS']['DB_USER'];
		$result['ADMIN_PASSWORD'] = $account_data['DBSETTINGS']['DB_PASSWORD'];
	}

	$result['DBNAME'] = $accountDBName;

	return $result;
}
public function getLastError()
{
	if($this->fatalError)
	return $this->errorStr;
	else
	return false;
}
}

class requirementsControl
{
	private $satisfy;
	private $requirements=array(//'module_name'=>(bool) is_optional
	'mbstring'=>false,
	'simpleXML'=>false,
	'DOM'=>false,
	'GD'=>true,
	'ZLib'=>true);
	//0 - not satisfy;1 -
	/*
	- mbstring (required)
	- GD (optional)  Image processing functions will be disabled.
	- ZLib (optional) Archive proccessing functions will be disabled.
	JavaScript must be enabled:
	The installation directory must be enabled for writing:<br><small><b>Note:</b> Permission value for UNIX-based server is usually 775.<br>Please contact your host service provider and refer to the WebAsyst Installation Guide for further details.

	Your server does not satisfy to the WebAsyst system requirements.
	Your system satisfies to the WebAsyst requirements.
	*/
	function echoResult($succes,$is_optional=false)
	{
		if($succes){
			return "<B><FONT COLOR='GREEN'>PASSED</FONT></B>";
		}else{
			return "<B><FONT COLOR='".($is_optional?'YELLOW':'RED')."'>FAILED</FONT></B>";
		}
	}
	function check_extension(&$msg=null)
	{
		if(!is_array($msg))$msg=array();
		$satisfy=true;
		$msg['ext']='';
		foreach ($this->requirements as $module=>$is_optional)
		{
			$module_installed=extension_loaded($module);

			$msg[$module]=$this->echoResult($module_installed,$is_optional);

			$satisfy=$satisfy&($is_optional|$module_installed);
		}

		return $satisfy;
	}
	function check_javascript($msg=null  )
	{
		if(!is_array($msg))$msg=array();
		if ( $_POST["nojs"] == 0 )
		{
			$msg['JavaScript']="<B><FONT COLOR=GREEN>PASSED</B>";
			return true;
		}

		$msg['JavaScript']="<B><FONT COLOR=RED>FAILED</FONT></B>";
		return false;
	}
	function check_phpversion(&$msg=null)
	{
		if(!is_array($msg))$msg=array();
		$ver = phpversion();
		$satisfy=false;

		if ( ereg ("([0-9]+).([0-9]+).([0-9]+)", $ver, $regs)) {
			$satisfy=( $regs[1]>4 );
		}

		$msg['PHP']=$this->echoResult($satisfy);
		return $satisfy;
	}
	function check_mysql(&$msg=null )
	{
		if(!is_array($msg))$msg=array();
		$satisfy=false;
		$ver = mysql_get_client_info();

		if (ereg ("([0-9]+).([0-9]+).([0-9]+)", $ver, $regs)) {
			$satisfy= ( ( $regs[1] >= 3 && $regs[2] >= 23 ) || $regs[1] >= 4 );

		}

		$msg['MySQL']=$this->echoResult($satisfy);
		return $satisfy;
	}
	function check_writable( &$msg=null )
	{
		if(!is_array($msg))$msg=array();
		$dir = defined('WBS_DIR')?constant('WBS_DIR'):'.';
		$satisfy= ( is_writable($dir) );
		$msg['WRITABLE']=$this->echoResult($satisfy);

		return $satisfy;

	}
	function check_all( &$msg=null )
	{
		$satisfy=true;
		$satisfy=$satisfy&$this->check_phpversion($msg);
		$satisfy=$satisfy&$this->check_extension($msg);
		$satisfy=$satisfy&$this->check_mysql($msg);
		$satisfy=$satisfy&$this->check_writable($msg);
		//$satisfy=$satisfy&$this-check_javascript($msg);
		//$satisfy=$satisfy&$this-
		return $satisfy;
	}
}

error_reporting (E_ERROR | E_WARNING | E_PARSE);
extract( $_POST );

function sortUpdateList( $a, $b )
{
	$aIsKernel = substr( $a, 0, 6 ) == "Kernel";
	$bIsKernel = substr( $b, 0, 6 ) == "Kernel";

	if ( $aIsKernel && !$bIsKernel ) return -1;
	if ( !$aIsKernel && $bIsKernel ) return 1;
	if ( $aIsKernel && $bIsKernel ) return 0;

	return strcmp( $a, $b );
}

class stepManager
{
	const STEP_STATE = '.stepmanager';

	function __construct()
	{

	}
	function checkSystemRequirements()
	{

	}
	function checkWebUpdates()
	{

	}
	function downloadUpdates()
	{

	}
	function Install()
	{

	}


}



function step3()
{
	global $displayLog;

	switch( true ) {
		case true : {
			$sysVersion = null;
			$updateVersion = null;
			$actionType = getActionType( $sysVersion, $updateVersion );
			if ( PEAR::isError($actionType) ) {
				$errorStr = $actionType->getMessage();
				$fatalError = true;

				break;
			}

			$updateVersion = getSystemVersion( UPDATE_VERSION_FILE, true );
			if ( PEAR::isError($updateVersion) ) {
				$errorStr = $updateVersion->getMessage();
				$fatalError = true;

				break;
			}

			switch ( $actionType ) {
				case ACTION_REPAIR : ;
				case ACTION_NOVERSION:
				case ACTION_INSTALL : $res = installScripts(); break;
				case ACTION_UPDATE :  $res = updateSystem( $sysVersion, $updateVersion ); break;
			}

			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$fatalError = true;

				break;
			}

			$res = setSystemVersion( SYS_VERSION_FILE, $updateVersion );
			if ( PEAR::isError($res) ) {
				$errorStr = $res->getMessage();
				$fatalError = true;

				break;
			}
		}
	}
}
?>