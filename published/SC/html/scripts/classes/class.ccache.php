<?php
class CCache{
	
	static $cache = array();
	
	static function is_set($cache_key){

		return isset(self::$cache[$cache_key]);
	}
	
	static function get($cache_key){
		
		return isset(self::$cache[$cache_key])?self::$cache[$cache_key]:null;
	}
	
	static function set($cache_key, $data){
		
		self::$cache[$cache_key] = $data;
	}
}
?>