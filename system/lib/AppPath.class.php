<?php

	define ("_ROOT_PATH", realpath(dirname(__FILE__). "/../.."));
	
	class AppPath
	{
		/**
		 * @return string
		 */
		public static function ROOT_PATH()
		{
			return _ROOT_PATH;
		}

		/**
		 * @return string
		 */
		public static function SYSTEM_PATH()
		{
			return self::ROOT_PATH() . "/system";
		}
		
		/**
		 * @return string
		 */
		public static function KERNEL_PATH()
		{
			return self::ROOT_PATH() . "/kernel";
		}
		
		/**
		 * @return string
		 */
		public static function TEMP_PATH()
		{
			return self::ROOT_PATH() . "/temp";
		}
		
		/**
		 * @return string
		 */
		public static function PUBLISHED_PATH()
		{
			return self::ROOT_PATH() . "/published";
		}
		
		/**
		 * @return string
		 */
		public static function DATA_PATH()
		{
			//return self::ROOT_PATH() . "/data";
			return Wbs::getSystemObj()->files()->getDataPath();
		}
		
		
		/**
		 * @return string
		 */
		public static function SMARTY_PATH()
		{
			return self::KERNEL_PATH() . "/includes/smarty";
		}
		
		/**
		 * @return string
		 */
		public static function APP_PATH($APP = '')
		{
			$path = self::PUBLISHED_PATH();
			if ( !empty($APP) ) {
				$path .= '/'. $APP;
			}
			return $path;
		}
	
	}

?>