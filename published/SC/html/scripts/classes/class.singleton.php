<?php
	class Singleton{
		
		/**
		 * @return Register
		 */
		static function &getInstance(){
			
			static $me;
			
			if(is_object($me)){
				
				return $me;
			}
			
			$me = new Register();
			return $me;
		}
	}
?>