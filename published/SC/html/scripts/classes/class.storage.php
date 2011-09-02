<?php
class Storage{
	const SESSION = 0;
	const COOKIE = 1;
	var $__name = '';
	var $__data = array();
	private $__type =  Storage::SESSION;


	function __construct($type = Storage::SESSION)
	{
		$this->__type = $type;
	}

	function init($name)
	{
		$this->__name = $name;
		switch($this->__type){
			case Storage::SESSION:{
				if(isset($_SESSION['xPOST'][$name])){
					$this->__data = $_SESSION['xPOST'][$name];
				}else{
					$this->__data = null;
				}
				break;
				
			}
			case Storage::COOKIE:{
				if(isset($_COOKIE['xPOST'][$name])){
					$this->__data = $_COOKIE['xPOST'][$name];
				}else{
					$this->__data = null;
				}
				break;
			}
		}
		return $this->__data;
	}

	function clean()
	{

		$this->__data = array();
		$this->saveData();
	}

	function setData($key, $data)
	{

		$this->__data[$key] = $data;
		$this->saveData();

	}

	function getData($key,$default_data = null)
	{

		return isset($this->__data[$key])?$this->__data[$key]:$default_data;
	}

	function getCount()
	{
		return count($this->__data);
	}

	/**
	 * @param string $name
	 * @return Storage
	 */
	static function &getInstance($name,$type = Storage::SESSION)
	{

		$storageEntry = new Storage($type);
		//$storageEntry = &$storageEntry;
		/*@var $storageEntry Storage*/
		$storageEntry->init($name);
		return $storageEntry;
	}

	protected function saveData()
	{
		switch($this->__type){
			case Storage::SESSION:{
				if(!isset($_SESSION['xPOST'])){
					$_SESSION['xPOST'] = array();
				}
				$_SESSION['xPOST'][$this->__name] = $this->__data;
				break;
			}
			case Storage::COOKIE:{
				$_COOKIE['xPOST'][$this->__name] = $this->__data;
				break;
			}
		}
	}

	protected function loadData($key,$default_data = null)
	{
		return isset($_SESSION['xPOST'][$_VarName])?$_SESSION['xPOST'][$_VarName]:$default_data;
	}
}
?>