<?php
class LocalizationLoader {
	function loadStrings ($dirPath, $baseName, $type = null, $fullStringInfo = false, $useCache = true)
	//
	// Loads strings from localization files
	//
	//		Parameters:
	//			$dirPath - path to directory where localization files stored
	//			$baseName - base part of file name (without extension)
	//			$type - return strings which belongs to specified type
	//			$fullStringInfo - return all information about strings
	//
	//		Returns array: array( eng=>array( 1=>"String 1"... )... )
	//
	{
		$result = array();
			
		if ($fullStringInfo) {
			return $this->loadDirData ($dirPath, $baseName, $type, $fullStringInfo);
		}
		if ($this->hasActuallyCache($dirPath, $baseName) && $useCache) {
			/*require_once($dirPath . "/.cache.php");

			$kn = substr(md5($dirPath),0,2);
			$csn = "ls" . $kn;

			if (!empty($GLOBALS[$csn]))
			return $GLOBALS[$csn];*/
			$cacheFilename = $dirPath . "/.cache." . $baseName . ".php";
			$result = unserialize(file_get_contents($cacheFilename));
			if (!is_array($result))
			$result = array ();
			return $result;
		}
			
		$result = $this->makeCacheFile ($dirPath, $baseName, $type);
			

		return $result;
	}


	function hasActuallyCache ($dirPath, $baseName) {
		$cacheFilename = ".cache." . $baseName . ".php";
		if (!file_exists($dirPath . "/" . $cacheFilename) || (filesize($dirPath . "/" . $cacheFilename)===0))
		return false;
			
		if ( !($handle = opendir($dirPath)) )
		return false;
			
		$cacheTime = filemtime($dirPath . "/" . $cacheFilename);
			
		while ( false !== ($file = readdir($handle)) ) {
			if ( substr($file,0,1) == "." || $file == $cacheFilename)
			continue;

			$filename = $dirPath.'/'.$file;

			if ( !is_file($filename) )
			continue;

			if (filemtime($filename) > $cacheTime) {
				return false;
			}
		}
			
			
		return true;
	}

	function makeCacheFile ($dirPath, $baseName, $type) {
		$locData = $this->loadDirData ($dirPath, $baseName, $type);
			
		$cacheFilename = $dirPath . "/.cache." . $baseName . ".php";
		$handle = @fopen ($cacheFilename, 'w');
		if (!$handle)
		return $locData;
			
		$fstr = serialize ($locData);
		fwrite ($handle, $fstr);
			
		fclose ($handle);
		return $locData;
			
	}


	function loadDirData ($dirPath, $baseName, $type, $fullStringInfo = false) {
		$result = array();
		if ( !($handle = opendir($dirPath)) )
		return $result;

		while ( false !== ($file = readdir($handle)) ) {
			if ( substr($file,0,1) == "." || $file == ".cache." . $baseName . ".php"){
				continue;
			}

			if (substr($file, 0,strlen($baseName))!= $baseName){
				continue;
			}

			if ( !is_file($filename = $dirPath.'/'.$file) ){
				continue;
			}

			$fileInfo = pathinfo( $filename );
			if ( !isset($fileInfo["extension"]) ){
				continue;
			}

			$extension = $fileInfo["extension"];
			$basePart = substr( $file, 0, strlen($file)-strlen($extension)-1 );
			if ( $basePart != $baseName ){
				continue;
			}

			$fileContent = file( $filename );
			for ( $i = 0; $i < count($fileContent); $i++ ) {
				if ( !strlen( trim($fileContent[$i]) ) ){
					continue;
				}

				$lineData = explode( "\t", $fileContent[$i] );
				if ( !is_null($type) &&( $type != $lineData[1] )){
					continue;
				}

				if ( isset( $lineData[3] ) ){
					$lineData[3] = str_replace(array("\r","\n"),"",$lineData[3]);
					$strValue = rtrim($lineData[3]);
				}else{
					$lineData[3] = str_replace(array("\r","\n"),"",$lineData[1]);
					$lineData[1] = $baseName;
					$strValue = trim($lineData[3]);
				}

				if ( empty($fullStringInfo) ){
					$result[$extension][$lineData[0]] = str_replace("\\n", "\n", isset( $strValue ) ? $strValue : "" );
				}else{
					$result[$extension][$lineData[0]] = $lineData;
				}
			}
		}
		closedir( $handle );

		if (!$fullStringInfo&&is_array($result)&&array_key_exists( LANG_ENG, $result ) ) {
			$engStrings = $result[LANG_ENG];
			$engKeys = array_keys($engStrings);

			foreach ( $result as $locLang=>$strings ) {
				if ( $locLang == LANG_ENG )
				continue;

				$langKeys = array_keys($strings);

				$engDiff = array_diff( $engKeys, $langKeys );

				foreach ( $engDiff as $key )
				$strings[$key] = $engStrings[$key];

				$result[$locLang] = $strings;
			}
		}
		return $result;
	}
}

?>