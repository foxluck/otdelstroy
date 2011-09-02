<?php
require_once "Archive/Tar.php";
class fastTar extends Archive_Tar
{
	private $tarSize;
	/*@var updateLog*/
	public $pLog;
	private $lastOffset;
	private $resumeOffset;
	private $resumeMode;
	/*
	 * @param    string  $p_tarname  The name of the tar archive to create
	 * @param    string  $p_compress can be null, 'gz' or 'bz2'. This
	 *                   parameter indicates if gzip or bz2 compression
	 *                   is required.  For compatibility reason the
	 *                   boolean value 'true' means 'gz'.
	 * @access public
	 */
	function fastTar($p_tarname,$p_compress = null,$resumeOffset = 0,$tarSize)
	{
		$this->tarSize = $tarSize;
		$this->resumeOffset = $resumeOffset;
		return parent::Archive_Tar($p_tarname,$p_compress);
	}


	function resume()
	{
		
		if($this->resumeOffset>1){
			updateManager::_setState(updateManager::STATE_INSTALL_SCRIPTS,array($this->resumeOffset,time(),
						floor($this->resumeOffset*100/$this->tarSize),$this->tarSize,'heart beat'));
			$this->_jumpBlock($this->resumeOffset);
			if($this->pLog instanceof updateLog){
				$this->pLog->write("Attempt offset".($this->resumeOffset));
				$realOffset = $this->_getOffset();
				$this->pLog->write("Real offset".($realOffset));
			}
		}else{
			$this->_getSize();
		}
		updateManager::_setState(updateManager::STATE_INSTALL_SCRIPTS,array($this->resumeOffset,time(),
						floor($this->resumeOffset*100/$this->tarSize),$this->tarSize,'heart beat'));
			
		$this->resumeMode = true;
	}

	function _extractList($p_path, &$p_list_detail, $p_mode, $p_file_list, $p_remove_path)
	{
		if($p_mode == 'complete'){
			$this->resume();
		}
		return parent::_extractList($p_path, $p_list_detail, $p_mode, $p_file_list, $p_remove_path);
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
		$currentBlock = $this->_getOffset($p_len);
		while(strlen(parent::_readBlock($p_len))){
			$this->_jumpBlock(4096);
			$tarSize = $this->_getOffset($p_len);
			if($tarSize>0){
				$this->tarSize = $tarSize;
			}else{
				break;
			}
			if(($this->pLog instanceof updateLog)&&false){
				$this->pLog->write('Attempt get size '.$this->tarSize."\t\t".$this->_getOffset($p_len));
			}
			if(++$count>128){
				$count=0;
				updateManager::_setState(updateManager::STATE_INSTALL_SCRIPTS,array($this->resumeOffset,time(),
						floor($this->resumeOffset*100/$this->tarSize),$this->tarSize,'heart beat'));
			}
		}
		$this->_rewind();
		$this->_jumpBlock($currentBlock);
		if($this->pLog instanceof updateLog){
				$this->pLog->write('Attempt rewind '.$this->tarSize."\t".$currentBlock."\t=\t".$this->_getOffset($p_len));
		}
		
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
		
		if($v_result&&$this->resumeMode&&(++$count>32)){
			$this->lastOffset = $this->_getOffset()-1;
			if($this->pLog instanceof updateLog){
				$this->pLog->write($this->lastOffset."\t".floor($this->lastOffset*100/$this->tarSize)."\t".$this->tarSize."\t_readHeader");
			}
			updateManager::_setState(updateManager::STATE_INSTALL_SCRIPTS,array($this->lastOffset,time(),floor($this->lastOffset*100/$this->tarSize),$this->tarSize,'heart beat'));
			$count = 0;
		}
		return $v_result;
	}
	
	/*function _readBlock($p_len = null)
	{
	static $count = 0;
		if((++$count > 512)&&($this->resumeMode)){
			$currOffset = $this->_getOffset();
			updateManager::_setState(updateManager::STATE_INSTALL_SCRIPTS,array($this->lastOffset,time(),floor($currOffset*100/$this->tarSize),$this->tarSize,'heart beat'));
			$count = 0;
		}
		return parent::_readBlock($p_len);
	}*/
}
?>