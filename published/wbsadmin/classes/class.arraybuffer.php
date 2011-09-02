<?php
class arrayBuffer
{
	private $id;
	private $fname;
	private $fp;
	private $keys;
	private $keysInited;
	private $modeWrite;
	function __construct($id = 'general',$modeWrite = true)
	{
		$this->modeWrite = $modeWrite?true:false;
		$this->fname = sprintf(WBS_DIR.'/temp/.cache.arraybuffer.%s',$id);
		$this->keys = array();
		$this->keysInited = false;
		$this->fp = fopen($this->fname,$this->modeWrite?'w':'r');
		/*if($this->fp&&!$this->modeWrite){
			$this->scanKeys();
		}*/
	}
	function __destruct()
	{
		$this->endReadWrite();
	}
	function endReadWrite()
	{
		if($this->fp){
			fclose($this->fp);
		}
		unset($this->fp);
	}
	function deleteBuffer()
	{
		$this->endReadWrite();
		unlink($this->fname);
	}
	private function scanKeys()
	{
		if($this->fp){
			rewind($this->fp);
			$position = ftell($this->fp);
			while($line = fgets($this->fp)){
				if($array = unserialize($line)){
					$this->keys[$array[0]]=$position;
				}	
				unset($array);			
				$position = ftell($this->fp);	
			}
		}
		$this->keysInited = true;
	}
	function getItem($key = null)
	{
		
		if(is_null($key)){
			if($this->keysInited){
				$key = each($this->keys);
				if($key === false){
					return null;
				}
			}
		}else{
			if($this->keysInited){
				$this->scanKeys();
			}
			$key = $this->keys[$key];
		}
		

		$value = null;
		if($this->fp){
			if($key){fseek($this->fp,$key[1]);}
			$line = fgets($this->fp);
			if($line = unserialize($line)){
				$value = base64_decode($line[1]); 
			}else{
				return null;
			}
		}
		return array('key'=>$key[0],'value'=>$value);
	}
	
	function addItem($value,$key = null)
	{
		if(is_null($key)){
			$key = $this->getNextKey();
		}elseif(isset($this->keys[$key])){
			return false;
		}
		if($this->fp){
			$this->keys[$key] = fwrite($this->fp,serialize(array($key,base64_encode($value)))."\n");
			return $this->keys[$key];
		}else{
			return false;
		}
	}
	private function getNextKey()
	{
		static $key = 0;
		while(isset($this->keys[$key])){
			$key++;
		}
		return $key;		
	}
}
?>