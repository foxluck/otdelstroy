<?php
class htmlCodesManager{

	/**
	 * Return assoc array of all html codes
	 *
	 * @return array - ('&lt;code_key&gt;' =&gt; '&lt;code_info&gt;')
	 */
	protected static $code_cache = null;


	static function readCache($theme_id)
	{
		static $inited = false;
		if(!$inited){
			$inited = true;
			self::$code_cache = array();
			$cache_path = DIR_WBS_TEMP.'/.cache.htmlcodes.'.$theme_id;
			if(file_exists($cache_path)&&($filesize = filesize($cache_path))&&($fp = fopen($cache_path,'r'))){
				$data = fread($fp,$filesize);
				if($data&&($data = unserialize(get_magic_quotes_gpc()?stripslashes($data):$data))){
					self::$code_cache = $data;
				}
				fclose($fp);
			}
		}
	}

	static function resetCache($theme_id = null)
	{
		if($theme_id){
			$cache_path = DIR_WBS_TEMP.'/.cache.htmlcodes.'.$theme_id;
			if(file_exists($cache_path)){
				unlink($cache_path);
			}

		}else{
			
		}
	}

	static function updateCache($theme_id)
	{
		$cache_path = DIR_WBS_TEMP.'/.cache.htmlcodes.'.$theme_id;
		if(self::$code_cache&&($fp = fopen($cache_path,'w'))){
			fwrite($fp,serialize(self::$code_cache));
			fclose($fp);
		}
	}

	function getCodesList(){

		$codes = array();

		$dbq = "SELECT `key`,`title` FROM ".HTMLCODES_TABLE." ORDER BY `title`";
		$dbres = db_query($dbq);
		while ($row = db_fetch_assoc($dbres)){

			$codes[] = $row;
		}

		return $codes;
	}

	static function getCodeInfo($code_key,$theme_id = null){
		if(isset($theme_id)){
			self::readCache($theme_id);
			if(isset(self::$code_cache[$code_key])){
				return self::$code_cache[$code_key];
			}
		}

		$dbq = "SELECT * FROM ".HTMLCODES_TABLE." WHERE `key`=?";
		$dbres = db_phquery($dbq, $code_key);
		$code_info = db_fetch_assoc($dbres);
		if(isset($theme_id)){
			self::$code_cache[$code_key] = $code_info;
			self::updateCache($theme_id);
		}

		return $code_info;
	}

	function addCode($params,$theme_id = null){

		$dbq = "INSERT IGNORE ".HTMLCODES_TABLE." (`key`, `title`, `code`) VALUES (?key, ?title, ?code)";
		db_phquery($dbq, $params);
		self::resetCache($theme_id);
	}

	function updateCode($key, $params,$theme_id = null){
		$params['orig_key'] = $key;
		$dbq = "UPDATE ".HTMLCODES_TABLE." SET `key`=?key, `title`=?title, `code`=?code WHERE `key`=?orig_key";
		$res = db_phquery($dbq, $params);
		if(($res['resource'] === true) &&(db_affected_rows() === 0)){
			$params['key'] = $key;
			self::addCode($params,$theme_id);
		}else{
			self::resetCache($theme_id);	
		}
	}

	function deleteCode($key,$theme_id = null){

		db_phquery("DELETE FROM ?#HTMLCODES_TABLE WHERE `key`=?", $key);
		self::resetCache($theme_id);
	}

	function renderCodeKey(){

		$max = 1;
		do{
			$_key = rand_name(8);
			$code_info = htmlCodesManager::getCodeInfo($_key);
		}while (isset($code_info['key']) && 100>$max++);

		return $_key;
	}
}
?>