<?php
	
	/**
	*	Kernel class for manage code including.
	**/
	class Kernel {
		
		/**
			@static
			@param string name of package for include
		**/
		static function incPackage($packageName) {
			include_once(SYSTEM_PATH . "/packages/" . $packageName . "/_package.php");
		}
		
		/**
			@static
			@param string name of package for include
		**/
		static function incPackageFile($packageName, $fileName) {
			include_once(SYSTEM_PATH . "/packages/" . $packageName . "/" . $fileName . ".php");
		}
		
		static function isHosted () {
			return file_exists(WBS_ROOT_PATH . "/kernel/hosting_plans.php");
		}
		
		static function incAppFile($app, $file) {
			include_once(WBS_ROOT_PATH . "/published/" . $app . "/2.0/${file}.php");
		}
	}
?>