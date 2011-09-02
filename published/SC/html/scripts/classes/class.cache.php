<?php
/**
 * Universal data cache
 *
 * @since May 2009
 * @author WebAsyst Team
 * @category Base
 * @version 2.0
 * @version SVN: $Id: class.cache.php 917 2010-02-09 14:55:44Z vlad $
 */
class Cache
{
	const DATA_PREFIX = '__WBS_SC_DATA';
	const DATA_NAME	= 'CACHE';
	const NONE		= 0;

	//TODO: add mem_cache namespace support
	/**
	 *
	 * @todo: complete memcache support
	 * @var int Store data at memcache
	 */
	const MEMCACHE	= 1;//not fully completed
	/**
	 *
	 * @var int Store data at files
	 */
	const FILE		= 2;
	/**
	 *
	 * @var int Store data at current session
	 * @see
	 */
	const SESSION	= 3;
	/**
	 *
	 * @var int Store data at memory (not shared memory)
	 */
	const MEMORY	= 4;
	/**
	 * @todo add it's support
	 * @var int Store data at browser cookies
	 */
	const COOKIE	= 5;

	/**
	 *
	 * @var int store data as includable PHP file
	 */
	const PHP		= 6;

	private $name;
	private $type;
	private $resource_name;
	private $data;
	private $updated = false;
	private $collection = array();

	private static $memcache_connection = null;
	private static $cache_stack = array();
	private static $cache_type_map = array();

	private static $counter =array('get'=>0,'get_external'=>0,'set'=>0,'get_variables'=>array());

	private static $debug_mode = false;

	/**
	 * Get cache instance
	 *
	 * @param string $name Cache name
	 * @param int $type storage type
	 * @return Cache
	 */
	static function &getInstance($name, $type = self::FILE)
	{
		$name = strtolower($name);
		if(!isset(self::$cache_stack[$name])){
			$type = self::setInstanceMap($name,$type);
			self::$cache_stack[$name] = new self($name,$type);
		}
		return self::$cache_stack[$name];
	}

	/**
	 * Drop cache instance
	 *
	 * @param string $name Cache name
	 * @return void
	 */
	static function dropInstance($name)
	{
		$name = strtolower($name);
		if(isset(self::$cache_stack[$name])){
			unset(self::$cache_stack[$name]);
		}
	}

	/**
	 * Initial set instance storage map
	 * @param string $name Cache name
	 * @param int $type storage type
	 * @return int setted storage type
	 */
	static function setInstanceMap($name,$type)
	{
		if(!isset(self::$cache_type_map[$name])){
			self::$cache_type_map[$name] = $type;
		}
		return self::$cache_type_map[$name];
	}

	/**
	 * Reset selected cache instances
	 * @param $name_list array
	 * @return void
	 */
	static function resetList($name_list = array()){
		if(!is_array($name_list)){
			$name_list = $name_list?array($name_list):array();
		}
		foreach($name_list as $name){
			$instance = self::getInstance($name);
			$instance->reset();
		}
	}

	protected function __construct($name, $type = self::FILE)
	{
		static $ext_loaded;

		$name = strtolower($name);
		if(!isset(self::$debug_mode)){
			self::$debug_mode =SystemSettings::is_debug();
		}
		if(!isset($ext_loaded)){
			$ext_loaded = extension_loaded('memcache');
		}
		if(self::$debug_mode){
			$debuger = Debug::getInstance('cache');
			$debuger->start();
		}
		$this->type = $type;
		$this->name = $name;
		$this->data = array();
		switch($this->type){
			case self::MEMCACHE: {
				if($ext_loaded&&!self::$memcache_connection){
					self::$memcache_connection = memcache_connect('localhost',11211);
				}
				if(!self::$memcache_connection){
					$this->type = self::FILE;
				}else{
					if(!is_array($this->collection = memcache_get(self::$memcache_connection,$this->name))){
						$this->collection = array();
					}
				}
				break;
			}
			case self::FILE: {
				$this->resource_name = sprintf('%s/.cache.%s.mem',DIR_WBS_TEMP,$this->name);
				checkPath(DIR_WBS_TEMP);

				if(file_exists($this->resource_name)&&($fp = fopen($this->resource_name,'r'))){
					$content = fread($fp,filesize($this->resource_name));
					if(is_array($content = unserialize($content))){
						$this->data = $content;
					}else{
						//TODO check it!!!
						$this->type = self::MEMORY;
					}
					fclose($fp);
				}elseif(is_writable(dirname($this->resource_name))){

				}else{
					$this->type = self::MEMORY;
				}
				break;

			}
				
			case self::PHP: {
				$this->resource_name = sprintf('%s/.cache.%s.php',DIR_WBS_TEMP,$this->name);
				checkPath(DIR_WBS_TEMP);

				if(file_exists($this->resource_name)){
					$this->data = include($this->resource_name);
				}elseif(is_writable(dirname($this->resource_name))){
					$this->data = array();
				}else{
					$this->type = self::MEMORY;
				}
				break;
			}
				
			case self::SESSION:{
				if(!session_id()){//check is session started //optional
					throw new Exception("Session not started");
				}

				if(!isset($_SESSION[self::DATA_PREFIX])){
					$_SESSION[self::DATA_PREFIX] = array();
				}
				if(!isset($_SESSION[self::DATA_PREFIX][self::DATA_NAME])){
					$_SESSION[self::DATA_PREFIX][self::DATA_NAME] = array();
				}
				if(!isset($_SESSION[self::DATA_PREFIX][self::DATA_NAME][$name])){
					$_SESSION[self::DATA_PREFIX][self::DATA_NAME][$name] = array();
				}
				$this->data = &$_SESSION[self::DATA_PREFIX][self::DATA_NAME][$name];
				break;
			}
			case self::COOKIE:{
				$data = Env::Cookie(self::DATA_PREFIX.self::DATA_NAME);
				if(!is_array($data)||!isset($data[$this->name])||!is_array($data[$this->name])){
					$this->data = array();
				}else{
					$this->data = $data[$this->name];
				}
				break;
			}
			case self::MEMORY:
			default: {
				$this->data = array();
			}
		}
		if(self::$debug_mode){
			$debuger->end(array('constructor',$name,$this->type),'cache constructor');
		}
	}

	function __destruct()
	{
		$this->store();
		if(!count(self::$cache_stack)&&self::$memcache_connection){
			memcache_close(self::$memcache_connection);
		}
		unset($this->data);
		/*if(isset(self::$cache_stack[$this->name])){
			unset(self::$cache_stack[$this->name]);
			}*/
	}

	/**
	 * Store cache data in the selected storage
	 * @return void
	 */
	public function store()
	{
		switch($this->type){
			case self::FILE: {
				if($this->updated){
					if($fp = fopen($this->resource_name,'w')){
						fwrite($fp,serialize($this->data));
						fclose($fp);
						$this->updated = false;
					}
				}
				break;
			}
			case self::PHP: {
				if($this->updated){
					if($fp = fopen($this->resource_name,'w')){
						fwrite($fp,"<?php\n\nreturn\tarray(");
						foreach($this->data as $key=>$value){
							//XXX unsafe for keys with special chars
							fwrite($fp,"\n\t'{$key}'=>");
							if(is_array($value)){
								fwrite($fp,"array(\n");
								foreach($value as $value_key=>$value_value){
									fwrite($fp,"\n\t\t'{$value_key}'=>".var_export($value_value,true).",");
								}
								fwrite($fp,"\n\t),");
							}else{
								fwrite($fp,var_export($value,true).",");
							}
						}
						fwrite($fp,"\n);");
						fclose($fp);
						$this->updated = false;
					}
				}
				break;
			}
			case self::COOKIE:{
				if($this->updated){
					if(!headers_sent()){
						if(!is_array($data = Env::Cookie(self::DATA_PREFIX.self::DATA_NAME,false,array()))){
							$data = array();
						}
						$data[$this->name] = $this->data;
						Env::setCookie(self::DATA_PREFIX.self::DATA_NAME,$data);
						$this->updated = false;
					}
				}
				break;
			}
			case self::MEMCACHE: {
				memcache_set(self::$memcache_connection,$this->name,$this->collection);
				break;
			}

		}
	}

	/**
	 * Return cached value for $variable
	 * @param string $variable
	 * @param mixed|null $default
	 * @return mixed|null
	 */
	public function get($variable,$default = null)
	{
		self::$counter['get']++;
		$external = false;
		if(self::$debug_mode){
			$debuger = Debug::getInstance('cache');
			$debuger->start();
		}
		$value = $default;
		switch($this->type){
			case self::NONE: {
				break;
			}
			case self::MEMCACHE: {
				if(isset($this->data[$variable])){
					$value = $this->data[$variable];
					break;
				}
				$external = true;
				self::$counter['get_external']++;
				$value = memcache_get(self::$memcache_connection,$this->name.':'.$variable);
				$this->data[$variable] = $value;
				break;
			}
			case self::SESSION:
			case self::FILE:
			case self::PHP:
			case self::MEMORY:
			default: {
				$value = isset($this->data[$variable])?$this->data[$variable]:$value;
				break;
			}
		}
		if(self::$debug_mode){
			$debuger->end(array('cache',$external?'external':'internal',$this->name,$variable),$external?'cache request':'cache bufer');
		}
		return $value;
	}

	/**
	 * Set into cache $variable into $value
	 *
	 * @todo add expire support
	 * @param string $variable
	 * @param mixed $value
	 * @param int|null $expire expire data time (0 - never, n - n seconds); null if not applicable
	 * @return void
	 */
	public function set($variable,$value,$expire = null)
	{
		self::$counter['set']++;
		switch($this->type){
			case self::MEMCACHE: {
				memcache_set(self::$memcache_connection,$this->name.':'.$variable,$value);
				if(!isset($this->collection[$variable])){
					$this->collection[$variable] = $variable;
				}
			}
			case self::COOKIE:
			case self::FILE:
			case self::PHP:
				$this->updated = true;
			case self::MEMORY:
			case self::SESSION:
			default: {
				$this->data[$variable] = &$value;
				break;
			}
		}
	}

	/**
	 *
	 * @param string $variable
	 * @param mixed $default
	 * @return mixed
	 */
	public function pop($variable,$default = null)
	{
		$value = $this->get($variable,$default);
		$this->reset($variable);
		return $value;
	}

	/**
	 * Reset cached variable
	 * @param $variable
	 * @return void
	 */
	public function reset($variable = null)
	{
		switch($this->type){
			case self::MEMCACHE: {
				if(is_null($variable)){
					foreach($this->collection as $variable){
						memcache_delete(self::$memcache_connection,$variable);
					}
				}else{
					memcache_delete(self::$memcache_connection,$variable);
				}
			}
			case self::COOKIE:{
				//TODO: add code here
				break;
			}
			case self::PHP:
			case self::FILE:
				$this->updated = true;
			case self::MEMORY:
			case self::SESSION:
			default: {
				if(is_null($variable)){
					$this->data = array();
				}elseif(isset($this->data[$variable])){
					unset($this->data[$variable]);
				}
				break;
			}
		}
	}

	/*
	 * Check for any changes at cache via current session
	 * @return bool
	 */
	public function isUpdated()
	{
		return $this->updated;
	}
}
?>