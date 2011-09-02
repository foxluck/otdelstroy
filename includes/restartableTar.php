<?php
//require_once('PEAR.php');
if(!defined('WBS_DIR')){
	define('WBS_DIR',realpath(dirname(__FILE__).'/../'));
}
require_once(WBS_DIR.'/includes/Tar.php');
error_reporting(E_ERROR | E_PARSE | E_ALL | E_WARNING);
@ini_set('max_execution_time', 3600 );
@set_magic_quotes_runtime(0);


//AJAX handler
class ajaxTar
{
	const STATE_WAIT	= 'PROGRESS';
	const STATE_RESTART = 'RESTART';
	const STATE_COMPLETE = 'COMPLETE';
	static function getState()
	{
		$state = restartableTar::getState();
		$result = ajaxTar::STATE_RESTART;
		$progress = 0;
		if($state){
			switch($state['state']){//Wait while tar extracting and get current progress
				case restartableTar::STATE_EXTRACT:
					$result = ajaxTar::STATE_WAIT;
					$progress = $state['progress'];
					break;
				case restartableTar::STATE_ERROR:
					$result = ajaxTar::STATE_COMPLETE;
					break;
				case restartableTar::STATE_COMPLETE://Follow for the next step on extraction complete or error
					$result = ajaxTar::STATE_COMPLETE;
					break;
			}
		}else{
			$state = restartableTar::makeState(restartableTar::STATE_NONE,'ungeted state');
		}
		print sprintf('%s:%d:%s',$result,$progress,base64_encode($state['msg'].' @'.$state['state']));
		exit;
	}
	static function extract($archivePath,$targetPath,$permission = null)
	{
		$result = false;
		$tar_out = '';
		$tar = new restartableTar($archivePath);
		if(@file_exists($archivePath)){
			$state = restartableTar::getState();
			if(!isset($_GET['debug'])){
				ajaxTar::closeBrowserConnect(sprintf('%s:%d:%s',$state['state'],$state['progress'],$state['msg']));
			}
			@ob_get_clean();
			ob_start();
			$tar->_chmod = $permission;
			$result = $tar->extract($targetPath);
			$tar_out = ob_get_clean();
			if(get_magic_quotes_gpc()){
				$tar_out = stripslashes($tar_out);
			}
				
		}else{
			$tar_out = "Couldn\'t extract file {$archivePath}";
		}
		$tar->_setState($result?restartableTar::STATE_COMPLETE:restartableTar::STATE_ERROR,$tar_out);
		if(isset($_GET['debug'])){
			var_dump(array($result,$tar_out,$archivePath,$targetPath,$permission,$tar));
		}
		return $result;
	}

	static function closeBrowserConnect($message='EMPTY MESSAGE')
	{
		@ob_end_clean();
		header('Pragma: public');
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");                  // Date in the past
		header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
		header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
		header ("Pragma: no-cache");
		header("Connection: close");
		ignore_user_abort(true);
		@ini_set('ignore_user_abort',1);
		ob_start();
		print $message;
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush(); // Strange behaviour, will not work
		flush();
	}
}

class restartableTar extends Archive_Tar
{
	static $statePath = null;
	const STATE_EXTRACT 	= 'extract';
	const STATE_NONE 		= 'none';
	const STATE_ERROR 		= 'error';
	const STATE_COMPLETE 	= 'complete';

	const WAIT_ON_ERROR		= 20;
	const WAIT_ON_COMPLETE	= 20;
	const WAIT_ON_EXTRACT	= 10;
	const MAX_RESUME_TIME	= 60;
	//const STATE_RESUME 		= 'resume';


	var $tarSize = 0;
	var $lastOffset = 0;
	var $resumeOffset = 0;
	var $resumeMode = false;
	var $restartable = true;
	var $errorLog = '';
	/*
	 * @param    string  $p_tarname  The name of the tar archive to create
	 * @param    string  $p_compress can be null, 'gz' or 'bz2'. This
	 *                   parameter indicates if gzip or bz2 compression
	 *                   is required.  For compatibility reason the
	 *                   boolean value 'true' means 'gz'.
	 * @access public
	 */
	function restartableTar($p_tarname,$p_compress = true)
	{

		$this->setErrorHandling(PEAR_ERROR_PRINT);
		$this->checkResumable();
		return parent::Archive_Tar($p_tarname,$p_compress);
	}
	static function init()
	{
		restartableTar::$statePath = (defined('WBS_DIR')?WBS_DIR:dirname(__FILE__)).'/.state.tar.tmp';
	}

	function checkResumable()
	{
		$state = restartableTar::getState(false);
		if($state){
			switch($state['state']){
				case restartableTar::STATE_NONE:
					$this->restartable = true;
					break;
				case restartableTar::STATE_EXTRACT:
					$delta = time()-$state['time'];
					$this->restartable 	= ($delta>restartableTar::WAIT_ON_EXTRACT)?true:false;
					if($delta<restartableTar::MAX_RESUME_TIME){
						$this->resumeOffset = $this->restartable?$state['offset']:0;
						$this->tarSize 		= $this->restartable?$state['tarsize']:0;
					}
					break;
				case restartableTar::STATE_COMPLETE:
					$this->restartable = ((time()-$state['time'])>restartableTar::WAIT_ON_COMPLETE)?true:false;
					break;
				case restartableTar::STATE_ERROR:
					$this->restartable = ((time()-$state['time'])>restartableTar::WAIT_ON_ERROR)?true:false;
					break;
			}
		}else{
			$this->restartable = true;
		}
		if($this->restartable){
			$this->_setState(restartableTar::STATE_EXTRACT,$state['msg'].'  initial start extract');
		}
	}

	function restart()
	{

		if($this->resumeOffset>1){
			$this->_setState(restartableTar::STATE_EXTRACT,'resume extracting');
			$this->_jumpBlock($this->resumeOffset);
		}else{
			$this->_setState(restartableTar::STATE_EXTRACT,'extracting');
			$this->_getSize();
		}

		$this->resumeMode = true;
	}

	function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path)
	{
		if($this->restartable){
			if($p_mode == 'complete'){
				$this->restart();
			}
			//ob_start();
			$res = parent::_extractList($p_path, $p_list_detail, $p_mode, $p_file_list, $p_remove_path);
			//$msg = ob_get_clean();
			//$this->_setState($res?restartableTar::STATE_COMPLETE:restartableTar::STATE_ERROR,$msg);
			return $res;
		}else{
			return parent::_extractList($p_path, $p_list_detail, $p_mode, $p_file_list, $p_remove_path);
		}
	}
	function _getOffset($p_len = null)
	{
		$ofset = null;
		if (is_resource($this->_file)) {
			if ($p_len === null)
			$p_len = 512;

			if ($this->_compress_type == 'gz'){
				$ofset = @gztell($this->_file)/512;
			}else if ($this->_compress_type == 'bz2') {
				// ----- Replace missing bztell() and bzseek()
				$ofset = 0;
			} else if ($this->_compress_type == 'none'){
				$ofset = @ftell($this->_file)/512;
			}else{
				$this->_error('Unknown or missing compression type ('.$this->_compress_type.')');
			}
		}
		return floor($ofset);
	}

	function _getSize($p_len = null)
	{
		if($this->tarSize)return;
		$count = 0;
		$currentBlock = $this->_getOffset($p_len);
		while(strlen(parent::_readBlock($p_len))){
			$this->_jumpBlock(4096);
			$tarSize = $this->_getOffset($p_len);
			if($tarSize>0){
				$this->tarSize = $tarSize;
			}else{
				break;
			}
			if(++$count>128){
				$count=0;
				$this->_setState(restartableTar::STATE_EXTRACT,'get size');
			}
		}
		$this->_rewind();
		$this->_jumpBlock($currentBlock);
	}
	function _rewind()
	{
		if (is_resource($this->_file)) {
			if ($this->_compress_type == 'gz')
			@gzrewind($this->_file);
			else if ($this->_compress_type == 'bz2') {
				// ----- Replace missing bztell() and bzseek()
			} else if ($this->_compress_type == 'none')
			@rewind($this->_file);
			else
			$this->_error('Unknown or missing compression type ('.$this->_compress_type.')');

		}
		return true;
	}

	function _readHeader($v_binary_data, &$v_header)
	{
		static $count = 0;
		$v_result = parent::_readHeader($v_binary_data, $v_header);
		if($v_result&&$this->restartable&&(++$count>32)){
			$this->lastOffset = $this->_getOffset()-1;
			$this->_setState(restartableTar::STATE_EXTRACT,'read header');
			$count = 0;
		}
		//var_dump(array($this->lastOffset,$this->resumeMode));
		return $v_result;
	}

	function _error($p_message)
	{
		$this->errorLog .= $p_message."<br>\n";
		parent::_error($p_message."<br>\n");
	}

	function _setState($state,$msg)
	{
		restartableTar::setState($state,$msg,$this->lastOffset,$this->tarSize);
	}
	static function setState($state=restartableTar::STATE_NONE,$msg='',$offset=null,$tarSize=null)
	{
		if(!isset(restartableTar::$statePath)){
			restartableTar::init();
		}
		$path = restartableTar::$statePath;
		$state = restartableTar::makeState($state,$msg,$offset,$tarSize);

		if($fp = fopen($path,'w')){
			fwrite($fp,serialize($state));
			fclose($fp);
		}
		$backtrace = debug_backtrace();
		$state['debug'] = array();
		$count = min(4,count($backtrace));
		for($i = 0;$i<$count;$i++){
			$state['debug'][] = "{$backtrace[$i]['function']}():{$backtrace[$i]['line']}";
		}
		if($fp = fopen($path.'.log','a')){

			fwrite($fp,var_export($state,true)."\n\n=====================\n");
			fclose($fp);
		}

	}
	static function makeState($state=restartableTar::STATE_NONE,$msg='',$offset=null,$tarSize=null)
	{
		$res = array(
		'state'=>$state,
		'offset'=>$offset,
		'progress'=>($tarSize?floor(100*$offset/$tarSize):0),
		'tarsize'=>$tarSize,
		'time'=>time(),
		'msg'=>$msg,
		);
		if($res['progress']>100)$res['progress'] = 100;
		return $res;
	}
	static function isExtractSucces(&$tar_out)
	{
		$result = restartableTar::getState();
		$tar_out = $result['msg'];
		$result = ($result['state']==restartableTar::STATE_COMPLETE)?true:false;
		restartableTar::setState(restartableTar::STATE_NONE,'install complete');
		return $result;
	}
	static function getState($check_time = true)
	{
		if(!isset(restartableTar::$statePath)){
			restartableTar::init();
		}
		$path = restartableTar::$statePath;
		if(file_exists($path)){
			$stateFile = file($path);
			if($stateFile){
				$state_ = implode('',$stateFile);
				if(get_magic_quotes_gpc()){
					$state_ = stripslashes($state_);
				}
				$state = unserialize($state_);
				if(!$state){
					$state = restartableTar::makeState(restartableTar::STATE_NONE,'unknown state '.$state_);
				}elseif($check_time){
					switch($state['state']){//Wait while tar extracting and get current progress
						case restartableTar::STATE_EXTRACT:
							if((time()-$state['time'])>restartableTar::WAIT_ON_EXTRACT){
								$state['state'] = restartableTar::STATE_NONE;
							}
							break;
						case restartableTar::STATE_ERROR:
							if((time()-$state['time'])>restartableTar::WAIT_ON_ERROR){
								$state['state'] = restartableTar::STATE_NONE;
							}
							break;
						case restartableTar::STATE_COMPLETE://Follow for the next step on extraction complete or error
							if((time()-$state['time'])>restartableTar::WAIT_ON_COMPLETE){
								$state['state'] = restartableTar::STATE_NONE;
							}
							break;
					}
				}
			}else{
				$state = restartableTar::makeState(restartableTar::STATE_NONE,'unknown state');
			}
		}else{
			$state = restartableTar::makeState(restartableTar::STATE_NONE,'file not exists');
		}
		return $state;
	}
}
?>