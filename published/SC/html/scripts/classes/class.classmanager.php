<?php
	class ClassManager{
		
		/**
		 * Include class file
		 *
		 * @param string $ClassName
		 */
		static function includeClass($ClassName){
			
			if(!class_exists(strtolower($ClassName),false)){
				if(!file_exists(DIR_CLASSES.'/class.'.strtolower($ClassName).'.php')){
					return false;
					die('Class '.$ClassName.' doesnt exist!');
				}
				include_once(DIR_CLASSES.'/class.'.strtolower($ClassName).'.php');
				if(isset($_GET['debug'])&&($_GET['debug'] == 'files')){
					$backtrace = debug_backtrace();
					$backtrace = $backtrace[2];
					$backtrace = str_replace(realpath(DIR_ROOT).DIRECTORY_SEPARATOR,'',$backtrace['file']).':'.$backtrace['line'];
					print "<pre>\ninclude {$ClassName}\n{$backtrace}\n</pre>";
				}
				
			}
			return true;
		}
		
		/**
		 * Get reference to new object of class
		 *
		 * @param string $ClassName
		 * @return mixed
		 */
		static function &getInstance($ClassName){
			
			$Object = null;
			if(ClassManager::includeClass($ClassName))
				$Object = new $ClassName();
			return $Object;
		}
	}
?>