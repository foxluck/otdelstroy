<?php
	define ("LANG_ENG", "eng");
	function translate($fullStringName) {
		if (strstr($fullStringName, "->") !== false) {
			list($section, $stringName) = explode("->", $fullStringName);
		} else {
			$section = 'aa';
			$stringName = $fullStringName;
		}
		return waLocale::getStr($section, $stringName);
	}
	
	class waLocale {
		private static $language;
		private static $locLoader;
		private static $strings;
		private static $engStrings;
		private static $initialized = false;
		private static $loadedFiles = array ();
		
		public static function init($language) {
			self::$initialized = true;
			self::$language = $language == 'deu' ? 'gem' : $language;
			self::$locLoader = new LocalizationLoader();
		}
		
		public static function loadAppFile($app) {
			
		}
		
		private static function checkInitialized() {
			if (!self::$initialized)
				throw new RuntimeException("Localization is not initialized");
		}
		
		public static function loadFile($dir, $filename) {
			self::checkInitialized();
			
			$fullFilename = $dir . $filename;
			
			$section = $filename;
			if (empty(self::$loadedFiles[$fullFilename])) {
				$filePath = $dir . $filename;
				
				$result = self::$locLoader->loadStrings($dir, $filename);
				self::$strings[$section] = isset($result[self::$language]) ? $result[self::$language] : array();
				if (self::$language != LANG_ENG)
					self::$engStrings[$section] = $result[LANG_ENG];
				
				self::$loadedFiles[$fullFilename] = true;
			}
		}
		
		public static function getSectionStrings($section) {
			self::checkInitialized();
			
			return self::$strings[$section] ? self::$strings[$section] : self::$engStrings[$section];
		}
		
		public static function getStr($section, $strName) {
			self::checkInitialized();
			
			$string = $section."->" . $strName;
			if (isset(self::$strings[$section][$strName]))
				$string = self::$strings[$section][$strName];
			elseif (self::$language != LANG_ENG && isset(self::$engStrings[$section][$strName]))
				$string = self::$engStrings[$section][$strName];
			return $string;
		}
		
		public static function loadCommonTemplatesFile() {
			self::loadFile(Wbs::getPublishedPath("common/templates/localization", true), "template_common");
		}
	}
?>
